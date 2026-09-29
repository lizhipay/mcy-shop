<?php
declare (strict_types=1);

namespace App\Controller\User\API\Plugin;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Group;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\PluginConfig as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Const\Point;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Group::class], type: Interceptor::API)]
class Config extends Base
{

    #[Inject]
    private Query $query;


    /**
     * 插件标识/handle 白名单：只允许字母数字下划线，杜绝路径穿越写入非法 plugin 值，
     * 该值后续会拼进 getPlugin() 的 require() 路径，属文件包含风险点。
     * @param string $name
     * @return void
     * @throws JSONException
     */
    private function assertPluginName(string $name): void
    {
        if ($name === "" || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new JSONException("非法的插件标识");
        }
    }


    /**
     * @param string $plugin
     * @param string $handle
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(string $plugin, string $handle): Response
    {
        $this->assertPluginName($plugin);
        $this->assertPluginName($handle);
        $get = new Get(Model::class);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $data = $this->query->get($get, function (Builder $builder) use ($handle, $plugin) {
            return $builder->where("handle", $handle)->where("user_id", $this->getUser()->id)->where("plugin", $plugin);
        });
        return $this->json(data: $data);
    }

    /**
     * @param string $plugin
     * @param string $handle
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Admin\PayConfig::class, "name"]
    ])]
    public function save(string $plugin, string $handle): Response
    {
        $this->assertPluginName($plugin);
        $this->assertPluginName($handle);
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $map = $this->request->post();

        $save->setMap([
            "name" => $map['name'],
            "plugin" => $plugin,
            "handle" => $handle,
            "id" => $map['id'] ?? null
        ]);

        if (isset($map['id'])) {
            unset($map['id']);
        }

        unset($map['name']);
        \Kernel\Plugin\Plugin::inst()->instantHook($plugin, $this->getUserPath(), Point::APP_SAVE_HANDLE_CFG_BEFORE, $map);
        $save->addMap("config", $map);
        $save->addForceMap("user_id", $this->getUser()->id);
        $save->addWhere("user_id", $this->getUser()->id); //归属校验：只能修改自己的插件配置（防跨租户接管支付/发货密钥）
        try {
            $this->query->save($save);
            \Kernel\Plugin\Plugin::inst()->instantHook($plugin, $this->getUserPath(), Point::APP_SAVE_HANDLE_CFG_AFTER, $map);
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }


    /**
     * @return Response
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $delete->setWhere("user_id", $this->getUser()->id);
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}