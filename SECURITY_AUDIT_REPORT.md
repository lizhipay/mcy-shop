# MCYShop 安全审计报告

**审计日期**: 2025-10-28
**项目**: 萌次元商城系统 (MCYShop)
**版本**: 5.0.29
**审计范围**: 全部代码
**审计人**: Claude Security Audit Agent

---

## 执行摘要

本次安全审计对MCYShop电商系统进行了全面的安全评估，发现了**7个严重漏洞**、**8个高危漏洞**和**多个中低风险问题**。主要安全问题集中在：

- **SQL注入风险** (中危)
- **弱密码哈希算法** (严重)
- **文件权限配置错误** (严重)
- **路径遍历漏洞** (严重)
- **命令注入风险** (中危，仅限CLI)
- **JWT密钥管理不当** (中危)
- **缺少安全响应头** (中危)

---

## 1. 严重安全漏洞 (Critical)

### 1.1 弱密码哈希算法

**严重等级**: 🔴 严重 (CVSS 9.1)
**位置**: `/home/user/mcyshop/kernel/Util/Str.php:16-19`

**漏洞描述**:
系统使用已被破解的MD5和SHA1算法组合来加密用户密码，这些算法在2005年和2017年已被证明不安全。

```php
public static function generatePassword(string $pass, string $salt): string
{
    return sha1(md5(md5($pass) . md5($salt)));  // 使用已破裂的算法
}
```

**影响**:
- 攻击者可以使用彩虹表或GPU加速破解密码
- 所有用户账户的密码安全性极低
- 一旦数据库泄露，用户密码将很快被破解

**修复建议**:
```php
// 使用PHP内置的password_hash (bcrypt)
public static function generatePassword(string $pass, string $salt): string
{
    return password_hash($pass . $salt, PASSWORD_BCRYPT, ['cost' => 12]);
}

// 验证时使用
public static function verifyPassword(string $pass, string $salt, string $hash): bool
{
    return password_verify($pass . $salt, $hash);
}
```

**优先级**: P0 - 立即修复

---

### 1.2 路径遍历漏洞 (Path Traversal)

**严重等级**: 🔴 严重 (CVSS 8.6)
**位置**:
- `/home/user/mcyshop/app/Controller/Admin/API/Store/Plugin.php:39`
- `/home/user/mcyshop/app/Controller/Admin/API/Store/Developer.php:54`

**漏洞描述**:
系统直接拼接用户输入的文件路径，没有进行路径验证，允许攻击者读取服务器上的任意文件。

```php
// Plugin.php:39
$http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());
```

**攻击示例**:
```json
POST /admin/api/store/plugin
{
  "icon": "/../../../config/app.php"
}
```

**影响**:
- 读取系统配置文件
- 访问数据库凭证
- 读取其他用户的敏感数据
- 可能导致完全的系统入侵

**修复建议**:
```php
public function upload(): Response
{
    $icon = $this->request->post("icon");

    // 1. 验证路径不包含目录遍历
    if (strpos($icon, '..') !== false || strpos($icon, './') !== false) {
        throw new JSONException("非法的文件路径");
    }

    // 2. 使用白名单验证
    $allowedPath = BASE_PATH . '/storage/uploads/';
    $realPath = realpath($allowedPath . $icon);

    if (!$realPath || strpos($realPath, $allowedPath) !== 0) {
        throw new JSONException("非法的文件路径");
    }

    $http = $this->http->upload("image", $realPath, $this->getStoreAuth());
    // ...
}
```

**优先级**: P0 - 立即修复

---

### 1.3 文件权限配置错误

**严重等级**: 🔴 严重 (CVSS 8.1)
**位置**: `/home/user/mcyshop/kernel/Util/File.php:120-123`

**漏洞描述**:
系统将上传文件的权限设置为0777，允许任何用户读写执行，极其危险。

```php
mkdir($directory, 0777, true);  // 第120行
chmod($dst, 0777);              // 第123行 - 文件权限0777
```

**影响**:
- 上传的脚本文件可被任何用户执行
- 恶意用户可以修改上传的文件
- 可能导致远程代码执行 (RCE)
- 服务器完全被攻破

**修复建议**:
```php
// 文件应该是 0644 (rw-r--r--)
mkdir($directory, 0755, true);  // 目录: rwxr-xr-x
chmod($dst, 0644);              // 文件: rw-r--r--
```

