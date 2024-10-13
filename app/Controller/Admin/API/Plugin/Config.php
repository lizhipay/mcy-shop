<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Plugin;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
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
use Kernel\Plugin\Usr;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Config extends Base
{

    #[Inject]
    private Query $query;


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
        $get = new Get(Model::class);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc",);
        $data = $this->query->get($get, function (Builder $builder) use ($handle, $plugin) {
            return $builder->where("handle", $handle)->whereNull("user_id")->where("plugin", $plugin);
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
        \Kernel\Plugin\Plugin::inst()->instantHook($plugin, Usr::MAIN, Point::APP_SAVE_HANDLE_CFG_BEFORE, $map);
        $save->addMap("config", $map);
        try {
            $this->query->save($save);
            \Kernel\Plugin\Plugin::inst()->instantHook($plugin, Usr::MAIN, Point::APP_SAVE_HANDLE_CFG_AFTER, $map);
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
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}