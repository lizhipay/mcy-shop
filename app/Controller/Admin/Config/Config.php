<?php
declare (strict_types=1);

namespace App\Controller\Admin\Config;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Model\Config as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Config extends Base
{

    #[Inject]
    private Query $query;


    public function index(): Response
    {
        $get = new Get(Model::class);
        $get->setOrderBy("id", "asc");
        $get->setColumn("id", "title", "icon", "bg_url", "key");
        $configs = $this->query->get($get, function (Builder $builder) {
            return $builder->whereNull("user_id");
        });
        return $this->render("Config/Config.html", "系统设置", ["configs" => $configs, "nginxConf" => BASE_PATH . "config/nginx/*.conf", "cli" => App::$cli]);
    }


    /**
     * @return Response
     */
    public function language(): Response
    {
        return $this->render("Config/Language.html", "国际化管理");
    }
}