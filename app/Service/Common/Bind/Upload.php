<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use Kernel\Util\Date;
use Kernel\Util\File;

class Upload implements \App\Service\Common\Upload
{

    /**
     * @param string $path
     * @param string $type
     * @param int|null $userId
     * @return void
     */
    public function add(string $path, string $type, ?int $userId = null): void
    {
        if (!is_file(BASE_PATH . $path)) {
            return;
        }
        $upload = new \App\Model\Upload();
        $upload->hash = md5_file(BASE_PATH . $path);
        $upload->type = $type;
        $upload->path = $path;
        $upload->create_time = Date::current();
        $userId && ($upload->user_id = $userId);
        $upload->save();
    }


    /**
     * @param string $hash
     * @return string|null
     */
    public function get(string $hash): ?string
    {
        return (\App\Model\Upload::query()->where("hash", $hash)->first())?->path;
    }

    /**
     * @param string $path
     * @return void
     */
    public function remove(string $path): void
    {
        if (!is_file(BASE_PATH . $path)) {
            return;
        }

        $baseImagePathInfo = pathinfo($path);
        $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];


        $hash = md5_file(BASE_PATH . $path);
        \App\Model\Upload::query()->where("hash", $hash)->delete(); //删除数据库
        File::remove(BASE_PATH . $path, BASE_PATH . $thumbPath);
    }
}