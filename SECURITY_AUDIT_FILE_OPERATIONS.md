# MCYShop 文件上传与文件操作安全审计报告

## 执行摘要

本审计涉及深入分析 MCYShop 项目中所有文件上传、文件操作和路径处理相关的代码。发现了**严重的路径遍历漏洞（2处）**、**文件权限配置问题**和**部分路径验证缺陷**。

---

## 一、关键安全发现

### 1. 严重级别：路径遍历漏洞（Path Traversal）

#### 漏洞位置 1：
**文件**: `/home/user/mcyshop/app/Controller/Admin/API/Store/Plugin.php`
**行号**: 第 35-43 行
**代码**：
```php
public function save(): Response
{
    $data = $this->request->post();
    //上传文件
    $http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());
    $data['icon'] = $http->data['url'];
    $this->plugin->createOrUpdate($data, $this->getStoreAuth());
    return $this->json();
}
```

**漏洞分析**：
- 第 39 行：`BASE_PATH . $data['icon']` 直接拼接用户输入
- `$data['icon']` 来自 `$this->request->post()`，完全由用户控制
- 攻击者可以使用 `"icon": "/../../../config/app.php"` 等路径遍历序列
- 上传函数会尝试读取该路径的文件（通过 `fopen`）
- **攻击场景**：读取任意敏感文件，如配置文件、密钥等

#### 漏洞位置 2：
**文件**: `/home/user/mcyshop/app/Controller/Admin/API/Store/Developer.php`
**行号**: 第 48-60 行
**代码**：
```php
public function createOrUpdatePlugin(): Response
{
    $data = $this->request->post();

    if (!isset($data['id'])) {
        //上传文件
        $http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());
        $data['icon'] = $http->data['url'];
    }

    $this->developer->createOrUpdatePlugin($data, $this->getStoreAuth());
    return $this->json();
}
```

**漏洞分析**：
- 完全相同的漏洞模式
- 第 54 行：同样的路径遍历问题
- 影响范围：开发者插件创建/更新功能

#### 上传函数调用链：
**上传服务文件**: `/home/user/mcyshop/app/Service/Store/Bind/Http.php`
**行号**: 第 230-262 行
```php
public function upload(string $mime, string $file, ?Authentication $authentication = null): \App\Entity\Store\Http
{
    // ... 其他代码
    try {
        $response = $this->httpClient->request("POST", $this->getBaseUrl() . "/user/upload?mime={$mime}", [
            "verify" => false,
            "multipart" => [
                [
                    "name" => "file",
                    "contents" => fopen($file, "r")  // 第 246 行：危险！
                ]
            ],
            "headers" => $headers
        ]);
    } catch (\Throwable $e) {
        throw new ServiceException("文件上传失败#0");
    }
    // ...
}
```

**危害**: `fopen($file, "r")` 会尝试打开用户指定路径的文件，可导致：
- 任意文件读取
- 信息泄露
- 进一步的攻击链

---

### 2. 中等级别：文件权限配置问题

#### 问题位置 1：
**文件**: `/home/user/mcyshop/kernel/Context/Abstract/File.php`
**行号**: 第 158-174 行

```php
public function save(string $path, array $ext = [...], int $size = 10240, string $dir = BASE_PATH): string
{
    // ... 验证代码
    $_tmpDir = $dir . $path . date("Y-m-d/", time());
    $unique = $path . date("Y-m-d/") . Str::generateRandStr(32) . "." . $this->getSuffix();

    if (!is_dir($_tmpDir)) {
        mkdir($_tmpDir, 0777, true);  // 第 164 行：权限过高！
    }

    if (!copy(from: $this->getTmp(), to: $dir . $unique)) {  // 第 167 行
        throw new JSONException("文件上传失败，服务器出错原因：{$path} 无写入权限");
    }
    
    return $unique;
}
```

**问题**：
- `mkdir` 使用权限 `0777`，过于宽松
- 建议改为 `0755`
- 允许所有用户对目录进行读/写/执行

#### 问题位置 2：
**文件**: `/home/user/mcyshop/kernel/Util/File.php`
**行号**: 第 113-125 行

```php
public static function copy(string $src, string $dst): bool
{
    if (!is_file($src)) {
        return false;
    }

    $directory = dirname($dst);
    !is_dir($directory) && (mkdir($directory, 0777, true));  // 第 120 行：权限问题

    $state = copy($src, $dst);
    chmod($dst, 0777);  // 第 123 行：更危险！给文件 0777 权限
    return $state;
}
```