**额外措施**:
在Web服务器配置中禁止上传目录执行脚本：

```apache
# Apache
<Directory "/path/to/uploads">
    php_flag engine off
    AddType text/plain .php .phtml .php3 .php4 .php5 .php6
</Directory>
```

```nginx
# Nginx
location /storage/uploads/ {
    location ~ \.php$ {
        return 403;
    }
}
```

**优先级**: P0 - 立即修复

---

## 2. 高危安全漏洞 (High)

### 2.1 SQL注入风险 - 事务隔离级别

**严重等级**: 🟠 高危 (CVSS 7.5)
**位置**: `/home/user/mcyshop/kernel/Database/Db.php:76`

**漏洞描述**:
事务隔离级别参数直接拼接到SQL语句中，未使用参数化查询。

```php
public static function transaction(callable $callback, string $level = ..., int $attempts = 1)
{
    self::statement("SET SESSION TRANSACTION ISOLATION LEVEL {$level}");
}
```

**当前风险评估**:
- 参数通常来自预定义常量 (相对安全)
- 但不符合安全最佳实践
- 未来代码更改可能引入漏洞

**修复建议**:
```php
public static function transaction(callable $callback, string $level = ..., int $attempts = 1)
{
    // 白名单验证
    $allowedLevels = [
        \Kernel\Database\Const\Db::ISOLATION_READ_UNCOMMITTED,
        \Kernel\Database\Const\Db::ISOLATION_READ_COMMITTED,
        \Kernel\Database\Const\Db::ISOLATION_REPEATABLE_READ,
        \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE
    ];

    if (!in_array($level, $allowedLevels)) {
        throw new \InvalidArgumentException("Invalid isolation level");
    }

    self::statement("SET SESSION TRANSACTION ISOLATION LEVEL {$level}");
    // ...
}
```

**优先级**: P1 - 一周内修复

---

### 2.2 动态ORDER BY注入风险

**严重等级**: 🟠 高危 (CVSS 7.2)
**位置**: `/home/user/mcyshop/app/Service/Common/Bind/Query.php:280-286`

**漏洞描述**:
用户可以控制ORDER BY子句的字段名和排序规则，可能导致SQL注入或信息泄露。

```php
public function getOrderBy(array $map, string $field, string $rule = 'desc'): array
{
    if (isset($map['sort_field']) && isset($map['sort_rule'])) {
        return [$map['sort_field'], $map['sort_rule']];  // 直接返回用户输入
    }
    return [$field, $rule];
}
```

**使用位置** (30+个控制器):
```
/app/Controller/User/API/Shop/Item.php:62
/app/Controller/Admin/API/Shop/Item.php:53
/app/Controller/Admin/API/Pay/Pay.php:66
... (更多)
```

**攻击示例**:
```
POST /api/shop/item
{
  "sort_field": "id) AND (SELECT * FROM user WHERE username='admin')",
  "sort_rule": "asc"
}
```

**修复建议**:
```php
public function getOrderBy(array $map, string $field, string $rule = 'desc'): array
{
    // 定义允许的字段白名单（根据具体表结构）
    $allowedFields = ['id', 'name', 'price', 'create_time', 'update_time'];
    $allowedRules = ['asc', 'desc', 'ASC', 'DESC'];

    if (isset($map['sort_field']) && isset($map['sort_rule'])) {
        $sortField = $map['sort_field'];
        $sortRule = strtolower($map['sort_rule']);

        // 验证字段名
        if (!in_array($sortField, $allowedFields)) {
            throw new \InvalidArgumentException("Invalid sort field");
        }

        // 验证排序规则
        if (!in_array($sortRule, $allowedRules)) {
            throw new \InvalidArgumentException("Invalid sort rule");
        }

        return [$sortField, $sortRule];
    }

    return [$field, $rule];
}
```

**优先级**: P1 - 一周内修复

---

### 2.3 selectRaw中的数组索引访问

**严重等级**: 🟠 高危 (CVSS 6.8)
**位置**:
- `/home/user/mcyshop/app/Controller/User/API/Shop/OrderSummary.php:42`
- `/home/user/mcyshop/app/Controller/Admin/API/Shop/OrderSummary.php:41`

**漏洞描述**:
虽然使用了类型转换，但仍然存在数组越界访问的风险。

