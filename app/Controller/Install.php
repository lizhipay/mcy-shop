<?php
declare (strict_types=1);

namespace App\Controller;

use App\Interceptor\PostDecrypt;
use App\Interceptor\Waf;
use App\Validator\Install\Database;
use App\Validator\Install\Finish;
use App\Validator\Install\Server;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Database\Dump;
use Kernel\Exception\JSONException;
use Kernel\Exception\ViewException;
use Kernel\Log\Const\Color;
use Kernel\Log\Log;
use Kernel\Util\Call;
use Kernel\Util\Config;
use Kernel\Util\Context;
use Kernel\Util\Shell;
use Kernel\Util\Str;
use Kernel\Util\System;
use Kernel\Waf\Filter;
use PDO;
use PDOException;


#[Interceptor(class: [PostDecrypt::class, Waf::class], type: Interceptor::API)]
class Install
{
    #[Inject]
    protected Request $request;

    #[Inject]
    protected Response $response;


    /**
     * 开始安装
     * @return Response
     * @throws ViewException
     */
    public function step(): Response
    {
        if (App::$install) {
            throw new ViewException("请勿重复安装");
        }


        $data = [
            "depend" => [
                "gd" => extension_loaded("gd"),
                "curl" => extension_loaded("curl"),
                "pdo" => extension_loaded("PDO"),
                "pdo_mysql" => extension_loaded("pdo_mysql"),
                "date" => extension_loaded("date"),
                "json" => extension_loaded("json"),
                "session" => extension_loaded("session"),
                "zip" => extension_loaded("zip"),
            ],
            "php_version" => phpversion(),
            "app_version" => App::$version,
            "shell_exec" => function_exists("shell_exec"),
            "cli" => App::$cli,
            "writable" => is_writable(BASE_PATH),
            "readable" => is_readable(BASE_PATH),
        ];


        $data['install'] = false;
        $checkCount = count($data['depend']) + 3;
        $check = 0;
        foreach ($data['depend'] as $ext) {
            if ($ext) {
                $check++;
            }
        }

        if (version_compare(phpversion(), "8.1.0", ">=") && $data['writable'] && $data['readable']) {
            $check += 3;
        }

        if ($check >= $checkCount) {
            $data['install'] = true;
        }

        $data["language"] = strtolower(Context::get(\Kernel\Language\Entity\Language::class)->preferred);

        return $this->response->render("Install.html", "开始安装", $data);
    }


    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [Database::class, ["dbHost", "dbName", "dbUser", "dbPass", "dbPrefix"]]
    ])]
    public function database(): Response
    {
        if (App::$install) {
            throw new JSONException("请勿重复安装");
        }

        $dbHost = explode(":", $this->request->post("db_host"));

        $host = $dbHost[0];
        $port = (isset($dbHost[1]) && $dbHost[1] != "3306") ? "port={$dbHost[1]};" : "";
        $db = $this->request->post("db_name");
        $user = $this->request->post("db_user");
        $pass = $this->request->post("db_pass");
        $prefix = $this->request->post("db_prefix");

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            new PDO("mysql:host=$host;{$port}dbname=$db;charset=utf8mb4", $user, $pass, $options);
            Config::set([
                'driver' => 'mysql',
                'host' => (isset($dbHost[1]) && $dbHost[1] != "3306") ? trim($this->request->post("db_host")) : $host,
                'database' => $db,
                'username' => $user,
                'password' => $pass,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => $prefix,
                'pool' => 10
            ], BASE_PATH . "/config/database.php");
            return $this->response->json(200, "连接成功");
        } catch (PDOException $e) {
            return $this->response->json(13, $e->getMessage());
        }
    }

    /**
     * @throws JSONException
     */
    #[Validator([
        [Server::class, ["cliName", "cliHost", "cliPort", "cliCpu"]]
    ])]
    public function server(): Response
    {
        if (App::$install) {
            throw new JSONException("请勿重复安装");
        }

        $host = $this->request->post("cli_host");
        $port = $this->request->post("cli_port", Filter::INTEGER);
        $cpu = $this->request->post("cli_cpu");
        $name = $this->request->post("cli_name");

        if (!System::checkPortAvailable($port, $host)) {
            throw new JSONException("端口被占用，请更换端口");
        }

        Config::set([
            'name' => $name,
            'host' => $host,
            'port' => $port,
            'cpu' => $cpu,
        ], BASE_PATH . "/config/server.php");

        return $this->response->json();
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Validator([
        [Finish::class, ['loginNickname', 'loginEmail', 'loginPassword', 'loginRePassword']]
    ])]
    public function finish(): Response
    {
        if (App::$install) {
            throw new JSONException("请勿重复安装");
        }
        $port = $this->request->post("cli_port", Filter::INTEGER);
        $host = $this->request->post("db_host");
        $db = $this->request->post("db_name");
        $user = $this->request->post("db_user");
        $pass = $this->request->post("db_pass");
        $prefix = $this->request->post("db_prefix");

        $loginNickname = $this->request->post("login_nickname");
        $loginEmail = $this->request->post("login_email");
        $loginPassword = $this->request->post("login_password");
        $salt = Str::generateRandStr(32);
        $password = Str::generatePassword($loginPassword, $salt);


        $file = BASE_PATH . "/kernel/Install/Install.sql";
        $sql = file_get_contents($file);
        if (!$sql) {
            throw new JSONException("安装文件已损坏，请下载最新版本进行安装");
        }

        $sql = str_replace('${prefix}', $prefix, $sql);
        $sql = str_replace('${email}', $loginEmail, $sql);
        $sql = str_replace('${password}', $password, $sql);
        $sql = str_replace('${nickname}', $loginNickname, $sql);
        $sql = str_replace('${salt}', $salt, $sql);

        if (file_put_contents($file . ".tmp", $sql) === false) {
            throw new JSONException("没有写入权限，请检查权限是否足够");
        }

        //导入数据库
        try {
            Dump::inst()->import($file . ".tmp", $host, $db, $user, $pass);
        } catch (\Throwable $e) {
            throw new JSONException("数据库出错，原因：" . $e->getMessage());
        }

        unlink($file . ".tmp");
        file_put_contents(BASE_PATH . '/kernel/Install/Lock', md5((string)time()));

        if (App::$cli) {
            //安装服务
            Log::inst()->stdout("开始安装system服务...", Color::YELLOW);
            Shell::inst()->exec(BASE_PATH . "/console.sh service.install");
            Log::inst()->stdout("安装已完成，反向代理地址：http://127.0.0.1:{$port}", Color::GREEN, true);
            Call::defer(function () {
                \Kernel\Service\App::inst()->shutdown();
            });
        }

        return $this->response->json(200, '安装完成');
    }
}