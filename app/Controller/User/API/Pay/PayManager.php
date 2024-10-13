<?php
declare (strict_types=1);

namespace App\Controller\User\API\Pay;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Pay as Model;
use App\Model\PluginConfig;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class PayManager extends Base
{

    #[Inject]
    private Query $query;


    /**
     * @param string $plugin
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function code(string $plugin): Response
    {
        $plg = Plugin::instance()->getPlugin($plugin, Usr::inst()->userToEnv($this->getUser()->id));
        if (!$plg) {
            throw new JSONException("插件不存在");
        }
        return $this->json(data: $plg->payCode);
    }

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($map, "sort", "asc"));
        $get->setWhereLeftJoin(PluginConfig::class, "id", "pay_config_id", ["plugin" => "plugin"]);
        $data = $this->query->get($get, function (Builder $builder) {
            $builder = $builder->where("pay.user_id", $this->getUser()->id);
            return $builder
                ->with(['config'])
                ->withSum("paidOrder as order_amount", "trade_amount")
                ->withSum("todayOrder as today_amount", "trade_amount")
                ->withSum("yesterdayOrder as yesterday_amount", "trade_amount")
                ->withSum("weekdayOrder as weekday_amount", "trade_amount")
                ->withSum("monthOrder as month_amount", "trade_amount")
                ->withSum("lastMonthOrder as last_month_amount", "trade_amount");
        });

        foreach ($data['list'] as &$item) {
            if (!$item['pid'] && isset($item['config'])) {
                $item['plugin'] = Plugin::instance()->getPlugin($item['config']['plugin'], Usr::inst()->userToEnv($this->getUser()->id));
                $item['scope'] = is_array($item['scope']) ? $item['scope'] : (array)json_decode((string)$item['scope'], true);
            }
        }

        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\User\Pay::class, ["id", "name", "pid", "code", "payConfigId"]]
    ])]
    public function save(): Response
    {
        $post = $this->request->post();

        if (isset($post['pid'])){
            unset($post['api_fee']);
        }

        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap(map: $post, forbidden: ['scope', 'substation_status', 'substation_fee' , 'api_fee_status']);
        $save->addForceMap("user_id", $this->getUser()->id);

        if (isset($post['pid'])) {
            /**
             * @var Model $pay
             */
            $pay = Model::query()->find($post['pid']);
            if (!$pay || $pay->user_id !== null) {
                throw new JSONException("上级支付接口不存在");
            }
            $save->addForceMap("scope", is_array($pay->scope) ? $pay->scope : []);
        } else if (isset($post['scope'])) {
            $scope = (array)$post['scope'];
            $scopeSys = [];
            if (in_array("product", $scope)) {
                $scopeSys[] = "product";
            }
            if (in_array("level", $scope)) {
                $scopeSys[] = "level";
            }
            $save->addForceMap("scope", $scopeSys);
        }

        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->json(message: "保存成功");
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $delete->setWhere("user_id", $this->getUser()->id);
        $this->query->delete($delete);
        return $this->json(message: "删除成功");
    }
}