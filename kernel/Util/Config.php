<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Exception\JSONException;

class Config
{
    /**
     * @var array
     */
    private static array $config = [];

    /**
     * 将数据写入PHP配置文件。
     *
     * @param array $data 要写入的数据数组。
     * @param string $file 配置文件的路径。
     * @param bool $merge 是否合并现有配置。
     *
     * @throws \RuntimeException 如果文件无法写入。
     * @throws \InvalidArgumentException|JSONException 如果数据类型不受支持。
     */
    public static function set(array $data, string $file, bool $merge = false): void
    {
        $config = [];

        // 如果合并现有配置，需要先加载现有数据。
        if ($merge && file_exists($file)) {
            $config = require $file;
        }

        // 合并新的数据。
        $config = array_merge($config, $data);

        // 构建配置文件内容。
        $content = "<?php\n";
        $content .= "declare(strict_types=1);\n\n";
        $content .= "return [\n";
        $content .= self::arrayToString($config);
        $content .= "];\n";

        // 尝试写入文件。
        if (file_put_contents($file, $content) === false) {
            throw new \RuntimeException("无法写入配置文件: {$file}");
        }
    }

    /**
     * 递归将数组转换成字符串。
     *
     * @param array $array 要转换的数组。
     * @param int $depth 当前深度，用于格式化。
     *
     * @return string
     */
    private static function arrayToString(array $array, int $depth = 1): string
    {
        $indent = str_repeat("    ", $depth);
        $result = '';
        foreach ($array as $key => $value) {
            $key = str_replace("'", "\\'", $key);
            if (is_array($value)) {
                $subArray = self::arrayToString($value, $depth + 1);
                $result .= "{$indent}'{$key}' => [\n{$subArray}{$indent}],\n";
            } elseif (is_string($value)) {
                $value = str_replace("'", "\\'", $value);
                $result .= "{$indent}'{$key}' => '{$value}',\n";
            } elseif (is_numeric($value)) {
                $result .= "{$indent}'{$key}' => {$value},\n";
            } elseif (is_bool($value)) {
                $result .= "{$indent}'{$key}' => " . ($value ? 'true' : 'false') . ",\n";
            } else {
                throw new \InvalidArgumentException("不支持的数据类型: key={$key}");
            }
        }
        return $result;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function get(string $name): mixed
    {
        $column = Arr::getChainFirst($name);

        if (isset(self::$config[$column])) {
            return Arr::get(self::$config[$column] , Arr::getChainIgnoreFirst($name));
        }
        $file = BASE_PATH . '/config/' . $name . ".php";
        if (!file_exists($file)) {
            return [];
        }
        $data = (array)require($file);
        self::$config[$column] = $data;

        return Arr::get($data , Arr::getChainIgnoreFirst($name));
    }

}