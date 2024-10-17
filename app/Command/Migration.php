<?php
declare (strict_types=1);

namespace App\Command;

use App\Model\User;
use App\Service\User\Level;
use App\Service\User\Lifetime;
use Kernel\Annotation\Inject;
use Kernel\Console\Command;
use Kernel\Log\Log;
use Kernel\Util\Decimal;
use Kernel\Util\Str;

class Migration extends Command
{

    #[Inject]
    private Level $level;

    #[Inject]
    private Lifetime $lifetime;

    /**
     * @param string $name
     * @return void
     */
    public function v3_user(string $name): void
    {
        $sql = file_get_contents(BASE_PATH . "/{$name}");
        preg_match_all("/\((\d+),\s*'([^']*)',\s*(NULL|'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'),\s*(NULL|'1[3-9]\d{9}'),\s*(NULL|\d+),\s*('[a-zA-Z0-9]{40}'),\s*('[a-zA-Z0-9]{32}'),\s*('[a-zA-Z0-9]{16}'),.*/", $sql, $matches);

        if (empty($matches[0])) {
            $this->error("没有找到用户数据");
        }

        $regex = "\((\d+),\s*'([^']*)',\s*(NULL|'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'),\s*(NULL|'1[3-9]\d{9}'),\s*(NULL|\d+),\s*'([a-zA-Z0-9]{40})',\s*'([a-zA-Z0-9]{32})',\s*'([a-zA-Z0-9]{16})',.*?,\s*'(\d+\.\d{2})',\s*'(\d+\.\d{2})',\s*(\d+),\s*'(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})',.*?,.*?,\s*'(.*?)',.*?,.*?,.*?,.*?,\s*(\d)";

        $total = count($matches[0]);
        $success = 0;
        $error = 0;

        $this->info("已检测到用户数：{$total}");

        foreach ($matches[0] as $match) {
            preg_match("/{$regex}/", $match, $result);
            if (count($result) == 15) {
                $username = $result[2];
                $email = trim($result[3], "'");
                $phone = trim($result[4], "'");
                $qq = trim($result[5], "'");
                $password = $result[6];
                $salt = $result[7];
                $appKey = $result[8];
                $balance = $result[9];
                $coin = $result[10];
                $integral = $result[11];
                $createTime = $result[12];
                $ip = $result[13];
                $status = $result[14];

                try {
                    $user = new User();
                    $user->username = $username;
                    $email != "NULL" && $user->email = $email;
                    $user->password = $password;
                    $user->salt = $salt;
                    $user->app_key = strtoupper(Str::generateRandStr(16));
                    $user->api_code = strtoupper(Str::generateRandStr(6));
                    $user->avatar = "/favicon.ico";
                    $user->integral = $integral;
                    $user->status = $status;
                    $user->balance = (new Decimal($balance))->add($coin)->getAmount();
                    $user->withdraw_amount = $coin;
                    $user->level_id = $this->level->getDefaultId(null);
                    $user->save();
                    $this->lifetime->create($user->id, $ip, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36");
                    $this->lifetime->update($user->id, "register_time", $createTime);
                    $this->success("会员:[{$username}] 迁移完成，资产：{$user->balance}，可提现：{$user->withdraw_amount}");
                    $success++;
                } catch (\Throwable $e) {
                    $this->error("会员:[{$username}]导入失败，原因：{$e->getMessage()}");
                    $error++;
                }
            }
        }


        $this->success("成功导入会员数: {$success}");
        $this->error("失败导入会员数: {$error}");
    }
}