**问题**：
- 第 120 行：目录权限设置为 `0777`
- 第 123 行：**更严重** - 文件权限设置为 `0777`
- 这允许任何用户修改/删除上传的文件
- 可导致上传的恶意脚本被修改或执行

#### 问题位置 3：
**文件**: `/home/user/mcyshop/app/Service/Common/Bind/Image.php`
**行号**: 第 74-81 行

```php
$pathInfo = pathinfo($imageDiskPath);
$thumbnailDirectory = $pathInfo['dirname'] . '/thumb/';

if (!file_exists($thumbnailDirectory)) {
    if (!mkdir($thumbnailDirectory, 0755, true)) {
        return false;
    }
}
```

**比较**：
- 这里使用的是 `0755`（较为安全）
- 但前两个地方都使用 `0777`（不一致且危险）

---

## 二、文件上传处理流程分析

### 2.1 管理员文件上传接口

**文件**: `/home/user/mcyshop/app/Controller/Admin/API/Upload.php`
**行号**: 第 18-76 行

```php
public function main(): Response
{
    $type = strtolower((string)$this->request->get("mime"));
    $thumbHeight = (int)$this->request->get("thumb_height");
    if (!in_array($type, self::MIME)) {
        throw new JSONException("mime not supported");
    }
    
    // MIME 类型白名单
    const MIME = ['image', 'video', 'doc', 'other'];
    
    $upload = new \Kernel\Context\Upload("file");
    
    // 获取最大上传大小配置
    $config = $this->config->getMainConfig("site");
    $maxSize = 20480;
    if (isset($config['max_upload_size']) && $config['max_upload_size'] > 0) {
        $maxSize = (int)$config['max_upload_size'];
    }
    
    // 上传文件到指定目录
    $fileName = $upload->save(path: "/assets/static/general/{$type}/", size: $maxSize);
    
    // 去重逻辑
    if ($tmp = $this->upload->get(md5_file(BASE_PATH . $fileName))) {
        File::remove(BASE_PATH . $fileName);
        $fileName = $tmp;
    } else {
        $this->upload->add($fileName, $type);
    }
    
    // 生成缩略图
    if ($type == self::MIME[0] && $thumbHeight > 0) {
        $imageFile = BASE_PATH . $fileName;
        $thumbUrl = $this->image->createThumbnail($fileName, $thumbHeight);
        if (!$thumbUrl) {
            if (is_file($imageFile)) {
                $this->upload->remove($fileName);
            }
            throw new JSONException("图片上传失败，原因：生成缩略图失败");
        }
        $append['thumb_url'] = $thumbUrl;
    }
    
    return $this->response->json(data: ["url" => $fileName, "append" => $append]);
}
```

**安全特性**：
- ✓ 文件类型白名单验证（4种：image, video, doc, other）
- ✓ 上传大小限制（可配置，默认 20480 KB）
- ✓ 随机文件名生成（32 位随机字符 + 后缀）
- ✓ 重复文件去重（基于 MD5 哈希）
- ✓ 权限检查（Admin 拦截器）
- ✗ 目录权限设置为 0777（不安全）
- ✗ 没有细粒度的文件类型验证

### 2.2 用户文件上传接口

**文件**: `/home/user/mcyshop/app/Controller/User/API/Upload.php`
**行号**: 第 39-78 行

```php
public function main(): Response
{
    $type = strtolower((string)$this->request->get("mime"));
    $thumbHeight = (int)$this->request->get("thumb_height");
    
    if (!in_array($type, self::MIME)) {
        throw new JSONException("mime not supported");
    }
    
    $upload = new \Kernel\Context\Upload("file");
    
    // 获取配置
    $config = $this->config->getMainConfig("site");
    $maxSize = 20480;
    if (isset($config['max_upload_size']) && $config['max_upload_size'] > 0) {
        $maxSize = (int)$config['max_upload_size'];
    }
    
    // 上传到用户特定目录
    $fileName = $upload->save(path: "/assets/static/{$this->getUser()->id}/{$type}/", size: $maxSize);
    
    // 去重
    if ($tmp = $this->upload->get(md5_file(BASE_PATH . $fileName))) {
        File::remove(BASE_PATH . $fileName);
        $fileName = $tmp;
    } else {
        $this->upload->add($fileName, $type, $this->getUser()?->id);
    }
    
    // 缩略图
    if ($type == self::MIME[0] && $thumbHeight > 0) {
        $imageFile = BASE_PATH . $fileName;
        $thumbUrl = $this->image->createThumbnail(imagePath: $fileName, newHeight: $thumbHeight);
        if (!$thumbUrl) {
            if (is_file($imageFile)) {
                unlink($imageFile);  // 第 70 行：直接使用 unlink（相对安全）
            }
            throw new JSONException("图片上传失败，原因：生成缩略图失败");
        }
        $append['thumb_url'] = $thumbUrl;
    }
    
    return $this->response->json(data: ["url" => $fileName, "append" => $append]);
}
```

