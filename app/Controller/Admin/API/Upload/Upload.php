<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Upload;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Upload as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Upload extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\Common\Upload $upload;

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
            if (isset($map['display_scope'])) {
                if ($map['display_scope'] == 1) {
                    $builder = $builder->whereNull("user_id");
                } elseif ($map['display_scope'] == 2) {
                    if (isset($map['user_id']) && $map['user_id'] > 0) {
                        $builder = $builder->where("user_id", $map['user_id']);
                    } else {
                        $builder = $builder->whereNotNull("user_id");
                    }
                }
            }

            /*        if (isset($map['user_id']) && $map['user_id'] > 0) {
                        $builder = $builder->where("user_id", $map['user_id']);
                    } else {
                        $builder = $builder->whereNull("user_id");
                    }*/
            return $builder->with(["user" => function (Relation $relation) {
                $relation->select(["id", "username", "avatar"]);
            }]);
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


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $list = (array)$this->request->post("list");
        if (count($list) > 0) {
            $uploads = \App\Model\Upload::query()->whereIn("id", $list)->get();
            foreach ($uploads as $upload) {
                $this->upload->remove($upload->path); //通过hash删除文件
            }
        }
        return $this->json();
    }
}