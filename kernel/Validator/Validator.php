<?php
declare (strict_types=1);

namespace Kernel\Validator;

use Kernel\Container\Di;
use SplFileObject;


class Validator
{


    /**
     * 自定义验证类型
     * @var array
     */
    protected static array $type = [];

    /**
     * 验证类型别名
     * @var array
     */
    protected array $alias = [
        '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt', '=' => 'eq', 'same' => 'eq',
    ];

    /**
     * 当前验证规则
     * @var array
     */
    protected array $rule = [];

    /**
     * 验证提示信息
     * @var array
     */
    protected array $message = [];

    /**
     * 验证字段描述
     * @var array
     */
    protected array $field = [];

    /**
     * 默认规则提示
     * @var array
     */
    protected static array $typeMsg = [
        'require' => ':attribute不能为空',
        'number' => ':attribute必须是数字',
        'integer' => ':attribute必须是整数',
        'float' => ':attribute必须是浮点数',
        'boolean' => ':attribute必须是布尔值',
        'email' => ':attribute格式不符',
        'array' => ':attribute必须是数组',
        'accepted' => ':attribute必须是yes、on或者1',
        'date' => ':attribute格式不符合',
        'file' => ':attribute不是有效的上传文件',
        'image' => ':attribute不是有效的图像文件',
        'alpha' => ':attribute只能是字母',
        'alphaNum' => ':attribute只能是字母和数字',
        'alphaDash' => ':attribute只能是字母、数字和下划线_及破折号-',
        'activeUrl' => ':attribute不是有效的域名或者IP',
        'chs' => ':attribute只能是汉字',
        'chsAlpha' => ':attribute只能是汉字、字母',
        'chsAlphaNum' => ':attribute只能是汉字、字母和数字',
        'chsDash' => ':attribute只能是汉字、字母、数字和下划线_及破折号-',
        'url' => ':attribute不是有效的URL地址',
        'ip' => ':attribute不是有效的IP地址',
        'dateFormat' => ':attribute必须使用日期格式 :rule',
        'in' => ':attribute必须在 :rule 范围内',
        'notIn' => ':attribute不能在 :rule 范围内',
        'between' => ':attribute只能在 :1 - :2 之间',
        'notBetween' => ':attribute不能在 :1 - :2 之间',
        'length' => ':attribute长度不符合要求 :rule',
        'max' => ':attribute长度不能超过 :rule',
        'min' => ':attribute长度不能小于 :rule',
        'after' => ':attribute日期不能小于 :rule',
        'before' => ':attribute日期不能超过 :rule',
        'expire' => '不在有效期内 :rule',
        'allowIp' => '不允许的IP访问',
        'denyIp' => '禁止的IP访问',
        'confirm' => ':attribute和确认字段:2不一致',
        'different' => ':attribute和比较字段:2不能相同',
        'egt' => ':attribute必须大于等于 :rule',
        'gt' => ':attribute必须大于 :rule',
        'elt' => ':attribute必须小于等于 :rule',
        'lt' => ':attribute必须小于 :rule',
        'eq' => ':attribute必须等于 :rule',
        'regex' => ':attribute不符合指定规则',
        'method' => '无效的请求类型',
        'fileSize' => '上传文件大小不符',
        'fileExt' => '上传文件后缀不符',
        'fileMime' => '上传文件类型不符',
        'password' => ':attribute应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串',
        'notZero' => ':attribute必须大于0'
    ];

    /**
     * 当前验证场景
     * @var array|null
     */
    protected ?array $currentScene = null;

