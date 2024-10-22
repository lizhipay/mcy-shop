SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `${prefix}bank`;
CREATE TABLE `${prefix}bank`  (
                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行图标',
                                  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行名称',
                                  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行代码',
                                  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=停用，1=启用',
                                  PRIMARY KEY (`id`) USING BTREE,
                                  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}bank` VALUES (1, '/assets/common/images/bank/ALIPAY.png', '支付宝', 'ALIPAY', 1);
INSERT INTO `${prefix}bank` VALUES (2, '/assets/common/images/bank/WECHAT.png', '微信', 'WECHAT', 1);
INSERT INTO `${prefix}bank` VALUES (3, '/assets/common/images/bank/BKCHCNBJ.png', '中国银行', 'BKCHCNBJ', 1);
INSERT INTO `${prefix}bank` VALUES (4, '/assets/common/images/bank/ICBKCNBJ.png', '中国工商银行', 'ICBKCNBJ', 1);
INSERT INTO `${prefix}bank` VALUES (5, '/assets/common/images/bank/ABOCCNBJ.png', '中国农业银行', 'ABOCCNBJ', 1);
INSERT INTO `${prefix}bank` VALUES (6, '/assets/common/images/bank/PCBCCNBJFJX.png', '中国建设银行', 'PCBCCNBJFJX', 1);
INSERT INTO `${prefix}bank` VALUES (7, '/assets/common/images/bank/CMBCCNBS.png', '中国招商银行', 'CMBCCNBS', 1);
INSERT INTO `${prefix}bank` VALUES (8, '/assets/common/images/bank/COMMCNSH.png', '中国交通银行', 'COMMCNSH', 1);
INSERT INTO `${prefix}bank` VALUES (11, '/assets/common/images/bank/PSBCCNBJ.png', '中国邮政银行', 'PSBCCNBJ', 1);


DROP TABLE IF EXISTS `${prefix}cart`;
CREATE TABLE `${prefix}cart`  (
                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `customer_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '用户ID，null=游客',
                                  `client_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '客户端ID',
                                  `create_time` datetime NOT NULL COMMENT '购物车创建时间',
                                  PRIMARY KEY (`id`) USING BTREE,
                                  UNIQUE INDEX `client_id`(`client_id`) USING BTREE,
                                  UNIQUE INDEX `customer_id`(`customer_id`) USING BTREE,
                                  CONSTRAINT `${prefix}cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}cart` VALUES (15, NULL, 'YTUA5NkadN0oh3RDgTt8eBaNB74VCOma', '2024-05-28 23:45:25');


DROP TABLE IF EXISTS `${prefix}cart_item`;
CREATE TABLE `${prefix}cart_item`  (
                                       `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                       `cart_id` bigint(20) UNSIGNED NOT NULL COMMENT '购物车ID',
                                       `quantity` int(11) UNSIGNED NOT NULL COMMENT '数量',
                                       `sku_id` bigint(20) UNSIGNED NOT NULL COMMENT 'SKU ID',
                                       `amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '预估金额',
                                       `price` decimal(10, 2) UNSIGNED NOT NULL COMMENT '单价',
                                       `option` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品选项',
                                       `create_time` datetime NOT NULL COMMENT '加入时间',
                                       `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
                                       PRIMARY KEY (`id`) USING BTREE,
                                       INDEX `sku_id`(`sku_id`) USING BTREE,
                                       INDEX `cart_id`(`cart_id`) USING BTREE,
                                       CONSTRAINT `${prefix}cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `${prefix}cart` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}category`;
