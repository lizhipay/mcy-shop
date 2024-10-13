<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Const\Session;
use App\Model\Config;
use App\Model\User;
use App\Service\Common\Bind\Code;
use App\Service\Common\Smtp;
use App\Service\User\Cart;
use App\Service\User\Level;
use App\Service\User\Lifetime;
use App\Service\User\Log;
use App\Service\User\LoginLog;
use Firebase\JWT\JWT;
use Kernel\Annotation\Inject;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Const\Plugin as PGI;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Util\Context;
use Kernel\Util\Date;
use Kernel\Util\Str;

class Auth implements \App\Service\User\Auth
{

    #[Inject]
    private Code $code;


    #[Inject]
    private Smtp $smtp;


    #[Inject]
    private Cart $cart;


    #[Inject]
    private Lifetime $lifetime;


    #[Inject]
    private LoginLog $loginLog;

    #[Inject]
    private Log $log;


    #[Inject]
    private Level $level;

    #[Inject]
    private \App\Service\Common\Config $config;

    /**
     * @param string $type
     * @param array $map
     * @return void
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function sendEmail(string $type, array $map): void
    {
        if (!in_array($type, ["register", "reset"])) {
            throw new JSONException("type error");
        }

        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_SEND_EMAIL_BEFORE, PGI::HOOK_TYPE_PAGE, $map);

        $register = Config::main("register");

        if (($type == "register" && $register['email_register_state'] != 1) || ($type == "reset" && $register['email_reset_state'] != 1)) {
            throw new JSONException("邮箱发信未开启");
        }

        $email = $map['email'];
        $code = $this->code->create(sprintf(Session::EMAIL_CODE, $type, $email));

        $title = match ($type) {
            "register" => "注册账号",
            "reset" => "重置密码"
        };

        $send = $this->smtp->send($email, "【{$title}】验证码", str_replace('{$code}', (string)$code, $register['email_template']));

        if (!$send) {
            Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_SEND_EMAIL_ERROR, PGI::HOOK_TYPE_PAGE, $map);
            throw new JSONException("验证码发送失败");
        }

        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_SEND_EMAIL_SUCCESS, PGI::HOOK_TYPE_PAGE, $map);
    }

    /**
     * @param array $map
     * @param string $clientId
     * @param string $ip
     * @param string $ua
     * @param User|null $merchant
     * @param User|null $inviter
     * @return User
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function register(array $map, string $clientId, string $ip, string $ua, ?User $merchant = null, ?User $inviter = null): User
    {
        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_REGISTER_BEFORE, PGI::HOOK_TYPE_PAGE, $map);

        if (User::query()->where("username", $map['username'])->exists()) {
            throw new JSONException("用户名已存在");
        }

        $config = Config::main("register");

        if ($config['status'] != 1) {
            throw new JSONException("当前无法注册新用户");
        }

        $user = new User();
        $user->username = trim((string)$map['username']);

        if (isset($config['email_register']) && $config['email_register'] == 1) {

            if (!$map['email']) {
                throw new JSONException("邮箱不能为空");
            }

            if (User::query()->where("email", $map['email'])->first()) {
                throw new JSONException("当前邮箱已被他人占用");
            }

            $user->email = trim((string)$map['email']);

            if ($config['email_register_state'] == 1) {
                if (!$this->code->verify(sprintf(Session::EMAIL_CODE, "register", $user->email), (int)$map['email_code'], 300)) {
                    throw new JSONException("邮箱验证码错误");
                }
            }
        }

        $user->salt = Str::generateRandStr(32);
        $user->password = Str::generatePassword(trim((string)$map['password']), $user->salt);
        $user->app_key = strtoupper(Str::generateRandStr(16));
        $user->status = 1;
        $user->balance = 0;
        $user->withdraw_amount = 0;
        $user->level_id = $this->level->getDefaultId($merchant);
        $user->avatar = "/favicon.ico"; //默认头像
        $user->api_code = strtoupper(Str::generateRandStr(6));
        $merchant && ($user->pid = $merchant->id); //上级id
        if ($inviter) {
            $user->invite_id = $inviter->id; //邀请者id
            $this->lifetime->increment($inviter->id, "total_referral_count");
        }

        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_REGISTER_READY, PGI::HOOK_TYPE_PAGE, $user);
        $user->save();
        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_REGISTER_SUCCESS, PGI::HOOK_TYPE_PAGE, $user);

        //创建生涯
        $this->lifetime->create($user->id, $ip, $ua);
        //更新购物车
        $this->cart->bindUser($user, $clientId);
        return $user;
    }


    /**
     * @param array $map
     * @param string $ip
     * @param string $ua
     * @param string $clientId
     * @return string
     * @throws JSONException
     * @throws NotFoundException
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function login(array $map, string $ip, string $ua, string $clientId): string
    {

        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_LOGIN_BEFORE, PGI::HOOK_TYPE_PAGE, $map, $ip, $ua);
        /**
         * @var User $user
         */
        $user = User::query()->where("username", $map['username'])->first() ?? User::query()->where("email", $map['username'])->first();
        if (!$user) {
            throw new JSONException("用户不存在");
        }

        if ($user->password != Str::generatePassword(trim((string)$map['password']), $user->salt)) {
            throw new JSONException("密码错误");
        }

        if ($user->status != 1) {
            throw new JSONException("You have been banned");
        }

        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_LOGIN_SUCCESS, PGI::HOOK_TYPE_PAGE, $user);
        return $this->setLoginSuccess($user);
    }

    /**
     * @param User $user
     * @return string
     * @throws ServiceException
     */
    public function setLoginSuccess(User $user): string
    {
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);
        if (!$request) {
            throw new ServiceException("此方法只能在HTTP环境中调用");
        }

        $ip = $request->clientIp();
        $ua = (string)$request->header("UserAgent");
        $clientId = (string)$request->cookie("client_id");
        $loginTime = Date::current();

        $config = $this->config->getMainConfig("site");

        $jwt = base64_encode(JWT::encode(
            payload: [
                "expire" => time() + $config['session_expire'],
                'loginTime' => $loginTime
            ],
            key: $user->password,
            alg: 'HS256',
            head: ["uid" => $user->id]
        ));

        //$data[Cookie::USER_ID] = $user->id;
        //$data[Cookie::USER_TOKEN] = $jwt;
        //更新上下文
        Context::set(\App\Model\User::class, $user);
        //更新生涯
        $this->lifetime->update($user->id, "last_login_time", $loginTime);
        //更新登录状态
        $this->lifetime->update($user->id, "login_status", 1);
        //登录日志
        $this->loginLog->create($user->id, $ip, $ua);

        //更新购物车
        $this->cart->bindUser($user, $clientId);
        return $jwt;
    }

    /**
     * @param array $map
     * @return void
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function reset(array $map): void
    {
        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_RESET_BEFORE, PGI::HOOK_TYPE_PAGE, $map);
        /**
         * @var User $user
         */
        $user = User::query()->where("email", $map['email'])->first();
        if (!$user) {
            throw new JSONException("该邮箱未注册");
        }

        if (!$this->code->verify(sprintf(Session::EMAIL_CODE, "reset", $user->email), (int)$map['email_code'], 300)) {
            throw new JSONException("邮箱验证码错误");
        }
        $user->password = Str::generatePassword(trim((string)$map['password']), $user->salt);
        $user->save();

        Context::set(\App\Model\User::class, $user);
        $this->log->create($user->id, "重置了密码");
        Plugin::instance()->hook(App::env(), Point::SERVICE_AUTH_RESET_SUCCESS, PGI::HOOK_TYPE_PAGE, $user, $map);
    }
}