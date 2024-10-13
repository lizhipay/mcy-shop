<?php
declare (strict_types=1);

namespace App\Controller\User\API\Upload;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Identity;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Upload as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Upload extends Base
{

    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
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
        $get->setOrderBy("id", "desc");

        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            return $builder->where("user_id", $this->getUser()->id);
        });

        foreach ($data['list'] as &$item) {
            $baseImagePathInfo = pathinfo($item['path']);
            $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];
            if (is_file(BASE_PATH . $thumbPath)) {
                $item['thumb_url'] = $thumbPath;
            }
        }

        return $this->json(data: $data);
    }
}