<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Assets;
use Kernel\Plugin\Composer;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Install;
use Kernel\Plugin\Sync;
use Kernel\Plugin\Usr;
use Kernel\Update\Database;
use Kernel\Util\Directory;
use Kernel\Util\File;
use Kernel\Util\Zip;
use Symfony\Component\Finder\Finder;

class Store implements \App\Service\Store\Store
{

    #[Inject]
    private \App\Service\Store\Http $http;

    /**
     * @param array $post
     * @param Authentication $authentication
     * @return array
     */
    public function list(array $post, Authentication $authentication): array
    {
        return $this->http->request("/store/list", $post, $authentication)->data;
    }

    /**
     * @param string $key
     * @param string $env
     * @param Authentication $authentication
     * @return void
     * @throws JSONException
     * @throws ServiceException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function install(string $key, string $env, Authentication $authentication): void
    {
        $path = BASE_PATH . "runtime/download/store/{$key}.zip";
        $pluginPath = BASE_PATH . "{$env}/{$key}";

        if (is_dir($pluginPath)) {
            throw new JSONException("应用已存在");
        }

        if (!$this->http->download("/store/installation/package", $path, $authentication, "POST", ["key" => $key])) {
            throw new JSONException("应用下载失败，请稍后再试");
        }


        //下载完成，解压插件
        if (!Zip::state()) {
            File::remove($path);
            throw new ServiceException("zip解压不可用");
        }

        if (!Zip::unzip($path, $pluginPath)) {
            File::remove($path);
            throw new ServiceException("zip解压失败");
        }

        File::remove($path);
        $userId = Usr::inst()->envToUsr($env);
        if ($userId != "*") {
            Install::inst()->createEnvironment((int)$userId, $key);
        }

        \Kernel\Plugin\Plugin::inst()->database("install", $key, $env);
        Assets::inst()->add("{$env}/{$key}/Wiki");


        \Kernel\Plugin\Plugin::inst()->instantHook($key, $env, Point::APP_INSTALL);
    }

    /**
     * @param string $key
     * @param Authentication $authentication
     * @return int
     * @throws ServiceException
     */
    public function getPluginType(string $key, Authentication $authentication): int
    {
        $http = $this->http->request("/store/plugin/type", ["plugin_key" => $key], $authentication);
        if ($http->code != 200) {
            throw new ServiceException("获取插件类型失败");
        }
        return (int)$http->data["type"];
    }

    /**
     * @param string $key
     * @param string $env
     * @return void
     * @throws JSONException
     * @throws RuntimeException
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function uninstall(string $key, string $env): void
    {
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($key, $env);
        if (!$plugin) {
            throw new ServiceException("插件异常，无法卸载");
        }

        \Kernel\Plugin\Plugin::inst()->instantHook($key, $env, Point::APP_UNINSTALL_BEFORE);

        $state = \Kernel\Plugin\Plugin::inst()->getState($key, $env);
        if ($state['run'] != 0) {
            \Kernel\Plugin\Plugin::inst()->stop($key, $env);
        }
        //删除依赖
        Composer::inst()->uninstall($key, $env);
        //删除插件
        \Kernel\Plugin\Plugin::inst()->database("uninstall", $key, $env);
        Assets::inst()->del("{$plugin->path}/Wiki");
        Directory::delete($plugin->path);

        \Kernel\Plugin\Plugin::inst()->instantHook($key, $env, Point::APP_UNINSTALL_AFTER);
    }


    /**
     * @param int $gift
     * @param Authentication $authentication
     * @return array
     */
    public function getGroup(int $gift, Authentication $authentication): array
    {
        return $this->http->request(url: "/store/group?gift=" . $gift, authentication: $authentication)->data;
    }