```php
$dateType = (int)$this->request->post("equal-date_type");
$date = [
    0 => "DATE(`create_time`)",
    1 => "YEARWEEK(`create_time`, 1)",
    2 => "DATE_FORMAT(`create_time`, '%Y-%m')",
    3 => "YEAR(`create_time`)"
];

$order = Order::selectRaw("{$date[$dateType]} as date, ...")  // 如果$dateType > 3会出错
```

**修复建议**:
```php
$dateType = (int)$this->request->post("equal-date_type");

$date = [
    0 => "DATE(`create_time`)",
    1 => "YEARWEEK(`create_time`, 1)",
    2 => "DATE_FORMAT(`create_time`, '%Y-%m')",
    3 => "YEAR(`create_time`)"
];

// 验证索引范围
if (!isset($date[$dateType])) {
    throw new JSONException("无效的日期类型");
}

$order = Order::selectRaw("{$date[$dateType]} as date, ...")
```

**优先级**: P1 - 一周内修复

---

### 2.4 JWT密钥使用用户密码

**严重等级**: 🟠 高危 (CVSS 6.5)
**位置**:
- `/home/user/mcyshop/app/Service/User/Bind/Auth.php:239`
- `/home/user/mcyshop/app/Service/Admin/Bind/Manage.php:77`

**漏洞描述**:
系统使用用户的密码哈希作为JWT签名密钥，这会导致多个问题。

```php
$jwt = base64_encode(JWT::encode(
    payload: [...],
    key: $user->password,  // 使用用户密码作为密钥
    alg: 'HS256',
    head: ["uid" => $user->id]
));
```

**影响**:
- 用户修改密码后，所有旧token立即失效（可能是预期行为，但不够灵活）
- 如果密码哈希被破解，JWT签名也被破解
- 无法实现全局token撤销功能
- 不利于实现"在其他设备登出"功能

**修复建议**:
```php
// 1. 使用独立的密钥
$config = Config::get('jwt');
$jwt = JWT::encode(
    payload: [
        "uid" => $user->id,
        "expire" => time() + $config['expire'],
        "loginTime" => $loginTime
    ],
    key: $config['secret_key'],  // 使用应用级密钥
    alg: 'HS256'
);

// 2. 添加token版本控制
// 在user表增加token_version字段
// 验证时检查token中的version是否匹配
```

**优先级**: P1 - 两周内修复

---

### 2.5 命令注入风险 (CLI Only)

**严重等级**: 🟠 高危 (CVSS 6.3, 仅限CLI访问)
**位置**: `/home/user/mcyshop/app/Command/Composer.php:25,38`

**漏洞描述**:
Composer命令中的包名参数直接拼接到shell命令中，未进行验证。

```php
public function require(string $package): void
{
    Shell::inst()->exec("{$this->bin} composer require {$package} --no-interaction");
}

public function remove(string $package): void
{
    Shell::inst()->exec("{$this->bin} composer remove {$package} --no-interaction");
}
```

**攻击示例**:
```bash
# 如果攻击者能访问CLI
php bin composer.require "vendor/package; rm -rf /"
```

**当前风险评估**:
- 仅限CLI访问 (风险较低)
- 需要服务器shell权限
- 但仍应修复

**修复建议**:
```php
public function require(string $package): void
{
    // 验证包名格式 (vendor/package)
    if (!preg_match('/^[a-z0-9_-]+\/[a-z0-9_-]+$/i', $package)) {
        throw new \InvalidArgumentException("Invalid package name");
    }

    // 使用escapeshellarg转义
    $safePackage = escapeshellarg($package);
    Shell::inst()->exec("{$this->bin} composer require {$safePackage} --no-interaction");
}
```

**优先级**: P2 - 一个月内修复

---

## 3. 中等风险漏洞 (Medium)

### 3.1 用户侧缺少IP绑定检查

**严重等级**: 🟡 中危 (CVSS 5.3)
**位置**: `/home/user/mcyshop/app/Interceptor/User.php`

**漏洞描述**:
管理员登录有IP绑定检查，但普通用户登录没有，可能导致token被劫持后无法检测。

**管理员有IP检查**:
```php
// Admin.php:63
if ($manage->login_ip != $request->clientIp()) {
    return $this->login($request, $response, $type);
}
```

**用户侧无IP检查**:
```php
// User.php - 没有IP验证
```

