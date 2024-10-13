<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Context\Interface\Request;
use Kernel\Database\Model;
use Kernel\Util\Context;
use Kernel\Util\Date;

/**
 * @property int $id
 * @property int $manage_id
 * @property string $email
 * @property string $nickname
 * @property string $content
 * @property string $request_url
 * @property string $request_method
 * @property string $request_param
 * @property string $create_time
 * @property string $create_ip
 * @property string $ua
 * @property string $client_token
 * @property int $risk
 */
class ManageLog extends Model
{
    /**
     * @var ?string
     */
    protected ?string $table = "manage_log";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'risk' => 'integer'];


    /**
     * 添加日志
     * @param string $content
     * @return void
     */
    public static function add(string $content): void
    {

        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);
        /**
         * @var Manage $manage
         */
        $manage = Context::get(Manage::class);

        $manageLog = new self();
        $manageLog->manage_id = $manage->id;
        $manageLog->email = $manage->email;
        $manageLog->nickname = $manage->nickname;
        $manageLog->content = $content;
        $manageLog->request_url = $request->url() . $request->uri();
        $manageLog->request_method = $request->method();
        $manageLog->request_param = json_encode($request->post(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $manageLog->create_time = Date::current();
        $manageLog->create_ip = $request->clientIp();
        $manageLog->ua = (string)$request->header("UserAgent");
        $manageLog->client_token = (string)$request->header("Token");
        $manageLog->risk = 0;

        if ($manage->login_ip != $manageLog->create_ip) {
            $manageLog->risk = 1;
            if ($manage->login_ua != $manageLog->ua && $manage->client_token != $manageLog->client_token) {
                $manageLog->risk = 2;
            }
        }

        $manageLog->save();
    }
}