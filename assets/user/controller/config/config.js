!function () {
    function getConfig(key, done = null) {
        util.post({
            url: "/user/config/get?key=" + key,
            done: res => {
                typeof done === "function" && done(res.data);
            }
        });
    }

    $('.site-setting').click(() => {
        getConfig("site", config => {
            component.popup({
                submit: '/user/config/save?key=site',
                tab: [
                    {
                        name: util.icon('icon-wangzhanshezhi') + " 网站设置",
                        form: [
                            {
                                title: "LOGO",
                                name: "logo",
                                type: "image",
                                placeholder: "请选择LOGO(.ico)",
                                uploadUrl: "/user/upload",
                                photoAlbumUrl: '/user/upload/get',
                                height: 64,
                                required: true
                            },
                            {
                                title: "网站背景图",
                                name: "bg_image",
                                type: "image",
                                placeholder: "请选择背景图片",
                                uploadUrl: "/user/upload",
                                photoAlbumUrl: '/user/upload/get',
                                height: 200,
                                tips: "背景图建议使用 1920*1080 分辨率"
                            },
                            {
                                title: "PC模板",
                                name: "pc_theme",
                                type: "select",
                                placeholder: "请选择",
                                dict: "theme",
                                default: "default",
                                required: true
                            },
                            {
                                title: "手机模板",
                                name: "mobile_theme",
                                type: "select",
                                placeholder: "请选择",
                                dict: "theme",
                                default: "default",
                                required: true
                            },
                            {
                                title: "网站标题",
                                name: "title",
                                type: "input",
                                placeholder: "请输入网站标题",
                                required: true
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
                                title: "备案号",
                                name: "icp",
                                type: "input",
                                placeholder: "ICP备案号"
                            }
                        ]
                    },
                    {
                        name: "<i class='fa fa-bullhorn'></i> 公告",
                        form: [
                            {
                                title: "公告背景",
                                name: "notice_banner",
                                type: "image",
                                placeholder: "请选择图片",
                                uploadUrl: "/user/upload",
                                photoAlbumUrl: '/user/upload/get',
                                height: 128
                            },
                            {
                                title: "网站公告",
                                titleHide: true,
                                name: "notice",
                                type: "editor",
                                placeholder: "网站公告",
                                height: 480,
                                uploadUrl: "/user/upload",
                                photoAlbumUrl: '/user/upload/get',
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
                width: "680px",
                assign: config
            });
        })
    });


    $('.email-setting').click(() => {
        getConfig("email", config => {
            component.popup({
                submit: '/user/config/save?key=email',
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
                                            inputPlaceholder: i18n("请输入邮箱地址"),
                                            confirmButtonText: i18n("立即发送"),
                                            inputValidator: function (value) {
                                                if (!/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/.test(value)) {
                                                    return i18n("邮箱地址不正确");
                                                }
                                                data.email = value;
                                                util.post({
                                                    url: "/user/config/smtp/test",
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
                assign: config
            });
        });
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
                submit: '/user/config/save?key=sms',
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
                                                    url: "/user/config/sms/test",
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


    $('.domain-setting').click(() => {
        component.popup({
            submit: false,
            tab: [
                {
                    name: util.icon('icon-DNS') + ' 域名配置',
                    form: [
                        {
                            name: "sku",
                            type: "custom",
                            complete: (popup, dom) => {

                                dom.html(`<div class="block block-rounded">
        <div class="block-header block-header-default">
            <button type="button" class="btn btn-outline-success btn-sm add-domain">` + util.icon("icon-tianjia") + `<space></space> ` + i18n("添加域名") + `
            </button>
        </div>
        <div class="block-content">
            <table id="subdomain-table"></table>
        </div>
    </div>`);
                                const subdomainTable = new Table("/user/config/site/get", dom.find('#subdomain-table'));
                                subdomainTable.disablePagination();
                                subdomainTable.setColumns([
                                    {checkbox: true},
                                    {field: 'host', title: '域名'},
                                    {field: 'type', title: '类型', dict: 'site_domain_type'},
                                    {
                                        field: 'ssl_domain', title: 'SSL证书信息', formatter: (value, item) => {
                                            if (item.type == 0) {
                                                return "-";
                                            }
                                            return `
                                                认证域名：${value}
                                                证书品牌：${item.ssl_issuer}
                                                到期时间：${item.ssl_expire_time}
                                                `.trim().replaceAll("\n", "<br>");
                                        }
                                    },
                                    {
                                        field: 'dns_status', title: 'DNS状态', formatter: (val, item) => {
                                            if (item.type == 0) {
                                                return format.success(`${util.icon("icon-chenggong")} 解析正常`);
                                            }
                                            return util.icon("icon-loading", `icon-spin status-${util.md5(item.host)}`);
                                        }
                                    },
                                    {
                                        field: 'operation', title: '操作', type: 'button', width: 220, buttons: [
                                            {
                                                icon: 'icon-biaoge-xiugai',
                                                title: '修改证书',
                                                class: 'acg-badge-h-dodgerblue certificate-modify',
                                                click: (event, value, row, index) => {
                                                    util.post("/user/config/site/certificate/get", {domain: row.host}, res => {
                                                        component.popup({
                                                            submit: '/user/config/site/certificate/modify',
                                                            tab: [
                                                                {
                                                                    name: util.icon('icon-DNS') + ' 修改SSL域名证书',
                                                                    form: [
                                                                        {
                                                                            name: "domain",
                                                                            type: "input",
                                                                            hide: true,
                                                                            default: row.host
                                                                        },
                                                                        {
                                                                            title: "SSL域名证书(PEM)",
                                                                            name: "pem",
                                                                            type: "textarea",
                                                                            height: 200,
                                                                            placeholder: "请填写SSL证书，PEM格式",
                                                                            default: res.data.pem
                                                                        },
                                                                        {
                                                                            title: "SSL证书私钥",
                                                                            name: "key",
                                                                            type: "textarea",
                                                                            height: 200,
                                                                            placeholder: "请填写SSL证书的私钥",
                                                                            default: res.data.key
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
                                                            width: "580px",
                                                            done: () => {
                                                                subdomainTable.refresh();
                                                            }
                                                        });
                                                    });
                                                },
                                                show: (row) => {
                                                    return row.type != 0;
                                                }
                                            },
                                            {
                                                icon: 'icon-shanchu1',
                                                class: 'acg-badge-h-red',
                                                title: "刪除",
                                                click: (event, value, row, index) => {
                                                    message.ask("一旦删除此域名，用户将无法再通过此域名访问您的店铺！", () => {
                                                        util.post("/user/config/site/del", {domain: row.host}, res => {
                                                            layer.msg("删除成功");
                                                            subdomainTable.refresh();
                                                        });
                                                    });
                                                }
                                            }
                                        ]
                                    },
                                ]);
                                subdomainTable.onComplete((res, unique, response) => {
                                    if (response?.data?.list) {
                                        let domains = [];
                                        response?.data?.list.forEach(item => {
                                            if (item.type == 1) {
                                                domains.push(item.host);
                                            }
                                        });

                                        util.post({
                                            url: "/user/config/site/getDnsRecord",
                                            data: {domains: domains},
                                            loader: false,
                                            done: res => {
                                                for (const host in res.data) {
                                                    const dns = res.data[host];
                                                    const md5 = util.md5(host);
                                                    const records = dns.records.join(",");
                                                    $(`.dns-${md5}`).parent().html(records ? records : "-");
                                                    $(`.status-${md5}`).parent().html(dns.status == 1 ? format.success(`${util.icon("icon-chenggong")} 解析正常`) : format.danger(`${util.icon("icon-yijujue")} 未生效`));
                                                }
                                            }
                                        });
                                    }
                                });
                                subdomainTable.render();

                                //-------------
                                $('.add-domain').click(() => {
                                    let config = getVar("subdomain");
                                    const displayDomainForm = {
                                        formKeys: {
                                            subdomain: ['domain', 'subdomain'],
                                            domain: ['private_domain', 'pem', 'key', 'private_domain_tips'],
                                        },
                                        displaySubdomain(form) {
                                            this.formKeys.subdomain.forEach(key => {
                                                form.show(key);
                                            });
                                            this.formKeys.domain.forEach(key => {
                                                form.hide(key);
                                            });
                                        },
                                        displayDomain(form) {
                                            this.formKeys.domain.forEach(key => {
                                                form.show(key);
                                            });
                                            this.formKeys.subdomain.forEach(key => {
                                                form.hide(key);
                                            });
                                        },
                                        done(form, value) {
                                            value = parseInt(value);
                                            switch (value) {
                                                case 0:
                                                    this.displaySubdomain(form);
                                                    break;
                                                case 1:
                                                    this.displayDomain(form);
                                                    break;
                                            }
                                        }
                                    }

                                    if (!config.dns_status && config.dict.length == 0) {
                                        layer.msg("系统未配置添加域名功能，请联系技术进行配置");
                                        return;
                                    }

                                    component.popup({
                                        submit: '/user/config/site/save',
                                        tab: [
                                            {
                                                name: util.icon('icon-tianjia') + ' 添加域名',
                                                form: [
                                                    {
                                                        title: "域名类型",
                                                        hide: !config.dns_status,
                                                        name: "type",
                                                        type: "radio",
                                                        dict: "site_domain_type",
                                                        tips: "【子域名】是使用主站提供的域名来作为后缀，新手推荐<br><br>【私有域名】是您自行提供域名解析到我们服务器，并且上传您的SSL域名证书",
                                                        change: (form, value) => {
                                                            displayDomainForm.done(form, value);
                                                        },
                                                        complete: (form, value) => {
                                                            displayDomainForm.done(form, config.dict.length > 0 ? 0 : 1);
                                                            if (config.dict.length == 0) {
                                                                form.hide('type');
                                                                form.setRadio("type", 1, true);
                                                            }
                                                        }
                                                    },
                                                    {
                                                        title: "域名后缀",
                                                        name: "domain",
                                                        type: "select",
                                                        dict: config.dict,
                                                        hide: true
                                                    },
                                                    {
                                                        title: "域名前缀",
                                                        name: "subdomain",
                                                        type: "input",
                                                        placeholder: "请输入域名前缀",
                                                        tips: "【域名】是访问您店铺的唯一途径<br><br>域名由【域名前缀】+【域名后缀】组成，如：abc.ok.com，ok.com 代表了后缀，abc 代表了前缀<br><br>此处你只需要填写前缀即可。",
                                                        hide: true
                                                    },
                                                    {
                                                        title: "私有域名",
                                                        name: "private_domain",
                                                        type: "input",
                                                        placeholder: "请输入完整的域名",
                                                        tips: "【私有域名】属于您自己注册购买的域名<br><br><b style='color:#b3f592;'>在添加私有域名之前，请先将您的私有域名解析至下方提供的服务器信息</b>",
                                                        hide: true
                                                    },
                                                    {
                                                        hide: true,
                                                        title: " ",
                                                        name: "private_domain_tips",
                                                        type: "custom",
                                                        complete: (form, dom) => {
                                                            dom.html(`
<div>
<table class="table table-vcenter">
                    <thead>
                      <tr>
                        <th class="">${i18n('解析方式')}</th>
                        <th class="">${i18n('记录值')}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                         <td class="">
                          ${config.dns_type == 0 ? '<span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success">A记录</span>' : '<span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-warning-light text-warning">CNAME</span>'}
                        </td>
                        <td class="fw-semibold fs-sm text-success">${config.dns_value}</td>
                      </tr>
                    </tbody>
                  </table>
</div>
                                    `);
                                                        }
                                                    },
                                                    {
                                                        title: "SSL域名证书(PEM)",
                                                        name: "pem",
                                                        type: "textarea",
                                                        height: 200,
                                                        placeholder: "请填写SSL证书，PEM格式",
                                                        tips: "【SSL域名证书】是一种用于在互联网上安全传输数据的数字证书，这里需要填写您私有域名的证书。",
                                                        hide: true
                                                    },
                                                    {
                                                        title: "SSL证书私钥",
                                                        name: "key",
                                                        type: "textarea",
                                                        height: 200,
                                                        placeholder: "请填写SSL证书的私钥",
                                                        hide: true
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
                                        width: "580px",
                                        done: () => {
                                            subdomainTable.refresh();
                                        }
                                    });
                                });
                            }
                        }
                    ]
                }
            ],
            autoPosition: true,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            height: "auto",
            width: "1080px",
        });
    });
}();