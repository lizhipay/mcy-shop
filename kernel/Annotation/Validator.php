<?php
declare(strict_types=1);

namespace Kernel\Annotation;


use Kernel\Container\Di;
use Kernel\Context\Interface\Request;
use Kernel\Exception\JSONException;
use Kernel\Exception\ViewException;
use Kernel\Util\Context;
use Kernel\Util\Str;
use Kernel\Validator\Method;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Validator
{
    /**
     * @param array $rules
     * @param int $method
     * @param int $renderType
     * @throws JSONException
     * @throws ViewException
     * @throws \ReflectionException
     */
    public function __construct(array $rules, int $method = Method::POST, int $renderType = Interceptor::API)
    {
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);
        $data = [];

        switch ($method) {
            case Method::POST:
                $data = $request->post();
                break;
            case Method::GET:
                $data = $request->get();
                break;
            case Method::COOKIE:
                $data = $request->cookie();
                break;
            case Method::JSON:
                $data = $request->json();
                break;
            case Method::HEADER:
                $data = $request->header();
                break;
        }

        foreach ($rules as $rule) {
            if (is_array($rule) && count($rule) === 2) {
                list($class, $action) = $rule;
                if (!is_array($action)) {
                    $action = [$action];
                }
                $this->check($class, $action, $renderType, $data);
            }
        }
    }

    /**
     * @param string $class
     * @param array $actions
     * @param int $renderType
     * @param array $data
     * @return void
     * @throws JSONException
     * @throws ViewException
     * @throws \ReflectionException
     */
    private function check(string $class, array $actions, int $renderType, array $data): void
    {
        foreach ($actions as $action) {
            $name = isset($data[$action]) ? $action : Str::camelToSnake($action, "_");
            Collector::instance()->methodParse($class, $action, function (\ReflectionAttribute $attribute) use ($renderType, &$name, $data) {
                $obj = $attribute->newInstance();
                if ($obj instanceof Name) {
                    $name = $obj->name;
                }
                if ($obj instanceof Required) {
                    switch ($obj->mode) {
                        case \Kernel\Validator\Required::EXTREME:
                            if (!isset($data[$name]) || $data[$name] === "") {
                                $this->exception($obj->message, $renderType);
                            }
                            break;
                        case \Kernel\Validator\Required::LOOSE:
                            if (isset($data[$name]) && $data[$name] === "") {
                                $this->exception($obj->message, $renderType);
                            }
                            break;
                    }
                }
                if ($obj instanceof Regex) {
                    if (isset($data[$name]) && $data[$name] !== "" && !preg_match($obj->regex, (string)$data[$name])) {
                        $this->exception($obj->message, $renderType);
                    }
                }
            });
            // $this->exception(Di::instance()->make($class)->$action($data[$name] ?? null, $data), $renderType);
            $validator = new $class;
            Di::inst()->inject($validator);
            $this->exception(call_user_func_array([$validator, $action], [$data[$name] ?? null, $data]), $renderType);
        }
    }


    /**
     * @param mixed $result
     * @param int $renderType
     * @return void
     * @throws JSONException
     * @throws ViewException
     */
    private function exception(mixed $result, int $renderType): void
    {
        if ($result !== true) {
            if ($renderType == Interceptor::VIEW) {
                throw new ViewException((string)$result, -10);
            } else {
                throw new JSONException((string)$result, -10);
            }
        }
    }
}