    /**
     * 内置正则验证规则
     * @var array
     */
    protected array $regex = [
        'alpha' => '/^[A-Za-z]+$/',
        'alphaNum' => '/^[A-Za-z0-9]+$/',
        'alphaDash' => '/^[A-Za-z0-9\-\_]+$/',
        'chs' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'chsAlpha' => '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',
        'chsAlphaNum' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u',
        'chsDash' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u',
        'mobile' => '/^1[3-9][0-9]\d{8}$/',
        'idCard' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$)/',
        'zip' => '/\d{6}/',
    ];

    /**
     * Filter_var 规则
     * @var array
     */
    protected array $filter = [
        /*        'email' => FILTER_VALIDATE_EMAIL,*/
        'ip' => [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6],
        'integer' => FILTER_VALIDATE_INT,
        'url' => FILTER_VALIDATE_URL,
        'macAddr' => FILTER_VALIDATE_MAC,
        'float' => FILTER_VALIDATE_FLOAT,
        'domain' => [FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME]
    ];

    /**
     * 验证场景定义
     * @var array
     */
    protected array $scene = [];

    /**
     * 验证失败错误信息
     * @var array
     */
    protected mixed $error = null;

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batch = false;

    /**
     * 场景需要验证的规则
     * @var array
     */
    protected array $only = [];

    /**
     * 场景需要移除的验证规则
     * @var array
     */
    protected array $remove = [];

    /**
     * 场景需要追加的验证规则
     * @var array
     */
    protected array $append = [];

    /**
     * 架构函数
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $field 验证字段描述信息
     */
    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->rule = $rules + $this->rule;
        $this->message = array_merge($this->message, $message);
        $this->field = array_merge($this->field, $field);
    }

    /**
     * 创建一个验证器类
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $field 验证字段描述信息
     */
    public static function make($rules = [], $message = [], $field = [])
    {
        return new self($rules, $message, $field);
    }

    /**
     * 添加字段验证规则
     * @access protected
     * @param string|array $name 字段名称或者规则数组
     * @param mixed $rule 验证规则或者字段描述信息
     * @return $this
     */
    public function rule($name, $rule = '')
    {
        if (is_array($name)) {
            $this->rule = $name + $this->rule;
            if (is_array($rule)) {
                $this->field = array_merge($this->field, $rule);
            }
        } else {
            $this->rule[$name] = $rule;
        }

        return $this;
    }

    /**
     * 注册扩展验证（类型）规则
     * @access public
     * @param string $type 验证规则类型
     * @param mixed $callback callback方法(或闭包)
     * @return void
     */
    public static function extend($type, $callback = null)
    {
        if (is_array($type)) {
            self::$type = array_merge(self::$type, $type);
        } else {
            self::$type[$type] = $callback;
        }
    }

    /**
     * 设置验证规则的默认提示信息
     * @access public
     * @param string|array $type 验证规则类型名称或者数组
     * @param string $msg 验证提示信息
     * @return void
     */
    public static function setTypeMsg($type, $msg = null)
    {
        if (is_array($type)) {
            self::$typeMsg = array_merge(self::$typeMsg, $type);
        } else {
            self::$typeMsg[$type] = $msg;
        }
    }

    /**
     * 设置提示信息
     * @access public
     * @param string|array $name 字段名称
     * @param string $message 提示信息
     * @return Validate
     */
    public function message($name, $message = '')
    {
        if (is_array($name)) {
            $this->message = array_merge($this->message, $name);
        } else {
            $this->message[$name] = $message;
        }

        return $this;
    }

    /**
     * 设置验证场景
     * @access public
     * @param string $name 场景名
     * @return $this
     */
    public function scene($name)
    {
        // 设置当前场景
        $this->currentScene = $name;

        return $this;
    }

    /**
     * 判断是否存在某个验证场景
     * @access public
     * @param string $name 场景名
     * @return bool
     */
    public function hasScene($name)
    {
        return isset($this->scene[$name]) || method_exists($this, 'scene' . $name);
    }

    /**
     * 设置批量验证
     * @access public
     * @param bool $batch 是否批量验证
     * @return $this
     */
    public function batch($batch = true)
    {
        $this->batch = $batch;

        return $this;
    }

    /**
     * 指定需要验证的字段列表
     * @access public
     * @param array $fields 字段名
     * @return $this
     */
    public function only($fields)
    {
        $this->only = $fields;

        return $this;
    }

    /**
     * 移除某个字段的验证规则
     * @access public
     * @param string|array $field 字段名
     * @param mixed $rule 验证规则 true 移除所有规则
     * @return $this
     */
    public function remove($field, $rule = true)
    {
        if (is_array($field)) {
            foreach ($field as $key => $rule) {
                if (is_int($key)) {
                    $this->remove($rule);
                } else {
                    $this->remove($key, $rule);
                }
            }
        } else {
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }

            $this->remove[$field] = $rule;
        }

        return $this;
    }

    /**
     * 追加某个字段的验证规则
     * @access public
     * @param string|array $field 字段名
     * @param mixed $rule 验证规则
     * @return $this
     */
    public function append($field, $rule = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $rule) {
                $this->append($key, $rule);
            }
        } else {
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }

            $this->append[$field] = $rule;
        }

        return $this;
    }

    /**
     * 数据自动验证
     * @access public
     * @param array $data 数据
     * @param mixed $rules 验证规则
     * @param string $scene 验证场景
     * @return bool
     * @throws \ReflectionException
     */
    public function check($data, $rules = [], $scene = '')
    {
        $this->error = [];

        if (empty($rules)) {
            // 读取验证规则
            $rules = $this->rule;
        }

        // 获取场景定义
        $this->getScene($scene);

        foreach ($this->append as $key => $rule) {
            if (!isset($rules[$key])) {
                $rules[$key] = $rule;
            }
        }

        foreach ($rules as $key => $rule) {
            // field => 'rule1|rule2...' field => ['rule1','rule2',...]
            if (strpos($key, '|')) {
                // 字段|描述 用于指定属性名称
                list($key, $title) = explode('|', $key);
            } else {
                $title = isset($this->field[$key]) ? $this->field[$key] : $key;
            }

            // 场景检测
            if (!empty($this->only) && !in_array($key, $this->only)) {
                continue;
            }

            // 获取数据 支持二维数组
            $value = $this->getDataValue($data, $key);

            // 字段验证
            if ($rule instanceof \Closure) {
                // 匿名函数验证 支持传入当前字段和所有字段两个数据
                $result = call_user_func_array($rule, [$value, $data]);
            } elseif ($rule instanceof Rule) {
                //  验证因子
                $result = $this->checkItem($key, $value, $rule->getRule(), $data, $rule->getTitle() ?: $title, $rule->getMsg());
            } elseif (is_array($rule) && count($rule) === 2) {
                $result = call_user_func([Di::instance()->make($rule[0]), $rule[1]], $value, $data);
            } else {
                $result = $this->checkItem($key, $value, $rule, $data, $title);
            }

            if (true !== $result) {
                // 没有返回true 则表示验证失败
                if (!empty($this->batch)) {
                    // 批量验证
                    if (is_array($result)) {
                        $this->error = array_merge($this->error, $result);
                    } else {
                        $this->error[$key] = $result;
                    }
                } else {
                    $this->error = $result;
                    return false;
                }
            }
        }

        return !empty($this->error) ? false : true;
    }

    /**
     * 根据验证规则验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rules 验证规则
     * @return bool
     */
    public function checkRule($value, $rules)
    {
        if ($rules instanceof \Closure) {
            return call_user_func_array($rules, [$value]);
        } elseif ($rules instanceof Rule) {
            $rules = $rules->getRule();
        } elseif (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $key => $rule) {
            if ($rule instanceof \Closure) {
                $result = call_user_func_array($rule, [$value]);
            } else {
                // 判断验证类型
                list($type, $rule) = $this->getValidateType($key, $rule);

                $callback = isset(self::$type[$type]) ? self::$type[$type] : [$this, $type];

                $result = call_user_func_array($callback, [$value, $rule]);
            }

            if (true !== $result) {
                return $result;
            }
        }

        return true;
    }

    /**
     * 获取当前验证类型及规则
     * @access public
     * @param mixed $key
     * @param mixed $rule
     * @return array
     */
    protected function getValidateType($key, $rule)
    {
        // 判断验证类型
        if (!is_numeric($key)) {
            return [$key, $rule, $key];
        }

        if (strpos($rule, ':')) {
            list($type, $rule) = explode(':', $rule, 2);
            if (isset($this->alias[$type])) {
                // 判断别名
                $type = $this->alias[$type];
            }
            $info = $type;
        } elseif (method_exists($this, $rule)) {
            $type = $rule;
            $info = $rule;
            $rule = '';
        } else {
            $type = 'is';
            $info = $rule;
        }

        return [$type, $rule, $info];
    }

    /**
     * 验证单个字段规则
     * @access protected
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param mixed $rules 验证规则
     * @param array $data 数据
     * @param string $title 字段描述
     * @param array $msg 提示信息
     * @return mixed
     */
    protected function checkItem($field, $value, $rules, $data, $title = '', $msg = [])
    {
        if (isset($this->remove[$field]) && true === $this->remove[$field] && empty($this->append[$field])) {
            // 字段已经移除 无需验证
            return true;
        }

        // 支持多规则验证 require|in:a,b,c|... 或者 ['require','in'=>'a,b,c',...]
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (isset($this->append[$field])) {
            // 追加额外的验证规则
            $rules = array_merge($rules, $this->append[$field]);
        }

        $i = 0;
        foreach ($rules as $key => $rule) {
            if ($rule instanceof \Closure) {
                $result = call_user_func_array($rule, [$value, $data]);
                $info = is_numeric($key) ? '' : $key;
            } else {
                // 判断验证类型
                list($type, $rule, $info) = $this->getValidateType($key, $rule);

                if (isset($this->append[$field]) && in_array($info, $this->append[$field])) {

                } elseif (isset($this->remove[$field]) && in_array($info, $this->remove[$field])) {
                    // 规则已经移除
                    $i++;
                    continue;
                }

                if ('must' == $info || 0 === strpos($info, 'require') || (!is_null($value) && '' !== $value)) {
                    // 验证类型
                    $callback = isset(self::$type[$type]) ? self::$type[$type] : [$this, $type];
                    // 验证数据
                    $result = call_user_func_array($callback, [$value, $rule, $data, $field, $title]);
                } else {
                    $result = true;
                }
            }

            if (false === $result) {
                // 验证失败 返回错误信息
                if (!empty($msg[$i])) {
                    $message = $msg[$i];
                } else {
                    $message = $this->getRuleMsg($field, $title, $info, $rule);
                }

                return $message;
            } elseif (true !== $result) {
                // 返回自定义错误信息
                if (is_string($result) && false !== strpos($result, ':')) {
                    $result = str_replace(
                        [':attribute', ':rule'],
                        [$title, (string)$rule],
                        $result);
                }

                return $result;
            }
            $i++;
        }

        return $result;
    }

    /**
     * 验证是否和某个字段的值一致
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @param string $field 字段名
     * @return bool
     */
    public function confirm($value, $rule, $data = [], $field = '')
    {
        if ('' == $rule) {
            if (strpos($field, '_confirm')) {
                $rule = strstr($field, '_confirm', true);
            } else {
                $rule = $field . '_confirm';
            }
        }

        return $this->getDataValue($data, $rule) === $value;
    }

    /**
     * 验证是否和某个字段的值是否不同
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function different($value, $rule, $data = [])
    {
        return $this->getDataValue($data, $rule) != $value;
    }

    /**
     * 验证是否大于等于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function egt($value, $rule, $data = [])
    {
        return $value >= $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否大于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function gt($value, $rule, $data)
    {
        return $value > $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否小于等于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function elt($value, $rule, $data = [])
    {
        return $value <= $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否小于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function lt($value, $rule, $data = [])
    {
        return $value < $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否等于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function eq($value, $rule)
    {
        return $value == $rule;
    }

    /**
     * 必须验证
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function must($value, $rule = null)
    {
        return !empty($value) || '0' == $value;
    }

    /**
     * 验证字段值是否为有效格式
     * @access public
     * @param mixed $value 字段值
     * @param string $rule 验证规则
     * @param array $data 验证数据
     * @return bool
     */
    public function is($value, $rule, $data = [])
    {
        $rule = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $rule);

        switch (lcfirst($rule)) {
            case 'require':
                // 必须
                $result = !empty($value) || '0' == $value;
                break;
            case 'accepted':
                // 接受
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'boolean':
            case 'bool':
                // 是否为布尔值
                $result = in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'number':
                $result = is_numeric($value);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
            case 'file':
                $result = $value instanceof SplFileObject;
                break;
            case 'image':
                $result = $value instanceof SplFileObject && in_array($this->getImageType($value->getRealPath()), [1, 2, 3, 6]);
                break;
            case 'password':
                $result = (bool)preg_match('/(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*()_.]+)$)^[\w~!@#$%^&*()_.]{8,26}$/', $value);
                break;
            case 'notZero':
                $result = $value > 0;
                break;
            case "email":
                $result = (bool)preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $value);
                break;
            default:
                if (isset(self::$type[$rule])) {
                    // 注册的验证规则
                    $result = call_user_func_array(self::$type[$rule], [$value]);
                } elseif (isset($this->filter[$rule])) {
                    // Filter_var验证规则
                    $result = $this->filter($value, $this->filter[$rule]);
                } else {
                    // 正则验证
                    $result = $this->regex($value, $rule);
                }
        }

        return $result;
    }

    // 判断图像类型
    protected function getImageType($image)
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($image);
        } else {
            try {
                $info = getimagesize($image);
                return $info ? $info[2] : false;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * 验证是否为合格的域名或者IP 支持A，MX，NS，SOA，PTR，CNAME，AAAA，A6， SRV，NAPTR，TXT 或者 ANY类型
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function activeUrl($value, $rule = 'MX')
    {
        if (!in_array($rule, ['A', 'MX', 'NS', 'SOA', 'PTR', 'CNAME', 'AAAA', 'A6', 'SRV', 'NAPTR', 'TXT', 'ANY'])) {
            $rule = 'MX';
        }

        return checkdnsrr($value, $rule);
    }

    /**
     * 验证是否有效IP
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则 ipv4 ipv6
     * @return bool
     */
    public function ip($value, $rule = 'ipv4')
    {
        if (!in_array($rule, ['ipv4', 'ipv6'])) {
            $rule = 'ipv4';
        }

        return $this->filter($value, [FILTER_VALIDATE_IP, 'ipv6' == $rule ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4]);
    }

    /**
     * 检测上传文件后缀
     * @param SplFileObject $file 上传文件
     * @param array|string $ext 允许后缀
     * @return bool
     */
    protected function checkExt($file, $ext)
    {
        $extension = strtolower(pathinfo($file->getfilename(), PATHINFO_EXTENSION));

        if (is_string($ext)) {
            $ext = explode(',', $ext);
        }

        if (!in_array($extension, $ext)) {
            return false;
        }

        return true;
    }

    /**
     * 验证上传文件后缀
     * @access public
     * @param SplFileObject $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileExt($file, $rule)
    {
        if (!($file instanceof SplFileObject)) {
            return false;
        }

        return $this->checkExt($file, $rule);
    }

    /**
     * 获取文件类型信息
     * @param SplFileObject $file 上传文件
     * @return string
     */
    protected function getMime($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        return finfo_file($finfo, $file->getRealPath() ?: $file->getPathname());
    }

    /**
     * 检测上传文件类型
     * @param SplFileObject $file 上传文件
     * @param array|string $mime 允许类型
     * @return bool
     */
    protected function checkMime($file, $mime)
    {
        if (is_string($mime)) {
            $mime = explode(',', $mime);
        }

        if (!in_array(strtolower($this->getMime($file)), $mime)) {
            return false;
        }

        return true;
    }

    /**
     * 验证上传文件类型
     * @access public
     * @param SplFileObject $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileMime($file, $rule)
    {
        if (!($file instanceof SplFileObject)) {
            return false;
        }

        return $this->checkMime($file, $rule);
    }

    /**
     * 验证上传文件大小
     * @access public
     * @param SplFileObject $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileSize($file, $rule)
    {
        if (!($file instanceof SplFileObject)) {
            return false;
        }

        return $file->getSize() <= $rule;
    }

    /**
     * 验证图片的宽高及类型
     * @access public
     * @param SplFileObject $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function image($file, $rule)
    {
        if (!($file instanceof SplFileInfo)) {
            return false;
        }

        if ($rule) {
            $rule = explode(',', $rule);

            list($width, $height, $type) = getimagesize($file->getRealPath());

            if (isset($rule[2])) {
                $imageType = strtolower($rule[2]);

                if ('jpeg' == $imageType) {
                    $imageType = 'jpg';
                }

                if (image_type_to_extension($type, false) != $imageType) {
                    return false;
                }
            }

            list($w, $h) = $rule;

            return $w == $width && $h == $height;
        }
        return in_array($this->getImageType($file->getRealPath()), [1, 2, 3, 6]);
    }

    /**
     * 验证请求类型
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function method($value, $rule)
    {
        return strtoupper($rule) == $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 验证时间和日期是否符合指定格式
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function dateFormat($value, $rule)
    {
        $info = date_parse_from_format($rule, $value);
        return 0 == $info['warning_count'] && 0 == $info['error_count'];
    }

    /**
     * 使用filter_var方式验证
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function filter($value, $rule)
    {
        if (is_string($rule) && strpos($rule, ',')) {
            list($rule, $param) = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = isset($rule[1]) ? $rule[1] : null;
            $rule = $rule[0];
        } else {
            $param = 0;
        }

        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

    /**
     * 验证某个字段等于某个值的时候必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireIf($value, $rule, $data)
    {
        list($field, $val) = explode(',', $rule);

        if ($this->getDataValue($data, $field) == $val) {
            return !empty($value) || '0' == $value;
        } else {
            return true;
        }
    }

    /**
     * 通过回调方法验证某个字段是否必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireCallback($value, $rule, $data)
    {
        $result = call_user_func_array($rule, [$value, $data]);

        if ($result) {
            return !empty($value) || '0' == $value;
        } else {
            return true;
        }
    }

    /**
     * 验证某个字段有值的情况下必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireWith($value, $rule, $data)
    {
        $val = $this->getDataValue($data, $rule);

        if (!empty($val)) {
            return !empty($value) || '0' == $value;
        } else {
            return true;
        }
    }

    /**
     * 验证是否在范围内
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function in($value, $rule)
    {
        return in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 验证是否不在某个范围
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function notIn($value, $rule)
    {
        return !in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * between验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function between($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        list($min, $max) = $rule;

        return $value >= $min && $value <= $max;
    }

    /**
     * 使用notbetween验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function notBetween($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        list($min, $max) = $rule;

        return $value < $min || $value > $max;
    }

    /**
     * 验证数据长度
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function length($value, $rule)
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string)$value);
        }

        if (strpos($rule, ',')) {
            // 长度区间
            list($min, $max) = explode(',', $rule);
            return $length >= $min && $length <= $max;
        } else {
            // 指定长度
            return $length == $rule;
        }
    }

    /**
     * 验证数据最大长度
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function max($value, $rule)
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string)$value);
        }

        return $length <= $rule;
    }

    /**
     * 验证数据最小长度
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function min($value, $rule)
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string)$value);
        }

        return $length >= $rule;
    }

    /**
     * 验证日期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function after($value, $rule)
    {
        return strtotime($value) >= strtotime($rule);
    }

    /**
     * 验证日期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function before($value, $rule)
    {
        return strtotime($value) <= strtotime($rule);
    }

    /**
     * 验证有效期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function expire($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }

        list($start, $end) = $rule;

        if (!is_numeric($start)) {
            $start = strtotime($start);
        }

        if (!is_numeric($end)) {
            $end = strtotime($end);
        }

        return $_SERVER['REQUEST_TIME'] >= $start && $_SERVER['REQUEST_TIME'] <= $end;
    }

    /**
     * 验证IP许可
     * @access public
     * @param string $value 字段值
     * @param mixed $rule 验证规则
     * @return mixed
     */
    public function allowIp($value, $rule)
    {
        return in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 验证IP禁用
     * @access public
     * @param string $value 字段值
     * @param mixed $rule 验证规则
     * @return mixed
     */
    public function denyIp($value, $rule)
    {
        return !in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则 正则规则或者预定义正则名
     * @return mixed
     */
    public function regex($value, $rule)
    {
        if (isset($this->regex[$rule])) {
            $rule = $this->regex[$rule];
        }


        if (0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }

        return is_scalar($value) && 1 === preg_match($rule, (string)$value);
    }

    // 获取错误信息
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取数据值
     * @access protected
     * @param array $data 数据
     * @param string $key 数据标识 支持二维
     * @return mixed
     */
    protected function getDataValue($data, $key)
    {
        if (is_numeric($key)) {
            $value = $key;
        } elseif (strpos($key, '.')) {
            // 支持二维数组验证
            list($name1, $name2) = explode('.', $key);
            $value = isset($data[$name1][$name2]) ? $data[$name1][$name2] : null;
        } else {
            $value = isset($data[$key]) ? $data[$key] : null;
        }

        return $value;
    }

    /**
     * 获取验证规则的错误提示信息
     * @access protected
     * @param string $attribute 字段英文名
     * @param string $title 字段描述名
     * @param string $type 验证规则名称
     * @param mixed $rule 验证规则数据
     * @return string
     */
    protected function getRuleMsg($attribute, $title, $type, $rule)
    {
        if (isset($this->message[$attribute . '.' . $type])) {
            $msg = $this->message[$attribute . '.' . $type];
        } elseif (isset($this->message[$attribute][$type])) {
            $msg = $this->message[$attribute][$type];
        } elseif (isset($this->message[$attribute])) {
            $msg = $this->message[$attribute];
        } elseif (isset(self::$typeMsg[$type])) {
            $msg = self::$typeMsg[$type];
        } elseif (0 === strpos($type, 'require')) {
            $msg = self::$typeMsg['require'];
        } else {
            $msg = $title . '规则不符';
        }

        if (is_string($msg) && is_scalar($rule) && false !== strpos($msg, ':')) {
            // 变量替换
            if (is_string($rule) && strpos($rule, ',')) {
                $array = array_pad(explode(',', $rule), 3, '');
            } else {
                $array = array_pad([], 3, '');
            }
            $msg = str_replace(
                [':attribute', ':rule', ':1', ':2', ':3'],
                [$title, (string)$rule, $array[0], $array[1], $array[2]],
                $msg);
        }

        return $msg;
    }

    /**
     * 获取数据验证的场景
     * @access protected
     * @param string $scene 验证场景
     * @return array
     */
    protected function getScene($scene = '')
    {
        if (empty($scene)) {
            // 读取指定场景
            $scene = $this->currentScene;
        }

        $this->only = $this->append = $this->remove = [];

        if (empty($scene)) {
            return;
        }

        if (method_exists($this, 'scene' . $scene)) {
            call_user_func([$this, 'scene' . $scene]);
        } elseif (isset($this->scene[$scene])) {
            // 如果设置了验证适用场景
            $scene = $this->scene[$scene];

            if (is_string($scene)) {
                $scene = explode(',', $scene);
            }

            $this->only = $scene;
        }
    }

    /**
     * 动态方法 直接调用is方法进行验证
     * @access protected
     * @param string $method 方法名
     * @param array $args 调用参数
     * @return bool
     */
    public function __call($method, $args)
    {
        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_push($args, lcfirst($method));

        return call_user_func_array([$this, 'is'], $args);
    }
}