**安全特性**：
- ✓ 与管理员接口相同的验证机制
- ✓ 用户隔离：上传路径包含用户 ID（`/assets/static/{$this->getUser()->id}/`）
- ✓ 权限检查（User 拦截器）
- ✗ 同样的权限问题

### 2.3 上传文件删除接口

**文件**: `/home/user/mcyshop/app/Controller/Admin/API/Upload/Upload.php`
**行号**: 第 85-95 行

```php
public function del(): Response
{
    $list = (array)$this->request->post("list");
    if (count($list) > 0) {
        $uploads = \App\Model\Upload::query()->whereIn("id", $list)->get();
        foreach ($uploads as $upload) {
            $this->upload->remove($upload->path);  // 删除文件
        }
    }
    return $this->json();
}
```

**安全分析**：
- ✓ 权限检查（Admin 拦截器）
- ✓ 删除前查询数据库验证（不是直接路径）
- ✗ 没有验证用户权限（admin 可删除任何文件）
- ✗ 没有恢复机制

**文件**: `/home/user/mcyshop/app/Controller/User/API/Upload/Upload.php`
**行号**: 第 37-58 行

```php
public function get(): Response
{
    $map = $this->request->post();
    $get = new Get(Model::class);
    $get->setWhere($map);
    $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
    $get->setOrderBy("id", "desc");
    
    $data = $this->query->get($get, function (Builder $builder) use ($map) {
        return $builder->where("user_id", $this->getUser()->id);
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
```

**安全分析**：
- ✓ 查询限制：仅显示当前用户的文件（`where("user_id", $this->getUser()->id)`）
- ✓ 路径信息通过 `pathinfo()` 提取（安全）
- ✓ 缩略图存在性检查
- ✗ **没有 del() 方法** - 用户无法删除自己的文件？

---

## 三、文件操作核心实现

### 3.1 文件上传核心类

**文件**: `/home/user/mcyshop/kernel/Context/Abstract/File.php`
**行号**: 第 144-174 行

```php
public function save(string $path, array $ext = ['jpg', 'png', ...], int $size = 10240, string $dir = BASE_PATH): string
{
    // 验证上传错误
    if ($this->getError() > 0) {
        throw new JSONException("文件上传失败，代码：" . $this->getError(), $this->getError());
    }
    
    // 验证文件扩展名（白名单）
    if (!in_array(strtolower($this->getSuffix()), $ext)) {
        throw new JSONException("您上传的文件类型不支持");
    }
    
    // 验证文件大小
    if ($size < $this->getSize() / 1024) {
        throw new JSONException("您的文件过大，当前上传限制：" . $size . "KB");
    }
    
    // 生成目录和唯一文件名
    $_tmpDir = $dir . $path . date("Y-m-d/", time());
    $unique = $path . date("Y-m-d/") . Str::generateRandStr(32) . "." . $this->getSuffix();
    
    // 插件 Hook
    if ($hook = Plugin::instance()->unsafeHook(...)) return $hook;
    
    // 创建目录（权限问题！）
    if (!is_dir($_tmpDir)) {
        mkdir($_tmpDir, 0777, true);
    }
    
    // 复制文件（权限问题！）
    if (!copy(from: $this->getTmp(), to: $dir . $unique)) {
        throw new JSONException("文件上传失败，服务器出错原因：{$path} 无写入权限");
    }
    
    // 插件 Hook
    if ($hook = Plugin::instance()->unsafeHook(...)) return $hook;
    
    return $unique;
}
```

**关键安全点**：
- ✓ 文件扩展名白名单验证
- ✓ 文件大小验证
- ✓ 随机文件名（32 位随机字符）
- ✓ 日期目录组织（年-月-日）
- ✓ 上传错误代码检查
- ✗ 目录权限 0777（太宽松）
- ✗ 没有 MIME 类型验证

### 3.2 文件上传服务（缓存/去重）

**文件**: `/home/user/mcyshop/app/Service/Common/Bind/Upload.php`
**行号**: 第 18-59 行

