<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use Kernel\Annotation\Inject;
use Kernel\Cache\Cache;
use Kernel\Exception\JSONException;
use Kernel\Session\Session;

class Captcha implements \App\Service\Common\Captcha
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 900;

    #[Inject]
    private Session $session;

    /**
     * @param string $key
     * @param int $expire second
     * @param int $limiter second
     * @return string
     * @throws JSONException
     */
    public function create(string $key, int $expire, int $limiter = 60): string
    {
        if ($this->session->has($key) && $limiter > 0) {
            if ($this->session->get($key)['time'] + $limiter > time()) {
                throw new JSONException("验证码创建频繁，请稍后再试");
            }
        }
        $code = mt_rand(100000, 999999);
        $this->session->set($key, ["time" => time(), "expire" => $expire, "code" => $code]);
        return (string)$code;
    }

    /**
     * @param string $key
     * @param string $code
     * @return bool
     */
    public function verify(string $key, string $code): bool
    {
        //失败计数与锁定（共享内存，独立于可重置的会话Cookie），杜绝对6位验证码的暴力枚举
        $attemptKey = "captcha_attempt_" . md5($key);
        $attempt = Cache::inst()->get($attemptKey);
        $attempt = is_array($attempt) ? $attempt : ["count" => 0, "time" => time()];
        if ((int)($attempt['time'] ?? 0) + self::LOCK_SECONDS <= time()) {
            $attempt = ["count" => 0, "time" => time()];
        }
        if ((int)($attempt['count'] ?? 0) >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (!$this->session->has($key)) {
            $this->bumpAttempt($attemptKey, $attempt);
            return false;
        }

        $data = $this->session->get($key);

        if ($data['time'] + $data['expire'] < time()) {
            return false;
        }

        if (!hash_equals((string)$data['code'], (string)$code)) {
            $this->bumpAttempt($attemptKey, $attempt);
            return false;
        }

        //验证成功：移除验证码防重放，并清除失败计数
        $this->session->remove($key);
        Cache::inst()->del($attemptKey);
        return true;
    }

    private function bumpAttempt(string $attemptKey, array $attempt): void
    {
        $attempt['count'] = (int)($attempt['count'] ?? 0) + 1;
        if (!isset($attempt['time'])) {
            $attempt['time'] = time();
        }
        Cache::inst()->set($attemptKey, $attempt);
    }

    /**
     * @param string $key
     * @return void
     */
    public function destroy(string $key): void
    {
        $this->session->remove($key);
    }
}