# MCYShop 文件操作代码快速索引

## 关键文件列表

### 1. 文件上传控制器

#### 管理员上传接口
- **文件**: `/home/user/mcyshop/app/Controller/Admin/API/Upload.php`
- **行号**: 18-76
- **方法**: main()
- **功能**: 管理员文件上传
- **关键验证**: 文件类型白名单、大小限制

#### 用户上传接口
- **文件**: `/home/user/mcyshop/app/Controller/User/API/Upload.php`
- **行号**: 39-78
- **方法**: main()
- **功能**: 用户文件上传（隔离到用户目录）
- **关键验证**: 用户 ID 隔离

### 2. 危险的路径遍历漏洞 - 需立即修复

#### 漏洞 1: Store Plugin
- **文件**: `/home/user/mcyshop/app/Controller/Admin/API/Store/Plugin.php`
- **行号**: 35-43
- **方法**: save()
- **漏洞**: 第 39 行 `BASE_PATH . $data['icon']` 直接拼接用户输入
- **严重级别**: CRITICAL

#### 漏洞 2: Store Developer  
- **文件**: `/home/user/mcyshop/app/Controller/Admin/API/Store/Developer.php`
- **行号**: 48-60
- **方法**: createOrUpdatePlugin()
- **漏洞**: 第 54 行同样的路径遍历问题
- **严重级别**: CRITICAL

### 3. 文件操作核心类

#### 文件上传抽象类（危险的权限设置）
- **文件**: `/home/user/mcyshop/kernel/Context/Abstract/File.php`
- **行号**: 144-174
- **方法**: save()
- **危险**: 第 164 行 `mkdir(..., 0777, true)` 权限过高
- **建议**: 改为 0755

#### 文件工具类（最危险的权限设置）
- **文件**: `/home/user/mcyshop/kernel/Util/File.php`
- **行号**: 42-125
- **方法**: write(), copy(), remove()
- **危险**: 
  - 第 120 行 `mkdir(..., 0777, true)` 
  - 第 123 行 `chmod(..., 0777)` - 文件权限 0777！
- **建议**: copy() 改为 chmod($dst, 0644)

#### 文件上传上下文
- **文件**: `/home/user/mcyshop/kernel/Context/Upload.php`
- **行号**: 1-26
- **父类**: Kernel\Context\Abstract\File
- **功能**: 包装 HTTP 上传文件

### 4. 上传服务实现

#### 上传数据库服务（去重和缓存）
- **文件**: `/home/user/mcyshop/app/Service/Common/Bind/Upload.php`
- **行号**: 18-59
- **方法**: add(), get(), remove()
- **功能**: 文件去重、缓存、删除
- **特性**: 基于 MD5 哈希去重

#### 上传服务接口
- **文件**: `/home/user/mcyshop/app/Service/Common/Upload.php`
- **行号**: 1-32
- **方法**: add(), get(), remove()
- **定义**: 上传服务接口

### 5. 缩略图和图片处理

#### 图片服务实现（较安全）
- **文件**: `/home/user/mcyshop/app/Service/Common/Bind/Image.php`
- **关键方法**:
  - createThumbnail() - 第 26-120 行 - 缩略图生成
  - downloadRemoteImage() - 第 172-228 行 - 远程图片下载
- **风险**: 
  - 第 189 行 `mkdir(..., 0777, true)` 权限问题
  - 没有 URL 白名单防护 SSRF

### 6. 文件管理API

#### 管理员文件删除接口
- **文件**: `/home/user/mcyshop/app/Controller/Admin/API/Upload/Upload.php`
- **行号**: 85-95
- **方法**: del()
- **风险**: 没有细粒度权限检查

#### 用户文件列表接口
- **文件**: `/home/user/mcyshop/app/Controller/User/API/Upload/Upload.php`
- **行号**: 37-58
- **方法**: get()
- **安全**: 有用户隔离检查 `where("user_id", $this->getUser()->id)`

### 7. 插件相关文件操作

#### 插件服务（项目更新）
- **文件**: `/home/user/mcyshop/app/Service/Store/Bind/Project.php`
- **行号**: 74-190
- **方法**: update()
- **功能**: 系统版本更新
- **操作**: ZIP 解压、文件复制、数据库升级