```php
public function add(string $path, string $type, ?int $userId = null): void
{
    if (!is_file(BASE_PATH . $path)) {
        return;
    }
    $upload = new \App\Model\Upload();
    $upload->hash = md5_file(BASE_PATH . $path);
    $upload->type = $type;
    $upload->path = $path;
    $upload->create_time = Date::current();
    $userId && ($upload->user_id = $userId);
    $upload->save();
}

public function get(string $hash): ?string
{
    return (\App\Model\Upload::query()->where("hash", $hash)->first())?->path;
}

public function remove(string $path): void
{
    if (!is_file(BASE_PATH . $path)) {
        return;
    }
    
    $baseImagePathInfo = pathinfo($path);
    $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];
    
    $hash = md5_file(BASE_PATH . $path);
    \App\Model\Upload::query()->where("hash", $hash)->delete();
    File::remove(BASE_PATH . $path, BASE_PATH . $thumbPath);
}
```

**去重机制**：
- 使用 MD5 哈希检测重复文件
- 同一内容的文件只存储一次
- 节省存储空间

**删除机制**：
- 删除缓略图
- 从数据库移除记录
- ✗ 没有权限验证（调用者责任）

### 3.3 缩略图生成

**文件**: `/home/user/mcyshop/app/Service/Common/Bind/Image.php`
**行号**: 第 26-120 行

```php
public function createThumbnail(string $imagePath, int $newHeight, string $basePath = BASE_PATH): bool|string
{
    $baseImagePathInfo = pathinfo($imagePath);
    $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];
    
    // 如果缩略图已存在，返回
    if (is_file($basePath . $thumbPath)) {
        return $thumbPath;
    }
    
    $imageDiskPath = $basePath . $imagePath;
    
    // 获取图片尺寸
    list($width, $height) = getimagesize($imageDiskPath);
    
    if ($newHeight >= $height) {
        return $imagePath;
    }
    
    // 检查图片类型
    $imageType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
    
    // 根据类型加载图片资源
    $source = null;
    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            $source = @imagecreatefromjpeg($imageDiskPath);
            break;
        case 'gif':
            $source = @imagecreatefromgif($imageDiskPath);
            break;
        case 'png':
            $source = @imagecreatefrompng($imageDiskPath);
            break;
        case 'webp':
            $source = @imagecreatefromwebp($imageDiskPath);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // 计算缩略图宽度
    $newWidth = (int)($width / $height * $newHeight);
    
    // 创建缩略图
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // 创建缩略图目录
    $pathInfo = pathinfo($imageDiskPath);
    $thumbnailDirectory = $pathInfo['dirname'] . '/thumb/';
    
    if (!file_exists($thumbnailDirectory)) {
        if (!mkdir($thumbnailDirectory, 0755, true)) {  // ✓ 0755 是好的
            return false;
        }
    }
    
    // 保存缩略图
    $thumbnailPath = $thumbnailDirectory . $pathInfo['basename'];
    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            if (!imagejpeg($thumb, $thumbnailPath)) {
                imagedestroy($thumb);
                imagedestroy($source);
                return false;
            }
            break;
        // ... 其他格式
    }
    
    // 清理资源
    imagedestroy($thumb);
    imagedestroy($source);
    
    return $thumbPath;
}
```

**安全特性**：
- ✓ 使用 `pathinfo()` 安全提取路径
- ✓ 检查文件是否为真实图片（`getimagesize()`）
- ✓ 仅支持特定图片格式
- ✓ 目录权限使用 0755（较安全）
- ✓ 缓存已生成的缩略图
- ✓ 错误处理和资源清理

### 3.4 远程图片下载

**文件**: `/home/user/mcyshop/app/Service/Common/Bind/Image.php`
**行号**: 第 172-228 行

```php
public function downloadRemoteImage(string $url, bool $isCreateThumbnail = true, ?int $userId = null): array
{
    $extension = $this->getImageExtensionFromURL($url);
    
    // 检查文件扩展名
    if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
        throw new ServiceException("检测到[$url]不是一张有效的图片");
    }
    
    // 检查是否为真实图片
    if (!$this->isRealImageFromURL($url)) {
        throw new ServiceException("检测到[{$url}]不是一张图片，风险较高，请慎重接入！");
    }
    
    // 生成本地路径
    $imagePath = "/assets/static/" . ($userId > 0 ? $userId : "general") . "/image/";
    $unique = $imagePath . date("Y-m-d/") . Str::generateRandStr() . ".{$extension}";
    
    // 创建目录
    $dir = dirname(BASE_PATH . $unique);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);  // ✗ 0777 不安全
    }
    
    // 下载文件
    Http::make()->get($url, [
        "sink" => BASE_PATH . $unique
    ]);
    
    if (!is_file(BASE_PATH . $unique)) {
        throw new ServiceException("图片下载失败：$url");
    }
    
    // 再次验证是真实图片
    if (!$this->isRealImage(BASE_PATH . $unique)) {
        File::remove(BASE_PATH . $unique);
        throw new ServiceException("检测到[{$url}]伪造成一张图片诱导本程序进行远程下载，风险极高，此文件已删除并粉碎！");
    }
    
    // 去重
    $hash = md5_file(BASE_PATH . $unique);
    $cache = $this->upload->get($hash);
    
    if ($cache) {
        if ($isCreateThumbnail) {
            $baseImagePathInfo = pathinfo($cache);
            $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];
            return [$cache, file_exists(BASE_PATH . $thumbPath) ? $thumbPath : $cache];
        }
        return [$cache];
    }
    
    // 生成缩略图
    if ($isCreateThumbnail) {
        $thumbUrl = $this->createThumbnail($unique, 128);
        if (!$thumbUrl) {
            if (is_file(BASE_PATH . $unique)) {
                File::remove(BASE_PATH . $unique);
            }
            throw new ServiceException("缩略图生成失败：{$url}");
        }
        
        $this->upload->add($unique, "image", $userId);
        return [$unique, $thumbUrl];
    }
    return [$unique];
}
```

