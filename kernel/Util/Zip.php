<?php
declare (strict_types=1);

namespace Kernel\Util;


use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Zip
{

    /**
     * @return bool
     */
    public static function state(): bool
    {
        return class_exists('ZipArchive');
    }

    /**
     * @param string|array $source
     * @param string $destination
     * @param array $excludes
     * @return bool
     */
    public static function createZip(string|array $source, string $destination, array $excludes = []): bool
    {
        if (!extension_loaded('zip')) {
            return false;
        }

        $targetPath = dirname($destination);

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZipArchive::CREATE)) {
            return false;
        }

        if (is_string($source) && is_dir($source)) {
            $source = realpath($source);
            $excludes = array_map('realpath', array_map(function ($path) use ($source) {
                return $source . '/' . $path;
            }, $excludes));
            $iterator = new RecursiveDirectoryIterator($source);
            $iterator->setFlags(FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);

                $excludeFile = false;
                foreach ($excludes as $exclude) {
                    if ($exclude !== false && str_starts_with($filePath, $exclude)) {
                        $excludeFile = true;
                        break;
                    }
                }

                if ($excludeFile) {
                    continue;
                }

                if ($file->isDir()) {
                    $zip->addEmptyDir($relativePath);
                } else {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } else if (is_array($source)) {
            foreach ($source as $item) {
                $zip->addFile($item[0], $item[1]);
            }
        }

        return $zip->close();
    }

    /**
     * @param string $file
     * @param string $targetPath
     * @return bool
     */
    public static function unzip(string $file, string $targetPath): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) === true) {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            $zip->extractTo($targetPath);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }
}