CREATE TABLE `${prefix}category`  (
                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                      `user_id` bigint(10) UNSIGNED NULL DEFAULT NULL COMMENT '会员id，空代表后台',
                                      `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分类名称',
                                      `sort` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                      `create_time` datetime NOT NULL COMMENT '创建时间',
                                      `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
                                      `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=停用，1=启用',
                                      `pid` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '上级分类ID',
                                      PRIMARY KEY (`id`) USING BTREE,
                                      INDEX `sort`(`sort`) USING BTREE,
                                      INDEX `status`(`status`) USING BTREE,
                                      INDEX `user_id`(`user_id`) USING BTREE,
                                      INDEX `pid`(`pid`) USING BTREE,
                                      CONSTRAINT `${prefix}category_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                      CONSTRAINT `${prefix}category_ibfk_2` FOREIGN KEY (`pid`) REFERENCES `${prefix}category` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 65 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}category` VALUES (64, NULL, 'DEMO', 0, '2024-05-28 23:30:11', '/favicon.ico', 1, NULL);


DROP TABLE IF EXISTS `${prefix}config`;
CREATE TABLE `${prefix}config`  (
                                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
                                    `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家ID，空为主站',
                                    `key` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '配置key',
                                    `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '配置内容',
                                    `title` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '配置名称',
                                    `icon` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
                                    `bg_url` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '背景图片',
                                    PRIMARY KEY (`id`) USING BTREE,
                                    UNIQUE INDEX `user_id`(`user_id`, `key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}config` VALUES (1, NULL, 'site', '{\"logo\":\"/favicon.ico\",\"bg_image\":\"/assets/user/images/bg.jpg\",\"pc_theme\":\"default\",\"mobile_theme\":\"default\",\"title\":\"異界の小店\",\"keywords\":\"異界,小店\",\"description\":\"这是異界の小店哦\",\"session_expire\":\"604800\",\"icp\":\"\",\"notice_banner\":\"/assets/user/images/test/fe9cda6ed905abc9a9a1e979c4234c46.jpg\",\"notice\":\"<p><span style=\\\"color:rgb(225,60,57);\\\"><strong>本程序是完全开源项目，作为学习开发商城为目的，请不要在没有运营资质情况下搭建运营以及二次开发，万不可触犯法律底线。</strong></span></p><p><span style=\\\"color:rgb(216,68,147);\\\"><strong>本页面仅供开源程序演示为目的，不提供任何实际功能进行操作。</strong></span></p>\",\"max_upload_size\":\"20480\",\"domains\":\"\",\"force_login\":0,\"is_get_location\":0}', '网站设置', 'icon-wangzhanshezhi', '/assets/admin/image/config/site.jpg');
INSERT INTO `${prefix}config` VALUES (2, NULL, 'register', '{\"status\":\"1\",\"email_template\":\"<p><span style=\\\"color:rgb(0,0,0);background-color:rgb(255,255,255);font-size:16px;\\\">您的验证码是：</span><span style=\\\"color:rgb(56,158,13);font-size:22px;\\\"><strong>{$code}</strong></span><span style=\\\"font-size:16px;\\\">，5分钟内有效，</span><span style=\\\"color:rgb(0,0,0);background-color:rgb(255,255,255);font-size:16px;\\\">打死也不要告诉别人!</span></p>\",\"terms\":\"<p>欢迎您注册成为我们网站的用户。在您注册成为用户之前，请仔细阅读以下协议内容。注册即表示您同意遵守本协议的所有条款。</p><h4>一、用户资格</h4><ol><li>您应具备完全的民事行为能力，未满18岁的未成年人应在监护人陪同下阅读本协议并在监护人的同意下使用本网站的服务。</li><li>注册时，您应提供真实、准确、完整的个人信息。若您的信息发生变更，请及时更新。</li></ol><h4>二、用户账号</h4><ol><li>用户账号为您注册成功后获取的唯一标识，您有责任妥善保管账号和密码，任何使用您的账号进行的活动均视为您本人的行为。</li><li>如发现账号被盗用，请立即通知我们。因未能及时通知导致的损失由您自行承担。</li></ol><h4>三、隐私政策</h4><ol><li>个人信息的收集和使用我们收集您的个人信息用于提供、维护和改进我们的服务，包括但不限于注册信息、联系方式和支付信息。我们不会将您的个人信息出售给任何第三方，但在法律要求或得到您的授权情况下，我们可能会与第三方共享您的信息。</li><li>Cookies的使用为提供更好的用户体验，我们会在您的设备上存储和使用Cookies。Cookies用于记住您的偏好、登录状态和分析网站流量等。您可以通过浏览器设置拒绝或删除Cookies，但这可能影响您使用本网站的部分功能。</li><li>数据安全我们采取适当的技术措施和管理措施保护您的个人信息安全，防止未经授权的访问、披露、修改或破坏。</li></ol><h4>四、用户行为</h4><ol><li>用户在使用本网站服务时，必须遵守所有适用的法律法规，不得利用本网站从事任何违法或侵权行为。</li><li>禁止发布、传输任何违法、骚扰、诽谤、辱骂、威胁、淫秽或其他不良信息。</li></ol><h4>五、服务中断或终止</h4><ol><li>在下列情况下，我们有权中断或终止向您提供服务，并无需对您或任何第三方承担任何责任：</li></ol><h4>六、免责声明</h4><ol><li>本网站提供的服务按“现状”提供，我们不对服务的连续性、准确性、可靠性作出任何保证。</li><li>在法律允许的最大范围内，我们不对因使用或不能使用本网站服务而导致的任何间接、附带、特殊、后果性或惩罚性的损害承担责任。</li></ol><h4>七、修改与解释权</h4><ol><li>我们保留随时修改本协议条款的权利，修改后的协议将在本网站上发布。若您在协议修改后继续使用本网站服务，即表示您接受修改后的条款。</li><li>本协议的最终解释权归本网站所有。</li></ol>\",\"identity_status\":\"0\",\"email\":0,\"email_code\":0}', '注册/登录设置', 'icon-yonghuzhuce', '/assets/admin/image/config/register.jpg');
INSERT INTO `${prefix}config` VALUES (3, NULL, 'email', '{\"host\":\"\",\"port\":\"\",\"secure\":\"ssl\",\"username\":\"\",\"from\":\"\",\"nickname\":\"\",\"password\":\"\"}', '邮件配置', 'icon-a-kl_e10', '/assets/admin/image/config/email.jpg');
INSERT INTO `${prefix}config` VALUES (4, NULL, 'pay', '{\"currency\":\"1\",\"exchange_rate\":\"7.24\",\"async_url\":\"\"}', '支付/回调设置', 'icon-zhifupeizhi', '/assets/admin/image/config/payment.jpg');
INSERT INTO `${prefix}config` VALUES (5, NULL, 'sms', '{\"platform\":\"0\",\"ali_access_key_id\":\"\",\"ali_access_key_secret\":\"\",\"ali_sign_name\":\"\",\"ali_template_code\":\"\",\"tencent_region\":\"ap-guangzhou\",\"tencent_secret_id\":\"\",\"tencent_secret_key\":\"\",\"tencent_sdk_app_id\":\"\",\"tencent_sign_name\":\"\",\"tencent_template_id\":\"\",\"dxb_username\":\"\",\"dxb_password\":\"\",\"dxb_template\":\"\"}', '短信配置', 'icon-duanxinpeizhi', '/assets/admin/image/config/sms.jpg');
INSERT INTO `${prefix}config` VALUES (6, NULL, 'waf', '{\"uri_scheme_filter_whitelist\":\"#B站\\n*.bilibili.com\\n#youtube\\n*.youtube.com\\n*.youtube-nocookie.com\\n#国外的一些网站\\n*.vimeo.com\\n*.dailymotion.com\\n*.facebook.com\\n*.twitch.tv\\n*.brightcove.com\\n*.wistia.com\\n*.spotlightr.com\\n*.vidyard.com\\n*.jetpack.com\\n*.jetpack1.com\"}', '防火墙', 'icon-anquan4', '/assets/admin/image/config/waf.jpg');
INSERT INTO `${prefix}config` VALUES (11, NULL, 'subdomain', '{\"subdomain\":\"\",\"dns_type\":\"0\",\"dns_value\":\"\",\"nginx_fpm_url\":\"http://127.0.0.1:2234\",\"dns_status\":0,\"nginx_conf\":\"server {\\r\\n    listen 80;\\r\\n    server_name ${server_name};\\r\\n    return 301 https://$server_name$request_uri;\\r\\n}\\r\\n\\r\\nserver {\\r\\n    listen 443 ssl;\\r\\n    server_name ${server_name};\\r\\n\\r\\n    ssl_certificate ${ssl_certificate};\\r\\n    ssl_certificate_key ${ssl_certificate_key};\\r\\n    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;\\r\\n    ssl_prefer_server_ciphers on;\\r\\n\\r\\n    location / {\\r\\n        proxy_pass ${proxy_pass};\\r\\n        proxy_set_header Host $host;\\r\\n        proxy_set_header X-Real-IP $remote_addr;\\r\\n        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\\r\\n        proxy_set_header REMOTE-HOST $remote_addr;\\r\\n        proxy_set_header Upgrade $http_upgrade;\\r\\n        proxy_set_header Connection $connection_upgrade;\\r\\n        proxy_set_header Scheme $scheme;\\r\\n        proxy_http_version 1.1;\\r\\n    }\\r\\n\\r\\n    # 禁止访问目录或者文件\\r\\n    location ~ ^/(\\\\.htaccess|\\\\.git|LICENSE|README.md|config|kernel|runtime|vendor)\\r\\n    {\\r\\n        return 404;\\r\\n    }\\r\\n}\"}', '子站配置', 'icon-DNS', '/assets/admin/image/config/subdomain.jpg');
INSERT INTO `${prefix}config` VALUES (13, NULL, 'composer', '{\"server\":\"official\",\"custom_url\":\"https://mirrors.aliyun.com/composer/\"}', 'Composer', 'icon-composerluojibianpai', '/assets/admin/image/config/composer.jpg');


DROP TABLE IF EXISTS `${prefix}item`;
CREATE TABLE `${prefix}item`  (
                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id，空代表后台',
                                  `repertory_item_id` bigint(20) UNSIGNED NOT NULL COMMENT '仓库-物品id',
                                  `category_id` bigint(20) UNSIGNED NOT NULL COMMENT '分类id',
                                  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品名称',
                                  `introduce` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品说明',
                                  `picture_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '封面图片',
                                  `picture_thumb_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩略图',
                                  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=下架，1=上架',
                                  `create_time` datetime NOT NULL COMMENT '创建时间',
                                  `markup` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '默认加价通用方案',
                                  `markup_mode` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步模式：0=自定义，1=模版同步',
                                  `markup_template_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '同步模版id',
                                  `attr` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品属性(JSON)',
                                  `sort` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '排序',
                                  `recommend` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '推荐：0=否，1=是',
                                  PRIMARY KEY (`id`) USING BTREE,
                                  INDEX `status`(`status`) USING BTREE,
                                  INDEX `item_list`(`user_id`, `category_id`, `status`) USING BTREE,
                                  INDEX `repertory_item_id`(`repertory_item_id`) USING BTREE,
                                  INDEX `category_id`(`category_id`) USING BTREE,
                                  INDEX `user_id`(`user_id`) USING BTREE,
                                  INDEX `markup_mode`(`markup_mode`) USING BTREE,
                                  INDEX `markup_template_id`(`markup_template_id`) USING BTREE,
                                  INDEX `recommend`(`recommend`) USING BTREE,
                                  INDEX `class_service_item_list_recommend`(`status`, `recommend`, `user_id`, `sort`) USING BTREE,
                                  INDEX `class_service_item_list_category`(`status`, `category_id`, `user_id`, `sort`) USING BTREE,
                                  CONSTRAINT `${prefix}item_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                  CONSTRAINT `${prefix}item_ibfk_2` FOREIGN KEY (`repertory_item_id`) REFERENCES `${prefix}repertory_item` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                  CONSTRAINT `${prefix}item_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `${prefix}category` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 90 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}item` VALUES (89, NULL, 22, 64, 'DEMO', '<p><strong>Cosplay</strong>（日语：コスプレ）为<a href=\"https://zh.wikipedia.org/wiki/和製英語\">和制英语</a>，<strong>costume play</strong>的<a href=\"https://zh.wikipedia.org/wiki/混成詞\">混成词</a>，它已成为世界通用的词汇。中文一般译为“<strong>角色扮演</strong>”<sup>[1]</sup>或“<strong>扮装</strong>”<sup>[2][3]</sup>，有时也会直接称为cos， 是指利用服装、饰品、道具及化妆搭配等，扮演动漫、游戏中人物角色的一种<a href=\"https://zh.wikipedia.org/wiki/表演藝術\">表演艺术</a>行为。常见于<a href=\"https://zh.wikipedia.org/wiki/同人誌即賣會\">同人志即售会</a>或<a href=\"https://zh.wikipedia.org/wiki/視覺系\">视觉系</a><a href=\"https://zh.wikipedia.org/wiki/樂團\">乐团</a>演唱会等同好聚集的活动中。而参与扮装活动的表演者，一般称呼为cosplayer，简称coser；中文称“<strong>扮装者</strong>”<sup>[4]</sup>、“<strong>角色扮演者</strong>”或“<strong>角色扮演员</strong>”。</p><p>近年来Cosplay的定义扩大，除了原本所指的同好活动、也泛指喜好特定职业、人物、文化等<a href=\"https://zh.wikipedia.org/wiki/角色扮演\">角色扮演</a>行为；而Cosplay爱好者的社群圈子又称<strong>C圈</strong>。</p><p><br /></p><h2>词源</h2><p><br /></p><p><a href=\"https://zh.wikipedia.org/wiki/遊戲王\">游</a><a href=\"https://zh.wikipedia.org/wiki/遊戲王\">戏王</a>角色“黑魔导女孩”的扮装者</p><p>Cosplay通常被视为一种<a href=\"https://zh.wikipedia.org/wiki/次文化\">次文化</a>活动。扮演的对象<a href=\"https://zh.wikipedia.org/wiki/角色\">角色</a>一般来自<a href=\"https://zh.wikipedia.org/wiki/動畫\">动画</a>、<a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>、<a href=\"https://zh.wikipedia.org/wiki/電子遊戲\">电子游戏</a>、<a href=\"https://zh.wikipedia.org/wiki/輕小說\">轻小说</a>、<a href=\"https://zh.wikipedia.org/wiki/電影\">电影</a>、影集、<a href=\"https://zh.wikipedia.org/wiki/特攝\">特摄</a>、<a href=\"https://zh.wikipedia.org/wiki/偶像團體\">偶像团体</a>、<a href=\"https://zh.wikipedia.org/wiki/職業\">职业</a>、<a href=\"https://zh.wikipedia.org/wiki/歷史\">历史</a><a href=\"https://zh.wikipedia.org/wiki/故事\">故事</a>、<a href=\"https://zh.wikipedia.org/wiki/社會\">社会</a>故事、现实世界中具有传奇性的独特事物（或其<a href=\"https://zh.wikipedia.org/wiki/擬人化\">拟人化</a>形态）、或是其他自创的有形<a href=\"https://zh.wikipedia.org/wiki/角色\">角色</a>。方法是特意穿戴相似的<a href=\"https://zh.wikipedia.org/wiki/服饰\">服饰</a>，加上<a href=\"https://zh.wikipedia.org/wiki/道具\">道具</a>的配搭，<a href=\"https://zh.wikipedia.org/wiki/化粧\">化妆</a>造型、<a href=\"https://zh.wikipedia.org/wiki/身體語言\">身体语言</a>等等<a href=\"https://zh.wikipedia.org/wiki/参数\">参数</a>来模仿该等角色。</p><p>历史上有<a href=\"https://zh.wikipedia.org/wiki/化裝舞會\">化装舞会</a>等变装活动；而1939年在美国<a href=\"https://zh.wikipedia.org/wiki/紐約\">纽约</a>举办的<a href=\"https://zh.wikipedia.org/wiki/世界科幻大会\">世界科幻大会</a>中，Morojo穿着\"未来派服装\"开创了这样的<a href=\"https://zh.wikipedia.org/wiki/表演藝術\">表演艺术</a>。此后不断有人仿效这类型的<a href=\"https://zh.wikipedia.org/wiki/角色扮演\">角色扮演</a>。而日本的<a href=\"https://zh.wikipedia.org/w/index.php?title=同人誌即售會&action=edit&redlink=1\">同人志即售会</a><a href=\"https://zh.wikipedia.org/wiki/Comic_Market\">Comic Market</a>中也开始出现装扮为<a href=\"https://zh.wikipedia.org/wiki/動畫\">动画</a>、<a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>角色的<a href=\"https://zh.wikipedia.org/wiki/角色扮演者\">角色扮演者</a>；1978年，<a href=\"https://zh.wikipedia.org/wiki/Comic_Market\">Comic Market</a>召集人米泽嘉博氏为场刊撰文时，以‘Costume play’来称呼这种行为，并在1984年美国<a href=\"https://zh.wikipedia.org/wiki/洛杉磯\">洛杉矶</a>举行的<a href=\"https://zh.wikipedia.org/wiki/世界科幻年會\">世界科幻年会</a>上，由赴会的日本动画家暨日本艺术工作室“Studio Hard”首席执行官高桥伸之把这种自力演绎角色的扮装性质表演艺术行为定义为<a href=\"https://zh.wikipedia.org/wiki/和製英語\">和制英语</a>词语“Cosplay”<sup>[5][6][7]</sup>。</p><p>之后到1990年代，日本ACG业界成功举办了大量的动漫画展和游戏展，业者为了宣传产品，在这些游戏展和漫画节中找人装扮成ACG作品中的角色以吸引参展人群。这个宣传策略与当初迪士尼开办<a href=\"https://zh.wikipedia.org/wiki/迪士尼樂園\">迪士尼乐园</a>时采用的方式如出一辙，因此可说当代Cosplay的蓬勃发展是拜ACG产业的发达。因此，Cosplay文化在ACG界热门化和发扬光大，同时借着各种Cosplay活动、传媒的介绍、<a href=\"https://zh.wikipedia.org/wiki/互聯網\">互联网</a>的大量传播等，使Cosplay的自由参与者激增，Cosplay才渐渐得到了真正的、独立的发展。更甚者，专门为Cosplay行为而举行的活动也渐渐出现，形式类似化妆舞会，民众逐渐能在越来越多的场合中看到奇装异服者，并了解到这集服饰、化妆、表演于一体的扮装文化现象──Cosplay。 <sup>[8]</sup></p><p>当代的Cosplay一般以<a href=\"https://zh.wikipedia.org/wiki/動畫\">动画</a>、<a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>、<a href=\"https://zh.wikipedia.org/wiki/遊戲\">游戏</a>、<a href=\"https://zh.wikipedia.org/wiki/電玩\">电玩</a>、<a href=\"https://zh.wikipedia.org/wiki/輕小說\">轻小说</a>、<a href=\"https://zh.wikipedia.org/wiki/電影\">电影</a>、影集、<a href=\"https://zh.wikipedia.org/wiki/特攝\">特摄</a>、<a href=\"https://zh.wikipedia.org/wiki/偶像團體\">偶像团体</a>、职业、历史故事、社会故事或是其他自创的有形<a href=\"https://zh.wikipedia.org/wiki/角色\">角色</a>为目标，刻意穿着类似的<a href=\"https://zh.wikipedia.org/wiki/服饰\">服饰</a>，加上<a href=\"https://zh.wikipedia.org/wiki/道具\">道具</a>的配搭，<a href=\"https://zh.wikipedia.org/wiki/化粧\">化妆</a>造型、<a href=\"https://zh.wikipedia.org/wiki/身體語言\">身体语言</a>等等<a href=\"https://zh.wikipedia.org/wiki/参数\">参数</a>，以人力扮演成一个“活起来”的角色。另一种Cosplay主要是对非人类的动物、军事武器、交通工具、土木基建、操作系统、网站等进行<a href=\"https://zh.wikipedia.org/wiki/擬人化\">拟人化</a>，灌以具智慧的灵魂，并以相应服饰、道具、化妆、身体语言等配套来呈现该等拟人化角色，其中一常见手法乃以<a href=\"https://zh.wikipedia.org/wiki/萌擬人化\">萌拟人化</a>形态出场。</p><p><br /></p><h3>角色扮装</h3><p><br /></p><p>最终幻想的Cosplay</p><p>扮装最早的起源可能是来自于对<a href=\"https://zh.wikipedia.org/wiki/神話\">神话</a><a href=\"https://zh.wikipedia.org/wiki/傳說\">传说</a>、民间逸闻等的演绎，以及节日故事、文艺作品、<a href=\"https://zh.wikipedia.org/wiki/哲學\">哲理学说</a>、<a href=\"https://zh.wikipedia.org/wiki/祭祖\">祭祖</a>情节、振奋助兴情节、侧绎愿望诉求、心灵幻想等，并以相应的<a href=\"https://zh.wikipedia.org/wiki/服饰\">服饰</a>、<a href=\"https://zh.wikipedia.org/wiki/道具\">道具</a>和情节，把要演绎的角色和内容活灵活现地呈现出来。这些活动通常属於戏剧表演、民俗活动等。比如说有<a href=\"https://zh.wikipedia.org/wiki/古希臘\">古希腊</a><a href=\"https://zh.wikipedia.org/wiki/古希腊宗教\">祭司</a>们的装扮，继而有两部伟大希腊史诗《<a href=\"https://zh.wikipedia.org/wiki/伊利亞特\">伊利亚特</a>》和《<a href=\"https://zh.wikipedia.org/wiki/奧德賽\">奥德赛</a>》的那群活跃于前8世纪的<a href=\"https://zh.wikipedia.org/wiki/吟遊詩人\">吟游诗人</a>们扮演着别人的角色。前者引变为后世的<a href=\"https://zh.wikipedia.org/wiki/先知\">先知</a>、先见，成功地演绎出神之<a href=\"https://zh.wikipedia.org/wiki/使徒\">使徒</a>的存在，而后者则如同是现今<a href=\"https://zh.wikipedia.org/wiki/話劇\">话剧</a>的鼻祖，出神入化地演绎出若干英雄事迹。</p><p>欧洲的游牧民族<a href=\"https://zh.wikipedia.org/wiki/吉普賽人\">吉普赛人</a>也可以说是最早的一批扮装表演者。每当路经一地，为了生存，他们就透过演出神话传说、民间逸闻、吟游弹唱的方式来获得面包与水，这其中各种演出用的服饰与道具自然是必不可少的装备。随时随地举行的角色化妆舞会、<a href=\"https://zh.wikipedia.org/wiki/万圣夜\">万圣节</a>游行、<a href=\"https://zh.wikipedia.org/wiki/新年\">新年</a>大游行、<a href=\"https://zh.wikipedia.org/wiki/國慶日\">国庆日</a>游行活动或特别盛典时中，不少人装扮成节日故事的人物或各类<a href=\"https://zh.wikipedia.org/wiki/吉祥物\">吉祥物</a>，浓厚的<a href=\"https://zh.wikipedia.org/wiki/扮裝\">扮装</a>文化得以体现。</p><p><a href=\"https://zh.wikipedia.org/wiki/中國\">中国</a>古代的先民也有着历史悠久的扮装文化。具有千年传统的<a href=\"https://zh.wikipedia.org/wiki/舞龍\">舞龙</a>仪式可以说是其中最具代表性的活动。舞龙在当时往往有两种寓意，一种是祈求上苍降甘露给农田，另一种则有祈求五谷丰登、万象吉祥之意。此活动进行前，首先要选出体格健壮、姿态威武的男性青年若干位，并让他们穿上黄、红色的代表喜庆的服饰（有时为贵族表演时衣服上甚至绣有花纹），按照事先的编舞他们将组成一支或多支舞龙队伍表演出各种方阵图案，并有<a href=\"https://zh.wikipedia.org/wiki/鼓\">鼓</a><a href=\"https://zh.wikipedia.org/wiki/鑼\">锣</a>声作为伴奏。到17世纪左右（即<a href=\"https://zh.wikipedia.org/wiki/明\">明</a>末<a href=\"https://zh.wikipedia.org/wiki/清\">清</a>初），由舞龙中又繁衍出了舞狮、<a href=\"https://zh.wikipedia.org/w/index.php?title=鳳舞龍翔&action=edit&redlink=1\">凤舞龙翔</a>的活动，这些都与以服饰扮装某些角色都有着紧密的联系。</p><p><a href=\"https://zh.wikipedia.org/wiki/西藏\">西藏</a>民间神话英雄的中国<a href=\"https://zh.wikipedia.org/wiki/藏戏\">藏戏</a>、<a href=\"https://zh.wikipedia.org/wiki/超渡\">超渡</a>亡魂到<a href=\"https://zh.wikipedia.org/wiki/極樂世界\">极乐世界</a>和<a href=\"https://zh.wikipedia.org/wiki/印度\">印度</a><a href=\"https://zh.wikipedia.org/wiki/佛教\">佛教</a>中的佛事、祭祀<a href=\"https://zh.wikipedia.org/w/index.php?title=山靈&action=edit&redlink=1\">山灵</a><a href=\"https://zh.wikipedia.org/wiki/神器\">神器</a>与<a href=\"https://zh.wikipedia.org/wiki/日本\">日本</a><a href=\"https://zh.wikipedia.org/wiki/神道教\">神道教</a><a href=\"https://zh.wikipedia.org/wiki/神社\">神社</a>活动，服饰、道具、表演都是这些活动的重要组成元素<sup>[9]</sup>。</p><p><br /></p><h3>迪士尼的推广</h3><p><br /></p><p>1930年代末期<a href=\"https://zh.wikipedia.org/wiki/和路迪士尼\">和路迪士尼</a><a href=\"https://zh.wikipedia.org/wiki/米奇老鼠\">米奇老鼠</a>出现，<a href=\"https://zh.wikipedia.org/wiki/美國\">美国</a>的动画风格有了一个确实的定义，而史上真正的第一个以动画人物为受扮者的Cosplay，也正是出于此时期。和路迪士尼看准时机适时的在1955年创建了世界上首座<a href=\"https://zh.wikipedia.org/wiki/迪士尼樂園\">迪士尼乐园</a>，同时为了替产品自身作宣传及为更好的吸引游客，和路迪士尼还特别请来员工，穿上米奇老鼠服饰以提供游客玩赏或是拍照留念。当初这群默默无闻的米奇老鼠装扮者就是当代全世界Cosplay行为的真正创始者。</p><p>起初为当时那群在迪士尼乐园中装扮成<a href=\"https://zh.wikipedia.org/wiki/米奇老鼠\">米奇老鼠</a>、<a href=\"https://zh.wikipedia.org/wiki/布鲁托_(大力水手)\">布鲁托（大力水手）</a>、<a href=\"https://zh.wikipedia.org/wiki/高飛狗\">高飞狗</a>、<a href=\"https://zh.wikipedia.org/wiki/唐老鴨\">唐老鸭</a>以及其他迪士尼人物制作Cosplay服饰的是和路迪士尼公司早期的道具部。在迪士尼乐园正式成立后不久，和路迪士尼扩大了道具部的规模，不仅要为影视作品制作道具，更负责所有在迪士尼乐园工作所需的Cosplay服饰。早期用作Cosplay的服饰只是一个拥有固定外形的“大纸袋”，缺乏美感和舒适，成品相对也较粗糙，装扮者穿上这种服饰后很容易发生呼吸不畅的现象。纵使如此，此时迪士尼的Cosplay服饰制作已算是拥有了一定的规模。</p><p>当代Cosplay最初成形的目的仍是出于一种商业上的形为而并非像现在这样是一种流行品位上的消费。将美国或是更确切的一点说，将迪士尼作为当代Cosplay的真正发源其实还有一个很重要的依据，那就是当时迪士尼卡通人物装扮者们身上所穿着Cosplay服饰的专业制作化。虽然以现在的Cosplay服饰而言，有许多是装扮者们自己所缝制的。但是作为一个当代Cosplay的鼻祖，拥有一个规范并且体系化的服饰制作组织是必要的条件。</p><p><br /></p><h3>与日本动漫结合</h3><p><br /></p><p><a href=\"https://zh.wikipedia.org/wiki/日本\">日本</a>的<a href=\"https://zh.wikipedia.org/wiki/ACG\">ACG</a>（指的是Animations动画、Comics漫画、Games游戏）市场兴起自1947年漫画之神<a href=\"https://zh.wikipedia.org/wiki/手塚治虫\">手冢治虫</a>根据酒井七马原作改编而成的<a href=\"https://zh.wikipedia.org/w/index.php?title=紅皮書&action=edit&redlink=1\">红皮书</a><a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>《<a href=\"https://zh.wikipedia.org/wiki/新宝岛_(漫画)\">新宝岛</a>》，为日本<a href=\"https://zh.wikipedia.org/wiki/ACG\">ACG</a>的地位打下了坚实的基础。恰好正在此时，迪士尼那种所为宣传而作的Cosplay活动被传入日本，有ACG界同好起而模仿，渐渐蔚为风潮，最终成了日本现在<a href=\"https://zh.wikipedia.org/wiki/ACG\">ACG</a>界的常态活动。直到在1955年左右，日本的扮装活动都仅仅只是小童间的玩意，但在服饰方面还是颇为讲究。当时不少小童都装扮《<a href=\"https://zh.wikipedia.org/wiki/月光假面\">月光假面</a>》与《<a href=\"https://zh.wikipedia.org/wiki/少年杰特\">少年杰特</a>》这两部作品的主人公。当时的日本并没有如迪士尼乐园般拥有专门的Cosplay服饰制作单位和行号，装扮者如想要拥有与动画中主人公相同服饰的话就必须先请画家绘好服饰设计图样，然后再到百货公司或裁缝店请师传缝制。现今著名的游戏制作人<a href=\"https://zh.wikipedia.org/wiki/廣井王子\">广井王子</a>小时候Cosplay的服饰设计图，便是请他家附近的一条<a href=\"https://zh.wikipedia.org/wiki/藝妓\">艺妓</a>街上的那些艺妓为他绘制的。这种较为粗制的状况一直维持了将近二十年的时间，直至1970年代末至1980年代初日本的ACG经历了探索和成长期之后，此时日本的Cosplay活动在起初是作为<a href=\"https://zh.wikipedia.org/w/index.php?title=看版娘&action=edit&redlink=1\">看版娘</a>在<a href=\"https://zh.wikipedia.org/wiki/同人誌即賣會\">同人志即卖会</a>而生，为各同好会等场合上活跃气氛的一种即兴节目，后期引申为伴随着动漫展览、游戏发布会上频繁出现。</p>', '/assets/user/images/test/33e500382a2db34c71d048b0ccc3a587.jpg', '/assets/user/images/test/thumb/33e500382a2db34c71d048b0ccc3a587.jpg', 1, '2024-05-28 23:31:01', '{\"mode\":1,\"template_id\":4}', 1, 1, '[]', 0, 1);


DROP TABLE IF EXISTS `${prefix}item_markup_template`;
CREATE TABLE `${prefix}item_markup_template`  (
                                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员ID',
                                                  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '模板名称',
                                                  `drift_model` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '加价方式：0=比例加价，1=固定金额加价（基数自动比例）',
                                                  `drift_value` decimal(14, 6) UNSIGNED NOT NULL COMMENT '加价数量/比例',
                                                  `drift_base_amount` decimal(14, 6) UNSIGNED NOT NULL COMMENT '基数',
                                                  `sync_amount` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步价格',
                                                  `sync_name` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步商品名称',
                                                  `sync_introduce` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步商品介绍',
                                                  `sync_picture` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步商品图片',
                                                  `sync_sku_name` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步SKU名称',
                                                  `sync_sku_picture` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步SKU图片',
                                                  `keep_decimals` tinyint(4) UNSIGNED NOT NULL DEFAULT 2 COMMENT '保留小数位数',
                                                  `create_time` datetime NOT NULL COMMENT '创建时间',
                                                  PRIMARY KEY (`id`) USING BTREE,
                                                  INDEX `user_id`(`user_id`) USING BTREE,
                                                  CONSTRAINT `${prefix}item_markup_template_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}item_markup_template` VALUES (1, NULL, '直接同步仓库', 1, 0.000000, 10.000000, 2, 1, 1, 1, 1, 1, 2, '2024-05-28 23:30:48');


DROP TABLE IF EXISTS `${prefix}item_sku`;
CREATE TABLE `${prefix}item_sku`  (
                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                      `repertory_item_sku_id` bigint(20) UNSIGNED NOT NULL COMMENT '仓库商品的sku对应id',
                                      `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id，空为后台',
                                      `item_id` bigint(20) UNSIGNED NOT NULL COMMENT '物品id',
                                      `picture_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '宣传图',
                                      `picture_thumb_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩略图',
                                      `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
                                      `price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '单价',
                                      `stock_price` decimal(16, 6) UNSIGNED NOT NULL COMMENT '进货价',
                                      `dividend_amount` decimal(14, 6) UNSIGNED NULL DEFAULT NULL COMMENT '分红金额',
                                      `sort` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '排序',
                                      `create_time` datetime NOT NULL COMMENT '创建时间',
                                      `private_display` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '私密展示：0=关，1=开',
                                      PRIMARY KEY (`id`) USING BTREE,
                                      INDEX `item_sku`(`item_id`, `sort`) USING BTREE,
                                      INDEX `item_id`(`item_id`) USING BTREE,
                                      INDEX `user_id`(`user_id`) USING BTREE,
                                      INDEX `repertory_item_sku_id`(`repertory_item_sku_id`, `item_id`) USING BTREE,
                                      CONSTRAINT `${prefix}item_sku_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `${prefix}item` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 180 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}item_sku` VALUES (177, 40, NULL, 89, '/assets/user/images/test/9baf7fc1f11f08b836bc16044781b648.jpg', '/assets/user/images/test/thumb/9baf7fc1f11f08b836bc16044781b648.jpg', '演示SKU1', 1.100000, 1.000000, NULL, 0, '2024-05-28 23:31:01', 0);
INSERT INTO `${prefix}item_sku` VALUES (178, 41, NULL, 89, '/assets/user/images/test/a592ec87c83405d86fffc5cc84d69523.jpg', '/assets/user/images/test/thumb/a592ec87c83405d86fffc5cc84d69523.jpg', '演示SKU2', 2.200000, 2.000000, NULL, 0, '2024-05-28 23:31:01', 0);
INSERT INTO `${prefix}item_sku` VALUES (179, 42, NULL, 89, '/assets/user/images/test/c0176e0509550ad018ed18170c79d917.jpg', '/assets/user/images/test/thumb/c0176e0509550ad018ed18170c79d917.jpg', '演示SKU3', 5.500000, 5.000000, NULL, 0, '2024-05-28 23:31:01', 0);