**安全特性**：
- ✓ 扩展名白名单（白名单中的图片格式）
- ✓ 远程 MIME 类型验证（Content-Type 检查）
- ✓ 本地图片验证（`getimagesize()` 检查）
- ✓ 防止假图片攻击（第二次验证后删除）
- ✓ 文件去重
- ✗ 目录权限 0777（不安全）
- ✗ 没有 URL 白名单（允许下载任意 URL）

---

## 四、系统级文件操作工具

### 4.1 文件工具类

**文件**: `/home/user/mcyshop/kernel/Util/File.php`
**行号**: 第 42-125 行

```php
public static function write(string $path, string $content): bool
{
    $directory = dirname($path);
    !is_dir($directory) && (mkdir($directory, 0755, true));
    return (bool)file_put_contents($path, $content);
}

public static function copy(string $src, string $dst): bool
{
    if (!is_file($src)) {
        return false;
    }
    
    $directory = dirname($dst);
    !is_dir($directory) && (mkdir($directory, 0777, true));  // ✗ 0777 不安全
    
    $state = copy($src, $dst);
    chmod($dst, 0777);  // ✗ 更严重：文件权限 0777！
    return $state;
}

public static function remove(string ...$path): void
{
    foreach ($path as $p) {
        if (is_file($p)) {
            unlink($p);
        }
    }
}
```

**分析**：
- `write()` 方法：权限合理（0755）
- `copy()` 方法：
  - 目录权限 0777（太宽松）
  - 文件权限 0777（非常危险！）
  - 导致任何用户都能修改上传的脚本文件
- `remove()` 方法：直接删除，但有前置检查

---

## 五、安装流程中的文件操作

**文件**: `/home/user/mcyshop/app/Controller/Install.php`
**行号**: 第 184-240 行

```php
public function finish(): Response
{
    if (App::$install) {
        throw new JSONException("请勿重复安装");
    }
    
    // 读取安装 SQL 文件
    $file = BASE_PATH . "/kernel/Install/Install.sql";
    $sql = file_get_contents($file);
    if (!$sql) {
        throw new JSONException("安装文件已损坏，请下载最新版本进行安装");
    }
    
    // 替换占位符
    $sql = str_replace('${prefix}', $prefix, $sql);
    $sql = str_replace('${email}', $loginEmail, $sql);
    $sql = str_replace('${password}', $password, $sql);
    $sql = str_replace('${nickname}', $loginNickname, $sql);
    $sql = str_replace('${salt}', $salt, $sql);
    
    // 写入临时文件
    if (file_put_contents($file . ".tmp", $sql) === false) {
        throw new JSONException("没有写入权限，请检查权限是否足够");
    }
    
    // 导入数据库
    try {
        Dump::inst()->import($file . ".tmp", $host, $db, $user, $pass);
    } catch (\Throwable $e) {
        throw new JSONException("数据库出错，原因：" . $e->getMessage());
    }
    
    unlink($file . ".tmp");
    file_put_contents(BASE_PATH . '/kernel/Install/Lock', md5((string)time()));
    
    // ...
}
```

**安全分析**：
- ✓ 安装锁检查
- ✓ 临时文件处理后删除
- ✗ 没有路径验证（但 Install 应该是受限的）

---

## 六、插件和主题管理中的文件操作

### 6.1 插件发布

**文件**: `/home/user/mcyshop/app/Service/Store/Bind/Developer.php`
**行号**: 第 110-145 行

