<?php
declare(strict_types=1);

namespace Kernel\Database\Exception;

use Kernel\Component\Make;

class Resolver
{

    use Make;


    /**
     * @var \Throwable
     */
    private \Throwable $exception;

    /**
     * @var array|\Closure[]
     */
    private array $errorCodeHandlers = [];


    /**
     * 字段的中文字典
     * @var array
     */
    private array $dict = [
        'price' => "价格",
        'drift_value' => '浮动值'
    ];


    /**
     * @param \Throwable $exception
     */
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
        $this->errorCodeHandlers = [
            '22003' => function ($exception) {
                if (str_contains($exception->getMessage(), 'Out of range value for column')) {
                    preg_match("/for column '(.*?)' at/", $exception->getMessage(), $matches);
                    $columnName = $matches[1] ?? '';
                    return "「 {$this->getDict($columnName)} 」超出了数值的范围，请降低该字段范围重新尝试";
                }
                return '遇到了未知的数值范围错误';
            },
            'HY000' => function ($exception) {
                if (str_contains($exception->getMessage(), 'Incorrect decimal value')) {
                    preg_match("/for column '(.*?)' at/", $exception->getMessage(), $matches);
                    $columnName = $matches[1] ?? '';
                    return "「 {$this->getDict($columnName)} 」" . '的值不是一个有效的数值';
                }

                if (str_contains($exception->getMessage(), "doesn't have a default value")) {
                    preg_match("/Field '(.*?)' doesn't/", $exception->getMessage(), $matches);
                    $columnName = $matches[1] ?? '';
                    return "「 {$this->getDict($columnName)} 」" . '必须设置一个值';
                }
                return '遇到了未知的一般错误';
            },
            '1054' => function ($exception) {
                if (str_contains($exception->getMessage(), 'Unknown column')) {
                    preg_match("/Unknown column '(.*?)' in/", $exception->getMessage(), $matches);
                    $columnName = $matches[1] ?? '';
                    return "「 {$this->getDict($columnName)} 」" . '该字段不存在，请删除该字段重新尝试';
                }
                return '遇到了未知的列找不到的错误';
            },
            '42S22' => function ($exception) {
                if (str_contains($exception->getMessage(), 'Unknown column')) {
                    preg_match("/Unknown column '(.*?)' in/", $exception->getMessage(), $matches);
                    $columnName = $matches[1] ?? '';
                    return "「 {$this->getDict($columnName)} 」" . '该字段不存在，请删除该字段重新尝试';
                }
                return '遇到了未知的列找不到的错误';
            },
            '23000' => function ($exception) {
                if (str_contains($exception->getMessage(), 'Duplicate entry')) {
                    preg_match("/Duplicate entry '(.*?)' for key/", $exception->getMessage(), $matches);
                    $columnName = $matches[1] ?? '';
                    return "「 {$this->getDict($columnName)} 」" . '该值已存在';
                }
                return '遇到了未知的重复键错误';
            },
        ];
    }

    /**
     * @param string $column
     * @return string
     */
    private function getDict(string $column): string
    {
        if (isset($this->dict[$column])) {
            return $this->dict[$column];
        }
        return $column;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        $errorCode = $this->exception->getCode();
        if (array_key_exists($errorCode, $this->errorCodeHandlers)) {
            $handler = $this->errorCodeHandlers[$errorCode];
            return $handler($this->exception);
        }
        return '数据库发生错误：' . $this->exception->getMessage();
    }


}