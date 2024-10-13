<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Model\User;
use App\Model\UserLoginLog;
use App\Service\User\Lifetime;
use Kernel\Annotation\Inject;
use Kernel\Util\Date;

class LoginLog implements \App\Service\User\LoginLog
{

    #[Inject]
    private Lifetime $lifetime;


    /**
     * @param int $userId
     * @param string $ip
     * @param string $ua
     * @return void
     */
    public function create(int $userId, string $ip, string $ua): void
    {
        $ipCount = UserLoginLog::query()->where("user_id", $userId)->where("ip", $ip)->count();
        $count = UserLoginLog::query()->where("user_id", $userId)->count();
        $log = new UserLoginLog();
        $log->user_id = $userId;
        $log->ip = $ip;
        $log->ua = $ua;
        $log->is_dangerous = (int)($count > 0 && $ipCount == 0);
        $log->create_time = Date::current();
        $log->save();
    }

    /**
     * 检测2个用户是否同一个人
     * @param int $userId
     * @param int $targetId
     * @return bool
     */
    public function isSame(int $userId, int $targetId): bool
    {
        $user = User::find($userId);
        $target = User::find($targetId);
        if (!$user || !$target) {
            return false;
        }

        //如果注册IP相同，代表是同一个人
        if ($this->lifetime->get($userId, "register_ip") == $this->lifetime->get($targetId, "register_ip")) {
            return true;
        }

        //取出100条登录记录做对比
        $userLoginLog = UserLoginLog::query()->where("user_id", $userId)->limit(100)->orderBy("id", "desc")->get()->toArray();
        $targetLoginLog = UserLoginLog::query()->where("user_id", $targetId)->limit(100)->orderBy("id", "desc")->get()->toArray();

        $ips1 = array_map(function ($item) {
            return $item['ip'];
        }, $userLoginLog);

        $ips2 = array_map(function ($item) {
            return $item['ip'];
        }, $targetLoginLog);

        $common = array_intersect($ips1, $ips2);

        //如果2个用户有相同的登录IP，代表是同一个人
        return !empty($common);
    }
}