```php
public function publishPlugin(string $name, Authentication $authentication): void
{
    // 检查插件
    $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, Usr::MAIN);
    if (!$plugin) {
        throw new ServiceException("插件不存在");
    }
    
    // 检查文档
    if (!file_exists($plugin->path . "/Wiki/Readme.md") || !file_exists($plugin->path . "/Wiki/Sidebar.md")) {
        throw new ServiceException("插件文档不存在");
    }
    
    // 打包插件
    if (!Zip::state()) {
        throw new ServiceException("PHP-ZIP扩展未开启");
    }
    
    if (!Zip::createZip($plugin->path, self::DEVELOPER_RUNTIME . "/{$name}.zip", ["State", "Config/Config", "Runtime", "Config/System", ".version"])) {
        throw new ServiceException("打包插件失败");
    }
    
    // 上传插件（调用 upload 方法）
    $http = $this->http->upload("other", self::DEVELOPER_RUNTIME . "/{$name}.zip", $authentication);
    
    // ...
}
```

**安全分析**：
- ✓ 插件路径由系统管理（通过 Plugin::getPlugin）
- ✓ 排除敏感目录（State, Config 等）
- ✓ 使用系统生成的 ZIP 路径
- ✓ 权限检查（Admin 拦截器）

### 6.2 插件图标读取

**文件**: `/home/user/mcyshop/app/Controller/Admin/API/Plugin/Plugin.php`
**行号**: 第 56-77 行

```php
public function icon(string $name): Response
{
    $path = realpath(BASE_PATH . Usr::MAIN . "/" . $name . "/Icon.ico");
    
    if (!$path) {
        throw new JSONException("ICON不存在");
    }
    
    $file = fopen($path, 'rb');
    if (!$file) {
        throw new JSONException("无法读取文件");
    }
    
    $image = stream_get_contents($file);
    fclose($file);
    
    return $this->response->raw($image)
        ->withHeader("Content-Type", "image/png")
        ->withHeader("Cache-Control", "public, max-age=31536000")
        ->withHeader("Pragma", "public, max-age=31536000")
        ->withHeader("Expires", gmdate("D, d M Y H:i:s", time() + 31536000) . " GMT")
        ->withHeader("Date", gmdate("D, d M Y H:i:s", time() + 31536000) . " GMT");
}
```

**安全分析**：
- ✓ **使用 `realpath()` 验证路径**（好的做法）
- ✓ 检查文件是否存在
- ✓ 固定的相对路径（`/Icon.ico`）
- ✓ 不受用户输入直接影响
- **这是正确的路径验证方式！**

---

## 七、数据库模型

**文件**: `/home/user/mcyshop/app/Model/Upload.php`
**行号**: 第 18-30 行

```php
class Upload extends Model
{
    protected ?string $table = 'upload';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer'];
    
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
}
```

**数据库字段**：
- `id` - 文件记录 ID
- `user_id` - 上传用户 ID
- `hash` - 文件 MD5 哈希（去重）
- `type` - 文件类型（image, video, doc, other）
- `path` - 文件相对路径
- `create_time` - 创建时间
- `note` - 备注

---

## 八、发现的漏洞和缺陷总结

### 严重级别（CRITICAL）

| # | 类型 | 位置 | 描述 | 影响 |
|---|------|------|------|------|
| 1 | 路径遍历 | Store/Plugin.php:39 | BASE_PATH + 用户输入直接拼接 | 任意文件读取 |
| 2 | 路径遍历 | Store/Developer.php:54 | 同上 | 任意文件读取 |

### 高级别（HIGH）

| # | 类型 | 位置 | 描述 | 影响 |
|---|------|------|------|------|
| 3 | 权限问题 | File.php:123 | 文件权限设置为 0777 | 任何用户可修改上传文件 |
| 4 | 权限问题 | Abstract/File.php:164 | 目录权限 0777 | 任何用户可写入上传目录 |

### 中级别（MEDIUM）

| # | 类型 | 位置 | 描述 | 影响 |
|---|------|------|------|------|
| 5 | 权限问题 | Image.php:189 | 目录权限 0777（下载图片时）| 任何用户可写入目录 |
| 6 | 缺少验证 | Image.php:172-228 | 没有远程 URL 白名单 | SSRF 可能性 |
| 7 | 信息泄露 | User/Upload/Upload.php:50-51 | pathinfo 返回完整路径 | 路径信息泄露（低风险） |

---

## 九、建议的修复方案

### 9.1 修复路径遍历漏洞（优先级：最高）

#### Store/Plugin.php 第 39 行
**修改前**：
```php
$http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());
```