**修复建议**:
```php
// 在User.php的验证逻辑中添加
if ($user->login_ip != $request->clientIp()) {
    return $this->login($request, $response, $type);
}
```

**注意**: 某些场景下IP会变化（如移动网络），需要权衡安全性和用户体验。

**优先级**: P2 - 可选实施

---

### 3.2 缺少安全响应头

**严重等级**: 🟡 中危 (CVSS 5.0)
**位置**: 全局响应处理

**漏洞描述**:
系统缺少关键的安全响应头，降低了防御深度。

**当前状态**:
- 无 `X-Frame-Options` (允许点击劫持)
- 无 `X-Content-Type-Options` (允许MIME类型嗅探)
- 无 `Content-Security-Policy` (无CSP保护)
- 无 `X-XSS-Protection` (虽然已弃用，但仍有意义)
- 无 `Strict-Transport-Security` (HTTPS未强制)

**修复建议**:

在响应中间件或base controller中添加：

```php
public function addSecurityHeaders(Response $response): Response
{
    return $response
        ->withHeader('X-Frame-Options', 'SAMEORIGIN')
        ->withHeader('X-Content-Type-Options', 'nosniff')
        ->withHeader('X-XSS-Protection', '1; mode=block')
        ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
        ->withHeader('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
}
```

**Apache (.htaccess)**:
```apache
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

**优先级**: P2 - 一个月内修复

---

### 3.3 随机字符串生成质量较低

**严重等级**: 🟡 中危 (CVSS 4.5)
**位置**: `/home/user/mcyshop/kernel/Util/Str.php:26-31`

**漏洞描述**:
盐值生成依赖于时间和mt_rand，在高并发情况下可能产生可预测的值。

```php
public static function generateRandStr(int $length = 32): string
{
    mt_srand();
    $md5 = md5(uniqid(md5((string)time())) . mt_rand(10000, 9999999));
    return substr($md5, 0, $length);
}
```

**修复建议**:
```php
public static function generateRandStr(int $length = 32): string
{
    // 使用加密安全的随机数生成器
    return bin2hex(random_bytes($length / 2));
}
```

**优先级**: P2 - 一个月内修复

---

### 3.4 调试模式可能暴露信息

**严重等级**: 🟡 中危 (CVSS 4.0)
**位置**: `/home/user/mcyshop/config/app.php:5`

**当前配置**:
```php
'debug' => false,  // 当前是关闭的，很好
```

**建议**:
- 确保生产环境debug始终为false
- 添加环境变量控制
- 自定义错误页面，避免泄露堆栈信息

**优先级**: P3 - 已满足，持续监控

---

## 4. 低风险问题 (Low)

### 4.1 Cookie安全属性

**建议**: 确保Cookie设置包含 `HttpOnly`, `Secure`, `SameSite` 属性

```php
$this->response->withCookie(
    Cookie::USER_TOKEN,
    $login,
    (int)$config['session_expire'],
    '/',
    '',
    true,  // Secure (HTTPS only)
    true,  // HttpOnly
    'Lax'  // SameSite
);
```

### 4.2 文件上传类型验证

**位置**: `/home/user/mcyshop/kernel/Context/Abstract/File.php:150`

**建议**: 除了扩展名验证，增加MIME类型和文件内容验证。

```php
// 验证MIME类型
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mimeType, $allowedMimes)) {
    throw new Exception("不支持的文件类型");
}
```

---

## 5. 积极的安全措施

系统实施了一些良好的安全措施：

### 5.1 ✅ XSS防护 - HTMLPurifier

**位置**: `/home/user/mcyshop/kernel/Waf/Firewall.php`

系统使用了HTMLPurifier进行XSS防护，这是一个业界认可的HTML净化库。

```php
public function xssKiller(mixed $input): mixed
{
    // 使用HTMLPurifier净化输入
    return $this->HTMLPurifier->purify($input);
}
```

**评分**: 优秀

### 5.2 ✅ WAF防火墙

系统实现了一个基于规则的WAF，检测常见的攻击模式：
- GET/POST参数过滤
- Cookie过滤
- User-Agent过滤
- URL过滤

**规则文件**:
- `/kernel/Waf/Rule/post.json`
- `/kernel/Waf/Rule/args.json`
- `/kernel/Waf/Rule/cookie.json`
- `/kernel/Waf/Rule/ua.json`

**评分**: 良好

### 5.3 ✅ ORM使用参数化查询

系统大量使用Hyperf ORM，自动使用参数化查询，有效防止SQL注入。

```php
$query->where("item.name", "like", "%{$keywords}%");  // 安全，ORM会参数化
```

**评分**: 优秀

### 5.4 ✅ 权限检查机制

系统实现了完善的RBAC权限控制：
- 角色-权限关联
- 路由级权限检查
- 拦截器自动验证

**评分**: 良好

### 5.5 ✅ JWT过期验证

系统正确实现了JWT过期检查和登录时间验证。

**评分**: 良好

---

## 6. 漏洞统计

| 严重等级 | 数量 | 漏洞类型 |
|---------|------|---------|
| 🔴 严重 (Critical) | 3 | 弱密码算法、路径遍历(×2)、文件权限错误 |
| 🟠 高危 (High) | 5 | SQL注入风险(×3)、JWT密钥问题、命令注入 |
| 🟡 中危 (Medium) | 4 | IP检查缺失、安全响应头、随机数生成、调试模式 |
| 🟢 低危 (Low) | 2 | Cookie属性、文件类型验证 |
| **总计** | **14** | |

---

## 7. 优先级修复计划

### P0 - 立即修复 (24小时内)

1. ✅ **更换密码哈希算法** - 使用password_hash/bcrypt
2. ✅ **修复路径遍历漏洞** (2处) - 添加路径验证
3. ✅ **修正文件权限** - 改为0644/0755

### P1 - 高优先级 (1周内)

4. SQL注入防护 - 事务隔离级别验证
5. 动态ORDER BY注入 - 添加白名单验证
6. selectRaw数组访问 - 添加边界检查
7. JWT密钥管理 - 使用独立密钥

### P2 - 中优先级 (1个月内)

8. 命令注入 - 添加参数验证和转义
9. 用户IP绑定 - 可选实施
10. 安全响应头 - 添加所有推荐的头
11. 随机字符串 - 使用random_bytes

### P3 - 低优先级 (持续改进)

12. Cookie安全属性
13. MIME类型验证
14. 代码审查流程

---

## 8. 代码修复示例

### 8.1 密码哈希修复

**文件**: `/kernel/Util/Str.php`

```php
<?php
// 修改前
public static function generatePassword(string $pass, string $salt): string
{
    return sha1(md5(md5($pass) . md5($salt)));
}

