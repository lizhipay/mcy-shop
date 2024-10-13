<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use App\Entity\Store\UpdateLog;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Plugin;
use Kernel\Update\Database;
use Kernel\Util\Config;
use Kernel\Util\Directory;
use Kernel\Util\File;
use Kernel\Log\Log;
use Kernel\Util\Zip;
use Symfony\Component\Finder\Finder;
use function Hyperf\Support\msleep;

class Project implements \App\Service\Store\Project
{
    #[Inject]
    private \App\Service\Store\Http $http;


    /**
     * @return Authentication|null
     */
    private function getStoreAuth(): ?Authentication
    {
        return Plugin::inst()->getStoreUser("main");
    }

    /**
     * @return array
     */
    public function getNotice(): array
    {
        return $this->http->request(url: "/store/notice/list", authentication: $this->getStoreAuth())->data;
    }


    /**
     * @return array
     */
    public function getVersionLatest(): array
    {
        return $this->http->request(url: "/store/version/latest", data: [
            "version" => App::$version
        ], authentication: $this->getStoreAuth())->data;
    }


    /**
     * @return array
     */
    public function getVersionList(): array
    {
        return $this->http->request(url: "/store/version/list", data: [
            "version" => App::$version
        ], authentication: $this->getStoreAuth())->data;
    }

    /**
     * @return void
     * @throws JSONException
     * @throws ServiceException
     * @throws RuntimeException
     */
    public function update(): void
    {
        //获取全部版本
        $versionList = array_reverse($this->getVersionList());
        Log::inst()->clear("update");
        Log::inst()->update("开始检查更新..");
        $basePath = rtrim(BASE_PATH, "/");
        try {
            foreach ($versionList as $v) {
                $cloudVersion = $v["version"];

                //如果云端版本大于本地版本则开始更新
                if (version_compare($cloudVersion, App::$version, '>')) {
                    $versionPath = $basePath . "/runtime/update/{$cloudVersion}";
                    Log::inst()->update("检查到新版本：{$cloudVersion}，开始下载..");
                    if (!$this->http->download($v['url'], $versionPath . ".zip")) {
                        if (App::$cli) {
                            Log::inst()->update("更新包下载失败，请在【本程序根目录】使用该命令重启服务【mcy service.restart】，重启后继续更新即可");
                        } else {
                            Log::inst()->update("更新包下载失败，可能遭遇权限不足，请重新赋予本程序所有文件【0755】或【0777】权限，赋予权限后继续更新即可");
                        }
                        throw new ServiceException("更新包下载失败");
                    }

                    Log::inst()->update("下载完成，开始解压：{$versionPath}.zip -> {$versionPath}");

                    //解压压缩包
                    if (!Zip::state()) {
                        Log::inst()->update("缺少php扩展：zip，请安装此扩展后再尝试更新");
                        throw new ServiceException("zip解压不可用");
                    }

                    if (!Zip::unzip($versionPath . ".zip", $versionPath)) {
                        Log::inst()->update("zip解压失败，请检查写入权限：" . $versionPath);
                        throw new ServiceException("zip解压失败");
                    }

                    Log::inst()->update("解压完成，开始升级..");

                    //升级数据库
                    $database = $versionPath . "/Database.php";
                    if (file_exists($database)) {
                        Log::inst()->update("检测到数据库升级文件，开始升级..");
                        require $database;

                        $class = "\\Update\\Version" . str_replace(".", "", $cloudVersion) . "\\Database";
                        if (!class_exists($class)) {
                            Log::inst()->update("数据库升级文件已损坏：" . $class);
                            throw new ServiceException("数据库升级文件已损坏");
                        }

                        $obj = new $class();

                        if ($obj instanceof Database) {
                            Di::inst()->inject($obj); //升级数据库文件支持依赖注入
                            $obj->handle();
                            Log::inst()->update("数据库升级完成");
                        } else {
                            Log::inst()->update("数据库升级文件不合法：" . $class);
                            throw new ServiceException("数据库升级文件不合法");
                        }
                    }

                    Log::inst()->update("开始升级[" . App::$version . "]主程序文件..");
                    $filePath = $versionPath . "/file";
                    $backupPath = $versionPath . "/backup";
                    $finder = Finder::create()->files()->in($filePath);
                    foreach ($finder as $file) {
                        $srcFile = $basePath . "/" . $file->getRelativePathname();
                        $dstFile = $backupPath . "/" . $file->getRelativePathname();
                        if (file_exists($srcFile)) {
                            if (File::copy($srcFile, $dstFile)) {
                                Log::inst()->update($srcFile . " -> " . $dstFile . " 已备份");
                            } else {
                                Log::inst()->update($srcFile . " -> " . $dstFile . " 备份写入失败，请检查写入权限");
                                throw new ServiceException("备份失败");
                            }
                        }

                        //升级主程序文件
                        if (File::copy($file->getRealPath(), $srcFile)) {
                            Log::inst()->update($srcFile . " 升级成功");
                        } else {
                            Log::inst()->update($dstFile . " 升级失败，请检查写入权限");
                            $backupFinder = Finder::create()->files()->in($backupPath);
                            foreach ($backupFinder as $backupFile) {
                                $dstFile = $basePath . "/" . $backupFile->getRelativePathname();
                                if (File::copy($backupFile->getRealPath(), $dstFile)) {
                                    Log::inst()->update($dstFile . " 已回滚");
                                } else {
                                    Log::inst()->update($dstFile . " 回滚失败，请检查写入权限");
                                }
                            }
                            throw new ServiceException("升级失败");
                        }
                    }

                    Config::set(["version" => $cloudVersion], $basePath . "/config/app.php", true);
                    Log::inst()->update("版本已从[" . App::$version . "]升级到[" . $cloudVersion . "]");
                    App::$version = $cloudVersion;
                }
            }
        } catch (\Throwable $e) {
            Log::inst()->update((string)$e->getMessage());
            throw new ServiceException("更新失败，请查看日志");
        }

        Log::inst()->update("开始清理缓存..");
        //通知已经更新
        File::write($basePath . "/runtime/updated", "success");
        //模版缓存
        Directory::delete($basePath . "/runtime/view");
        Log::inst()->update("版本已升级完成");
        if (App::$cli) {
            Log::inst()->update("正在重启HTTP服务，如超过10秒没反应请手动重启服务..");
        }
    }

    /**
     * @param string $hash
     * @return UpdateLog
     */
    public function getUpdateLog(string $hash): UpdateLog
    {
        $log = BASE_PATH . '/runtime/update.log';
        if (!file_exists($log)) {
            file_put_contents($log, "");
        }
        $timeout = 0;

        //3秒超时
        while ($timeout <= 60) {
            clearstatcache();
            $md5 = md5_file($log);
            if ($md5 != $hash) {
                $hash = $md5;
                break;
            } else {
                usleep(50000);
            }

            $timeout++;
        }

        return new UpdateLog($hash, file_get_contents($log));
    }
}