**修改后**：
```php
// 方案 1：使用已经上传的文件
// $data['icon'] 应该是通过标准上传接口获得的相对路径
// 不应该由用户直接提供

// 方案 2：如果必须从用户输入读取，进行严格验证
if (isset($data['icon']) && is_string($data['icon'])) {
    // 验证路径不包含目录遍历序列
    if (strpos($data['icon'], '..') !== false || strpos($data['icon'], '//') !== false) {
        throw new JSONException("非法的文件路径");
    }
    // 使用 realpath 验证
    $realPath = realpath(BASE_PATH . $data['icon']);
    if ($realPath === false || strpos($realPath, BASE_PATH) !== 0) {
        throw new JSONException("非法的文件路径");
    }
    $http = $this->http->upload("image", $realPath, $this->getStoreAuth());
} else {
    throw new JSONException("图标路径无效");
}
```

### 9.2 修复文件权限问题（优先级：高）

#### File.php 第 120、123 行
**修改前**：
```php
!is_dir($directory) && (mkdir($directory, 0777, true));
$state = copy($src, $dst);
chmod($dst, 0777);
```

**修改后**：
```php
!is_dir($directory) && (mkdir($directory, 0755, true));
$state = copy($src, $dst);
chmod($dst, 0644);  // 或 0640，取决于需求
```

#### Abstract/File.php 第 164 行
**修改前**：
```php
mkdir($_tmpDir, 0777, true);
```

**修改后**：
```php
mkdir($_tmpDir, 0755, true);
```

### 9.3 增强 MIME 类型验证（优先级：中）

**在 Context/Abstract/File.php 中添加**：
```php
public function save(string $path, array $ext = [...], int $size = 10240, string $dir = BASE_PATH): string
{
    // ... 现有代码 ...
    
    // 添加 MIME 类型验证
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $this->getTmp());
    finfo_close($finfo);
    
    $allowedMimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'video/mp4' => ['mp4'],
        // ... 其他允许的 MIME 类型
    ];
    
    if (!isset($allowedMimes[$mimeType]) || !in_array(strtolower($this->getSuffix()), $allowedMimes[$mimeType])) {
        throw new JSONException("文件类型与扩展名不匹配");
    }
    
    // ... 继续现有代码 ...
}
```

### 9.4 添加 URL 白名单（优先级：低）

**在 Service/Common/Bind/Image.php 中添加**：
```php
public function downloadRemoteImage(string $url, ...): array
{
    // 验证 URL
    $allowedDomains = [
        'https://example.com',
        'https://cdn.example.com',
        // ... 允许的域名列表
    ];
    
    $urlHost = parse_url($url, PHP_URL_HOST);
    $allowed = false;
    
    foreach ($allowedDomains as $domain) {
        $domainHost = parse_url($domain, PHP_URL_HOST);
        if ($urlHost === $domainHost) {
            $allowed = true;
            break;
        }
    }
    
    if (!$allowed) {
        throw new ServiceException("不允许从此域名下载图片");
    }
    
    // ... 继续现有代码 ...
}
```

### 9.5 添加删除权限验证（优先级：中）

**在 Controller/Admin/API/Upload/Upload.php 中修改**：
```php
public function del(): Response
{
    $list = (array)$this->request->post("list");
    if (count($list) > 0) {
        $uploads = \App\Model\Upload::query()->whereIn("id", $list)->get();
        foreach ($uploads as $upload) {
            // 添加权限检查
            // 选项 1：只允许文件所有者删除
            // if ($upload->user_id !== null && $upload->user_id !== $this->getUser()->id && !$this->isAdmin()) {
            //     continue; // 跳过无权限的文件
            // }
            
            // 选项 2：管理员可删除所有文件，用户只能删除自己的
            if ($upload->user_id !== null && $upload->user_id !== $this->getUser()->id) {
                throw new JSONException("无权删除此文件");
            }
            
            $this->upload->remove($upload->path);
        }
    }
    return $this->json();
}
```

---

## 十、安全配置建议

### 10.1 Web 服务器配置

#### Nginx 配置
```nginx
# 禁止直接执行上传目录中的脚本
location /assets/static/ {
    # 禁止执行 PHP 等脚本
    location ~ \.(php|php5|php7|phtml|phar|shtml|pht)$ {
        deny all;
    }
    
    # 允许浏览器缓存
    expires 30d;
    add_header Cache-Control "public, immutable";
}
```

#### Apache 配置
```apache
# 在上传目录创建 .htaccess
<Directory "/path/to/assets/static">
    # 禁止执行脚本
    php_flag engine off
    
    # 禁止 .htaccess 文件本身执行
    <FilesMatch "^\.ht">
        Deny from all
    </FilesMatch>
</Directory>
```