#### 开发者插件管理
- **文件**: `/home/user/mcyshop/app/Service/Store/Bind/Developer.php`
- **关键方法**:
  - publishPlugin() - 第 110-145 行 - 插件发布
  - updatePlugin() - 第 155-205 行 - 插件更新
- **安全**: 使用 realpath() 验证路径

#### 店铺 HTTP 服务（重要）
- **文件**: `/home/user/mcyshop/app/Service/Store/Bind/Http.php`
- **关键方法**:
  - download() - 第 174-220 行 - 文件下载
  - upload() - 第 230-262 行 - 文件上传（调用链中的危险处）
- **第 246 行**: `fopen($file, "r")` - 受到路径遍历影响

#### 插件图标读取（安全的示例）
- **文件**: `/home/user/mcyshop/app/Controller/Admin/API/Plugin/Plugin.php`
- **行号**: 56-77
- **方法**: icon()
- **安全**: 第 58 行使用 `realpath()` 进行路径验证
- **推荐**: 其他地方应该效仿这种做法

### 8. 系统级操作

#### 安装流程
- **文件**: `/home/user/mcyshop/app/Controller/Install.php`
- **行号**: 184-240
- **方法**: finish()
- **操作**: SQL 导入、Lock 文件创建
- **风险**: 低（在安装期间）

### 9. 数据库模型

#### 上传文件模型
- **文件**: `/home/user/mcyshop/app/Model/Upload.php`
- **表名**: upload
- **字段**:
  - id (PK)
  - user_id (FK)
  - hash (VARCHAR - MD5)
  - type (VARCHAR - 文件类型)
  - path (VARCHAR - 相对路径)
  - create_time (DATETIME)
  - note (TEXT)

---

## 安全问题快速定位

### 严重级别 (CRITICAL)

1. **路径遍历漏洞**
   - Store/Plugin.php 第 39 行
   - Store/Developer.php 第 54 行
   - **问题**: `BASE_PATH . $data['icon']`
   - **修复**: 使用 realpath() 验证或校验路径

### 高级别 (HIGH)

1. **文件权限问题**
   - File.php 第 120 行 `mkdir(..., 0777, true)`
   - File.php 第 123 行 `chmod($dst, 0777)`
   - Abstract/File.php 第 164 行 `mkdir(..., 0777, true)`
   - **修复**: 改为 0755 (目录) 和 0644 (文件)

### 中级别 (MEDIUM)

1. **缺少 MIME 验证**
   - Abstract/File.php - 只验证扩展名

2. **缺少 URL 白名单**
   - Image.php 第 172-228 行 downloadRemoteImage()

3. **缺少执行脚本防护**
   - Web 服务器配置

---

## 所有文件操作函数位置表

| 函数 | 位置 | 行号 | 风险等级 |
|------|------|------|---------|
| mkdir (0777) | File.php | 120 | HIGH |
| chmod (0777) | File.php | 123 | HIGH |
| mkdir (0777) | Abstract/File.php | 164 | HIGH |
| copy | Abstract/File.php | 167 | MEDIUM |
| fopen | Store/Http.php | 246 | CRITICAL |
| realpath | Plugin/Plugin.php | 58 | SAFE |
| file_put_contents | Install.php | 215 | LOW |
| unlink | Install.php | 226 | LOW |
| md5_file | Upload (service) | 24 | SAFE |

---

## 修复优先级

### P0 - 立即修复 (24小时)
1. Store/Plugin.php:39 - 路径遍历
2. Store/Developer.php:54 - 路径遍历

### P1 - 优先修复 (1周内)
1. File.php:120, 123 - 文件权限
2. Abstract/File.php:164 - 目录权限
3. Image.php:189 - 目录权限

### P2 - 后续改进 (1个月内)
1. 增加 MIME 类型验证
2. 添加 URL 白名单
3. Web 服务器配置

---

## 关键安全实践清单

- [ ] 使用 realpath() 验证所有用户输入的路径
- [ ] 设置目录权限为 0755，文件权限为 0644
- [ ] 在 Web 服务器禁止执行上传目录中的脚本
- [ ] 验证 MIME 类型，不仅仅是扩展名
- [ ] 为远程下载功能添加 URL 白名单
- [ ] 检查文件所有权和权限后再进行操作
- [ ] 对所有用户输入的路径进行严格验证
- [ ] 使用随机文件名防止目录枚举
- [ ] 实现文件大小和类型的白名单验证
- [ ] 为文件删除操作添加权限检查

