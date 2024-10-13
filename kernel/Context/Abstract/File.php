<?php
declare(strict_types=1);

namespace Kernel\Context\Abstract;

use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Const\Plugin as PGI;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Str;

abstract class File implements \Kernel\Context\Interface\File
{

    /**
     * @var array
     */
    protected array $files = [];

    /**
     * 文件名称
     * @var string
     */
    protected string $fileName;

    /**
     * 文件类型
     * @var string
     */
    protected string $mime;


    /**
     * 缓存地址
     * @var string
     */
    protected string $tmp;


    /**
     * @var int
     */
    protected int $error;

    /**
     * @var int
     */
    protected int $size;


    /**
     * @var string
     */
    protected string $suffix;


    /**
     * @var string
     */
    protected string $name;

    /**
     * @throws JSONException
     */
    public function __construct()
    {
        if (!isset($this->files[$this->name])) {
            throw new JSONException("没有任何文件被上传");
        }
        $file = $this->files[$this->name];
        $this->name = $file['name'];
        $this->mime = $file['type'];
        $this->error = $file['error'];
        $this->size = $file['size'];
        $this->tmp = $file['tmp_name'];
        $ext = (array)explode(".", $this->name);
        if (count($ext) < 2) {
            throw new JSONException("您的文件后缀无法识别，请选择其他文件在进行上传");
        }
        $this->suffix = end($ext);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @return string
     */
    public function getTmp(): string
    {
        return $this->tmp;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }


    /**
     * @param string $path
     * @param array $ext
     * @param int $size 单位KB
     * @param string $dir
     * @return string
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function save(string $path, array $ext = ['jpg', 'png', 'jpeg', 'bmp', 'webp', 'ico', 'gif', 'mp4', 'zip', 'woff', 'woff2', 'ttf', 'otf'], int $size = 10240, string $dir = BASE_PATH): string
    {
        if ($this->getError() > 0) {
            throw new JSONException("文件上传失败，代码：" . $this->getError(), $this->getError());
        }

        if (!in_array(strtolower($this->getSuffix()), $ext)) {
            throw new JSONException("您上传的文件类型不支持");
        }
        if ($size < $this->getSize() / 1024) {
            throw new JSONException("您的文件过大，当前上传限制：" . $size . "KB");
        }


        $_tmpDir = $dir . $path . date("Y-m-d/", time());
        $unique = $path . date("Y-m-d/") . Str::generateRandStr(32) . "." . $this->getSuffix();

        if ($hook = Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::HTTP_UPLOAD_SAVE_READY, PGI::HOOK_TYPE_PAGE, $this, $unique, $dir)) return $hook;

        if (!is_dir($_tmpDir)) {
            mkdir($_tmpDir, 0777, true);
        }

        if (!copy(from: $this->getTmp(), to: $dir . $unique)) {
            throw new JSONException("文件上传失败，服务器出错原因：{$path} 无写入权限");
        }

        if ($hook = Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::HTTP_UPLOAD_SAVE_COMPLETE, PGI::HOOK_TYPE_PAGE, $this, $unique, $dir)) return $hook;

        return $unique;
    }

}