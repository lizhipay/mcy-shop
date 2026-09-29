<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use Kernel\Annotation\Inject;
use Kernel\Cache\Cache;
use Kernel\Exception\JSONException;
use Kernel\Session\Session;

class Code implements \App\Service\Common\Code
{

    /**
     * 单个验证码允许的最大失败尝试次数
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * 达到失败上限后的锁定时长（秒）
     */
    private const LOCK_SECONDS = 900;

    #[Inject]
    private Session $session;


    /**
     * @throws JSONException
     */
    public function create(string $key, int $expire = 60): int
    {

        $key = sprintf(\App\Const\Session::CODE, $key);

        //基于共享内存的发送频率限制：按目标(邮箱/类型)维度限制，独立于可被任意重置的会话Cookie，
        //防止攻击者通过轮换 acg_session 无限刷新验证码窗口 / 邮件轰炸。
        $throttleKey = "cc_throttle_" . md5($key);
        $last = Cache::inst()->get($throttleKey);
        if (is_array($last) && isset($last['time']) && ($last['time'] + $expire) > time()) {
            throw new JSONException(sprintf("验证码创建频繁，%d后再进行尝试", ($last['time'] + $expire) - time()));
        }

        $var = $this->session->get($key);
        if ($var) {
            $tm = $var['time'] + $expire;
            if ($tm > time()) {
                throw new JSONException(sprintf("验证码创建频繁，%d后再进行尝试", $tm - time()));
            }
        }

        mt_srand();
        $code = random_int(100000, 999999);
        $this->session->set($key, [
            "code" => $code,
            "time" => time()
        ]);

        //记录发送时间。注意：不在此清除失败计数，否则攻击者可用「重发验证码」重置锁定实现无限爆破；
        //失败计数仅在验证成功(verify)或锁定窗口自然过期后重置。
        Cache::inst()->set($throttleKey, ["time" => time()]);

        return $code;
    }


    /**
     * @param string $key
     * @param int $code
     * @param int $expire
     * @return bool
     */
    public function verify(string $key, int $code, int $expire = 300): bool
    {
        if ($code == 0) {
            return false;
        }

        $key = sprintf(\App\Const\Session::CODE, $key);

        //失败计数与锁定：按目标维度记录于共享内存，独立于会话Cookie，杜绝暴力枚举验证码。
        $attemptKey = "cc_attempt_" . md5($key);
        $attempt = Cache::inst()->get($attemptKey);
        $attempt = is_array($attempt) ? $attempt : ["count" => 0, "time" => time()];

        //锁定窗口已过期：重置计数
        if ((int)($attempt['time'] ?? 0) + self::LOCK_SECONDS <= time()) {
            $attempt = ["count" => 0, "time" => time()];
        }

        //达到失败上限且仍在锁定窗口内：直接拒绝
        if ((int)($attempt['count'] ?? 0) >= self::MAX_ATTEMPTS) {
            return false;
        }

        $var = $this->session->get($key);

        if (!$var) {
            $this->bumpAttempt($attemptKey, $attempt);
            return false;
        }

        $tm = $var['time'] + $expire;

        if ($tm < time()) {
            return false;
        }

        if ($code != $var['code']) {
            $this->bumpAttempt($attemptKey, $attempt);
            return false;
        }

        $this->session->remove($key);
        Cache::inst()->del($attemptKey);
        return true;
    }

    /**
     * @param string $attemptKey
     * @param array $attempt
     * @return void
     */
    private function bumpAttempt(string $attemptKey, array $attempt): void
    {
        $attempt['count'] = (int)($attempt['count'] ?? 0) + 1;
        if (!isset($attempt['time'])) {
            $attempt['time'] = time();
        }
        Cache::inst()->set($attemptKey, $attempt);
    }
}