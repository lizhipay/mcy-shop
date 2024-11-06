!function () {
    function getConfig(key, done = null) {
        util.post({
            url: "/admin/config/get?key=" + key,
            done: res => {
                typeof done === "function" && done(res.data);
            }
        });
    }

    $('.site-setting').click(() => {
        getConfig("site", config => {
            component.popup({
                submit: '/admin/config/save?key=site',
                tab: [
                    {
                        name: util.icon('icon-wangzhanshezhi') + " 网站设置",
                        form: [
                            {
                                title: "主站域名",
                                name: "domains",
                                type: "html",
                                language: "ini",
                                tips: "域名一行一个",
                                height: 90
                            },
                            {
                                title: "LOGO",
                                name: "logo",
                                type: "image",
                                placeholder: "请选择LOGO(.ico)",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                                height: 64
                            },
                            {
                                title: "网站背景图",
                                name: "bg_image",
                                type: "image",
                                placeholder: "请选择背景图片",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                                height: 200,
                                tips: "背景图建议使用 1920*1080 分辨率"
                            },
                            {
                                title: "PC模板",
                                name: "pc_theme",
                                type: "select",
                                placeholder: "请选择",
                                dict: "theme"
                            },
                            {
                                title: "手机模板",
                                name: "mobile_theme",
                                type: "select",
                                placeholder: "请选择",
                                dict: "theme"
                            },
                            {
                                title: "网站标题",
                                name: "title",
                                type: "input",
                                placeholder: "请输入网站标题"
                            },
                            {
                                title: "关键词",
                                name: "keywords",
                                type: "input",
                                placeholder: "请输入网站关键词"
                            },
                            {
                                title: "网站描述",
                                name: "description",
                                type: "textarea",
                                placeholder: "请输入网站描述"
                            },
                            {
                                title: "会话过期时间",
                                name: "session_expire",
                                type: "number",
                                placeholder: "请输入会话过期时间",
                                tips: "该时间控制用户登录的SESSION存活时间，不能大于：31536000"
                            },
                            {
                                title: "强制登录",
                                name: "force_login",
                                type: "switch",
                                placeholder: "启用|关闭",
                                tips: "用户必须登录才能访问网站"
                            },
                            {
                                title: "HTTPS",
                                name: "is_https",
                                type: "switch",
                                placeholder: "启用|关闭",
                                tips: "您的网站是否支持HTTPS，如果支持，请务必勾选此选项"
                            },
                            {
                                title: "IP获取模式",
                                name: "ip_mode",
                                type: "select",
                                placeholder: "请选择",
                                dict: "ipMode",
                                default: "auto"
                            },
                            {
                                title: "备案号",
                                name: "icp",
                                type: "textarea",
                                height: 38,
                                placeholder: "ICP备案号"
                            }
                        ]
                    },
                    {
                        name: util.icon("icon-gonggao2") + " 公告",
                        form: [
                            {
                                title: "公告背景",
                                name: "notice_banner",
                                type: "image",
                                placeholder: "请选择图片",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                                height: 128
                            },
                            {
                                title: "网站公告",
                                titleHide: true,
                                name: "notice",
                                type: "editor",
                                placeholder: "网站公告",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                                height: 500
                            }
                        ]
                    },
                    {
                        name: util.icon("icon-044-folder") + " 文件上传",
                        form: [
                            {
                                title: "最大上传文件(KB)",
                                name: "max_upload_size",
                                type: "input",
                                placeholder: "请输入最大上传文件大小",
                                default: 20480,
                                tips: "修改了此项后，还需要修改nginx配置文件中的client_max_body_size"
                            }
                        ]
                    }
                ],
                autoPosition: true,
                content: {
                    css: {
                        height: "auto",
                        overflow: "inherit"
                    }
                },
                height: "auto",
                width: "760px",
                assign: config
            });
        })
    });

    $('.register-setting').click(() => {
        getConfig("register", config => {
            component.popup({
                submit: '/admin/config/save?key=register',
                tab: [
                    {
                        name: util.icon('icon-yonghuzhuce') + " 注册/登录配置",
                        form: [
                            {
                                title: "注册开关",
                                name: "status",
                                type: "switch",
                                placeholder: "启用|关闭"
                            },
                            {
                                title: "用户服务协议",
                                name: "agreement_status",
                                type: "switch",
                                placeholder: "启用|关闭"
                            },
                            {
                                title: "邮箱注册",
                                name: "email_register",
                                type: "switch",
                                placeholder: "启用|关闭",
                                change: (popup, val) => {
                                    val ? popup.show('email_register_state') : popup.hide('email_register_state');
                                    !val && popup.setSwitch("email_register_state", val);
                                },
                                complete: (popup, val) => {
                                    val ? popup.show('email_register_state') : popup.hide('email_register_state');
                                }
                            },
                            {
                                title: "注册账号(Email)",
                                name: "email_register_state",
                                type: "switch",
                                placeholder: "启用|关闭",
                                hide: true
                            },
                            {
                                title: "重置密码(Email)",
                                name: "email_reset_state",
                                type: "switch",
                                placeholder: "启用|关闭"
                            },
                            {
                                title: "绑定邮箱(Email)",
                                name: "email_bind_state",
                                type: "switch",
                                placeholder: "启用|关闭"
                            },
                            {
                                title: "邮箱验证码模板(通用)",
                                name: "email_template",
                                type: "editor",
                                placeholder: "注册时邮件发送模板",
                                height: 180,
                                tips: "模板中使用{$code}来代替验证码变量",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                            }
                        ]
                    },
                    {
                        name: util.icon("icon-lanVrenzheng") + " 实名认证",
                        form: [
                            {
                                title: "初审状态",
                                titleHide: true,
                                name: "identity_status",
                                type: "select",
                                placeholder: "请选择",
                                dict: [
                                    {id: 0, name: "审核中(人工审核)"},
                                    {id: 1, name: "立即实名成功"}
                                ],
                                default: 0
                            }
                        ]
                    },
                    {
                        name: util.icon("icon-sirendingzhi") + " 用户协议",
                        form: [
                            {
                                title: false,
                                titleHide: true,
                                name: "user_agreement",
                                type: "editor",
                                placeholder: "用户协议",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                            }
                        ]
                    },
                    {
                        name: util.icon("icon-yinsizhengce") + " 隐私政策",
                        form: [
                            {
                                title: false,
                                titleHide: true,
                                name: "privacy_policy",
                                type: "editor",
                                placeholder: "隐私政策",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                            }
                        ]
                    },
                    {
                        name: util.icon("icon-fuwuxieyi") + " 商品服务协议",
                        form: [
                            {
                                title: false,
                                titleHide: true,
                                name: "service_agreement",
                                type: "editor",
                                placeholder: "商品服务协议",
                                uploadUrl: "/admin/upload",
                                photoAlbumUrl: '/admin/upload/get',
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true,
                content: {
                    css: {
                        height: "auto",
                        overflow: "inherit"
                    }
                },
                height: "auto",
                width: "860px"
            });
        });
    });

    $('.email-setting').click(() => {
        getConfig("email", config => {
            component.popup({
                submit: '/admin/config/save?key=email',
                tab: [
                    {
                        name: util.icon('icon-a-kl_e10') + ' 邮件配置',
                        form: [
                            {
                                title: "SMTP服务器",
                                name: "host",
                                type: "input",
                                placeholder: "请输入SMTP服务器地址"
                            },
                            {
                                title: "端口",
                                name: "port",
                                type: "input",
                                placeholder: "请输入端口",
                                default: 465
                            },
                            {
                                title: "安全协议",
                                name: "secure",
                                type: "radio",
                                dict: [
                                    {id: "ssl", name: "SSL"},
                                    {id: "tls", name: "TLS"},
                                    {id: "none", name: "不加密"}
                                ]
                            },
                            {
                                title: "用户名",
                                name: "username",
                                type: "input",
                                placeholder: "请输入用户名"
                            },
                            {
                                title: "发件人",
                                name: "from",
                                type: "input",
                                placeholder: "请输入发件人邮箱地址",
                                tips: "大部分邮件服务商的发件人都和用户名相同，只有少部分邮件服务商的用户名不是发件人邮箱"
                            },
                            {
                                title: "称呼",
                                name: "nickname",
                                type: "input",
                                placeholder: "请输入您的称呼，可以是你的网站简称"
                            },
                            {
                                title: "授权码/密码",
                                name: "password",
                                type: "input",
                                placeholder: "请输入授权码/密码",
                                tips: "请注意，很多邮件服务商采用的不是密码，而是授权码，如：腾讯，网易等均采用的授权码发信机制。"
                            },
                            {
                                title: false,
                                name: "custom",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`<button class="smtp-test-btn" type="button">${i18n('发信测试')}</button>`);
                                    $('.smtp-test-btn').click(() => {
                                        let data = form.getData();
                                        message.prompt({
                                            title: util.icon('icon-a-kl_e10') + '<space></space><space></space>' + i18n("发信测试"),
                                            html: "<span style='color: #b98f13;font-size: 16px;'>" + i18n('安全提醒：如果您的网站使用了CDN，请慎重使用SMTP邮件发信功能，大部分的SMTP服务商都会泄漏你的源服务器IP。') + "</span>",
                                            inputPlaceholder: i18n("请输入邮箱地址"),
                                            confirmButtonText: i18n("立即发送"),
                                            inputValidator: function (value) {
                                                if (!/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/.test(value)) {
                                                    return i18n("邮箱地址不正确");
                                                }
                                                data.email = value;
                                                util.post({
                                                    url: "/admin/config/smtp/test",
                                                    data: data,
                                                    done: () => {
                                                        message.alert("邮件发送成功，注意查看您的邮箱", "success");
                                                    }
                                                });
                                            }
                                        });
                                    });
                                }
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true,
            });
        });
    });

    $('.pay-setting').click(() => {
        getConfig("pay", config => {
            component.popup({
                submit: '/admin/config/save?key=pay',
                tab: [
                    {
                        name: util.icon('icon-mianxing-disanfangzhifu') + ' 自定义回调域名',
                        form: [
                            {
                                title: false,
                                name: "async_custom_tips",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`
<div class="block-tips">
<p>如果我们的服务器启用了防火墙（例如宝塔 Nginx 防火墙），或者我们的域名使用了带有 WAF 防护功能的 CDN，那么支付平台回调我们程序的请求可能会被这些防火墙拦截。</p>
<p>为了解决这个问题，我们可以解析一个复杂的多级子域名（A记录）指向你的服务器 IP，例如：b30b6e83c2d79194fb703.pay.baidu.com。当然，这个子域名是你可以自定义的。支付回调域名只能用于回调，如果直接访问该域名，我们的程序会返回 404 错误，表示该页面不存在。如果你访问自定义的支付回调域名并看到 404 错误，说明配置成功了。</p>
</div>
                                    `);

                                }
                            },
                            {
                                title: "功能开启",
                                name: "async_custom",
                                type: "switch",
                            },
                            {
                                title: "回调协议",
                                name: "async_protocol",
                                type: "radio",
                                tips: "如果你的域名支持HTTPS，请选择HTTPS，否则请选择HTTP",
                                dict: [
                                    {id: "http", name: "HTTP"},
                                    {id: "https", name: "HTTPS"}
                                ],
                            },
                            {
                                title: "回调域名",
                                name: "async_host",
                                type: "input",
                                placeholder: "支付回调域名",
                                tips: "IP+端口例子：1.1.1.1:8090，域名例子：pay.baidu.com",
                                regex: {
                                    value: "^(?:(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}|(?:[0-9]{1,3}\.){3}[0-9]{1,3}(?::[0-9]{1,5})?)$",
                                    message: "支付回调域名格式不正确"
                                }
                            }
                        ]
                    },
                    {
                        name: util.icon('icon-zhifupeizhi') + ' 币种配置',
                        form: [
                            {
                                title: "币种",
                                name: "currency",
                                type: "radio",
                                dict: "currency",
                                default: "rmb",
                                change: (form, value) => {
                                    if (value == "rmb") {
                                        form.hide("exchange_rate");
                                    } else {
                                        form.show("exchange_rate");
                                    }
                                },
                                complete: (form, value) => {
                                    form.triggerOtherPopupChange("currency", value);
                                }
                            },
                            {
                                title: "汇率(币种->人民币)",
                                name: "exchange_rate",
                                type: "input",
                                placeholder: "汇率",
                                tips: "请指出1元人民币对应的外币金额。例如，若1美元等于7.23元人民币，则此处填写7.23。同理，可按此方式计算其他币种的兑换比例。"
                            }
                        ]
                    },
                    {
                        name: util.icon('icon-shouyintai-copy') + ' 收银台',
                        form: [
                            {
                                title: false,
                                name: "checkout_counter_tips",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`
<div class="block-tips">
<p>收银台功能是指在支付过程中，避免跳转至外部支付平台网站的情况下，系统通过本地化处理直接提供支付二维码或相关支付场景的渲染。启用此功能后，用户能够在本地完成支付操作，无需跳转至第三方网站，提升支付体验的流畅度与安全性。</p>
</div>
                                    `);

                                }
                            },
                            {
                                title: "启用收银台",
                                name: "checkout_counter",
                                type: "switch",
                                default: 0
                            }
                        ]
                    }
                ],
                autoPosition: true,
                assign: config
            });
        })
    });


    const displayForm = {
        formKeys: {
            ali: ['ali_access_key_id', 'ali_access_key_secret', 'ali_sign_name', 'ali_template_code'],
            tencent: ['tencent_secret_id', 'tencent_secret_key', 'tencent_sdk_app_id', 'tencent_sign_name', 'tencent_template_id', 'tencent_region'],
            dxb: ['dxb_username', 'dxb_password', 'dxb_template']
        },
        displayAli(form) {
            this.formKeys.ali.forEach(key => {
                form.show(key);
            });

            this.formKeys.tencent.concat(this.formKeys.dxb).forEach(key => {
                form.hide(key);
            });
        },
        displayTencent(form) {
            this.formKeys.tencent.forEach(key => {
                form.show(key);
            });

            this.formKeys.ali.concat(this.formKeys.dxb).forEach(key => {
                form.hide(key);
            });
        },
        displayDxb(form) {
            this.formKeys.dxb.forEach(key => {
                form.show(key);
            });

            this.formKeys.ali.concat(this.formKeys.tencent).forEach(key => {
                form.hide(key);
            });
        },
        done(form, value) {
            value = parseInt(value);
            switch (value) {
                case 0:
                    this.displayAli(form);
                    break;
                case 1:
                    this.displayTencent(form);
                    break;
                case 2:
                    this.displayDxb(form);
                    break;
            }
        }
    }


    $('.sms-setting').click(() => {
        getConfig("sms", config => {
            component.popup({
                submit: '/admin/config/save?key=sms',
                tab: [
                    {
                        name: util.icon('icon-duanxinpeizhi') + ' 短信配置',
                        form: [
                            {
                                title: "短信平台",
                                name: "platform",
                                type: "radio",
                                dict: [
                                    {id: 0, name: "阿里云"},
                                    {id: 1, name: "腾讯云"},
                                    {id: 2, name: "短信宝"}
                                ],
                                default: 0,
                                change: (form, value) => {
                                    displayForm.done(form, value);
                                },
                                complete: (form, value) => {
                                    displayForm.done(form, value);
                                }
                            },
                            {
                                title: "AccessKeyId",
                                name: "ali_access_key_id",
                                type: "input",
                                placeholder: "AccessKeyId",
                                hide: true
                            },
                            {
                                title: "AccessKeySecret",
                                name: "ali_access_key_secret",
                                type: "input",
                                placeholder: "AccessKeySecret",
                                hide: true
                            },
                            {
                                title: "签名名称",
                                name: "ali_sign_name",
                                type: "input",
                                placeholder: "签名名称，如：【阿里云】，只需要填写：阿里云",
                                hide: true
                            },
                            {
                                title: "模版CODE[验证码]",
                                name: "ali_template_code",
                                type: "input",
                                placeholder: "请填写验证码模版CODE",
                                hide: true
                            },
                            {
                                title: "API地域",
                                name: "tencent_region",
                                type: "select",
                                dict: [
                                    {id: "ap-guangzhou", name: "华南地区（广州）"},
                                    {id: "ap-beijing", name: "华北地区（北京）"},
                                    {id: "ap-nanjing", name: "华东地区（南京）"}
                                ],
                                default: "ap-guangzhou",
                                hide: true
                            },
                            {
                                title: "SecretId",
                                name: "tencent_secret_id",
                                type: "input",
                                placeholder: "SecretId",
                                hide: true
                            },
                            {
                                title: "SecretKey",
                                name: "tencent_secret_key",
                                type: "input",
                                placeholder: "SecretKey",
                                hide: true
                            },
                            {
                                title: "SDK-AppId",
                                name: "tencent_sdk_app_id",
                                type: "input",
                                placeholder: "SDK-AppId",
                                hide: true
                            },
                            {
                                title: "签名名称",
                                name: "tencent_sign_name",
                                type: "input",
                                placeholder: "签名名称，如：【腾讯云】，只需要填写：腾讯云",
                                hide: true
                            },
                            {
                                title: "模版ID[验证码]",
                                name: "tencent_template_id",
                                type: "input",
                                placeholder: "请填写验证码模版ID",
                                hide: true
                            },
                            {
                                title: "账号",
                                name: "dxb_username",
                                type: "input",
                                placeholder: "请填写短信宝账号",
                                hide: true
                            },
                            {
                                title: "密码",
                                name: "dxb_password",
                                type: "input",
                                placeholder: "请填写短信宝的密码",
                                hide: true
                            },
                            {
                                title: "短信模版",
                                name: "dxb_template",
                                type: "textarea",
                                placeholder: "请填写模版",
                                tips: "例子：【XXX】您的验证码为{code}，在5分钟内有效。<br><br>【XXX】需要改成你的模版签名<br>{code} 代表了验证码，这个不能更改哦",
                                hide: true
                            },
                            {
                                title: false,
                                name: "custom",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`<button class="sms-test-btn" type="button">${i18n('短信发送测试')}</button>`);
                                    $('.sms-test-btn').click(() => {
                                        let data = form.getData();
                                        message.prompt({
                                            title: util.icon('icon-duanxinpeizhi') + '<space></space><space></space>' + i18n("短信发送测试"),
                                            html: "<span style='color: #b98f13;font-size: 16px;'>" + i18n('友情提醒：非大陆需加国家代码，如香港：+85244667788') + "</span>",
                                            inputPlaceholder: i18n("请输入手机号"),
                                            confirmButtonText: i18n("立即发送"),
                                            inputValidator: function (value) {
                                                if (!/^1[3-9]\d{9}$/.test(value) && !/^\+\d{1,3}\d{6,10}$/.test(value)) {
                                                    return i18n("手机号不正确");
                                                }
                                                data.phone = value;
                                                util.post({
                                                    url: "/admin/config/sms/test",
                                                    data: data,
                                                    done: () => {
                                                        message.alert("短信发送成功，注意查看手机短信", "success");
                                                    }
                                                });
                                            }
                                        });
                                    });
                                }
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true
            });
        });
    });


    $('.subdomain-setting').click(() => {
        getConfig("subdomain", config => {
            component.popup({
                submit: '/admin/config/save?key=subdomain',
                tab: [
                    {
                        name: util.icon('icon-DNS') + ' 子域名配置',
                        form: [
                            {
                                title: false,
                                name: "subdomain_tips",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`
<div class="block-tips">
<p>${i18n('子域名的说明：当您向系统提供一个或多个主域名（例如：“abc.com”）后，您便可以授权分站商家使用该主域名来创建属于他们自己的子域名。例如，如果您提供了“abc.com”，分站商家就可以使用如“king.abc.com”这样的子域名来作为他们分站的前缀。')}</p>
<p>${i18n('泛解析配置要求： 为了让这一功能顺利运作，您需要为您的主域名添加一条泛解析记录，具体来说，就是将“*.abc.com”指向您的服务器。这样做可以确保所有以“abc.com”为后缀的子域名都能正确地解析到您的服务器上。')}</p>
<p>${i18n('服务器配置说明： 在您的服务器上，您需要进行域名绑定的设置，确保可以正确处理这些子域名的请求。如果您使用的是宝塔等管理面板，通过其提供的界面进行设置应该是一个简单而直接的过程。')}</p>
</div>
                                    `);

                                }
                            },
                            {
                                title: false,
                                name: "subdomain",
                                type: "html",
                                language: "ini",
                                tips: "请填写主域名，一行一个",
                                height: 120
                            }
                        ]
                    },
                    {
                        name: util.icon('icon-duliyumingdz') + ' 独立域名配置',
                        form: [
                            {
                                title: false,
                                name: "domain_tips",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`
<div class="block-tips">
<p>${i18n('独立域名说明：独立域名指的是分站商家自行注册并使用的域名。为了能够通过该独立域名访问到商家的子站，商家需要将其域名的DNS解析指向我们提供的服务IP地址或CNAME地址。')}</p>
<p>${i18n('服务器配置要求：当一个独立域名被解析至我们的服务器之后，由于服务器默认配置中并不包含该新域名的信息，因此必须通过Nginx进行相应的域名配置，所以你还需要进行Nginx配置（功能就在右侧）才可以正常使用。')}</p>
</div>
                                    `);

                                }
                            },
                            {
                                title: "商家解析方式",
                                name: "dns_type",
                                type: "radio",
                                dict: [
                                    {id: 0, name: "服务器IP"},
                                    {id: 1, name: "CNAME"}
                                ]
                            },
                            {
                                title: "解析值",
                                name: "dns_value",
                                type: "input",
                                placeholder: "填写服务器IP 或 域名CNAME"
                            },
                            {
                                title: "功能开关",
                                name: "dns_status",
                                type: "switch",
                                tips: "必须启用此功能，商家才可以使用独立域名"
                            }
                        ]
                    },
                    {
                        name: util.icon('icon-Nginx') + ' NGINX配置',
                        form: [
                            {
                                title: false,
                                name: "nginx_tips",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`
<div class="block-tips">
${i18n(`<p>让Nginx服务器加载我们的配置文件，请将该参数：【<b style="color:#8c2ae9;">include ${getVar("nginxConf")}</b>】复制到【<b style="color: red;">nginx.conf</b>】的底部（花括号结束之前）</p>`)}
${i18n(`<p>1.apt-get或yum安装的nginx.conf位置：<b style="color: green;">/etc/nginx/nginx.conf</b></p>`)}
${i18n(`<p>2.源代码编译安装nginx.conf位置：<b style="color: green;">/usr/local/nginx/conf/nginx.conf</b></p>`)}
${i18n(`<p>3.FreeBSD或Unix系统的nginx.conf位置：<b style="color: green;">/usr/local/etc/nginx/nginx.conf</b></p>`)}
${i18n(`<p>4.宝塔的nginx.conf：<b style="color: green;">/www/server/nginx/conf/nginx.conf</b></p>`)}
</div>
                                    `);
                                }
                            },
                            /*                            {
                                                            title: "重启命令",
                                                            name: "nginx_reload_command",
                                                            type: "input",
                                                            placeholder: "请填写重启您服务器nginx配置的命令",
                                                            default: "sudo nginx -s reload",
                                                            required: true
                                                        },*/
                            {
                                hide: getVar("cli"),
                                title: "FPM地址",
                                name: "nginx_fpm_url",
                                type: "input",
                                required: getVar("cli") == false,
                                placeholder: "请填写FPM网站地址",
                                tips: `
                                FPM是什么？FPM是一种帮助你运行PHP程序的技术。如果你在设置时看到这个选项，那就意味着你正在使用这种方式来运行本程序。
那么，你怎么设置这个FPM的地址呢？其实，这个地址是你自己决定的。例如，你可以设定它为http://127.0.0.1:911。设置之后，只需在你的网站配置中添加这个地址就行了。如果你用的是像宝塔这样的服务器管理工具，那么添加这个地址的过程会非常简单，直接在网站的设置界面绑定域名就可以了。
使用127.0.0.1这个地址是因为，我们通常会通过nginx这样的服务来内部转发请求，这样做不需要让外界直接访问，所以使用[127.0.0.1:端口号]来绑定就足够了。这个地址指的是本机，意味着它不对外部网络开放，而是在内部转发处理请求，增加了安全性。
                                `.trim().replaceAll("\n", "<br><br>")
                            },
                            {
                                title: "NGINX配置文件",
                                name: "nginx_conf",
                                type: "html",
                                required: true,
                                language: "ini",
                                tips: "非专业人士，请勿随意改动配置文件，默认配置即可!",
                                default: 'server {\n' +
                                    '    listen 80;\n' +
                                    '    server_name ${server_name};\n' +
                                    '    return 301 https://$server_name$request_uri;\n' +
                                    '}\n' +
                                    '\n' +
                                    'server {\n' +
                                    '    listen 443 ssl;\n' +
                                    '    server_name ${server_name};\n' +
                                    '\n' +
                                    '    ssl_certificate ${ssl_certificate};\n' +
                                    '    ssl_certificate_key ${ssl_certificate_key};\n' +
                                    '    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;\n' +
                                    '    ssl_prefer_server_ciphers on;\n' +
                                    '\n' +
                                    '    location / {\n' +
                                    '        proxy_pass ${proxy_pass};\n' +
                                    '        proxy_set_header Host $host;\n' +
                                    '        proxy_set_header X-Real-IP $remote_addr;\n' +
                                    '        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\n' +
                                    '        proxy_set_header REMOTE-HOST $remote_addr;\n' +
                                    '        proxy_set_header Upgrade $http_upgrade;\n' +
                                    '        proxy_set_header Connection $connection_upgrade;\n' +
                                    '        proxy_set_header Scheme $scheme;\n' +
                                    '        proxy_http_version 1.1;\n' +
                                    '    }\n' +
                                    '\n' +
                                    '    # 禁止访问目录或者文件\n' +
                                    '    location ~ ^/(\\.htaccess|\\.git|LICENSE|README.md|config|kernel|runtime|vendor)\n' +
                                    '    {\n' +
                                    '        return 404;\n' +
                                    '    }\n' +
                                    '}'
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true,
                height: "auto"
            });
        });
    });


    $('.waf-setting').click(() => {
        getConfig("waf", config => {
            component.popup({
                submit: '/admin/config/save?key=waf',
                tab: [
                    {
                        name: util.icon('icon-icon-shurukuang') + ' 内容过滤',
                        form: [
                            {
                                title: "外链白名单",
                                name: "uri_scheme_filter_whitelist",
                                type: "html",
                                language: "ini",
                                placeholder: "白名单域名，一行一个",
                                tips: "只有在白名单中的域名，才能够使用视频或图片外链功能，否则都会被底层安全过滤拦截",
                                height: 280
                            },
                            {
                                title: "不过滤外链",
                                name: "uri_scheme_filter_open",
                                type: "switch",
                                tips: "不过滤外链开启后，无需外链白名单，任何URL都可以添加至你的网站"
                            },
                        ]
                    }
                ],
                assign: config,
                autoPosition: true,
                height: "auto"
            });
        });
    });

    $('.composer-setting').click(() => {
        getConfig("composer", config => {
            component.popup({
                submit: '/admin/config/save?key=composer',
                tab: [
                    {
                        name: util.icon('icon-composerluojibianpai') + ' Composer',
                        form: [
                            {
                                title: "服务器",
                                name: "server",
                                type: "radio",
                                dict: [
                                    {id: "official", name: "官方(境外)"},
                                    {id: "aliyun", name: "阿里云"},
                                    {id: "tencent", name: "腾讯云"},
                                    {id: "huaweicloud", name: "华为云"},
                                    {id: "custom", name: "自定义"},
                                ],
                                change: (form, val) => {
                                    if (val == "custom") {
                                        form.show("custom_url");
                                    } else {
                                        form.hide("custom_url");
                                    }
                                }
                            },
                            {
                                title: "镜像地址",
                                name: "custom_url",
                                type: "input",
                                placeholder: "自定义镜像地址",
                                tips: "自定义镜像服务器地址，如：https://mirrors.aliyun.com/composer/",
                                default: "https://mirrors.aliyun.com/composer/",
                                hide: config?.server != "custom"
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true,
                width: "600px"
            });
        });
    });


    $('.withdraw-setting').click(() => {
        getConfig("withdraw", config => {
            component.popup({
                submit: '/admin/config/save?key=withdraw',
                tab: [
                    {
                        name: util.icon('icon-tixian') + ' 兑现设置',
                        form: [
                            {
                                title: "单次最低兑现金额",
                                name: "min_withdraw_amount",
                                type: "number",
                                placeholder: "单次最低兑现金额",
                                tips: "单次最低兑现金额，0 为不限制",
                                required: true,
                                default: 0
                            },
                            {
                                title: "单次最大兑现金额",
                                name: "max_withdraw_amount",
                                type: "number",
                                placeholder: "单次最大兑现金额",
                                tips: "单次最大兑现金额，0 为不限制",
                                required: true,
                                default: 0
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true
            });
        });
    });

    $('.repertory-setting').click(() => {
        getConfig("repertory", config => {
            component.popup({
                submit: '/admin/config/save?key=repertory',
                tab: [
                    {
                        name: util.icon('icon-cangkuguanli') + ' 仓库设置',
                        form: [
                            {
                                title: "货源变更审核",
                                name: "is_modify_review",
                                type: "switch",
                                placeholder: "是|否",
                                tips: "当供货商的货源的介绍信息或名称变更时，商品会进入审核"
                            }
                        ]
                    }
                ],
                assign: config,
                autoPosition: true
            });
        });
    });
}();