<?php
declare (strict_types=1);

namespace Kernel\Validator;

/**
 * @method Rule confirm(mixed $field, string $msg = '') static 验证是否和某个字段的值一致
 * @method Rule different(mixed $field, string $msg = '') static 验证是否和某个字段的值是否不同
 * @method Rule egt(mixed $value, string $msg = '') static 验证是否大于等于某个值
 * @method Rule gt(mixed $value, string $msg = '') static 验证是否大于某个值
 * @method Rule elt(mixed $value, string $msg = '') static 验证是否小于等于某个值
 * @method Rule lt(mixed $value, string $msg = '') static 验证是否小于某个值
 * @method Rule eg(mixed $value, string $msg = '') static 验证是否等于某个值
 * @method Rule in(mixed $values, string $msg = '') static 验证是否在范围内
 * @method Rule notIn(mixed $values, string $msg = '') static 验证是否不在某个范围
 * @method Rule between(mixed $values, string $msg = '') static 验证是否在某个区间
 * @method Rule notBetween(mixed $values, string $msg = '') static 验证是否不在某个区间
 * @method Rule length(mixed $length, string $msg = '') static 验证数据长度
 * @method Rule max(mixed $max, string $msg = '') static 验证数据最大长度
 * @method Rule min(mixed $min, string $msg = '') static 验证数据最小长度
 * @method Rule after(mixed $date, string $msg = '') static 验证日期
 * @method Rule before(mixed $date, string $msg = '') static 验证日期
 * @method Rule expire(mixed $dates, string $msg = '') static 验证有效期
 * @method Rule allowIp(mixed $ip, string $msg = '') static 验证IP许可
 * @method Rule denyIp(mixed $ip, string $msg = '') static 验证IP禁用
 * @method Rule regex(mixed $rule, string $msg = '') static 使用正则验证数据
 * @method Rule token(mixed $token, string $msg = '') static 验证表单令牌
 * @method Rule is(mixed $rule = null, string $msg = '') static 验证字段值是否为有效格式
 * @method Rule isRequire(mixed $rule = null, string $msg = '') static 验证字段必须
 * @method Rule isNumber(mixed $rule = null, string $msg = '') static 验证字段值是否为数字
 * @method Rule isArray(mixed $rule = null, string $msg = '') static 验证字段值是否为数组
 * @method Rule isInteger(mixed $rule = null, string $msg = '') static 验证字段值是否为整形
 * @method Rule isFloat(mixed $rule = null, string $msg = '') static 验证字段值是否为浮点数
 * @method Rule isMobile(mixed $rule = null, string $msg = '') static 验证字段值是否为手机
 * @method Rule isIdCard(mixed $rule = null, string $msg = '') static 验证字段值是否为身份证号码
 * @method Rule isChs(mixed $rule = null, string $msg = '') static 验证字段值是否为中文
 * @method Rule isChsDash(mixed $rule = null, string $msg = '') static 验证字段值是否为中文字母及下划线
 * @method Rule isChsAlpha(mixed $rule = null, string $msg = '') static 验证字段值是否为中文和字母
 * @method Rule isChsAlphaNum(mixed $rule = null, string $msg = '') static 验证字段值是否为中文字母和数字
 * @method Rule isDate(mixed $rule = null, string $msg = '') static 验证字段值是否为有效格式
 * @method Rule isBool(mixed $rule = null, string $msg = '') static 验证字段值是否为布尔值
 * @method Rule isAlpha(mixed $rule = null, string $msg = '') static 验证字段值是否为字母
 * @method Rule isAlphaDash(mixed $rule = null, string $msg = '') static 验证字段值是否为字母和下划线
 * @method Rule isAlphaNum(mixed $rule = null, string $msg = '') static 验证字段值是否为字母和数字
 * @method Rule isAccepted(mixed $rule = null, string $msg = '') static 验证字段值是否为yes, on, 或是 1
 * @method Rule isEmail(mixed $rule = null, string $msg = '') static 验证字段值是否为有效邮箱格式
 * @method Rule isUrl(mixed $rule = null, string $msg = '') static 验证字段值是否为有效URL地址
 * @method Rule activeUrl(mixed $rule = null, string $msg = '') static 验证是否为合格的域名或者IP
 * @method Rule ip(mixed $rule = null, string $msg = '') static 验证是否有效IP
 * @method Rule fileExt(mixed $ext, string $msg = '') static 验证文件后缀
 * @method Rule fileMime(mixed $mime, string $msg = '') static 验证文件类型
 * @method Rule fileSize(mixed $size, string $msg = '') static 验证文件大小
 * @method Rule image(mixed $rule, string $msg = '') static 验证图像文件
 * @method Rule method(mixed $method, string $msg = '') static 验证请求类型
 * @method Rule dateFormat(mixed $format, string $msg = '') static 验证时间和日期是否符合指定格式
 * @method Rule unique(mixed $rule, string $msg = '') static 验证是否唯一
 * @method Rule behavior(mixed $rule, string $msg = '') static 使用行为类验证
 * @method Rule filter(mixed $rule, string $msg = '') static 使用filter_var方式验证
 * @method Rule requireIf(mixed $rule, string $msg = '') static 验证某个字段等于某个值的时候必须
 * @method Rule requireCallback(mixed $rule, string $msg = '') static 通过回调方法验证某个字段是否必须
 * @method Rule requireWith(mixed $rule, string $msg = '') static 验证某个字段有值的情况下必须
 * @method Rule must(mixed $rule = null, string $msg = '') static 必须验证
 */
class Rule
{
    /**
     * @var string
     */
    protected string $title;

    /**
     * @var array
     */
    protected array $rule = [];

    /**
     * @var array
     */
    protected array $message = [];

    /**
     * 添加验证因子
     * @access protected
     * @param string $name 验证名称
     * @param mixed|null $rule 验证规则
     * @param string $msg 提示信息
     * @return $this
     */
    protected function addItem(string $name, mixed $rule = null, string $msg = ''): static
    {
        if ($rule || 0 === $rule) {
            $this->rule[$name] = $rule;
        } else {
            $this->rule[] = $name;
        }

        $this->message[] = $msg;

        return $this;
    }

    /**
     * 获取验证规则
     * @access public
     * @return array
     */
    public function getRule(): array
    {
        return $this->rule;
    }

    /**
     * 获取验证字段名称
     * @access public
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * 获取验证提示
     * @access public
     * @return array
     */
    public function getMsg(): array
    {
        return $this->message;
    }

    /**
     * 设置验证字段名称
     * @access public
     * @return $this
     */
    public function title($title): static
    {
        $this->title = $title;

        return $this;
    }

    public function __call($method, $args)
    {
        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_unshift($args, lcfirst($method));

        return call_user_func_array([$this, 'addItem'], $args);
    }

    public static function __callStatic($method, $args)
    {
        $rule = new static();

        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_unshift($args, lcfirst($method));

        return call_user_func_array([$rule, 'addItem'], $args);
    }
}