    /**
     * @param int $type
     * @param int $itemId
     * @param int $subscription
     * @param int $subscriptionId
     * @param int $payId
     * @param bool $balance
     * @param string $syncUrl
     * @param int $isGift
     * @param string $giftUsername
     * @param Authentication $authentication
     * @param int $device
     * @return array
     * @throws ServiceException
     */
    public function purchase(int $type, int $itemId, int $subscription, int $subscriptionId, int $payId, bool $balance, string $syncUrl, int $isGift, string $giftUsername, Authentication $authentication, int $device = 0): array
    {
        $http = $this->http->request("/store/purchase", [
            "type" => $type,
            "item_id" => $itemId,
            "subscription" => $subscription,
            "pay_id" => $payId,
            "balance" => $balance,
            "sync_url" => $syncUrl,
            "subscription_id" => $subscriptionId,
            "is_gift" => $isGift,
            "gift_username" => $giftUsername,
            "device" => $device
        ], $authentication);

        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "下单失败");
        }

        return $http->data;
    }

    /**
     * @param string $amount
     * @param int $payId
     * @param string $syncUrl
     * @param Authentication $authentication
     * @param int $device
     * @return array
     * @throws ServiceException
     */
    public function recharge(string $amount, int $payId, string $syncUrl, Authentication $authentication, int $device = 0): array
    {
        $http = $this->http->request("/store/recharge", [
            "amount" => $amount,
            "pay_id" => $payId,
            "sync_url" => $syncUrl,
            "device" => $device
        ], $authentication);

        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "下单失败");
        }

        return $http->data;
    }

    /**
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function powers(Authentication $authentication): array
    {
        $http = $this->http->request(url: "/store/powers", authentication: $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取我的应用失败");
        }
        return $http->data;
    }


    /**
     * @param int $itemId
     * @param bool $isGroup
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function powerDetail(int $itemId, bool $isGroup, Authentication $authentication): array
    {
        $http = $this->http->request(url: "/store/power/detail", data: [
            "item_id" => $itemId,
            "is_group" => $isGroup
        ], authentication: $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取订阅项目失败");
        }
        return $http->data;
    }

    /**
     * @param int $type
     * @param int $itemId
     * @param int $subscription
     * @param Authentication $authentication
     * @return bool
     */
    public function powerRenewal(int $type, int $itemId, int $subscription, Authentication $authentication): bool
    {
        try {
            $http = $this->http->request(url: "/store/power/renewal", data: [
                "type" => $type,
                "item_id" => $itemId,
                "subscription" => $subscription,
            ], authentication: $authentication);
            if ($http->code != 200) {
                return false;
            }

            return (bool)$http->data['status'];
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param int $type
     * @param int $itemId
     * @param Authentication $authentication
     * @return bool
     */
    public function powerBind(int $type, int $itemId, Authentication $authentication): bool
    {
        try {
            $http = $this->http->request(url: "/store/power/renewal/bind", data: [
                "type" => $type,
                "item_id" => $itemId
            ], authentication: $authentication);
            if ($http->code != 200) {
                return false;
            }
            return (bool)$http->data['status'];
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param int $type
     * @param int $itemId
     * @param Authentication $authentication
     * @return bool
     */
    public function openPowerAutoRenewal(int $type, int $itemId, Authentication $authentication): bool
    {
        try {
            $http = $this->http->request(url: "/store/power/renewal/auto", data: [
                "type" => $type,
                "item_id" => $itemId,
            ], authentication: $authentication);
            if ($http->code != 200) {
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param array $users
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function getSubPowers(array $users, Authentication $authentication): array
    {
        $http = $this->http->request(url: "/store/power/sub/powers", data: [
            "users" => $users,
        ], authentication: $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取子站授权插件失败");
        }
        return $http->data;
    }

    /**
     * @param int $userId
     * @param string $expireTime
     * @param int $status
     * @param Authentication $authentication
     * @return bool
     */
    public function setSubPower(int $userId, string $expireTime, int $status, Authentication $authentication): bool
    {
        try {
            $http = $this->http->request(url: "/store/power/sub/power/set", data: [
                "user_id" => $userId,
                "expire_time" => $expireTime,
                "status" => $status
            ], authentication: $authentication);
            if ($http->code != 200) {
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }


    /**
     * @param int $itemId
     * @param Authentication $authentication
     * @return bool
     */
    public function openSubFree(int $itemId, Authentication $authentication): bool
    {
        try {
            $http = $this->http->request(url: "/store/power/sub/free", data: [
                "item_id" => $itemId,
            ], authentication: $authentication);
            if ($http->code != 200) {
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param array $plugins
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function getPluginVersions(array $plugins, Authentication $authentication): array
    {
        $http = $this->http->request(url: "/store/plugin/versions", data: [
            "plugins" => $plugins
        ], authentication: $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取插件版本号失败");
        }
        return $http->data;
    }

    /**
     * @param string $key
     * @param Authentication $authentication
     * @return array
     * @throws ServiceException
     */
    public function getPluginVersionList(string $key, Authentication $authentication): array
    {
        $http = $this->http->request(url: "/store/plugin/version/updates", data: [
            "key" => $key
        ], authentication: $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取插件版本列表失败");
        }
        return $http->data;
    }

    /**
     * @param string $key
     * @param string $env
     * @param Authentication $authentication
     * @return void
     * @throws JSONException
     * @throws RuntimeException
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function pluginVersionUpdate(string $key, string $env, Authentication $authentication): void
    {
        if (Sync::inst()->has($key, $env)) {
            throw new JSONException("该插件正在同步中，无法对其进行更新");
        }

        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($key, $env);
        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }

        \Kernel\Plugin\Plugin::inst()->instantHook($key, $env, Point::APP_UPGRADE_BEFORE);
        $isRunning = $plugin->state['run'] == 1;

        \Kernel\Plugin\Plugin::inst()->clearLog($key, $env);

        if ($isRunning) {
            $plugin->log("正在停止插件..", true);
            \Kernel\Plugin\Plugin::inst()->stop($key, $env);
        }

        $plugin->log("开始检查更新..", true);

        $basePath = rtrim($plugin->path, "/");

        $versionList = array_reverse($this->getPluginVersionList($key, $authentication));
        $localVersion = $plugin->info['version'];
        $isUpdate = false;

        try {
            foreach ($versionList as $v) {
                $cloudVersion = $v["version"];
                //如果云端版本大于本地版本则开始更新
                if (version_compare($cloudVersion, $localVersion, '>')) {
                    $versionPath = $basePath . "/Download/Update/{$cloudVersion}";
                    $plugin->log("检查到新版本：{$cloudVersion}，开始下载..", true);

                    if (!$this->http->download("/store/plugin/version/package", $versionPath . ".zip", $authentication, "POST", ["key" => $key, "version_id" => $v['id']])) {
                        $plugin->log("更新包下载失败，请检查写入权限：" . $versionPath, true);
                        throw new ServiceException("更新包下载失败");
                    }
                    $plugin->log("下载完成，开始解压：{$versionPath}.zip -> {$versionPath}", true);

                    //解压压缩包
                    if (!Zip::state()) {
                        $plugin->log("缺少php扩展：zip，请安装此扩展后再尝试更新", true);
                        throw new ServiceException("zip解压不可用");
                    }

                    if (!Zip::unzip($versionPath . ".zip", $versionPath)) {
                        $plugin->log("zip解压失败，请检查写入权限：" . $versionPath, true);
                        throw new ServiceException("zip解压失败");
                    }

                    $plugin->log("解压完成，开始升级..", true);

                    //升级数据库
                    $database = $versionPath . "/Database.php";
                    if (file_exists($database)) {
                        $plugin->log("检测到数据库升级文件，开始升级..", true);
                        require $database;

                        $class = "\\{$key}\\Update\\Version" . str_replace(".", "", $cloudVersion) . "\\Database";
                        if (!class_exists($class)) {
                            $plugin->log("数据库升级文件已损坏：" . $class, true);
                            throw new ServiceException("数据库升级文件已损坏");
                        }

                        $obj = new $class();

                        if ($obj instanceof Database) {
                            Di::inst()->inject($obj); //升级数据库文件支持依赖注入
                            $obj->handle();
                            $plugin->log("数据库升级完成", true);
                        } else {
                            $plugin->log("数据库升级文件不合法：" . $class, true);
                            throw new ServiceException("数据库升级文件不合法");
                        }
                    }

                    $plugin->log("开始升级[" . $localVersion . "]插件文件..", true);
                    $backupPath = $basePath . "/Download/Backup/{$cloudVersion}";
                    $finder = Finder::create()->files()->in($versionPath);
                    foreach ($finder as $file) {
                        $srcFile = $basePath . "/" . $file->getRelativePathname();
                        $dstFile = $backupPath . "/" . $file->getRelativePathname();
                        if (file_exists($srcFile)) {
                            if (File::copy($srcFile, $dstFile)) {
                                $plugin->log($srcFile . " -> " . $dstFile . " 已备份", true);
                            } else {
                                $plugin->log($srcFile . " -> " . $dstFile . " 备份写入失败，请检查写入权限", true);
                                throw new ServiceException("备份失败");
                            }
                        }

                        //升级主程序文件
                        if (File::copy($file->getRealPath(), $srcFile)) {
                            $plugin->log($srcFile . " 升级成功", true);
                        } else {
                            $plugin->log($dstFile . " 升级失败，请检查写入权限", true);
                            $backupFinder = Finder::create()->files()->in($backupPath);
                            foreach ($backupFinder as $backupFile) {
                                $dstFile = $basePath . "/" . $backupFile->getRelativePathname();
                                if (File::copy($backupFile->getRealPath(), $dstFile)) {
                                    $plugin->log($dstFile . " 已回滚", true);
                                } else {
                                    $plugin->log($dstFile . " 回滚失败，请检查写入权限", true);
                                }
                            }
                            throw new ServiceException("升级失败");
                        }
                    }

                    $plugin->log("插件版本已从[" . $localVersion . "]升级到[" . $cloudVersion . "]", true);
                    $localVersion = $cloudVersion;
                    $isUpdate = true;
                }
            }

            if ($isUpdate) {
                $plugin->log("开始清理缓存..", true);
                $userId = Usr::inst()->envToUsr($env);
                if ($userId != "*") {
                    Install::inst()->createEnvironment((int)$userId, $key);
                }
                Directory::delete(BASE_PATH . "/runtime/view");
                \Kernel\Plugin\Plugin::inst()->setSystemConfig($key, $env, ["update" => 0]);
                $plugin->log("版本升级成功", true);
            }

            if ($isRunning) {
                $plugin->log("正在启动插件..", true);
                \Kernel\Plugin\Plugin::inst()->start($key, $env);
            }
        } catch (\Throwable $e) {
            $plugin->log((string)$e->getMessage(), true);
            throw new ServiceException("更新失败，请查看插件日志");
        }

        if ($isUpdate) {
            \Kernel\Plugin\Plugin::inst()->instantHook($key, $env, Point::APP_UPGRADE_AFTER);
        }
    }
}