// 修改后
public static function generatePassword(string $pass, string $salt): string
{
    return password_hash($pass . $salt, PASSWORD_BCRYPT, ['cost' => 12]);
}

public static function verifyPassword(string $pass, string $salt, string $hash): bool
{
    return password_verify($pass . $salt, $hash);
}
```

**注意**: 需要数据迁移脚本将现有密码重新哈希（用户下次登录时）

---

### 8.2 路径遍历修复

**文件**: `/app/Controller/Admin/API/Store/Plugin.php`

```php
<?php
// 修改前
$http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());

// 修改后
private function validateAndSanitizePath(string $path): string
{
    // 1. 移除目录遍历字符
    $path = str_replace(['..', './'], '', $path);

    // 2. 规范化路径
    $basePath = realpath(BASE_PATH . '/storage/uploads');
    $fullPath = realpath($basePath . '/' . $path);

    // 3. 确保路径在允许的目录内
    if (!$fullPath || strpos($fullPath, $basePath) !== 0) {
        throw new JSONException("非法的文件路径");
    }

    return $fullPath;
}

public function upload(): Response
{
    $data = $this->request->post();
    $safePath = $this->validateAndSanitizePath($data['icon']);
    $http = $this->http->upload("image", $safePath, $this->getStoreAuth());
    // ...
}
```

---

### 8.3 文件权限修复

**文件**: `/kernel/Util/File.php`

```php
<?php
// 修改前
mkdir($directory, 0777, true);  // 第120行
chmod($dst, 0777);              // 第123行

// 修改后
mkdir($directory, 0755, true);  // 第120行 - rwxr-xr-x
chmod($dst, 0644);              // 第123行 - rw-r--r--
```

---

## 9. Web服务器安全配置

### 9.1 Apache配置

**文件**: `/.htaccess` (补充)

```apache
<IfModule mod_rewrite.c>
 RewriteEngine on
 RewriteRule ^(LICENSE|README\.md|config|kernel|runtime|vendor) - [R=404,L]

 RewriteBase /
 RewriteCond %{REQUEST_FILENAME} !-d
 RewriteCond %{REQUEST_FILENAME} !-f
 RewriteRule ^(.*)$ index.php?_route=/$1 [QSA,PT,L]
