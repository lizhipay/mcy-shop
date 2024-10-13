<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Inject;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Usr;
use Kernel\Util\Directory;
use Kernel\Util\File;
use Kernel\Util\Zip;
use Symfony\Component\Finder\Finder;

class Developer implements \App\Service\Store\Developer
{
    #[Inject]
    private \App\Service\Store\Http $http;

    public const DEVELOPER_RUNTIME = BASE_PATH . "/runtime/developer";

    /**
     * @param array $post
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function pluginList(array $post, Authentication $authentication): array
    {
        $http = $this->http->request("/store/plugin/get", $post, $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
        return $http->data;
    }


    /**
     * @param array $post
     * @param Authentication $authentication
     * @return void
     * @throws ServiceException
     */
    public function createOrUpdatePlugin(array $post, Authentication $authentication): void
    {
        $http = $this->http->request("/store/plugin/save", $post, $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
    }


    /**
     * @param string $name
     * @return array
     * @throws ServiceException
     */
    public function getPluginTrackedFiles(string $name): array
    {
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, Usr::MAIN);
        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }

        $finder = Finder::create()->files()->in($plugin->path)->exclude(".version");

        $files = [];

        $excludes = ["State", "Config/Config", "Runtime", "Config/System", ".version"];
        $source = realpath($plugin->path);
        $excludes = array_map('realpath', array_map(function ($path) use ($source) {
            return $source . '/' . $path;
        }, $excludes));

        clearstatcache();
        foreach ($finder as $file) {
            $excludeFile = false;
            foreach ($excludes as $exclude) {
                if ($exclude !== false && str_starts_with($file->getRealPath(), $exclude)) {
                    $excludeFile = true;
                    break;
                }
            }
            if ($excludeFile) {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $masterPath = $plugin->path . "/.version/master/" . $relativePath;
            $master = realpath($masterPath);

            if (!$master) {
                $files[] = $file->getRealPath();
            } else {
                if (md5_file($master) != md5_file($file->getRealPath())) {
                    $files[] = $file->getRealPath();
                }
            }
        }

        return $files;
    }

    /**
     * @throws ServiceException
     * @throws RuntimeException
     */
    public function publishPlugin(string $name, Authentication $authentication): void
    {
        //检查插件
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, Usr::MAIN);
        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }

        //检查文档是否存在
        if (!file_exists($plugin->path . "/Wiki/Readme.md") || !file_exists($plugin->path . "/Wiki/Sidebar.md")) {
            throw new ServiceException("插件文档不存在");
        }

        //将插件打包成zip
        if (!Zip::state()) {
            throw new ServiceException("PHP-ZIP扩展未开启");
        }

        if (!Zip::createZip($plugin->path, self::DEVELOPER_RUNTIME . "/{$name}.zip", ["State", "Config/Config", "Runtime", "Config/System", ".version"])) {
            throw new ServiceException("打包插件失败");
        }

        //上传插件
        $http = $this->http->upload("other", self::DEVELOPER_RUNTIME . "/{$name}.zip", $authentication);
        $data = ['key' => $name, 'release' => $http->data['url']];
        $res = $this->http->request("/store/plugin/publish", $data, $authentication);

        if ($res->code != 200) {
            throw new ServiceException($res->message ?? "插件发布失败");
        }

        //创建版本管理
        Directory::delete($plugin->path . "/.version");
        Zip::unzip(self::DEVELOPER_RUNTIME . "/{$name}.zip", $plugin->path . "/.version/master"); //创建主版本目录
        File::remove(self::DEVELOPER_RUNTIME . "/{$name}.zip");
    }

    /**
     * @param string $name
     * @param string $content
     * @param Authentication $authentication
     * @return void
     * @throws JSONException
     * @throws ServiceException
     */
    public function updatePlugin(string $name, string $content, Authentication $authentication): void
    {
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, Usr::MAIN);
        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }

        $files = $this->getPluginTrackedFiles($name);
        if (empty($files)) {
            throw new JSONException("插件没有任何变动");
        }

        if (!Zip::state()) {
            throw new ServiceException("PHP-ZIP扩展未开启");
        }

        $updatePack = self::DEVELOPER_RUNTIME . "/{$name}/update/{$plugin->info['version']}.zip";

        $zips = [];

        foreach ($files as $file) {
            $relativePath = str_replace($plugin->path . '/', '', $file);
            $zips[] = [$file, $relativePath];
        }

        if (!Zip::createZip($zips, $updatePack)) {
            throw new ServiceException("打包插件失败");
        }

        //上传插件
        $http = $this->http->upload("other", $updatePack, $authentication);
        $data = ['key' => $name, 'release' => $http->data['url'], "version" => $plugin->info['version'], "content" => $content];
        $res = $this->http->request("/store/plugin/updated", $data, $authentication);

        if ($res->code != 200) {
            throw new ServiceException($res->message ?? "版本提交失败");
        }

        //覆盖版本管理
        foreach ($files as $file) {
            $relativePath = str_replace(rtrim($plugin->path, "/") . '/', '', $file);
            $dstFile = $plugin->path . "/.version/master/" . $relativePath;
            if (!File::copy($file, $dstFile)) {
                throw new ServiceException("版本覆盖失败，文件：{$file} -> {$dstFile}");
            }
        }

        //解压版本
        Zip::unzip($updatePack, $plugin->path . "/.version/{$plugin->info['version']}"); //创建当前版本目录
        File::remove($updatePack);
    }

    /**
     * @param int $pluginId
     * @param int $page
     * @param int $limit
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function getPluginVersionList(int $pluginId, int $page, int $limit, Authentication $authentication): array
    {
        $http = $this->http->request("/store/plugin/version/list", [
            "plugin_id" => $pluginId,
            "page" => $page,
            "limit" => $limit
        ], $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
        return $http->data;
    }

    /**
     * @param int $pluginId
     * @param array $post
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function getPluginAuthorizationList(int $pluginId, array $post, Authentication $authentication): array
    {
        $http = $this->http->request("/store/plugin/authorization/list?pluginId=" . $pluginId, $post, $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
        return $http->data;
    }

    /**
     * @param array $post
     * @param Authentication $authentication
     * @return void
     * @throws ServiceException
     */
    public function addPluginAuthorization(array $post, Authentication $authentication): void
    {
        $http = $this->http->request("/store/plugin/authorization/add", $post, $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
    }

    /**
     * @param int $authId
     * @param Authentication $authentication
     * @return void
     */
    public function removePluginAuthorization(int $authId, Authentication $authentication): void
    {
        $this->http->request("/store/plugin/authorization/remove", [
            "id" => $authId
        ], $authentication);
    }
}