DROP TABLE IF EXISTS `${prefix}item_sku_level`;
CREATE TABLE `${prefix}item_sku_level`  (
                                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家ID，空代表后台',
                                            `level_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '等级ID',
                                            `sku_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT 'SKU ID',
                                            `price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '单价',
                                            `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                            `create_time` datetime NOT NULL COMMENT '创建时间',
                                            `markup` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '加价方案',
                                            `dividend_amount` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '分红金额',
                                            PRIMARY KEY (`id`) USING BTREE,
                                            INDEX `user_id`(`user_id`) USING BTREE,
                                            INDEX `sku_id`(`sku_id`) USING BTREE,
                                            INDEX `level_id`(`level_id`, `sku_id`) USING BTREE,
                                            CONSTRAINT `${prefix}item_sku_level_ibfk_1` FOREIGN KEY (`sku_id`) REFERENCES `${prefix}item_sku` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}item_sku_user`;
CREATE TABLE `${prefix}item_sku_user`  (
                                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家ID，空则为后台',
                                           `customer_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '顾客ID（user_id）',
                                           `sku_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT 'SKU ID',
                                           `price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '单价',
                                           `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                           `create_time` datetime NOT NULL COMMENT '创建时间',
                                           `markup` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '加价方案',
                                           `dividend_amount` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '分红金额',
                                           PRIMARY KEY (`id`) USING BTREE,
                                           INDEX `user_id`(`user_id`) USING BTREE,
                                           INDEX `customer_id`(`customer_id`) USING BTREE,
                                           INDEX `sku_id`(`sku_id`) USING BTREE,
                                           CONSTRAINT `${prefix}item_sku_user_ibfk_1` FOREIGN KEY (`sku_id`) REFERENCES `${prefix}item_sku` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}item_sku_wholesale`;
