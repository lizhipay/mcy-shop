<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\UserLevel;
use App\Model\UserLevel as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class Level extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setOrderBy("sort", "asc");

        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            return $builder->where("user_id", $this->getUser()->id)->withCount('member');
        });

        isset($data[0]) && $data[0]['default'] = true;

        return $this->json(data: ['list' => $data]);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\Level::class, ['name', 'id']]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();
        $privilegeIntroduce = $this->request->post("privilege_introduce", Filter::NORMAL);
        $privilegeContent = $this->request->post("privilege_content", Filter::NORMAL);
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($map, ["icon", "name", "upgrade_price", "sort", "is_upgradable"]);
        $save->addForceMap("upgrade_requirements", json_encode($this->getUpgradeRequirements($map)));
        $save->addForceMap("user_id", $this->getUser()->id);
        $privilegeIntroduce && $save->addForceMap("privilege_introduce", $privilegeIntroduce);
        $privilegeContent && $save->addForceMap("privilege_content", $privilegeContent);
        if (isset($map['sort']) && $map['sort'] === "") {
            $level = UserLevel::query()->where("user_id", $this->getUser()->id)->orderBy("sort", "desc")->first();
            if ($level) {
                $save->addForceMap("sort", $level->sort + 1);
            }
        }

        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }


    /**
     * @param array $map
     * @return array
     */
    private function getUpgradeRequirements(array $map): array
    {
        $requirements = [
            "total_consumption_amount" => 0,
            "total_recharge_amount" => 1,
            "total_referral_count" => 2,
            "total_profit_amount" => 3
        ];

        $data = [];
        if (isset($map['upgrade_requirements']) && is_array($map['upgrade_requirements'])) {
            foreach ($requirements as $key => $val) {
                if (in_array($val, $map['upgrade_requirements']) && isset($map[$key]) && $map[$key] > 0) {
                    $data[$key] = $map[$key];
                }
            }
        }
        return $data;
    }


    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\Level::class, 'id']
    ])]
    public function del(): Response
    {
        $id = $this->request->post("id", Filter::INTEGER);

        if (Model::query()->where("user_id", $this->getUser()->id)->count() <= 1) {
            throw new JSONException("必须保留1个默认等级");
        }

        //判断是否存在该会员等级
        if (\App\Model\User::query()->where("level_id", $id)->exists()) {
            throw new JSONException("该会员等级下有会员，无法删除");
        }

        $delete = new Delete(Model::class, [$id]);
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}