### 10.2 PHP 配置

```ini
; php.ini 配置建议
; 禁止上传目录在 open_basedir 中执行
open_basedir = /path/to/project:/tmp:/var/tmp

; 禁用危险函数
disable_functions = exec,system,shell_exec,passthru,proc_open,proc_close,escapeshellcmd,escapeshellarg,disk_free,disk_total,getcwd,is_writable,fileperms,readlink

; 限制上传大小
upload_max_filesize = 50M
post_max_size = 50M
```

### 10.3 文件系统权限

```bash
# 创建上传目录
mkdir -p /path/to/project/assets/static
mkdir -p /path/to/project/runtime/upload

# 设置正确的权限
chmod 755 /path/to/project/assets/static
chmod 755 /path/to/project/runtime/upload

# 设置所有者为 web 服务器用户（例如 www-data）
chown -R www-data:www-data /path/to/project/assets/static
chown -R www-data:www-data /path/to/project/runtime/upload

# 不允许上传目录有 write 权限（由 web 服务器进程写入）
chmod 755 /path/to/project/assets/static
```

---

## 十一、测试和验证清单

### 11.1 安全测试计划

- [ ] **路径遍历测试**
  - [ ] 尝试上传 `icon: "/../../../config/app.php"`
  - [ ] 尝试上传 `icon: "../../../../../etc/passwd"`
  - [ ] 验证是否能读取系统文件

- [ ] **文件权限测试**
  - [ ] 上传文件后检查权限 `ls -la /path/to/upload`
  - [ ] 尝试以不同用户修改已上传的文件
  - [ ] 验证权限是否为 0777（不安全）

- [ ] **MIME 类型验证测试**
  - [ ] 尝试上传 `.php` 文件，伪装成 `.jpg`
  - [ ] 尝试上传包含恶意代码的图片
  - [ ] 验证是否执行了恶意代码

- [ ] **文件删除权限测试**
  - [ ] 用户 A 尝试删除用户 B 的文件
  - [ ] 验证是否进行了权限检查

- [ ] **远程文件下载测试**
  - [ ] 尝试下载恶意 URL 指向的文件
  - [ ] 尝试下载非图片文件
  - [ ] 验证 SSRF 防护

### 11.2 漏洞验证脚本

```php
// 测试路径遍历
$payload = [
    "icon" => "/../../../config/app.php"
];

$response = $http->post('/api/admin/store/plugin/save', $payload);
// 验证是否返回 config/app.php 的内容

// 测试文件权限
$upload_dir = "/path/to/project/assets/static";
$files = scandir($upload_dir);
foreach ($files as $file) {
    $path = $upload_dir . '/' . $file;
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    if ($perms === '0777') {
        echo "WARNING: File $file has 0777 permissions";
    }
}
```

---

## 十二、检查清单总结

### 已实现的安全措施
- ✓ 文件类型白名单验证
- ✓ 文件大小限制
- ✓ 随机文件名生成
- ✓ 用户隔离（不同用户不同目录）
- ✓ 权限拦截器（Admin、User）
- ✓ 文件去重（MD5 哈希）
- ✓ 某些路径使用 `realpath()` 验证
- ✓ 缩略图生成时的双重验证
- ✓ 远程图片的 MIME 类型检查

### 需要改进的安全措施
- ✗ 两个关键的路径遍历漏洞
- ✗ 文件权限设置过宽松（0777）
- ✗ 缺少上传目录执行脚本的防护
- ✗ 缺少细粒度的 MIME 类型验证
- ✗ 缺少远程 URL 白名单
- ✗ 某些地方缺少路径验证

---

## 十三、结论与优先级建议

### 立即修复（P0 - 24 小时内）
1. **Store/Plugin.php 和 Store/Developer.php 的路径遍历漏洞**
   - 这是最严重的安全问题
   - 可导致任意文件读取
   - 需要立即打补丁

### 优先修复（P1 - 一周内）
1. **文件权限问题（0777 改为 0755/0644）**
   - 影响系统安全性
   - 允许任何用户修改上传文件
2. **Web 服务器配置**
   - 禁止执行上传目录中的脚本

### 后续改进（P2 - 一个月内）
1. **增加 MIME 类型验证**
2. **添加 URL 白名单**
3. **细化权限检查逻辑**
4. **添加安全日志和审计**

---

## 十四、参考资源

- OWASP 文件上传安全：https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload
- 路径遍历攻击：https://owasp.org/www-community/attacks/Path_Traversal
- PHP 文件操作安全：https://www.php.net/manual/en/wrappers.file.php
- 文件权限最佳实践：https://wiki.debian.org/UnixPermissions