CREATE TABLE `${prefix}item_sku_wholesale`  (
                                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                `repertory_item_sku_wholesale_id` bigint(20) UNSIGNED NOT NULL,
                                                `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家id，空为后台',
                                                `sku_id` bigint(20) UNSIGNED NOT NULL COMMENT 'SKU ID',
                                                `quantity` bigint(20) UNSIGNED NOT NULL COMMENT '数量',
                                                `price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '单价',
                                                `stock_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '仓库价',
                                                `create_time` datetime NOT NULL COMMENT '创建时间',
                                                `dividend_amount` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '分红金额',
                                                PRIMARY KEY (`id`) USING BTREE,
                                                INDEX `user_id`(`user_id`) USING BTREE,
                                                INDEX `repertory_item_sku_wholesale_id`(`repertory_item_sku_wholesale_id`, `sku_id`) USING BTREE,
                                                INDEX `sku_id`(`sku_id`) USING BTREE,
                                                INDEX `quantity`(`quantity`) USING BTREE,
                                                CONSTRAINT `${prefix}item_sku_wholesale_ibfk_1` FOREIGN KEY (`repertory_item_sku_wholesale_id`) REFERENCES `${prefix}repertory_item_sku_wholesale` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                                CONSTRAINT `${prefix}item_sku_wholesale_ibfk_2` FOREIGN KEY (`sku_id`) REFERENCES `${prefix}item_sku` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}item_sku_wholesale_level`;
CREATE TABLE `${prefix}item_sku_wholesale_level`  (
                                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                      `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家id，空为后台',
                                                      `wholesale_id` bigint(20) UNSIGNED NOT NULL COMMENT '批发规则id',
                                                      `level_id` int(11) UNSIGNED NOT NULL COMMENT '等级id',
                                                      `price` decimal(14, 6) UNSIGNED NOT NULL COMMENT '单价',
                                                      `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                                      `create_time` datetime NOT NULL COMMENT '创建时间',
                                                      `dividend_amount` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '分红金额',
                                                      PRIMARY KEY (`id`) USING BTREE,
                                                      INDEX `user_id`(`user_id`) USING BTREE,
                                                      INDEX `wholesale_id`(`wholesale_id`) USING BTREE,
                                                      INDEX `level_id`(`level_id`) USING BTREE,
                                                      INDEX `item_sku_wholesale_level`(`user_id`, `wholesale_id`, `level_id`) USING BTREE,
                                                      CONSTRAINT `${prefix}item_sku_wholesale_level_ibfk_1` FOREIGN KEY (`wholesale_id`) REFERENCES `${prefix}item_sku_wholesale` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                                      CONSTRAINT `${prefix}item_sku_wholesale_level_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `${prefix}user_level` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}item_sku_wholesale_user`;
CREATE TABLE `${prefix}item_sku_wholesale_user`  (
                                                     `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                     `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家id，空为后台',
                                                     `wholesale_id` bigint(20) UNSIGNED NOT NULL COMMENT '批发规则id',
                                                     `customer_id` bigint(20) UNSIGNED NOT NULL COMMENT '会员id',
                                                     `price` decimal(14, 6) UNSIGNED NOT NULL COMMENT '单价',
                                                     `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                                     `create_time` datetime NOT NULL COMMENT '创建时间',
                                                     `dividend_amount` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '分红金额',
                                                     PRIMARY KEY (`id`) USING BTREE,
                                                     INDEX `user_id`(`user_id`) USING BTREE,
                                                     INDEX `wholesale_id`(`wholesale_id`) USING BTREE,
                                                     INDEX `customer_id`(`customer_id`) USING BTREE,
                                                     INDEX `item_sku_wholesale_user`(`user_id`, `wholesale_id`, `customer_id`) USING BTREE,
                                                     CONSTRAINT `${prefix}item_sku_wholesale_user_ibfk_1` FOREIGN KEY (`wholesale_id`) REFERENCES `${prefix}item_sku_wholesale` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}manage`;
CREATE TABLE `${prefix}manage`  (
                                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                    `email` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱',
                                    `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
                                    `security_password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '安全密码',
                                    `nickname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '昵称',
                                    `salt` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '盐',
                                    `avatar` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '头像',
                                    `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=冻结，1=正常',
                                    `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型：0=系统账号，1=子账号',
                                    `create_time` datetime NOT NULL COMMENT '创建时间',
                                    `login_time` datetime NULL DEFAULT NULL COMMENT '登录时间',
                                    `last_login_time` datetime NULL DEFAULT NULL COMMENT '上一次登录时间',
                                    `login_ip` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录IP',
                                    `last_login_ip` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '上一次登录IP',
                                    `login_ua` varchar(768) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录UA',
                                    `last_login_ua` varchar(768) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '上次登录UA',
                                    `client_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '客户端-token',
                                    `note` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
                                    `login_status` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '登录状态',
                                    PRIMARY KEY (`id`) USING BTREE,
                                    UNIQUE INDEX `email`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}manage` VALUES (1, '${email}', '${password}', '${password}', '${nickname}', '${salt}', '/favicon.ico', 1, 0, '2024-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL , NULL , NULL, 'system', 1);


DROP TABLE IF EXISTS `${prefix}manage_log`;
CREATE TABLE `${prefix}manage_log`  (
                                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                        `manage_id` bigint(20) UNSIGNED NOT NULL COMMENT '管理员id',
                                        `email` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱',
                                        `nickname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '呢称',
                                        `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志内容',
                                        `request_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'url地址',
                                        `request_method` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '请求方法',
                                        `request_param` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '相关参数',
                                        `create_time` datetime NOT NULL COMMENT '创建时间',
                                        `create_ip` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'IP地址',
                                        `ua` varchar(768) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '浏览器UA',
                                        `client_token` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'client_token',
                                        `risk` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '风险：0=正常，1=一般风险，2=高危风险',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        INDEX `email`(`email`) USING BTREE,
                                        INDEX `create_ip`(`create_ip`) USING BTREE,
                                        INDEX `manage_id`(`manage_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 458 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `${prefix}manage_login_log`;
CREATE TABLE `${prefix}manage_login_log`  (
                                              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                              `manage_id` bigint(20) UNSIGNED NOT NULL,
                                              `create_time` datetime NOT NULL COMMENT '创建时间',
                                              `ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'IP地址',
                                              `ua` varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '浏览器UA',
                                              `is_dangerous` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否危险：0=否，1=是',
                                              PRIMARY KEY (`id`) USING BTREE,
                                              INDEX `ip`(`ip`) USING BTREE,
                                              INDEX `manage_id`(`manage_id`) USING BTREE,
                                              INDEX `is_dangerous`(`is_dangerous`) USING BTREE,
                                              CONSTRAINT `${prefix}manage_login_log_ibfk_1` FOREIGN KEY (`manage_id`) REFERENCES `${prefix}manage` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 33 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `${prefix}manage_role`;
CREATE TABLE `${prefix}manage_role`  (
                                         `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
                                         `manage_id` bigint(20) UNSIGNED NOT NULL COMMENT '管理员id',
                                         `role_id` bigint(20) UNSIGNED NOT NULL COMMENT '角色id',
                                         PRIMARY KEY (`id`) USING BTREE,
                                         UNIQUE INDEX `manage_id`(`manage_id`, `role_id`) USING BTREE,
                                         INDEX `role_id`(`role_id`) USING BTREE,
                                         CONSTRAINT `${prefix}manage_role_ibfk_1` FOREIGN KEY (`manage_id`) REFERENCES `${prefix}manage` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                         CONSTRAINT `${prefix}manage_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `${prefix}role` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}manage_role` VALUES (25, 1, 1);


DROP TABLE IF EXISTS `${prefix}order`;
CREATE TABLE `${prefix}order`  (
                                   `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家ID，null=后台',
                                   `customer_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '用户ID，null=游客',
                                   `invite_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '推广用户ID',
                                   `client_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '客户端ID',
                                   `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
                                   `total_amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '订单总价',
                                   `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=未付款，1=已付款，2=已取消，3=正在付款',
                                   `type` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单类型：0=商品订单，1=充值订单，2=升级用户组',
                                   `create_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '下单IP',
                                   `create_browser` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '下单浏览器',
                                   `create_device` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '设备型号',
                                   `create_time` datetime NOT NULL COMMENT '创建时间',
                                   `pay_time` datetime NULL DEFAULT NULL COMMENT '支付时间',
                                   `option` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '可选参数',
                                   PRIMARY KEY (`id`) USING BTREE,
                                   UNIQUE INDEX `trade_no`(`trade_no`) USING BTREE,
                                   INDEX `user_id`(`user_id`) USING BTREE,
                                   INDEX `total_amount`(`total_amount`) USING BTREE,
                                   INDEX `status`(`status`) USING BTREE,
                                   INDEX `create_time`(`create_time`) USING BTREE,
                                   INDEX `customer_id`(`customer_id`) USING BTREE,
                                   INDEX `type`(`type`) USING BTREE,
                                   INDEX `client_id`(`client_id`) USING BTREE,
                                   INDEX `create_ip`(`create_ip`) USING BTREE,
                                   INDEX `invite_id`(`invite_id`) USING BTREE,
                                   INDEX `limiter`(`type`, `create_ip`, `create_time`) USING BTREE,
                                   INDEX `clear_unpaid_order`(`customer_id`, `type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 790 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}order_item`;
CREATE TABLE `${prefix}order_item`  (
                                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家id，null=后台',
                                        `order_id` bigint(20) UNSIGNED NOT NULL COMMENT '订单id',
                                        `item_id` bigint(20) UNSIGNED NOT NULL COMMENT '商品id',
                                        `sku_id` bigint(20) UNSIGNED NOT NULL COMMENT 'SKU id',
                                        `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
                                        `quantity` int(11) UNSIGNED NOT NULL COMMENT '商品数量',
                                        `amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格',
                                        `dividend_amount` decimal(10, 2) UNSIGNED NULL DEFAULT 0.00 COMMENT '分红金额',
                                        `status` tinyint(4) UNSIGNED NOT NULL COMMENT '状态：0=等待发货，1=已发货，2=发货失败，3=已收货(确认收货)，4=正在维权，5=已退款',
                                        `refund_mode` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '退款方式：退款方式：0=不支持，1=有条件退款，2=无理由退款',
                                        `treasure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '宝贝内容',
                                        `widget` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '控件内容',
                                        `contact` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系方式',
                                        `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
                                        `create_time` datetime NOT NULL COMMENT '创建时间',
                                        `auto_receipt_time` datetime NULL DEFAULT NULL COMMENT '自动收货时间',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        INDEX `order_id`(`order_id`) USING BTREE,
                                        INDEX `item_id`(`item_id`) USING BTREE,
                                        INDEX `sku_id`(`sku_id`) USING BTREE,
                                        INDEX `status`(`status`) USING BTREE,
                                        INDEX `itemId_and_status`(`item_id`, `status`) USING BTREE,
                                        INDEX `create_time`(`create_time`) USING BTREE,
                                        INDEX `user_id`(`user_id`) USING BTREE,
                                        INDEX `auto_receipt_time`(`auto_receipt_time`) USING BTREE,
                                        INDEX `contact`(`contact`) USING BTREE,
                                        CONSTRAINT `${prefix}order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `${prefix}order` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 529 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}order_recharge`;
CREATE TABLE `${prefix}order_recharge`  (
                                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id，null为后台',
                                            `order_id` bigint(20) UNSIGNED NOT NULL COMMENT '订单id',
                                            `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
                                            `amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '充值金额',
                                            `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '充值状态：0=等待充值，1=充值成功',
                                            `create_time` datetime NOT NULL COMMENT '创建时间',
                                            `recharge_time` datetime NULL DEFAULT NULL COMMENT '充值时间',
                                            PRIMARY KEY (`id`) USING BTREE,
                                            UNIQUE INDEX `order_id`(`order_id`) USING BTREE,
                                            UNIQUE INDEX `trade_no`(`trade_no`) USING BTREE,
                                            INDEX `user_id`(`user_id`) USING BTREE,
                                            INDEX `status`(`status`) USING BTREE,
                                            INDEX `create_time`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}order_report`;
CREATE TABLE `${prefix}order_report`  (
                                          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                          `order_item_id` bigint(20) UNSIGNED NOT NULL COMMENT '物品订单id',
                                          `supply_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商id，null=主站',
                                          `merchant_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家id，null=主站',
                                          `customer_id` bigint(20) UNSIGNED NOT NULL COMMENT '消费者id',
                                          `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=待处理，1=商家处理中，2=用户处理中，3=处理完毕',
                                          `refund_amount` decimal(10, 2) UNSIGNED NULL DEFAULT 0.00 COMMENT '退款给顾客的金额',
                                          `refund_merchant_amount` decimal(10, 2) UNSIGNED NULL DEFAULT NULL COMMENT '退款给商家的金额',
                                          `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '维权原因',
                                          `expect` tinyint(4) UNSIGNED NOT NULL COMMENT '维权意向：0=更换商品，1=申请退款，2=假一赔三',
                                          `handle_type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '处理方式：0=交涉中，1=更换/补发商品，2=部分退款，3=全额退款，4=已撤诉',
                                          `create_time` datetime NOT NULL COMMENT '创建时间',
                                          PRIMARY KEY (`id`) USING BTREE,
                                          INDEX `order_item_id`(`order_item_id`) USING BTREE,
                                          INDEX `supply_id`(`supply_id`) USING BTREE,
                                          INDEX `merchant_id`(`merchant_id`) USING BTREE,
                                          INDEX `customer_id`(`customer_id`) USING BTREE,
                                          INDEX `status`(`status`) USING BTREE,
                                          INDEX `type`(`type`) USING BTREE,
                                          INDEX `expect`(`expect`) USING BTREE,
                                          INDEX `handle_type`(`handle_type`) USING BTREE,
                                          INDEX `create_time`(`create_time`) USING BTREE,
                                          CONSTRAINT `${prefix}order_report_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `${prefix}order_item` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                          CONSTRAINT `${prefix}order_report_ibfk_2` FOREIGN KEY (`supply_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                          CONSTRAINT `${prefix}order_report_ibfk_3` FOREIGN KEY (`merchant_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                          CONSTRAINT `${prefix}order_report_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}order_report_message`;
CREATE TABLE `${prefix}order_report_message`  (
                                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                  `order_report_id` bigint(20) UNSIGNED NOT NULL COMMENT '投诉订单id',
                                                  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '消息内容',
                                                  `image_url` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '相关图片',
                                                  `role` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色：0=平台，1=供货商，2=消费者',
                                                  `create_time` datetime NOT NULL COMMENT '创建时间',
                                                  PRIMARY KEY (`id`) USING BTREE,
                                                  INDEX `order_report_id`(`order_report_id`) USING BTREE,
                                                  CONSTRAINT `${prefix}order_report_message_ibfk_1` FOREIGN KEY (`order_report_id`) REFERENCES `${prefix}order_report` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 109 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}pay`;
CREATE TABLE `${prefix}pay`  (
                                 `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员ID',
                                 `name` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '支付名称',
                                 `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
                                 `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'code(可自定义)',
                                 `create_time` datetime NOT NULL COMMENT '添加时间',
                                 `pay_config_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '配置id',
                                 `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                 `equipment` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '设备：0=通用，1=手机，2=电脑',
                                 `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '交易：0=关闭，1=开启',
                                 `pid` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '上级ID（如果商家使用系统支付，则启用此ID）',
                                 `scope` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '应用范围',
                                 `substation_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否支持分站：0=不支持，1=支持',
                                 `substation_fee` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT '分站手续费',
                                 `api_fee` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT 'API调用手续费',
                                 PRIMARY KEY (`id`) USING BTREE,
                                 INDEX `user_id`(`user_id`) USING BTREE,
                                 INDEX `sort`(`sort`) USING BTREE,
                                 INDEX `equipment`(`equipment`) USING BTREE,
                                 INDEX `pay_config_id`(`pay_config_id`) USING BTREE,
                                 INDEX `trade_status`(`status`) USING BTREE,
                                 INDEX `func_get_list`(`user_id`, `equipment`, `status`) USING BTREE,
                                 INDEX `pid`(`pid`) USING BTREE,
                                 CONSTRAINT `${prefix}pay_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                 CONSTRAINT `${prefix}pay_ibfk_3` FOREIGN KEY (`pid`) REFERENCES `${prefix}pay` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                 CONSTRAINT `plugin_config` FOREIGN KEY (`pay_config_id`) REFERENCES `${prefix}plugin_config` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}pay_config`;
CREATE TABLE `${prefix}pay_config`  (
                                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id',
                                        `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '配置名称',
                                        `plugin` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '支付插件',
                                        `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '配置文件',
                                        `create_time` datetime NOT NULL COMMENT '创建时间',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        INDEX `plugin`(`plugin`) USING BTREE,
                                        INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}pay_group`;
CREATE TABLE `${prefix}pay_group`  (
                                       `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                       `group_id` int(10) UNSIGNED NOT NULL,
                                       `pay_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
                                       `temp_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
                                       `fee` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000,
                                       `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
                                       `create_time` datetime NOT NULL,
                                       PRIMARY KEY (`id`) USING BTREE,
                                       UNIQUE INDEX `group_pay`(`group_id`, `pay_id`) USING BTREE,
                                       UNIQUE INDEX `temp_pay`(`temp_id`, `pay_id`) USING BTREE,
                                       INDEX `group_id`(`group_id`) USING BTREE,
                                       INDEX `pay_id`(`pay_id`) USING BTREE,
                                       INDEX `status`(`status`) USING BTREE,
                                       INDEX `create_time`(`create_time`) USING BTREE,
                                       CONSTRAINT `pay_group_ibfk1` FOREIGN KEY (`group_id`) REFERENCES `${prefix}user_group` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                       CONSTRAINT `pay_group_ibfk2` FOREIGN KEY (`pay_id`) REFERENCES `${prefix}pay` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;




DROP TABLE IF EXISTS `${prefix}pay_order`;
CREATE TABLE `${prefix}pay_order`  (
                                       `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                       `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商家ID，null=后台',
                                       `customer_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '用户ID，null=游客',
                                       `pay_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '支付ID',
                                       `order_id` bigint(20) UNSIGNED NOT NULL COMMENT '订单id',
                                       `order_amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '订单总金额',
                                       `trade_amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '付款金额',
                                       `balance_amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '余额抵扣金额',
                                       `status` tinyint(4) UNSIGNED NOT NULL COMMENT '状态：0=未支付，1=正在支付，2=支付成功，3=支付关闭',
                                       `pay_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付地址',
                                       `render_mode` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '渲染方式：0=直接跳转，1=表单POST提交，2=本地渲染',
                                       `timeout` datetime NULL DEFAULT NULL COMMENT '订单到期时间',
                                       `create_time` datetime NOT NULL COMMENT '创建时间',
                                       `pay_time` datetime NULL DEFAULT NULL COMMENT '支付时间',
                                       `fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '手续费',
                                       `api_fee` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'API手续费',
                                       PRIMARY KEY (`id`) USING BTREE,
                                       UNIQUE INDEX `order_id`(`order_id`) USING BTREE,
                                       INDEX `pay_id`(`pay_id`) USING BTREE,
                                       INDEX `user_id`(`user_id`) USING BTREE,
                                       INDEX `customer_id`(`customer_id`) USING BTREE,
                                       INDEX `timeout`(`timeout`) USING BTREE,
                                       INDEX `create_time`(`create_time`) USING BTREE,
                                       INDEX `pay_time`(`pay_time`) USING BTREE,
                                       INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 349 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}pay_order_option`;
CREATE TABLE `${prefix}pay_order_option`  (
                                              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                              `pay_order_id` bigint(20) UNSIGNED NOT NULL,
                                              `option` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                                              `create_date` datetime NOT NULL COMMENT '创建时间',
                                              PRIMARY KEY (`id`) USING BTREE,
                                              UNIQUE INDEX `pay_order_id`(`pay_order_id`) USING BTREE,
                                              INDEX `create_date`(`create_date`) USING BTREE,
                                              CONSTRAINT `${prefix}pay_order_option_ibfk_1` FOREIGN KEY (`pay_order_id`) REFERENCES `${prefix}pay_order` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;



DROP TABLE IF EXISTS `${prefix}pay_user`;
CREATE TABLE `${prefix}pay_user`  (
                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `user_id` bigint(20) UNSIGNED NOT NULL,
                                      `pay_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
                                      `temp_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
                                      `fee` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000,
                                      `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
                                      `create_time` datetime NOT NULL,
                                      PRIMARY KEY (`id`) USING BTREE,
                                      UNIQUE INDEX `user_pay`(`user_id`, `pay_id`) USING BTREE,
                                      UNIQUE INDEX `temp_pay`(`temp_id`, `pay_id`) USING BTREE,
                                      INDEX `user_id`(`user_id`) USING BTREE,
                                      INDEX `pay_id`(`pay_id`) USING BTREE,
                                      INDEX `status`(`status`) USING BTREE,
                                      INDEX `create_time`(`create_time`) USING BTREE,
                                      CONSTRAINT `pay_user_ibfk1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                      CONSTRAINT `pay_user_ibfk2` FOREIGN KEY (`pay_id`) REFERENCES `${prefix}pay` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;




DROP TABLE IF EXISTS `${prefix}permission`;
CREATE TABLE `${prefix}permission`  (
                                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
                                        `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '权限名称',
                                        `pid` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '上级id',
                                        `route` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '路由',
                                        `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型：0=纯菜单，1=PAGE，2=API，3=不渲染',
                                        `rank` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                        `icon` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图标',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        UNIQUE INDEX `route`(`route`) USING BTREE,
                                        INDEX `pid`(`pid`) USING BTREE,
                                        INDEX `rank`(`rank`) USING BTREE,
                                        CONSTRAINT `${prefix}permission_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `${prefix}permission` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 257 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}permission` VALUES (1, 'CONFIG', NULL, 'admin.config', 0, 6, '');
INSERT INTO `${prefix}permission` VALUES (2, '管理员', 11, '/admin/manage@GET', 1, 2, 'icon-OAtubiao_xitongguanliyuan');
INSERT INTO `${prefix}permission` VALUES (3, '获取数据', 2, '/admin/manage/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (4, 'MAIN', NULL, 'admin.main', 0, 0, '');
INSERT INTO `${prefix}permission` VALUES (5, '控制台', 4, '/admin/dashboard@GET', 1, 0, 'icon-shouye1');
INSERT INTO `${prefix}permission` VALUES (6, '保存数据', 2, '/admin/manage/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (7, '数据字典', NULL, 'admin.dict', 3, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (8, '角色列表-字典', 7, '/admin/dict/role@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (9, '工具接口', NULL, 'admin.tool.api', 3, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (10, '上传文件', 9, '/admin/upload@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (11, '访问管理', 1, 'admin.config.access', 0, 0, 'icon-anquan');
INSERT INTO `${prefix}permission` VALUES (12, '角色管理', 11, '/admin/role@GET', 1, 0, 'icon-jiaoseguanli');
INSERT INTO `${prefix}permission` VALUES (13, '获取数据', 12, '/admin/role/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (14, '保存数据', 12, '/admin/role/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (15, '权限列表-字典', 7, '/admin/dict/permission@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (16, '删除数据', 12, '/admin/role/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (17, '删除数据', 2, '/admin/manage/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (18, 'REPERTORY', NULL, 'admin.repertory', 0, 3, '');
INSERT INTO `${prefix}permission` VALUES (19, '仓库分类', 168, '/admin/repertory/category@GET', 1, 0, 'icon-fenlei');
INSERT INTO `${prefix}permission` VALUES (20, '获取数据', 19, '/admin/repertory/category/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (21, '保存数据', 19, '/admin/repertory/category/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (22, '删除数据', 19, '/admin/repertory/category/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (23, '货源管理', 168, '/admin/repertory/item@GET', 1, 1, 'icon-m_whms');
INSERT INTO `${prefix}permission` VALUES (24, '获取数据', 23, '/admin/repertory/item/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (25, '保存数据', 23, '/admin/repertory/item/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (26, '删除数据', 23, '/admin/repertory/item/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (27, '仓库分类-字典', 7, '/admin/dict/repertoryCategory@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (28, '获取SKU列表', 23, '/admin/repertory/item/sku/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (29, '保存SKU', 23, '/admin/repertory/item/sku/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (30, '删除SKU', 23, '/admin/repertory/item/sku/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (31, 'SKU-获取定价列表-用户组', 23, '/admin/repertory/item/sku/group/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (32, 'SKU-保存定价设置-用户组', 23, '/admin/repertory/item/sku/group/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (33, 'SKU-获取定价列表-会员', 23, '/admin/repertory/item/sku/user/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (34, 'SKU-保存定价设置-会员', 23, '/admin/repertory/item/sku/user/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (35, 'SKU-获取批发列表', 23, '/admin/repertory/item/sku/wholesale/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (36, 'SKU-保存批发设置', 23, '/admin/repertory/item/sku/wholesale/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (37, 'SKU-删除批发配置', 23, '/admin/repertory/item/sku/wholesale/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (38, 'SKU-获取批发列表-用户组', 23, '/admin/repertory/item/sku/wholesale/group/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (39, 'SKU-保存批发设置-用户组', 23, '/admin/repertory/item/sku/wholesale/group/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (40, 'SKU-获取批发列表-会员', 23, '/admin/repertory/item/sku/wholesale/user/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (41, 'SKU-保存批发设置-会员', 23, '/admin/repertory/item/sku/wholesale/user/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (44, '系统设置', 1, '/admin/config@GET', 1, 9, 'icon-shezhi3');
INSERT INTO `${prefix}permission` VALUES (45, '获取数据', 44, '/admin/config/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (46, '保存数据', 44, '/admin/config/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (47, '模板列表-字典', 7, '/admin/dict/theme@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (48, 'SHOP', NULL, 'admin.shop', 0, 1, NULL);
INSERT INTO `${prefix}permission` VALUES (49, '商品分类', 169, '/admin/shop/category@GET', 1, 0, 'icon-fenlei5');
INSERT INTO `${prefix}permission` VALUES (50, '保存数据', 49, '/admin/shop/category/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (51, '获取数据', 49, '/admin/shop/category/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (52, '删除数据', 49, '/admin/shop/category/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (53, '商品管理', 169, '/admin/shop/item@GET', 1, 1, 'icon-shangpin1');
INSERT INTO `${prefix}permission` VALUES (54, '保存数据', 53, '/admin/shop/item/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (55, '获取数据', 53, '/admin/shop/item/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (56, '删除数据', 53, '/admin/shop/item/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (57, '商品分类-字典', 7, '/admin/dict/shopCategory@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (58, '仓库商品导入到直营店', 23, '/admin/repertory/item/transferShop@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (59, '货源插件列表-字典', 7, '/admin/dict/ship@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (60, 'APP', NULL, 'admin.plugin', 0, 5, '');
INSERT INTO `${prefix}permission` VALUES (61, '插件管理', 60, '/admin/plugin@GET', 1, 2, 'icon-chajianhua');
INSERT INTO `${prefix}permission` VALUES (62, '获取Submit', 61, '/admin/plugin/submit/js@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (63, '进货订单', 18, '/admin/repertory/order@GET', 1, 0, 'icon-dingdan');
INSERT INTO `${prefix}permission` VALUES (64, '获取数据', 63, '/admin/repertory/order/get@POST', 2, 0, '');
INSERT INTO `${prefix}permission` VALUES (65, '会员列表-字典', 7, '/admin/dict/user@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (66, '商品列表-字典', 7, '/admin/dict/repertoryItem@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (67, '商品SKU列表-字典', 7, '/admin/dict/repertoryItemSku@POST', 2, 0, '');
INSERT INTO `${prefix}permission` VALUES (69, 'SKU-获取数据', 53, '/admin/shop/item/sku/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (70, 'SKU-保存数据', 53, '/admin/shop/item/sku/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (71, 'SKU-会员等级-获取数据', 53, '/admin/shop/item/sku/level/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (72, 'SKU-会员等级-保存数据', 53, '/admin/shop/item/sku/level/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (73, 'SKU-会员-获取数据', 53, '/admin/shop/item/sku/user/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (74, 'SKU-会员-保存数据', 53, '/admin/shop/item/sku/user/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (75, '获取加价模板-字典', 7, '/admin/dict/itemMarkupTemplate@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (76, '同步模板', 169, '/admin/shop/item/markup@GET', 1, 2, 'icon-jiage');
INSERT INTO `${prefix}permission` VALUES (77, '同步模板-获取数据', 76, '/admin/shop/item/markup/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (78, '同步模板-保存数据', 76, '/admin/shop/item/markup/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (79, '同步模板-删除数据', 76, '/admin/shop/item/markup/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (80, 'SKU-批发设置-获取数据', 53, '/admin/shop/item/sku/wholesale/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (81, 'SKU-批发设置-修改价格', 53, '/admin/shop/item/sku/wholesale/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (82, 'SKU-批发设置-会员等级-获取数据', 53, '/admin/shop/item/sku/wholesale/level/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (83, 'SKU-批发设置-会员等级-保存数据', 53, '/admin/shop/item/sku/wholesale/level/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (84, 'SKU-批发设置-会员-获取数据', 53, '/admin/shop/item/sku/wholesale/user/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (85, 'SKU-批发设置-会员-保存数据', 53, '/admin/shop/item/sku/wholesale/user/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (86, '应用商店', 60, '/admin/store@GET', 2, 1, 'icon-yingyongzhongxin');
INSERT INTO `${prefix}permission` VALUES (87, '获取插件列表', 61, '/admin/plugin/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (88, '启动插件', 61, '/admin/plugin/start@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (89, '停止插件', 61, '/admin/plugin/stop@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (90, '获取插件日志', 61, '/admin/plugin/getLogs@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (91, '清空插件日志', 61, '/admin/plugin/clearLog@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (92, '设置插件配置', 61, '/admin/plugin/setCfg@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (93, '获取handle配置文件列表', 61, '/admin/plugin/config/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (94, '保存handle配置文件', 61, '/admin/plugin/config/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (95, '删除handle配置文件', 61, '/admin/plugin/config/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (96, 'Pay', NULL, 'admin.pay', 0, 4, NULL);
INSERT INTO `${prefix}permission` VALUES (97, '支付接口', 96, '/admin/pay@GET', 1, 0, 'icon-zhifu');
INSERT INTO `${prefix}permission` VALUES (98, '获取数据', 97, '/admin/pay/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (99, '保存数据', 97, '/admin/pay/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (100, '删除数据', 97, '/admin/pay/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (101, '支付插件-列表', 7, '/admin/dict/pay@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (102, '支付接口-获取插件配置列表', 97, '/admin/pay/config@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (103, '支付插件-获取CODE', 97, '/admin/pay/code@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (104, '其他订单', 170, '/admin/shop/order@GET', 1, 4, 'icon-dingdan1');
INSERT INTO `${prefix}permission` VALUES (105, '获取数据', 104, '/admin/shop/order/get@POST', 2, 0, '');
INSERT INTO `${prefix}permission` VALUES (106, '获取物品列表', 104, '/admin/shop/order/items@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (107, '下载宝贝信息', 104, '/admin/shop/order/download@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (109, '插件文档', 61, '/admin/plugin/wiki@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (110, '支付订单', 96, '/admin/pay/order@GET', 1, 1, 'icon-dingdan4');
INSERT INTO `${prefix}permission` VALUES (111, '获取数据', 110, '/admin/pay/order/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (112, '关闭订单', 110, '/admin/pay/order/close@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (113, '获取支付列表', 7, '/admin/dict/payApi@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (114, '获取最新订单id', 110, '/admin/pay/order/getLatestOrderId@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (115, '批量上架/下架', 23, '/admin/repertory/item/updateStatus@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (116, '获取插件的商品', 7, '/admin/dict/repertoryPluginItem@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (117, 'TASK', NULL, 'admin.task', 3, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (118, '自动更新账单', 117, '/admin/task/autoReceipt@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (119, '维权订单', 170, '/admin/shop/report/order@GET', 1, 3, 'icon-tousu');
INSERT INTO `${prefix}permission` VALUES (120, '获取数据', 119, '/admin/shop/report/order/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (121, '获取维权交涉记录', 119, '/admin/shop/report/order/message@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (122, '处理维权订单', 119, '/admin/shop/report/order/handle@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (123, '维权消息心跳', 119, '/admin/shop/report/order/heartbeat@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (124, '获取宝贝信息', 104, '/admin/shop/order/item@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (125, '单独获取订单发货信息', 63, '/admin/repertory/order/detail@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (126, 'User', NULL, 'admin.user', 0, 2, NULL);
INSERT INTO `${prefix}permission` VALUES (127, '会员管理', 167, '/admin/user@GET', 1, 1, 'icon-huiyuanguanli');
INSERT INTO `${prefix}permission` VALUES (128, '获取数据', 127, '/admin/user/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (129, '获取用户组', 7, '/admin/dict/group@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (130, '保存数据', 127, '/admin/user/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (131, '获取会员等级', 7, '/admin/dict/level@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (132, '余额变更', 127, '/admin/user/balanceChange@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (133, '账单记录', 167, '/admin/user/bill@GET', 1, 3, 'icon-zhangdan');
INSERT INTO `${prefix}permission` VALUES (134, '获取数据', 133, '/admin/user/bill/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (135, '会员等级', 167, '/admin/user/level@GET', 1, 5, 'icon-huiyuandengji');
INSERT INTO `${prefix}permission` VALUES (136, '获取数据', 135, '/admin/user/level/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (137, '保存数据', 135, '/admin/user/level/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (138, '删除数据', 135, '/admin/user/level/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (139, '实名认证', 167, '/admin/user/identity@GET', 1, 2, 'icon-lanVrenzheng');
INSERT INTO `${prefix}permission` VALUES (140, '获取数据', 139, '/admin/user/identity/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (141, '保存数据', 139, '/admin/user/identity/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (142, '删除数据', 139, '/admin/user/identity/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (143, '银行管理', 126, '/admin/user/bank@GET', 1, 8, 'icon-yinhangjigou');
INSERT INTO `${prefix}permission` VALUES (144, '获取数据', 143, '/admin/user/bank/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (145, '保存数据', 143, '/admin/user/bank/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (146, '删除数据', 143, '/admin/user/bank/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (147, '银行卡', 167, '/admin/user/bank/card@GET', 1, 7, 'icon-yinhangka1');
INSERT INTO `${prefix}permission` VALUES (148, '获取数据', 147, '/admin/user/bank/card/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (149, '冻结银行卡', 147, '/admin/user/bank/card/abnormality@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (150, '删除数据', 147, '/admin/user/bank/card/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (151, '提现管理', 126, '/admin/user/withdraw@GET', 1, 4, 'icon-tixian1');
INSERT INTO `${prefix}permission` VALUES (152, '获取数据', 151, '/admin/user/withdraw/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (153, '处理提现数据', 151, '/admin/user/withdraw/processed@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (154, '商家权限', 167, '/admin/user/group@GET', 1, 6, 'icon-yonghuzu');
INSERT INTO `${prefix}permission` VALUES (155, '获取数据', 154, '/admin/user/group/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (156, '保存数据', 154, '/admin/user/group/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (157, '删除数据', 154, '/admin/user/group/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (158, '订单汇总', 170, '/admin/shop/order/summary@GET', 1, 1, 'icon-shujuhuizong');
INSERT INTO `${prefix}permission` VALUES (159, '获取数据', 158, '/admin/shop/order/summary/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (160, '数据概览', 5, '/admin/dashboard/statistics@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (161, '注销登录', 4, '/admin/personal/logout@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (162, '修改资料', 4, '/admin/personal/edit@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (163, '登录日志', 4, '/admin/personal/login/log@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (165, '获取登录日志', 163, '/admin/personal/login/log@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (167, '用户管理', 126, 'admin.user.menu', 0, 5, 'icon-zhanghaoguanli');
INSERT INTO `${prefix}permission` VALUES (168, '仓库管理', 18, 'admin.repertory.menu', 0, 0, 'icon-cangkuguanli');
INSERT INTO `${prefix}permission` VALUES (169, '店铺管理', 48, 'admin.shop.menu', 0, 0, 'icon-woyaokaidian');
INSERT INTO `${prefix}permission` VALUES (170, '订单管理', 48, 'admin.shop.order', 0, 1, 'icon-dingdanguanli');
INSERT INTO `${prefix}permission` VALUES (171, '系统相关', NULL, 'admin.system', 3, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (172, '重启程序', 171, '/admin/system/restart@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (173, '获取状态', 171, '/admin/system/state@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (174, '获取个人信息', 86, '/admin/store/personal/info@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (175, '图形验证码', 86, '/admin/store/auth/captcha@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (176, '登录应用商店', 86, '/admin/store/auth/login@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (177, '发送验证码', 86, '/admin/store/auth/sms/send@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (178, '注册应用商店', 86, '/admin/store/auth/register@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (179, '重置应用商店密码', 86, '/admin/store/auth/reset@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (180, '获取应用列表', 86, '/admin/store/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (181, '创建/保存插件', 204, '/admin/store/developer/plugin/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (182, '安装插件', 86, '/admin/store/install@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (183, '卸载插件', 86, '/admin/store/uninstall@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (184, '删除会员', 127, '/admin/user/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (185, '文件管理', 1, '/admin/upload@GET', 1, 8, 'icon-044-folder');
INSERT INTO `${prefix}permission` VALUES (186, '获取文件列表', 185, '/admin/upload/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (187, '删除文件', 185, '/admin/upload/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (188, '商品订单', 170, '/admin/shop/order/item@GET', 1, 2, 'icon-dingdan5');
INSERT INTO `${prefix}permission` VALUES (189, '获取订单数据', 188, '/admin/shop/order/item/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (190, '商品列表', 7, '/admin/dict/item@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (191, 'SKU列表', 7, '/admin/dict/itemSku@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (192, '站点管理', 126, '/admin/site@GET', 1, 0, 'icon-duliyumingdz');
INSERT INTO `${prefix}permission` VALUES (193, '获取站点列表', 192, '/admin/site/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (194, '保存站点数据', 192, '/admin/site/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (195, '删除站点数据', 192, '/admin/site/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (196, '获取DNS记录', 192, '/admin/site/dnsRecord@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (197, '获取证书', 192, '/admin/site/certificate/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (198, '修改证书', 192, '/admin/site/certificate/modify@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (199, '手动补单', 110, '/admin/pay/order/successful@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (200, '完成维权订单', 119, '/admin/shop/report/order/finish@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (201, '权限管理', 11, '/admin/permission@GET', 1, 1, 'icon-caidanguanli');
INSERT INTO `${prefix}permission` VALUES (202, '获取权限列表', 201, '/admin/permission/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (203, '保存权限信息', 201, '/admin/permission/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (204, '开发者中心', NULL, '/admin/store/developer@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (205, '我的插件列表', 204, '/admin/store/developer/plugin/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (206, '应用商店用户组', 7, '/admin/dict/storeGroup@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (207, '发布插件', 204, '/admin/store/developer/plugin/publish@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (208, '获取文件修改记录', 204, '/admin/store/developer/plugin/tracked@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (209, '更新插件', 204, '/admin/store/developer/plugin/update@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (210, '版本列表', 204, '/admin/store/developer/plugin/version/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (211, '实名状态', 204, '/admin/store/identity/status@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (212, '提交实名信息', 204, '/admin/store/identity/certification@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (213, '获取插件授权记录', 204, '/admin/store/developer/plugin/authorization/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (214, '添加授权', 204, '/admin/store/developer/plugin/authorization/add@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (215, '删除授权', 204, '/admin/store/developer/plugin/authorization/remove@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (216, '获取应用商店用户组列表', 86, '/admin/store/group@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (217, '获取应用商店支付列表', 86, '/admin/store/pay/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (218, '购买产品', 86, '/admin/store/purchase@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (219, '钱包充值', 86, '/admin/store/recharge@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (220, '我的订阅', 86, '/admin/store/powers@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (221, '获取订阅产品详细', 86, '/admin/store/power/detail@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (222, '续订项目', 86, '/admin/store/power/renewal@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (223, '开启/关闭自动续费', 86, '/admin/store/power/renewal/auto@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (224, '绑定产品到本机', 86, '/admin/store/power/renewal/bind@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (225, '插件图标', 61, '/admin/plugin/icon@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (226, '获取多个插件版本', 86, '/admin/store/plugin/versions@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (227, '获取插件更新列表', 86, '/admin/store/plugin/version/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (228, '更新插件', 86, '/admin/store/plugin/version/update@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (229, '获取用户组', 97, '/admin/pay/group/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (230, '保存用户组配置', 97, '/admin/pay/group/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (231, '获取商家', 97, '/admin/pay/user/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (232, '保存商家配置', 97, '/admin/pay/user/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (233, '获取插件配置文件', 7, '/admin/dict/pluginConfig@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (234, '拉取外部货源列表', 61, '/admin/plugin/ship/items@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (235, '导入外部货源', 61, '/admin/plugin/ship/import@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (236, '货源同步模版', 7, '/admin/dict/repertoryItemMarkupTemplate@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (237, '获取币种列表', 7, '/admin/dict/currency@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (238, '同步模版', 168, '/admin/repertory/item/markup@GET', 1, 0, 'icon-jiage');
INSERT INTO `${prefix}permission` VALUES (239, '获取模版列表', 238, '/admin/repertory/item/markup/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (240, '保存模版', 238, '/admin/repertory/item/markup/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (241, '删除模版', 238, '/admin/repertory/item/markup/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (242, '获取远程同步列表', 61, '/admin/plugin/ship/remote/items@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (243, '同步远程商品', 61, '/admin/plugin/ship/remote/sync@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (244, '重启插件', 61, '/admin/plugin/restart@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (245, '语言管理', 1, '/admin/config/language@GET', 1, 7, 'icon-guojihua');
INSERT INTO `${prefix}permission` VALUES (246, '获取数据', 245, '/admin/config/language/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (247, '保存数据', 245, '/admin/config/language/save@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (248, '删除数据', 245, '/admin/config/language/del@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (249, '盈利中心', 1, '/admin/store/trade@GET', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (250, '兑现记录', 249, '/admin/store/withdrawal/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (251, '申请兑现', 249, '/admin/store/withdrawal/apply@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (252, '账单记录', 249, '/admin/store/bill/get@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (253, '子站插件免费', 86, '/admin/store/power/sub/free@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (254, '获取子站列表', 86, '/admin/store/power/sub/list@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (255, '设置子站插件授权', 86, '/admin/store/power/sub/auth@POST', 2, 0, NULL);
INSERT INTO `${prefix}permission` VALUES (256, '获取应用商店订单状态', 86, '/admin/store/pay/order@POST', 2, 0, NULL);

DROP TABLE IF EXISTS `${prefix}plugin_config`;
CREATE TABLE `${prefix}plugin_config`  (
                                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id',
                                           `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '配置名称',
                                           `plugin` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '插件key',
                                           `handle` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '插件的业务',
                                           `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '配置文件内容',
                                           `create_time` datetime NOT NULL,
                                           PRIMARY KEY (`id`) USING BTREE,
                                           INDEX `user_id`(`user_id`) USING BTREE,
                                           INDEX `plugin`(`plugin`) USING BTREE,
                                           INDEX `handle`(`handle`) USING BTREE,
                                           CONSTRAINT `user_cascade` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;




DROP TABLE IF EXISTS `${prefix}repertory_category`;
CREATE TABLE `${prefix}repertory_category`  (
                                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                                `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分类名称',
                                                `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                                `create_time` datetime NOT NULL COMMENT '创建时间',
                                                `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
                                                `status` tinyint(4) UNSIGNED NOT NULL COMMENT '状态：0=停用，1=启用',
                                                PRIMARY KEY (`id`) USING BTREE,
                                                INDEX `sort`(`sort`) USING BTREE,
                                                INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}repertory_category` VALUES (8, 'DEMO', 0, '2024-05-28 23:21:57', '/favicon.ico', 1);


DROP TABLE IF EXISTS `${prefix}repertory_item`;
CREATE TABLE `${prefix}repertory_item`  (
                                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                            `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商id，NULL=后台直供',
                                            `repertory_category_id` bigint(20) UNSIGNED NOT NULL COMMENT '仓库id',
                                            `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品名称',
                                            `introduce` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品说明',
                                            `picture_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '封面图片',
                                            `picture_thumb_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩略图',
                                            `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=审核中，1=未上架，2=已上架，3=锁定',
                                            `create_time` datetime NOT NULL COMMENT '创建时间',
                                            `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                            `plugin` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '货源插件',
                                            `widget` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '控件(JSON)',
                                            `attr` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品属性(JSON)',
                                            `api_code` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '对接码',
                                            `privacy` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '0=完全隐私，1=对接码，2=完全开放',
                                            `refund_mode` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '退款方式：0=不支持，1=有条件退款，2=无理由退款',
                                            `auto_receipt_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '无理由退款，钱款冻结时间，单位/天',
                                            `hand_delivery_method` tinyint(3) UNSIGNED NULL DEFAULT NULL,
                                            `virtual_card_sequence` tinyint(3) UNSIGNED NULL DEFAULT NULL,
                                            `ship_config_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '插件配置id',
                                            `unique_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '第三方唯一标识',
                                            `markup` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '默认加价通用方案',
                                            `markup_mode` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步模式：0=非远程商品，1=自定义，2=模版',
                                            `markup_template_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '同步模版id',
                                            `version` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '远程商品版本',
                                            `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
                                            `plugin_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '插件数据',
                                            `exception_total` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '异常次数',
                                            PRIMARY KEY (`id`) USING BTREE,
                                            UNIQUE INDEX `api_code`(`api_code`) USING BTREE,
                                            INDEX `repertory_category_id`(`repertory_category_id`) USING BTREE,
                                            INDEX `user_id`(`user_id`) USING BTREE,
                                            INDEX `status`(`status`) USING BTREE,
                                            INDEX `sort`(`sort`) USING BTREE,
                                            INDEX `privacy`(`privacy`) USING BTREE,
                                            INDEX `ship_config_id`(`ship_config_id`) USING BTREE,
                                            INDEX `unique_id`(`unique_id`) USING BTREE,
                                            INDEX `update_time`(`update_time`) USING BTREE,
                                            INDEX `markup_mode`(`markup_mode`) USING BTREE,
                                            INDEX `markup_template_id`(`markup_template_id`) USING BTREE,
                                            CONSTRAINT `${prefix}repertory_item_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                            CONSTRAINT `${prefix}repertory_item_ibfk_2` FOREIGN KEY (`repertory_category_id`) REFERENCES `${prefix}repertory_category` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                            CONSTRAINT `ship_config` FOREIGN KEY (`ship_config_id`) REFERENCES `${prefix}plugin_config` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}repertory_item` VALUES (22, NULL, 8, 'DEMO', '<p><strong>Cosplay</strong>（日语：コスプレ）为<a href=\"https://zh.wikipedia.org/wiki/和製英語\">和制英语</a>，<strong>costume play</strong>的<a href=\"https://zh.wikipedia.org/wiki/混成詞\">混成词</a>，它已成为世界通用的词汇。中文一般译为“<strong>角色扮演</strong>”<sup>[1]</sup>或“<strong>扮装</strong>”<sup>[2][3]</sup>，有时也会直接称为cos， 是指利用服装、饰品、道具及化妆搭配等，扮演动漫、游戏中人物角色的一种<a href=\"https://zh.wikipedia.org/wiki/表演藝術\">表演艺术</a>行为。常见于<a href=\"https://zh.wikipedia.org/wiki/同人誌即賣會\">同人志即售会</a>或<a href=\"https://zh.wikipedia.org/wiki/視覺系\">视觉系</a><a href=\"https://zh.wikipedia.org/wiki/樂團\">乐团</a>演唱会等同好聚集的活动中。而参与扮装活动的表演者，一般称呼为cosplayer，简称coser；中文称“<strong>扮装者</strong>”<sup>[4]</sup>、“<strong>角色扮演者</strong>”或“<strong>角色扮演员</strong>”。</p><p>近年来Cosplay的定义扩大，除了原本所指的同好活动、也泛指喜好特定职业、人物、文化等<a href=\"https://zh.wikipedia.org/wiki/角色扮演\">角色扮演</a>行为；而Cosplay爱好者的社群圈子又称<strong>C圈</strong>。</p><p><br /></p><h2>词源</h2><p><br /></p><p><a href=\"https://zh.wikipedia.org/wiki/遊戲王\">游</a><a href=\"https://zh.wikipedia.org/wiki/遊戲王\">戏王</a>角色“黑魔导女孩”的扮装者</p><p>Cosplay通常被视为一种<a href=\"https://zh.wikipedia.org/wiki/次文化\">次文化</a>活动。扮演的对象<a href=\"https://zh.wikipedia.org/wiki/角色\">角色</a>一般来自<a href=\"https://zh.wikipedia.org/wiki/動畫\">动画</a>、<a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>、<a href=\"https://zh.wikipedia.org/wiki/電子遊戲\">电子游戏</a>、<a href=\"https://zh.wikipedia.org/wiki/輕小說\">轻小说</a>、<a href=\"https://zh.wikipedia.org/wiki/電影\">电影</a>、影集、<a href=\"https://zh.wikipedia.org/wiki/特攝\">特摄</a>、<a href=\"https://zh.wikipedia.org/wiki/偶像團體\">偶像团体</a>、<a href=\"https://zh.wikipedia.org/wiki/職業\">职业</a>、<a href=\"https://zh.wikipedia.org/wiki/歷史\">历史</a><a href=\"https://zh.wikipedia.org/wiki/故事\">故事</a>、<a href=\"https://zh.wikipedia.org/wiki/社會\">社会</a>故事、现实世界中具有传奇性的独特事物（或其<a href=\"https://zh.wikipedia.org/wiki/擬人化\">拟人化</a>形态）、或是其他自创的有形<a href=\"https://zh.wikipedia.org/wiki/角色\">角色</a>。方法是特意穿戴相似的<a href=\"https://zh.wikipedia.org/wiki/服饰\">服饰</a>，加上<a href=\"https://zh.wikipedia.org/wiki/道具\">道具</a>的配搭，<a href=\"https://zh.wikipedia.org/wiki/化粧\">化妆</a>造型、<a href=\"https://zh.wikipedia.org/wiki/身體語言\">身体语言</a>等等<a href=\"https://zh.wikipedia.org/wiki/参数\">参数</a>来模仿该等角色。</p><p>历史上有<a href=\"https://zh.wikipedia.org/wiki/化裝舞會\">化装舞会</a>等变装活动；而1939年在美国<a href=\"https://zh.wikipedia.org/wiki/紐約\">纽约</a>举办的<a href=\"https://zh.wikipedia.org/wiki/世界科幻大会\">世界科幻大会</a>中，Morojo穿着\"未来派服装\"开创了这样的<a href=\"https://zh.wikipedia.org/wiki/表演藝術\">表演艺术</a>。此后不断有人仿效这类型的<a href=\"https://zh.wikipedia.org/wiki/角色扮演\">角色扮演</a>。而日本的<a href=\"https://zh.wikipedia.org/w/index.php?title=同人誌即售會&action=edit&redlink=1\">同人志即售会</a><a href=\"https://zh.wikipedia.org/wiki/Comic_Market\">Comic Market</a>中也开始出现装扮为<a href=\"https://zh.wikipedia.org/wiki/動畫\">动画</a>、<a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>角色的<a href=\"https://zh.wikipedia.org/wiki/角色扮演者\">角色扮演者</a>；1978年，<a href=\"https://zh.wikipedia.org/wiki/Comic_Market\">Comic Market</a>召集人米泽嘉博氏为场刊撰文时，以‘Costume play’来称呼这种行为，并在1984年美国<a href=\"https://zh.wikipedia.org/wiki/洛杉磯\">洛杉矶</a>举行的<a href=\"https://zh.wikipedia.org/wiki/世界科幻年會\">世界科幻年会</a>上，由赴会的日本动画家暨日本艺术工作室“Studio Hard”首席执行官高桥伸之把这种自力演绎角色的扮装性质表演艺术行为定义为<a href=\"https://zh.wikipedia.org/wiki/和製英語\">和制英语</a>词语“Cosplay”<sup>[5][6][7]</sup>。</p><p>之后到1990年代，日本ACG业界成功举办了大量的动漫画展和游戏展，业者为了宣传产品，在这些游戏展和漫画节中找人装扮成ACG作品中的角色以吸引参展人群。这个宣传策略与当初迪士尼开办<a href=\"https://zh.wikipedia.org/wiki/迪士尼樂園\">迪士尼乐园</a>时采用的方式如出一辙，因此可说当代Cosplay的蓬勃发展是拜ACG产业的发达。因此，Cosplay文化在ACG界热门化和发扬光大，同时借着各种Cosplay活动、传媒的介绍、<a href=\"https://zh.wikipedia.org/wiki/互聯網\">互联网</a>的大量传播等，使Cosplay的自由参与者激增，Cosplay才渐渐得到了真正的、独立的发展。更甚者，专门为Cosplay行为而举行的活动也渐渐出现，形式类似化妆舞会，民众逐渐能在越来越多的场合中看到奇装异服者，并了解到这集服饰、化妆、表演于一体的扮装文化现象──Cosplay。 <sup>[8]</sup></p><p>当代的Cosplay一般以<a href=\"https://zh.wikipedia.org/wiki/動畫\">动画</a>、<a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>、<a href=\"https://zh.wikipedia.org/wiki/遊戲\">游戏</a>、<a href=\"https://zh.wikipedia.org/wiki/電玩\">电玩</a>、<a href=\"https://zh.wikipedia.org/wiki/輕小說\">轻小说</a>、<a href=\"https://zh.wikipedia.org/wiki/電影\">电影</a>、影集、<a href=\"https://zh.wikipedia.org/wiki/特攝\">特摄</a>、<a href=\"https://zh.wikipedia.org/wiki/偶像團體\">偶像团体</a>、职业、历史故事、社会故事或是其他自创的有形<a href=\"https://zh.wikipedia.org/wiki/角色\">角色</a>为目标，刻意穿着类似的<a href=\"https://zh.wikipedia.org/wiki/服饰\">服饰</a>，加上<a href=\"https://zh.wikipedia.org/wiki/道具\">道具</a>的配搭，<a href=\"https://zh.wikipedia.org/wiki/化粧\">化妆</a>造型、<a href=\"https://zh.wikipedia.org/wiki/身體語言\">身体语言</a>等等<a href=\"https://zh.wikipedia.org/wiki/参数\">参数</a>，以人力扮演成一个“活起来”的角色。另一种Cosplay主要是对非人类的动物、军事武器、交通工具、土木基建、操作系统、网站等进行<a href=\"https://zh.wikipedia.org/wiki/擬人化\">拟人化</a>，灌以具智慧的灵魂，并以相应服饰、道具、化妆、身体语言等配套来呈现该等拟人化角色，其中一常见手法乃以<a href=\"https://zh.wikipedia.org/wiki/萌擬人化\">萌拟人化</a>形态出场。</p><p><br /></p><h3>角色扮装</h3><p><br /></p><p>最终幻想的Cosplay</p><p>扮装最早的起源可能是来自于对<a href=\"https://zh.wikipedia.org/wiki/神話\">神话</a><a href=\"https://zh.wikipedia.org/wiki/傳說\">传说</a>、民间逸闻等的演绎，以及节日故事、文艺作品、<a href=\"https://zh.wikipedia.org/wiki/哲學\">哲理学说</a>、<a href=\"https://zh.wikipedia.org/wiki/祭祖\">祭祖</a>情节、振奋助兴情节、侧绎愿望诉求、心灵幻想等，并以相应的<a href=\"https://zh.wikipedia.org/wiki/服饰\">服饰</a>、<a href=\"https://zh.wikipedia.org/wiki/道具\">道具</a>和情节，把要演绎的角色和内容活灵活现地呈现出来。这些活动通常属於戏剧表演、民俗活动等。比如说有<a href=\"https://zh.wikipedia.org/wiki/古希臘\">古希腊</a><a href=\"https://zh.wikipedia.org/wiki/古希腊宗教\">祭司</a>们的装扮，继而有两部伟大希腊史诗《<a href=\"https://zh.wikipedia.org/wiki/伊利亞特\">伊利亚特</a>》和《<a href=\"https://zh.wikipedia.org/wiki/奧德賽\">奥德赛</a>》的那群活跃于前8世纪的<a href=\"https://zh.wikipedia.org/wiki/吟遊詩人\">吟游诗人</a>们扮演着别人的角色。前者引变为后世的<a href=\"https://zh.wikipedia.org/wiki/先知\">先知</a>、先见，成功地演绎出神之<a href=\"https://zh.wikipedia.org/wiki/使徒\">使徒</a>的存在，而后者则如同是现今<a href=\"https://zh.wikipedia.org/wiki/話劇\">话剧</a>的鼻祖，出神入化地演绎出若干英雄事迹。</p><p>欧洲的游牧民族<a href=\"https://zh.wikipedia.org/wiki/吉普賽人\">吉普赛人</a>也可以说是最早的一批扮装表演者。每当路经一地，为了生存，他们就透过演出神话传说、民间逸闻、吟游弹唱的方式来获得面包与水，这其中各种演出用的服饰与道具自然是必不可少的装备。随时随地举行的角色化妆舞会、<a href=\"https://zh.wikipedia.org/wiki/万圣夜\">万圣节</a>游行、<a href=\"https://zh.wikipedia.org/wiki/新年\">新年</a>大游行、<a href=\"https://zh.wikipedia.org/wiki/國慶日\">国庆日</a>游行活动或特别盛典时中，不少人装扮成节日故事的人物或各类<a href=\"https://zh.wikipedia.org/wiki/吉祥物\">吉祥物</a>，浓厚的<a href=\"https://zh.wikipedia.org/wiki/扮裝\">扮装</a>文化得以体现。</p><p><a href=\"https://zh.wikipedia.org/wiki/中國\">中国</a>古代的先民也有着历史悠久的扮装文化。具有千年传统的<a href=\"https://zh.wikipedia.org/wiki/舞龍\">舞龙</a>仪式可以说是其中最具代表性的活动。舞龙在当时往往有两种寓意，一种是祈求上苍降甘露给农田，另一种则有祈求五谷丰登、万象吉祥之意。此活动进行前，首先要选出体格健壮、姿态威武的男性青年若干位，并让他们穿上黄、红色的代表喜庆的服饰（有时为贵族表演时衣服上甚至绣有花纹），按照事先的编舞他们将组成一支或多支舞龙队伍表演出各种方阵图案，并有<a href=\"https://zh.wikipedia.org/wiki/鼓\">鼓</a><a href=\"https://zh.wikipedia.org/wiki/鑼\">锣</a>声作为伴奏。到17世纪左右（即<a href=\"https://zh.wikipedia.org/wiki/明\">明</a>末<a href=\"https://zh.wikipedia.org/wiki/清\">清</a>初），由舞龙中又繁衍出了舞狮、<a href=\"https://zh.wikipedia.org/w/index.php?title=鳳舞龍翔&action=edit&redlink=1\">凤舞龙翔</a>的活动，这些都与以服饰扮装某些角色都有着紧密的联系。</p><p><a href=\"https://zh.wikipedia.org/wiki/西藏\">西藏</a>民间神话英雄的中国<a href=\"https://zh.wikipedia.org/wiki/藏戏\">藏戏</a>、<a href=\"https://zh.wikipedia.org/wiki/超渡\">超渡</a>亡魂到<a href=\"https://zh.wikipedia.org/wiki/極樂世界\">极乐世界</a>和<a href=\"https://zh.wikipedia.org/wiki/印度\">印度</a><a href=\"https://zh.wikipedia.org/wiki/佛教\">佛教</a>中的佛事、祭祀<a href=\"https://zh.wikipedia.org/w/index.php?title=山靈&action=edit&redlink=1\">山灵</a><a href=\"https://zh.wikipedia.org/wiki/神器\">神器</a>与<a href=\"https://zh.wikipedia.org/wiki/日本\">日本</a><a href=\"https://zh.wikipedia.org/wiki/神道教\">神道教</a><a href=\"https://zh.wikipedia.org/wiki/神社\">神社</a>活动，服饰、道具、表演都是这些活动的重要组成元素<sup>[9]</sup>。</p><p><br /></p><h3>迪士尼的推广</h3><p><br /></p><p>1930年代末期<a href=\"https://zh.wikipedia.org/wiki/和路迪士尼\">和路迪士尼</a><a href=\"https://zh.wikipedia.org/wiki/米奇老鼠\">米奇老鼠</a>出现，<a href=\"https://zh.wikipedia.org/wiki/美國\">美国</a>的动画风格有了一个确实的定义，而史上真正的第一个以动画人物为受扮者的Cosplay，也正是出于此时期。和路迪士尼看准时机适时的在1955年创建了世界上首座<a href=\"https://zh.wikipedia.org/wiki/迪士尼樂園\">迪士尼乐园</a>，同时为了替产品自身作宣传及为更好的吸引游客，和路迪士尼还特别请来员工，穿上米奇老鼠服饰以提供游客玩赏或是拍照留念。当初这群默默无闻的米奇老鼠装扮者就是当代全世界Cosplay行为的真正创始者。</p><p>起初为当时那群在迪士尼乐园中装扮成<a href=\"https://zh.wikipedia.org/wiki/米奇老鼠\">米奇老鼠</a>、<a href=\"https://zh.wikipedia.org/wiki/布鲁托_(大力水手)\">布鲁托（大力水手）</a>、<a href=\"https://zh.wikipedia.org/wiki/高飛狗\">高飞狗</a>、<a href=\"https://zh.wikipedia.org/wiki/唐老鴨\">唐老鸭</a>以及其他迪士尼人物制作Cosplay服饰的是和路迪士尼公司早期的道具部。在迪士尼乐园正式成立后不久，和路迪士尼扩大了道具部的规模，不仅要为影视作品制作道具，更负责所有在迪士尼乐园工作所需的Cosplay服饰。早期用作Cosplay的服饰只是一个拥有固定外形的“大纸袋”，缺乏美感和舒适，成品相对也较粗糙，装扮者穿上这种服饰后很容易发生呼吸不畅的现象。纵使如此，此时迪士尼的Cosplay服饰制作已算是拥有了一定的规模。</p><p>当代Cosplay最初成形的目的仍是出于一种商业上的形为而并非像现在这样是一种流行品位上的消费。将美国或是更确切的一点说，将迪士尼作为当代Cosplay的真正发源其实还有一个很重要的依据，那就是当时迪士尼卡通人物装扮者们身上所穿着Cosplay服饰的专业制作化。虽然以现在的Cosplay服饰而言，有许多是装扮者们自己所缝制的。但是作为一个当代Cosplay的鼻祖，拥有一个规范并且体系化的服饰制作组织是必要的条件。</p><p><br /></p><h3>与日本动漫结合</h3><p><br /></p><p><a href=\"https://zh.wikipedia.org/wiki/日本\">日本</a>的<a href=\"https://zh.wikipedia.org/wiki/ACG\">ACG</a>（指的是Animations动画、Comics漫画、Games游戏）市场兴起自1947年漫画之神<a href=\"https://zh.wikipedia.org/wiki/手塚治虫\">手冢治虫</a>根据酒井七马原作改编而成的<a href=\"https://zh.wikipedia.org/w/index.php?title=紅皮書&action=edit&redlink=1\">红皮书</a><a href=\"https://zh.wikipedia.org/wiki/漫畫\">漫画</a>《<a href=\"https://zh.wikipedia.org/wiki/新宝岛_(漫画)\">新宝岛</a>》，为日本<a href=\"https://zh.wikipedia.org/wiki/ACG\">ACG</a>的地位打下了坚实的基础。恰好正在此时，迪士尼那种所为宣传而作的Cosplay活动被传入日本，有ACG界同好起而模仿，渐渐蔚为风潮，最终成了日本现在<a href=\"https://zh.wikipedia.org/wiki/ACG\">ACG</a>界的常态活动。直到在1955年左右，日本的扮装活动都仅仅只是小童间的玩意，但在服饰方面还是颇为讲究。当时不少小童都装扮《<a href=\"https://zh.wikipedia.org/wiki/月光假面\">月光假面</a>》与《<a href=\"https://zh.wikipedia.org/wiki/少年杰特\">少年杰特</a>》这两部作品的主人公。当时的日本并没有如迪士尼乐园般拥有专门的Cosplay服饰制作单位和行号，装扮者如想要拥有与动画中主人公相同服饰的话就必须先请画家绘好服饰设计图样，然后再到百货公司或裁缝店请师传缝制。现今著名的游戏制作人<a href=\"https://zh.wikipedia.org/wiki/廣井王子\">广井王子</a>小时候Cosplay的服饰设计图，便是请他家附近的一条<a href=\"https://zh.wikipedia.org/wiki/藝妓\">艺妓</a>街上的那些艺妓为他绘制的。这种较为粗制的状况一直维持了将近二十年的时间，直至1970年代末至1980年代初日本的ACG经历了探索和成长期之后，此时日本的Cosplay活动在起初是作为<a href=\"https://zh.wikipedia.org/w/index.php?title=看版娘&action=edit&redlink=1\">看版娘</a>在<a href=\"https://zh.wikipedia.org/wiki/同人誌即賣會\">同人志即卖会</a>而生，为各同好会等场合上活跃气氛的一种即兴节目，后期引申为伴随着动漫展览、游戏发布会上频繁出现。</p>', '/assets/user/images/test/33e500382a2db34c71d048b0ccc3a587.jpg', '/assets/user/images/test/thumb/33e500382a2db34c71d048b0ccc3a587.jpg', 2, '2024-05-28 23:29:49', 0, 'HandShip', '[]', '[]', 'c49a3', 2, 2, 5040, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0);


DROP TABLE IF EXISTS `${prefix}repertory_item_markup_template`;
CREATE TABLE `${prefix}repertory_item_markup_template`  (
                                                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                            `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id',
                                                            `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '模板名称',
                                                            `drift_model` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '加价方式：0=比例加价，1=固定金额加价（基数自动比例）',
                                                            `drift_value` decimal(14, 6) UNSIGNED NOT NULL COMMENT '加价数量/比例',
                                                            `drift_base_amount` decimal(14, 6) UNSIGNED NOT NULL COMMENT '基数',
                                                            `sync_amount` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步价格',
                                                            `sync_name` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步商品名称',
                                                            `sync_introduce` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步商品介绍',
                                                            `sync_picture` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步商品图片',
                                                            `sync_sku_name` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步SKU名称',
                                                            `sync_sku_picture` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步SKU图片',
                                                            `sync_remote_download` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '远程下载',
                                                            `exchange_rate` decimal(14, 6) NOT NULL DEFAULT 0.000000 COMMENT '货币汇率',
                                                            `keep_decimals` tinyint(3) UNSIGNED NOT NULL DEFAULT 2 COMMENT '保留小数位数',
                                                            `create_time` datetime NOT NULL COMMENT '创建时间',
                                                            PRIMARY KEY (`id`) USING BTREE,
                                                            INDEX `user_id`(`user_id`) USING BTREE,
                                                            CONSTRAINT `repertory_item_markup_template_user_cascade` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `${prefix}repertory_item_sku`;
CREATE TABLE `${prefix}repertory_item_sku`  (
                                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                                `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商id，0=后台直供',
                                                `repertory_item_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '物品id',
                                                `picture_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '宣传图',
                                                `picture_thumb_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩略图',
                                                `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
                                                `stock_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '进货价',
                                                `supply_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '供货商-供货价格',
                                                `market_control_status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '市场控制：0=关闭，1=开启',
                                                `market_control_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '最小销售金额，0代表不限制',
                                                `market_control_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '最大销售金额，0代表不限制',
                                                `cost` decimal(14, 6) UNSIGNED NOT NULL COMMENT '成本',
                                                `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                                `create_time` datetime NOT NULL COMMENT '创建时间',
                                                `temp_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '临时ID',
                                                `plugin_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '插件数据',
                                                `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '留言',
                                                `hand_delivery_contents` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                                                `market_control_level_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                `market_control_level_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                `market_control_user_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                `market_control_user_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                `market_control_min_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                `market_control_max_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                `market_control_only_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                `unique_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '第三方唯一标识',
                                                `version` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '远程商品SKU版本',
                                                `private_display` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '私密模式：0=关闭，1=启动',
                                                PRIMARY KEY (`id`) USING BTREE,
                                                INDEX `repertory_item_id`(`repertory_item_id`) USING BTREE,
                                                INDEX `temp_id`(`temp_id`) USING BTREE,
                                                INDEX `user_id`(`user_id`) USING BTREE,
                                                INDEX `sort`(`sort`) USING BTREE,
                                                INDEX `create_time`(`create_time`) USING BTREE,
                                                INDEX `unique_id`(`unique_id`) USING BTREE,
                                                CONSTRAINT `${prefix}repertory_item_sku_ibfk_1` FOREIGN KEY (`repertory_item_id`) REFERENCES `${prefix}repertory_item` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 43 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}repertory_item_sku` VALUES (40, NULL, 22, '/assets/user/images/test/9baf7fc1f11f08b836bc16044781b648.jpg', '/assets/user/images/test/thumb/9baf7fc1f11f08b836bc16044781b648.jpg', '演示SKU1', 1.000000, 0.000000, 0, 0.000000, 0.000000, 0.000000, 0, '2024-05-28 23:29:03', 'a6ZYa6dlbh076LO2', NULL, NULL, NULL, 0.000000, 0.000000, 0.000000, 0.000000, 0, 0, 0, NULL, NULL , 0);
INSERT INTO `${prefix}repertory_item_sku` VALUES (41, NULL, 22, '/assets/user/images/test/a592ec87c83405d86fffc5cc84d69523.jpg', '/assets/user/images/test/thumb/a592ec87c83405d86fffc5cc84d69523.jpg', '演示SKU2', 2.000000, 0.000000, 0, 0.000000, 0.000000, 0.000000, 0, '2024-05-28 23:29:14', 'a6ZYa6dlbh076LO2', NULL, NULL, NULL, 0.000000, 0.000000, 0.000000, 0.000000, 0, 0, 0, NULL, NULL, 0);
INSERT INTO `${prefix}repertory_item_sku` VALUES (42, NULL, 22, '/assets/user/images/test/c0176e0509550ad018ed18170c79d917.jpg', '/assets/user/images/test/thumb/c0176e0509550ad018ed18170c79d917.jpg', '演示SKU3', 5.000000, 0.000000, 0, 0.000000, 0.000000, 0.000000, 0, '2024-05-28 23:29:28', 'a6ZYa6dlbh076LO2', NULL, NULL, NULL, 0.000000, 0.000000, 0.000000, 0.000000, 0, 0, 0, NULL, NULL, 0);


DROP TABLE IF EXISTS `${prefix}repertory_item_sku_cache`;
CREATE TABLE `${prefix}repertory_item_sku_cache`  (
                                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                      `sku_id` bigint(20) UNSIGNED NOT NULL,
                                                      `type` tinyint(4) UNSIGNED NOT NULL COMMENT '0=库存数量，1=库存是否充足',
                                                      `value` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
                                                      `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
                                                      PRIMARY KEY (`id`) USING BTREE,
                                                      UNIQUE INDEX `sku_id`(`sku_id`, `type`) USING BTREE,
                                                      INDEX `sku_id_find`(`sku_id`) USING BTREE,
                                                      INDEX `create_time`(`create_time`) USING BTREE,
                                                      CONSTRAINT `${prefix}repertory_item_sku_cache_ibfk_1` FOREIGN KEY (`sku_id`) REFERENCES `${prefix}repertory_item_sku` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}repertory_item_sku_cache` VALUES (13, 40, 0, '0', '2024-10-13 22:36:56');
INSERT INTO `${prefix}repertory_item_sku_cache` VALUES (14, 40, 1, '0', '2024-10-13 22:36:56');
INSERT INTO `${prefix}repertory_item_sku_cache` VALUES (15, 41, 0, '0', '2024-10-13 22:36:56');
INSERT INTO `${prefix}repertory_item_sku_cache` VALUES (16, 41, 1, '0', '2024-10-13 22:36:56');
INSERT INTO `${prefix}repertory_item_sku_cache` VALUES (17, 42, 0, '0', '2024-10-13 22:36:56');
INSERT INTO `${prefix}repertory_item_sku_cache` VALUES (18, 42, 1, '0', '2024-10-13 22:36:56');


DROP TABLE IF EXISTS `${prefix}repertory_item_sku_group`;
CREATE TABLE `${prefix}repertory_item_sku_group`  (
                                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                      `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商ID',
                                                      `group_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '会员等级ID',
                                                      `sku_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT 'SKU ID',
                                                      `stock_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '进货价',
                                                      `market_control_status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '市场控制：0=关闭，1=开启',
                                                      `market_control_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '最小销售金额，0代表不限制',
                                                      `market_control_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '最大销售金额，0代表不限制',
                                                      `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                                      `temp_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'sku缓存id',
                                                      `create_time` datetime NOT NULL COMMENT '创建时间',
                                                      `market_control_level_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                      `market_control_level_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                      `market_control_user_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                      `market_control_user_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                      `market_control_min_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                      `market_control_max_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                      `market_control_only_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                      PRIMARY KEY (`id`) USING BTREE,
                                                      UNIQUE INDEX `group_sku`(`group_id`, `sku_id`) USING BTREE,
                                                      UNIQUE INDEX `group_temp`(`group_id`, `temp_id`) USING BTREE,
                                                      INDEX `sku`(`sku_id`) USING BTREE,
                                                      INDEX `group`(`group_id`) USING BTREE,
                                                      INDEX `group_id`(`group_id`, `sku_id`, `status`) USING BTREE,
                                                      INDEX `user_id`(`user_id`) USING BTREE,
                                                      CONSTRAINT `${prefix}repertory_item_sku_group_ibfk_1` FOREIGN KEY (`sku_id`) REFERENCES `${prefix}repertory_item_sku` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                                      CONSTRAINT `${prefix}repertory_item_sku_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `${prefix}user_group` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}repertory_item_sku_user`;
CREATE TABLE `${prefix}repertory_item_sku_user`  (
                                                     `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                     `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商ID',
                                                     `customer_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员ID',
                                                     `sku_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT 'SKU ID',
                                                     `stock_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '进货价',
                                                     `market_control_status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '市场控制：0=关闭，1=开启',
                                                     `market_control_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '最小销售金额，0代表不限制',
                                                     `market_control_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '最大销售金额，0代表不限制',
                                                     `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                                     `temp_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'sku缓存id',
                                                     `create_time` datetime NOT NULL COMMENT '创建时间',
                                                     `market_control_level_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                     `market_control_level_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                     `market_control_user_min_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                     `market_control_user_max_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000,
                                                     `market_control_min_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                     `market_control_max_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                     `market_control_only_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                                     PRIMARY KEY (`id`) USING BTREE,
                                                     UNIQUE INDEX `customer_id_sku`(`sku_id`, `customer_id`) USING BTREE,
                                                     UNIQUE INDEX `customer_temp`(`customer_id`, `temp_id`) USING BTREE,
                                                     INDEX `sku`(`sku_id`) USING BTREE,
                                                     INDEX `customer_id`(`customer_id`, `sku_id`, `status`) USING BTREE,
                                                     INDEX `user_id`(`user_id`) USING BTREE,
                                                     CONSTRAINT `${prefix}repertory_item_sku_user_ibfk_1` FOREIGN KEY (`sku_id`) REFERENCES `${prefix}repertory_item_sku` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                                     CONSTRAINT `${prefix}repertory_item_sku_user_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}repertory_item_sku_wholesale`;
CREATE TABLE `${prefix}repertory_item_sku_wholesale`  (
                                                          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                          `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商ID',
                                                          `sku_id` bigint(20) UNSIGNED NOT NULL COMMENT 'SKU ID',
                                                          `quantity` int(10) UNSIGNED NOT NULL COMMENT '数量',
                                                          `stock_price` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '进货价',
                                                          `create_time` datetime NOT NULL COMMENT '创建时间',
                                                          PRIMARY KEY (`id`) USING BTREE,
                                                          UNIQUE INDEX `service_get_amount`(`sku_id`, `quantity`) USING BTREE,
                                                          INDEX `quantity`(`quantity`) USING BTREE,
                                                          INDEX `sku_id`(`sku_id`) USING BTREE,
                                                          INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 31 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}repertory_item_sku_wholesale_group`;
CREATE TABLE `${prefix}repertory_item_sku_wholesale_group`  (
                                                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                                `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商ID',
                                                                `wholesale_id` bigint(20) UNSIGNED NOT NULL COMMENT '批发规则id',
                                                                `group_id` int(11) UNSIGNED NOT NULL COMMENT '组id',
                                                                `stock_price` decimal(14, 6) UNSIGNED NOT NULL COMMENT '单价',
                                                                `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                                                `create_time` datetime NOT NULL COMMENT '创建时间',
                                                                PRIMARY KEY (`id`) USING BTREE,
                                                                UNIQUE INDEX `wholesale_id`(`wholesale_id`, `group_id`) USING BTREE,
                                                                INDEX `user_id`(`user_id`) USING BTREE,
                                                                INDEX `service_get_amount`(`wholesale_id`, `group_id`, `status`) USING BTREE,
                                                                INDEX `group_id`(`group_id`) USING BTREE,
                                                                CONSTRAINT `${prefix}repertory_item_sku_wholesale_group_ibfk_1` FOREIGN KEY (`wholesale_id`) REFERENCES `${prefix}repertory_item_sku_wholesale` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                                                CONSTRAINT `${prefix}repertory_item_sku_wholesale_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `${prefix}user_group` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}repertory_item_sku_wholesale_user`;
CREATE TABLE `${prefix}repertory_item_sku_wholesale_user`  (
                                                               `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                               `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '供货商ID',
                                                               `customer_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
                                                               `wholesale_id` bigint(20) UNSIGNED NOT NULL COMMENT '批发规则id',
                                                               `stock_price` decimal(14, 6) UNSIGNED NOT NULL COMMENT '单价',
                                                               `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=关闭，1=启用',
                                                               `create_time` datetime NOT NULL COMMENT '创建时间',
                                                               PRIMARY KEY (`id`, `stock_price`) USING BTREE,
                                                               UNIQUE INDEX `customer_id`(`customer_id`, `wholesale_id`) USING BTREE,
                                                               INDEX `user_id`(`user_id`) USING BTREE,
                                                               INDEX `customer_id_2`(`customer_id`, `wholesale_id`, `status`) USING BTREE,
                                                               INDEX `wholesale_id`(`wholesale_id`) USING BTREE,
                                                               CONSTRAINT `${prefix}repertory_item_sku_wholesale_user_ibfk_1` FOREIGN KEY (`wholesale_id`) REFERENCES `${prefix}repertory_item_sku_wholesale` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                                               CONSTRAINT `${prefix}repertory_item_sku_wholesale_user_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}repertory_order`;
CREATE TABLE `${prefix}repertory_order`  (
                                             `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                             `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '商户ID，空代表后台',
                                             `customer_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '顾客ID（user_id），空代表后台',
                                             `repertory_item_id` bigint(20) UNSIGNED NOT NULL COMMENT '物品ID',
                                             `repertory_item_sku_id` bigint(20) UNSIGNED NOT NULL COMMENT 'SKUID',
                                             `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '24位订单号',
                                             `item_trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '物品订单(第三方)',
                                             `main_trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '主订单号(第三方)',
                                             `amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '订单金额',
                                             `quantity` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '数量',
                                             `trade_time` datetime NOT NULL COMMENT '交易时间',
                                             `trade_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '交易IP',
                                             `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=等待发货，1=已发货，2=发货失败，3=已退款',
                                             `supply_profit` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '供货商获利',
                                             `office_profit` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '总站获利',
                                             `widget` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '控件内容',
                                             `item_cost` decimal(14, 6) UNSIGNED NOT NULL DEFAULT 0.000000 COMMENT '物品成本',
                                             `contents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '发货内容',
                                             PRIMARY KEY (`id`) USING BTREE,
                                             UNIQUE INDEX `trade_no`(`trade_no`) USING BTREE,
                                             UNIQUE INDEX `item_trade_no`(`item_trade_no`) USING BTREE,
                                             INDEX `user_id`(`user_id`) USING BTREE,
                                             INDEX `customer_id`(`customer_id`) USING BTREE,
                                             INDEX `repertory_item_id`(`repertory_item_id`) USING BTREE,
                                             INDEX `repertory_item_sku_id`(`repertory_item_sku_id`) USING BTREE,
                                             INDEX `status`(`status`) USING BTREE,
                                             INDEX `trade_time`(`trade_time`) USING BTREE,
                                             INDEX `main_trade_no`(`main_trade_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 218 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}repertory_order_commission`;
CREATE TABLE `${prefix}repertory_order_commission`  (
                                                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                        `order_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '订单ID',
                                                        `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
                                                        `pid` bigint(20) UNSIGNED NOT NULL COMMENT '上级ID',
                                                        `amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '佣金',
                                                        `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '24位订单号',
                                                        PRIMARY KEY (`id`) USING BTREE,
                                                        INDEX `user_id`(`user_id`, `pid`) USING BTREE,
                                                        INDEX `order_id`(`order_id`) USING BTREE,
                                                        INDEX `trade_no`(`trade_no`) USING BTREE,
                                                        CONSTRAINT `${prefix}repertory_order_commission_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `${prefix}repertory_order` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}role`;
CREATE TABLE `${prefix}role`  (
                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
                                  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
                                  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
                                  `status` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '状态：0=停用，1=启用',
                                  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}role` VALUES (1, '超级管理员', '2022-11-29 17:59:35', 1);


DROP TABLE IF EXISTS `${prefix}role_permission`;
CREATE TABLE `${prefix}role_permission`  (
                                             `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
                                             `role_id` bigint(20) UNSIGNED NOT NULL COMMENT '角色id',
                                             `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限id',
                                             PRIMARY KEY (`id`) USING BTREE,
                                             UNIQUE INDEX `role_id`(`role_id`, `permission_id`) USING BTREE,
                                             INDEX `permission_id`(`permission_id`) USING BTREE,
                                             CONSTRAINT `${prefix}role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `${prefix}role` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                             CONSTRAINT `${prefix}role_permission_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `${prefix}permission` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 251 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}role_permission` VALUES (1, 1, 1);
INSERT INTO `${prefix}role_permission` VALUES (2, 1, 2);
INSERT INTO `${prefix}role_permission` VALUES (3, 1, 3);
INSERT INTO `${prefix}role_permission` VALUES (4, 1, 4);
INSERT INTO `${prefix}role_permission` VALUES (5, 1, 5);
INSERT INTO `${prefix}role_permission` VALUES (6, 1, 6);
INSERT INTO `${prefix}role_permission` VALUES (7, 1, 7);
INSERT INTO `${prefix}role_permission` VALUES (8, 1, 8);
INSERT INTO `${prefix}role_permission` VALUES (9, 1, 9);
INSERT INTO `${prefix}role_permission` VALUES (10, 1, 10);
INSERT INTO `${prefix}role_permission` VALUES (11, 1, 11);
INSERT INTO `${prefix}role_permission` VALUES (12, 1, 12);
INSERT INTO `${prefix}role_permission` VALUES (13, 1, 13);
INSERT INTO `${prefix}role_permission` VALUES (14, 1, 14);
INSERT INTO `${prefix}role_permission` VALUES (15, 1, 15);
INSERT INTO `${prefix}role_permission` VALUES (16, 1, 16);
INSERT INTO `${prefix}role_permission` VALUES (17, 1, 17);
INSERT INTO `${prefix}role_permission` VALUES (18, 1, 18);
INSERT INTO `${prefix}role_permission` VALUES (19, 1, 19);
INSERT INTO `${prefix}role_permission` VALUES (20, 1, 20);
INSERT INTO `${prefix}role_permission` VALUES (21, 1, 21);
INSERT INTO `${prefix}role_permission` VALUES (22, 1, 22);
INSERT INTO `${prefix}role_permission` VALUES (23, 1, 23);
INSERT INTO `${prefix}role_permission` VALUES (24, 1, 24);
INSERT INTO `${prefix}role_permission` VALUES (25, 1, 25);
INSERT INTO `${prefix}role_permission` VALUES (26, 1, 26);
INSERT INTO `${prefix}role_permission` VALUES (27, 1, 27);
INSERT INTO `${prefix}role_permission` VALUES (28, 1, 28);
INSERT INTO `${prefix}role_permission` VALUES (29, 1, 29);
INSERT INTO `${prefix}role_permission` VALUES (30, 1, 30);
INSERT INTO `${prefix}role_permission` VALUES (31, 1, 31);
INSERT INTO `${prefix}role_permission` VALUES (32, 1, 32);
INSERT INTO `${prefix}role_permission` VALUES (33, 1, 33);
INSERT INTO `${prefix}role_permission` VALUES (34, 1, 34);
INSERT INTO `${prefix}role_permission` VALUES (35, 1, 35);
INSERT INTO `${prefix}role_permission` VALUES (36, 1, 36);
INSERT INTO `${prefix}role_permission` VALUES (37, 1, 37);
INSERT INTO `${prefix}role_permission` VALUES (38, 1, 38);
INSERT INTO `${prefix}role_permission` VALUES (39, 1, 39);
INSERT INTO `${prefix}role_permission` VALUES (40, 1, 40);
INSERT INTO `${prefix}role_permission` VALUES (41, 1, 41);
INSERT INTO `${prefix}role_permission` VALUES (42, 1, 44);
INSERT INTO `${prefix}role_permission` VALUES (43, 1, 45);
INSERT INTO `${prefix}role_permission` VALUES (44, 1, 46);
INSERT INTO `${prefix}role_permission` VALUES (45, 1, 47);
INSERT INTO `${prefix}role_permission` VALUES (46, 1, 48);
INSERT INTO `${prefix}role_permission` VALUES (47, 1, 49);
INSERT INTO `${prefix}role_permission` VALUES (48, 1, 50);
INSERT INTO `${prefix}role_permission` VALUES (49, 1, 51);
INSERT INTO `${prefix}role_permission` VALUES (50, 1, 52);
INSERT INTO `${prefix}role_permission` VALUES (51, 1, 53);
INSERT INTO `${prefix}role_permission` VALUES (52, 1, 54);
INSERT INTO `${prefix}role_permission` VALUES (53, 1, 55);
INSERT INTO `${prefix}role_permission` VALUES (54, 1, 56);
INSERT INTO `${prefix}role_permission` VALUES (55, 1, 57);
INSERT INTO `${prefix}role_permission` VALUES (56, 1, 58);
INSERT INTO `${prefix}role_permission` VALUES (57, 1, 59);
INSERT INTO `${prefix}role_permission` VALUES (58, 1, 60);
INSERT INTO `${prefix}role_permission` VALUES (59, 1, 61);
INSERT INTO `${prefix}role_permission` VALUES (60, 1, 62);
INSERT INTO `${prefix}role_permission` VALUES (61, 1, 63);
INSERT INTO `${prefix}role_permission` VALUES (62, 1, 64);
INSERT INTO `${prefix}role_permission` VALUES (63, 1, 65);
INSERT INTO `${prefix}role_permission` VALUES (64, 1, 66);
INSERT INTO `${prefix}role_permission` VALUES (65, 1, 67);
INSERT INTO `${prefix}role_permission` VALUES (66, 1, 69);
INSERT INTO `${prefix}role_permission` VALUES (67, 1, 70);
INSERT INTO `${prefix}role_permission` VALUES (68, 1, 71);
INSERT INTO `${prefix}role_permission` VALUES (69, 1, 72);
INSERT INTO `${prefix}role_permission` VALUES (70, 1, 73);
INSERT INTO `${prefix}role_permission` VALUES (71, 1, 74);
INSERT INTO `${prefix}role_permission` VALUES (72, 1, 75);
INSERT INTO `${prefix}role_permission` VALUES (73, 1, 76);
INSERT INTO `${prefix}role_permission` VALUES (74, 1, 77);
INSERT INTO `${prefix}role_permission` VALUES (75, 1, 78);
INSERT INTO `${prefix}role_permission` VALUES (76, 1, 79);
INSERT INTO `${prefix}role_permission` VALUES (77, 1, 80);
INSERT INTO `${prefix}role_permission` VALUES (78, 1, 81);
INSERT INTO `${prefix}role_permission` VALUES (79, 1, 82);
INSERT INTO `${prefix}role_permission` VALUES (80, 1, 83);
INSERT INTO `${prefix}role_permission` VALUES (81, 1, 84);
INSERT INTO `${prefix}role_permission` VALUES (82, 1, 85);
INSERT INTO `${prefix}role_permission` VALUES (83, 1, 86);
INSERT INTO `${prefix}role_permission` VALUES (84, 1, 87);
INSERT INTO `${prefix}role_permission` VALUES (85, 1, 88);
INSERT INTO `${prefix}role_permission` VALUES (86, 1, 89);
INSERT INTO `${prefix}role_permission` VALUES (87, 1, 90);
INSERT INTO `${prefix}role_permission` VALUES (88, 1, 91);
INSERT INTO `${prefix}role_permission` VALUES (89, 1, 92);
INSERT INTO `${prefix}role_permission` VALUES (90, 1, 93);
INSERT INTO `${prefix}role_permission` VALUES (91, 1, 94);
INSERT INTO `${prefix}role_permission` VALUES (92, 1, 95);
INSERT INTO `${prefix}role_permission` VALUES (93, 1, 96);
INSERT INTO `${prefix}role_permission` VALUES (94, 1, 97);
INSERT INTO `${prefix}role_permission` VALUES (95, 1, 98);
INSERT INTO `${prefix}role_permission` VALUES (96, 1, 99);
INSERT INTO `${prefix}role_permission` VALUES (97, 1, 100);
INSERT INTO `${prefix}role_permission` VALUES (98, 1, 101);
INSERT INTO `${prefix}role_permission` VALUES (99, 1, 102);
INSERT INTO `${prefix}role_permission` VALUES (100, 1, 103);
INSERT INTO `${prefix}role_permission` VALUES (101, 1, 104);
INSERT INTO `${prefix}role_permission` VALUES (102, 1, 105);
INSERT INTO `${prefix}role_permission` VALUES (103, 1, 106);
INSERT INTO `${prefix}role_permission` VALUES (104, 1, 107);
INSERT INTO `${prefix}role_permission` VALUES (105, 1, 109);
INSERT INTO `${prefix}role_permission` VALUES (106, 1, 110);
INSERT INTO `${prefix}role_permission` VALUES (107, 1, 111);
INSERT INTO `${prefix}role_permission` VALUES (108, 1, 112);
INSERT INTO `${prefix}role_permission` VALUES (109, 1, 113);
INSERT INTO `${prefix}role_permission` VALUES (110, 1, 114);
INSERT INTO `${prefix}role_permission` VALUES (111, 1, 115);
INSERT INTO `${prefix}role_permission` VALUES (112, 1, 116);
INSERT INTO `${prefix}role_permission` VALUES (113, 1, 117);
INSERT INTO `${prefix}role_permission` VALUES (114, 1, 118);
INSERT INTO `${prefix}role_permission` VALUES (115, 1, 119);
INSERT INTO `${prefix}role_permission` VALUES (116, 1, 120);
INSERT INTO `${prefix}role_permission` VALUES (117, 1, 121);
INSERT INTO `${prefix}role_permission` VALUES (118, 1, 122);
INSERT INTO `${prefix}role_permission` VALUES (119, 1, 123);
INSERT INTO `${prefix}role_permission` VALUES (120, 1, 124);
INSERT INTO `${prefix}role_permission` VALUES (121, 1, 125);
INSERT INTO `${prefix}role_permission` VALUES (122, 1, 126);
INSERT INTO `${prefix}role_permission` VALUES (123, 1, 127);
INSERT INTO `${prefix}role_permission` VALUES (124, 1, 128);
INSERT INTO `${prefix}role_permission` VALUES (125, 1, 129);
INSERT INTO `${prefix}role_permission` VALUES (126, 1, 130);
INSERT INTO `${prefix}role_permission` VALUES (127, 1, 131);
INSERT INTO `${prefix}role_permission` VALUES (128, 1, 132);
INSERT INTO `${prefix}role_permission` VALUES (129, 1, 133);
INSERT INTO `${prefix}role_permission` VALUES (130, 1, 134);
INSERT INTO `${prefix}role_permission` VALUES (131, 1, 135);
INSERT INTO `${prefix}role_permission` VALUES (132, 1, 136);
INSERT INTO `${prefix}role_permission` VALUES (133, 1, 137);
INSERT INTO `${prefix}role_permission` VALUES (134, 1, 138);
INSERT INTO `${prefix}role_permission` VALUES (135, 1, 139);
INSERT INTO `${prefix}role_permission` VALUES (136, 1, 140);
INSERT INTO `${prefix}role_permission` VALUES (137, 1, 141);
INSERT INTO `${prefix}role_permission` VALUES (138, 1, 142);
INSERT INTO `${prefix}role_permission` VALUES (139, 1, 143);
INSERT INTO `${prefix}role_permission` VALUES (140, 1, 144);
INSERT INTO `${prefix}role_permission` VALUES (141, 1, 145);
INSERT INTO `${prefix}role_permission` VALUES (142, 1, 146);
INSERT INTO `${prefix}role_permission` VALUES (143, 1, 147);
INSERT INTO `${prefix}role_permission` VALUES (144, 1, 148);
INSERT INTO `${prefix}role_permission` VALUES (145, 1, 149);
INSERT INTO `${prefix}role_permission` VALUES (146, 1, 150);
INSERT INTO `${prefix}role_permission` VALUES (147, 1, 151);
INSERT INTO `${prefix}role_permission` VALUES (148, 1, 152);
INSERT INTO `${prefix}role_permission` VALUES (149, 1, 153);
INSERT INTO `${prefix}role_permission` VALUES (150, 1, 154);
INSERT INTO `${prefix}role_permission` VALUES (151, 1, 155);
INSERT INTO `${prefix}role_permission` VALUES (152, 1, 156);
INSERT INTO `${prefix}role_permission` VALUES (153, 1, 157);
INSERT INTO `${prefix}role_permission` VALUES (154, 1, 158);
INSERT INTO `${prefix}role_permission` VALUES (155, 1, 159);
INSERT INTO `${prefix}role_permission` VALUES (156, 1, 160);
INSERT INTO `${prefix}role_permission` VALUES (157, 1, 161);
INSERT INTO `${prefix}role_permission` VALUES (158, 1, 162);
INSERT INTO `${prefix}role_permission` VALUES (159, 1, 163);
INSERT INTO `${prefix}role_permission` VALUES (160, 1, 165);
INSERT INTO `${prefix}role_permission` VALUES (161, 1, 167);
INSERT INTO `${prefix}role_permission` VALUES (162, 1, 168);
INSERT INTO `${prefix}role_permission` VALUES (163, 1, 169);
INSERT INTO `${prefix}role_permission` VALUES (164, 1, 170);
INSERT INTO `${prefix}role_permission` VALUES (165, 1, 171);
INSERT INTO `${prefix}role_permission` VALUES (166, 1, 172);
INSERT INTO `${prefix}role_permission` VALUES (167, 1, 173);
INSERT INTO `${prefix}role_permission` VALUES (168, 1, 174);
INSERT INTO `${prefix}role_permission` VALUES (169, 1, 175);
INSERT INTO `${prefix}role_permission` VALUES (170, 1, 176);
INSERT INTO `${prefix}role_permission` VALUES (171, 1, 177);
INSERT INTO `${prefix}role_permission` VALUES (172, 1, 178);
INSERT INTO `${prefix}role_permission` VALUES (173, 1, 179);
INSERT INTO `${prefix}role_permission` VALUES (174, 1, 180);
INSERT INTO `${prefix}role_permission` VALUES (175, 1, 181);
INSERT INTO `${prefix}role_permission` VALUES (176, 1, 182);
INSERT INTO `${prefix}role_permission` VALUES (177, 1, 183);
INSERT INTO `${prefix}role_permission` VALUES (178, 1, 184);
INSERT INTO `${prefix}role_permission` VALUES (179, 1, 185);
INSERT INTO `${prefix}role_permission` VALUES (180, 1, 186);
INSERT INTO `${prefix}role_permission` VALUES (181, 1, 187);
INSERT INTO `${prefix}role_permission` VALUES (182, 1, 188);
INSERT INTO `${prefix}role_permission` VALUES (183, 1, 189);
INSERT INTO `${prefix}role_permission` VALUES (184, 1, 190);
INSERT INTO `${prefix}role_permission` VALUES (185, 1, 191);
INSERT INTO `${prefix}role_permission` VALUES (186, 1, 192);
INSERT INTO `${prefix}role_permission` VALUES (187, 1, 193);
INSERT INTO `${prefix}role_permission` VALUES (188, 1, 194);
INSERT INTO `${prefix}role_permission` VALUES (189, 1, 195);
INSERT INTO `${prefix}role_permission` VALUES (190, 1, 196);
INSERT INTO `${prefix}role_permission` VALUES (191, 1, 197);
INSERT INTO `${prefix}role_permission` VALUES (192, 1, 198);
INSERT INTO `${prefix}role_permission` VALUES (193, 1, 199);
INSERT INTO `${prefix}role_permission` VALUES (194, 1, 200);
INSERT INTO `${prefix}role_permission` VALUES (195, 1, 201);
INSERT INTO `${prefix}role_permission` VALUES (196, 1, 202);
INSERT INTO `${prefix}role_permission` VALUES (197, 1, 203);
INSERT INTO `${prefix}role_permission` VALUES (198, 1, 204);
INSERT INTO `${prefix}role_permission` VALUES (199, 1, 205);
INSERT INTO `${prefix}role_permission` VALUES (200, 1, 206);
INSERT INTO `${prefix}role_permission` VALUES (201, 1, 207);
INSERT INTO `${prefix}role_permission` VALUES (202, 1, 208);
INSERT INTO `${prefix}role_permission` VALUES (203, 1, 209);
INSERT INTO `${prefix}role_permission` VALUES (204, 1, 210);
INSERT INTO `${prefix}role_permission` VALUES (205, 1, 211);
INSERT INTO `${prefix}role_permission` VALUES (206, 1, 212);
INSERT INTO `${prefix}role_permission` VALUES (207, 1, 213);
INSERT INTO `${prefix}role_permission` VALUES (208, 1, 214);
INSERT INTO `${prefix}role_permission` VALUES (209, 1, 215);
INSERT INTO `${prefix}role_permission` VALUES (210, 1, 216);
INSERT INTO `${prefix}role_permission` VALUES (211, 1, 217);
INSERT INTO `${prefix}role_permission` VALUES (212, 1, 218);
INSERT INTO `${prefix}role_permission` VALUES (213, 1, 219);
INSERT INTO `${prefix}role_permission` VALUES (214, 1, 220);
INSERT INTO `${prefix}role_permission` VALUES (215, 1, 221);
INSERT INTO `${prefix}role_permission` VALUES (216, 1, 222);
INSERT INTO `${prefix}role_permission` VALUES (217, 1, 223);
INSERT INTO `${prefix}role_permission` VALUES (218, 1, 224);
INSERT INTO `${prefix}role_permission` VALUES (219, 1, 225);
INSERT INTO `${prefix}role_permission` VALUES (220, 1, 226);
INSERT INTO `${prefix}role_permission` VALUES (221, 1, 227);
INSERT INTO `${prefix}role_permission` VALUES (222, 1, 228);
INSERT INTO `${prefix}role_permission` VALUES (223, 1, 229);
INSERT INTO `${prefix}role_permission` VALUES (224, 1, 230);
INSERT INTO `${prefix}role_permission` VALUES (225, 1, 231);
INSERT INTO `${prefix}role_permission` VALUES (226, 1, 232);
INSERT INTO `${prefix}role_permission` VALUES (227, 1, 233);
INSERT INTO `${prefix}role_permission` VALUES (228, 1, 234);
INSERT INTO `${prefix}role_permission` VALUES (229, 1, 235);
INSERT INTO `${prefix}role_permission` VALUES (230, 1, 236);
INSERT INTO `${prefix}role_permission` VALUES (231, 1, 237);
INSERT INTO `${prefix}role_permission` VALUES (232, 1, 238);
INSERT INTO `${prefix}role_permission` VALUES (233, 1, 239);
INSERT INTO `${prefix}role_permission` VALUES (234, 1, 240);
INSERT INTO `${prefix}role_permission` VALUES (235, 1, 241);
INSERT INTO `${prefix}role_permission` VALUES (236, 1, 242);
INSERT INTO `${prefix}role_permission` VALUES (237, 1, 243);
INSERT INTO `${prefix}role_permission` VALUES (238, 1, 244);
INSERT INTO `${prefix}role_permission` VALUES (239, 1, 245);
INSERT INTO `${prefix}role_permission` VALUES (240, 1, 246);
INSERT INTO `${prefix}role_permission` VALUES (241, 1, 247);
INSERT INTO `${prefix}role_permission` VALUES (242, 1, 248);
INSERT INTO `${prefix}role_permission` VALUES (243, 1, 249);
INSERT INTO `${prefix}role_permission` VALUES (244, 1, 250);
INSERT INTO `${prefix}role_permission` VALUES (245, 1, 251);
INSERT INTO `${prefix}role_permission` VALUES (246, 1, 252);
INSERT INTO `${prefix}role_permission` VALUES (247, 1, 253);
INSERT INTO `${prefix}role_permission` VALUES (248, 1, 254);
INSERT INTO `${prefix}role_permission` VALUES (249, 1, 255);
INSERT INTO `${prefix}role_permission` VALUES (250, 1, 256);


DROP TABLE IF EXISTS `${prefix}site`;
CREATE TABLE `${prefix}site`  (
                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户id',
                                  `host` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '绑定域名',
                                  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
                                  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型：0=子域名，1=独立域名',
                                  `ssl_expire_time` datetime NULL DEFAULT NULL COMMENT 'SSL证书到期时间',
                                  `ssl_issuer` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'SSL颁发者',
                                  `ssl_domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'SSL证书域名',
                                  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '域名状态：0=BAN，1=正常',
                                  PRIMARY KEY (`id`) USING BTREE,
                                  UNIQUE INDEX `host`(`host`) USING BTREE,
                                  INDEX `user_id`(`user_id`) USING BTREE,
                                  INDEX `type`(`type`) USING BTREE,
                                  INDEX `status`(`status`) USING BTREE,
                                  CONSTRAINT `${prefix}site_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}upload`;
CREATE TABLE `${prefix}upload`  (
                                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                    `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT 'null=后台',
                                    `hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件MD5',
                                    `type` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件类型',
                                    `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件路径',
                                    `create_time` datetime NOT NULL COMMENT '上传时间',
                                    `note` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件备注',
                                    PRIMARY KEY (`id`) USING BTREE,
                                    UNIQUE INDEX `hash`(`hash`) USING BTREE,
                                    INDEX `user_id`(`user_id`) USING BTREE,
                                    INDEX `type`(`type`) USING BTREE,
                                    INDEX `create_time`(`create_time`) USING BTREE,
                                    INDEX `note`(`note`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 55 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user`;
CREATE TABLE `${prefix}user`  (
                                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '会员名',
                                  `email` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮箱',
                                  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录密码',
                                  `salt` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '盐',
                                  `app_key` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '对接密钥',
                                  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '头像',
                                  `integral` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分',
                                  `pid` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '上级ID',
                                  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=封禁，1=正常',
                                  `note` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
                                  `balance` decimal(13, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '账户余额',
                                  `withdraw_amount` decimal(13, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '可提现的额度',
                                  `group_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户组ID',
                                  `level_id` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '会员等级ID',
                                  `api_code` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '对接码',
                                  `invite_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '邀请人ID',
                                  PRIMARY KEY (`id`) USING BTREE,
                                  UNIQUE INDEX `username`(`username`) USING BTREE,
                                  UNIQUE INDEX `email`(`email`) USING BTREE,
                                  UNIQUE INDEX `api_code`(`api_code`) USING BTREE,
                                  INDEX `pid`(`pid`) USING BTREE,
                                  INDEX `integral`(`integral`) USING BTREE,
                                  INDEX `status`(`status`) USING BTREE,
                                  INDEX `note`(`note`) USING BTREE,
                                  INDEX `group_id`(`group_id`) USING BTREE,
                                  INDEX `level_id`(`level_id`) USING BTREE,
                                  INDEX `balance`(`balance`) USING BTREE,
                                  INDEX `invite_id`(`invite_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_bank_card`;
CREATE TABLE `${prefix}user_bank_card`  (
                                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户id',
                                            `bank_id` bigint(20) UNSIGNED NOT NULL COMMENT '银行id',
                                            `card_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行卡号',
                                            `card_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '银行卡图片',
                                            `card_image_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '银行卡图片hash',
                                            `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '银行卡状态：0=异常，1=正常',
                                            `create_time` datetime NOT NULL COMMENT '添加时间',
                                            PRIMARY KEY (`id`) USING BTREE,
                                            UNIQUE INDEX `card_no`(`card_no`) USING BTREE,
                                            UNIQUE INDEX `card_image_hash`(`card_image_hash`) USING BTREE,
                                            INDEX `user_id`(`user_id`) USING BTREE,
                                            INDEX `bank_id`(`bank_id`) USING BTREE,
                                            INDEX `status`(`status`) USING BTREE,
                                            CONSTRAINT `${prefix}user_bank_card_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                            CONSTRAINT `${prefix}user_bank_card_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `${prefix}bank` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_bill`;
CREATE TABLE `${prefix}user_bill`  (
                                       `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                       `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
                                       `amount` decimal(10, 2) UNSIGNED NOT NULL COMMENT '金额数量',
                                       `before_balance` decimal(13, 2) UNSIGNED NULL DEFAULT NULL COMMENT '操作之前余额',
                                       `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '账单类型：0=进货支付，1=供货结算，2=下级返佣，3=订单分红，4=购物消费',
                                       `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0=完成，1=冻结中，2=已回滚',
                                       `unfreeze_time` datetime NULL DEFAULT NULL COMMENT '解冻时间',
                                       `after_balance` decimal(13, 2) UNSIGNED NULL DEFAULT NULL COMMENT '操作之后余额',
                                       `action` tinyint(4) UNSIGNED NOT NULL COMMENT '账变类型：0=支出，1=收入',
                                       `is_withdraw` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否可以提现：0=否，1=是',
                                       `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '关联订单号',
                                       `remark` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
                                       `update_time` datetime NULL DEFAULT NULL COMMENT '变更时间',
                                       `create_time` datetime NOT NULL COMMENT '创建时间',
                                       PRIMARY KEY (`id`) USING BTREE,
                                       INDEX `user_id`(`user_id`) USING BTREE,
                                       INDEX `trade_no`(`trade_no`) USING BTREE,
                                       INDEX `type`(`type`) USING BTREE,
                                       INDEX `status`(`status`) USING BTREE,
                                       INDEX `action`(`action`) USING BTREE,
                                       INDEX `unfreeze_time`(`unfreeze_time`) USING BTREE,
                                       INDEX `remark`(`remark`) USING BTREE,
                                       CONSTRAINT `${prefix}user_bill_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 567 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_group`;
CREATE TABLE `${prefix}user_group`  (
                                        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '身份标识(图标)',
                                        `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '组名称',
                                        `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                        `price` decimal(14, 3) UNSIGNED NOT NULL COMMENT '开通价格',
                                        `is_merchant` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家权限：0=关闭，1=启用',
                                        `is_supplier` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '供货商权限：0=关闭，1=启用',
                                        `tax_ratio` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT '税收比例，0=免税',
                                        `dividend_amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商家分红',
                                        `create_time` datetime NOT NULL COMMENT '创建时间',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}user_group` VALUES (1, '/favicon.ico', '经销商', 0, 0.000, 1, 1, 0.100, 10.00, '2024-04-01 00:00:00');


DROP TABLE IF EXISTS `${prefix}user_identity`;
CREATE TABLE `${prefix}user_identity`  (
                                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '会员id',
                                           `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '姓名',
                                           `id_card` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '证件号码',
                                           `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '证件类型：0=中国居民身份证，1=香港永久居民身份证，2=澳门永久性居民身份证，3=国际护照（包括大陆/台湾/国际）',
                                           `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '认证状态：0=审核中，1=认证成功，2=认证失败',
                                           `create_time` datetime NOT NULL COMMENT '创建时间',
                                           `review_time` datetime NULL DEFAULT NULL COMMENT '审核时间',
                                           PRIMARY KEY (`id`) USING BTREE,
                                           UNIQUE INDEX `user_id`(`user_id`) USING BTREE,
                                           UNIQUE INDEX `id_card`(`id_card`) USING BTREE,
                                           INDEX `status`(`status`) USING BTREE,
                                           CONSTRAINT `${prefix}user_identity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_level`;
CREATE TABLE `${prefix}user_level`  (
                                        `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id，null为后台',
                                        `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '等级图标',
                                        `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '等级名称',
                                        `upgrade_requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '免费升级要求(JSON)',
                                        `upgrade_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '直升价格，0代表不支持',
                                        `privilege_introduce` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '特权介绍',
                                        `privilege_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '特权内容',
                                        `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
                                        `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
                                        PRIMARY KEY (`id`) USING BTREE,
                                        UNIQUE INDEX `level_sort`(`user_id`, `sort`) USING BTREE,
                                        INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `${prefix}user_level` VALUES (1, NULL, '/assets/user/images/lv1.png', '平民', '[]', 0.00, '', '', 0, NULL);


DROP TABLE IF EXISTS `${prefix}user_lifetime`;
CREATE TABLE `${prefix}user_lifetime`  (
                                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '会员ID',
                                           `total_consumption_amount` decimal(14, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '总消费',
                                           `total_recharge_amount` decimal(14, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '总充值',
                                           `total_referral_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总推广人数',
                                           `favorite_item_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '最爱的商品ID(购买次数最多的商品)',
                                           `favorite_item_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最爱的商品总购买次数',
                                           `total_login_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总登录次数',
                                           `total_profit_amount` decimal(14, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '总盈利(赚到的钱)',
                                           `total_withdraw_amount` decimal(14, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '总提现金额',
                                           `total_withdraw_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总提现次数',
                                           `share_item_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '最喜欢分享的商品',
                                           `share_item_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最喜欢分享的商品分享的次数',
                                           `last_consumption_time` datetime NULL DEFAULT NULL COMMENT '最后一次消费时间',
                                           `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后一次登录时间',
                                           `last_active_time` datetime NULL DEFAULT NULL COMMENT '最后一次活跃时间',
                                           `register_time` datetime NOT NULL COMMENT '账号注册时间',
                                           `register_ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '账号注册IP地址',
                                           `register_ua` varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '注册时的浏览器UA',
                                           `login_status` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '登录状态，0=离线，1=在线',
                                           PRIMARY KEY (`id`) USING BTREE,
                                           UNIQUE INDEX `user_id`(`user_id`) USING BTREE,
                                           INDEX `last_active_time`(`last_active_time`) USING BTREE,
                                           INDEX `last_login_time`(`last_login_time`) USING BTREE,
                                           INDEX `register_time`(`register_time`) USING BTREE,
                                           CONSTRAINT `${prefix}user_lifetime_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_log`;
CREATE TABLE `${prefix}user_log`  (
                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                      `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '会员id',
                                      `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志内容',
                                      `create_time` datetime NOT NULL COMMENT '创建时间',
                                      `create_ip` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'IP地址',
                                      `create_ua` varchar(768) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '浏览器UA',
                                      PRIMARY KEY (`id`) USING BTREE,
                                      INDEX `user_id`(`user_id`) USING BTREE,
                                      CONSTRAINT `${prefix}user_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 92 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_login_log`;
CREATE TABLE `${prefix}user_login_log`  (
                                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `user_id` bigint(20) UNSIGNED NOT NULL,
                                            `create_time` datetime NOT NULL COMMENT '创建时间',
                                            `ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'IP地址',
                                            `ua` varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '浏览器UA',
                                            `is_dangerous` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否危险：0=否，1=是',
                                            PRIMARY KEY (`id`) USING BTREE,
                                            INDEX `user_id`(`user_id`) USING BTREE,
                                            INDEX `ip`(`ip`) USING BTREE,
                                            CONSTRAINT `${prefix}user_login_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `${prefix}user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;




DROP TABLE IF EXISTS `${prefix}user_withdraw`;
CREATE TABLE `${prefix}user_withdraw`  (
                                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '会员id',
                                           `card_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '银行卡id',
                                           `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
                                           `amount` decimal(14, 2) UNSIGNED NOT NULL COMMENT '提现金额',
                                           `status` tinyint(4) UNSIGNED NOT NULL COMMENT '状态：0=银行处理中，1=提现已到账，2=提现被驳回',
                                           `handle_message` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '处理消息',
                                           `create_time` datetime NOT NULL COMMENT '创建时间',
                                           `handle_time` datetime NULL DEFAULT NULL COMMENT '处理时间',
                                           PRIMARY KEY (`id`) USING BTREE,
                                           UNIQUE INDEX `trade_no`(`trade_no`) USING BTREE,
                                           INDEX `user_id`(`user_id`) USING BTREE,
                                           INDEX `card_id`(`card_id`) USING BTREE,
                                           INDEX `status`(`status`) USING BTREE,
                                           INDEX `create_time`(`create_time`) USING BTREE,
                                           INDEX `handle_time`(`handle_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;



SET FOREIGN_KEY_CHECKS = 1;