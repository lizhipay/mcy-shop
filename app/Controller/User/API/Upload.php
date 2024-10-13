<?php
declare(strict_types=1);

namespace App\Controller\User\API;

use App\Controller\User\Base;
use App\Interceptor\User;
use App\Service\Common\Config;
use App\Service\Common\Image;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Util\File;

#[Interceptor(class: [User::class], type: Interceptor::API)]
class Upload extends Base
{

    const MIME = ['image', 'video', 'doc', 'other'];

    #[Inject]
    private Image $image;

    #[Inject]
    private Config $config;


    #[Inject]
    private \App\Service\Common\Upload $upload;

    /**
     * @return Response
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function main(): Response
    {
        $type = strtolower((string)$this->request->get("mime"));
        $thumbHeight = (int)$this->request->get("thumb_height");
        if (!in_array($type, self::MIME)) {
            throw new JSONException("mime not supported");
        }
        $upload = new \Kernel\Context\Upload("file");

        $config = $this->config->getMainConfig("site");
        $maxSize = 20480;
        if (isset($config['max_upload_size']) && $config['max_upload_size'] > 0) {
            $maxSize = (int)$config['max_upload_size'];
        }

        $fileName = $upload->save(path: "/assets/static/{$this->getUser()->id}/{$type}/", size: $maxSize);

        if ($tmp = $this->upload->get(md5_file(BASE_PATH . $fileName))) {
            File::remove(BASE_PATH . $fileName);
            $fileName = $tmp;
        } else {
            $this->upload->add($fileName, $type, $this->getUser()?->id);
        }

        $append = [];
        //生成缩略图
        if ($type == self::MIME[0] && $thumbHeight > 0) {
            $imageFile = BASE_PATH . $fileName;
            $thumbUrl = $this->image->createThumbnail(imagePath: $fileName, newHeight: $thumbHeight);
            if (!$thumbUrl) {
                if (is_file($imageFile)) {
                    unlink($imageFile);
                }
                throw new JSONException("图片上传失败，原因：生成缩略图失败");
            }
            $append['thumb_url'] = $thumbUrl;
        }

        return $this->response->json(data: ["url" => $fileName, "append" => $append]);
    }
}