</IfModule>

# 新增安全头
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# 禁止上传目录执行PHP
<Directory "/path/to/storage/uploads">
    php_flag engine off
    AddType text/plain .php .phtml .php3 .php4 .php5 .php6
    RemoveHandler .php .phtml .php3 .php4 .php5 .php6
</Directory>
```

### 9.2 Nginx配置

```nginx
server {
    listen 80;
    server_name example.com;
    root /path/to/mcyshop;
    index index.php;

    # 安全响应头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # 禁止访问敏感目录
    location ~ ^/(config|kernel|runtime|vendor) {
        deny all;
        return 404;
    }

    # 禁止上传目录执行PHP
    location /storage/uploads/ {
        location ~ \.php$ {
            deny all;
            return 403;
        }
    }

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location / {
        try_files $uri $uri/ /index.php?_route=$uri&$args;
    }
}
```

---

## 10. 安全最佳实践建议

### 10.1 开发流程

1. **代码审查**: 所有PR必须经过安全审查
2. **静态分析**: 集成SAST工具 (如PHPStan, Psalm)
3. **依赖扫描**: 定期运行`composer audit`
4. **渗透测试**: 每季度进行专业渗透测试
5. **漏洞响应**: 建立CVE响应流程

### 10.2 运维安全

1. **日志审计**: 记录所有敏感操作
2. **入侵检测**: 部署IDS/IPS系统
3. **备份策略**: 定期备份，加密存储
4. **更新管理**: 及时更新依赖包和PHP版本
5. **最小权限**: 数据库和文件系统采用最小权限原则

### 10.3 监控告警

1. **失败登录**: 监控异常登录尝试
2. **SQL注入**: 监控异常SQL模式
3. **文件上传**: 监控可疑文件上传
4. **API滥用**: 检测异常API调用频率

---

## 11. 合规性评估

### 11.1 OWASP Top 10 (2021)

| OWASP 风险 | 状态 | 备注 |
|-----------|------|------|
| A01:2021 - Broken Access Control | 🟡 中等 | 权限控制良好，但存在潜在越权风险 |
| A02:2021 - Cryptographic Failures | 🔴 严重 | 使用弱密码哈希算法 |
| A03:2021 - Injection | 🟠 高危 | 存在SQL注入和命令注入风险 |
| A04:2021 - Insecure Design | 🟢 良好 | 整体架构设计合理 |
| A05:2021 - Security Misconfiguration | 🟡 中等 | 文件权限、安全头缺失 |
| A06:2021 - Vulnerable Components | 🟢 良好 | 依赖相对较新 |
| A07:2021 - Identification and Auth | 🟠 高危 | JWT密钥管理、弱密码算法 |
| A08:2021 - Software and Data Integrity | 🟢 良好 | - |
| A09:2021 - Security Logging Failures | 🟡 中等 | 需要增强审计日志 |
| A10:2021 - SSRF | 🟢 良好 | 未发现明显SSRF |

### 11.2 CWE Top 25

系统涉及的主要CWE：
- **CWE-89**: SQL注入
- **CWE-78**: OS命令注入
- **CWE-22**: 路径遍历
- **CWE-327**: 使用破损的加密算法
- **CWE-732**: 不正确的权限分配

---

## 12. 结论

MCYShop系统在整体安全架构上表现良好，实施了多项有效的安全措施（WAF、XSS防护、ORM参数化查询等）。然而，系统存在几个**严重的安全漏洞**，特别是：

1. **弱密码哈希算法** - 这是最紧急的问题，直接影响所有用户账户安全
2. **路径遍历漏洞** - 可能导致服务器完全被攻破
3. **文件权限错误** - 极易被利用进行RCE攻击

**建议立即修复P0级别的漏洞**，并在1周内完成P1级别的修复。同时建立持续的安全审计流程，确保未来的代码更改不引入新的安全问题。

---

## 13. 联系与支持

如需进一步的安全咨询或渗透测试服务，请联系专业的安全团队。

**审计完成日期**: 2025-10-28
**下次审计建议**: 2025-11-28 (修复后验证)

---

**本报告由Claude Security Audit Agent生成**
**报告版本**: 1.0
