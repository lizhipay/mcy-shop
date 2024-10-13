!function () {
    const table = new Table("/admin/site/get", "#site-table");
    table.setUpdate("/admin/site/save");
    table.setColumns([
        {field: 'user', title: '商家', formatter: format.user},
        {field: 'site.title', title: '网站名称'},
        {field: 'host', title: '域名', formatter: host => `<a href="http://${host}" target="_blank" class="text-primary">${host}</a>`},
        {field: 'type', title: '域名类型', dict: "site_domain_type"},

        {
            field: 'dns_record', title: 'DNS记录', formatter: (val, item) => {
                if (item.type == 0) {
                    return "-";
                }
                return util.icon("icon-loading", `icon-spin dns-${util.md5(item.host)}`);
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
        {field: 'status', title: '状态', type: "switch", text: "ACTIVE|BAN"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    tips: '修改证书',
                    click: (event, value, row, index) => {
                        util.post("/admin/site/certificate/get", {domain: row.host}, res => {
                            component.popup({
                                submit: '/admin/site/certificate/modify',
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
                                    table.refresh();
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
                    tips: '移除该域名',
                    click: (event, value, row, index) => {
                        message.dangerPrompt("您即将进行高风险的站点删除操作。删除完成后，该域名将永久无法访问。如需重新访问，您需重新绑定该域名", "我确认删除", () => {
                            util.post("/admin/site/del", {domain: row.host}, res => {
                                layer.msg("删除成功");
                                table.refresh();
                            });
                        });
                    }
                }
            ]
        },
    ]);
    table.setFloatMessage([
        {field: 'ssl_domain', title: '证书域名'},
        {field: 'ssl_issuer', title: '证书颁发者'},
        {field: 'ssl_expire_time', title: '证书到期时间'},
        {field: 'create_time', title: '生效时间'},
    ]);
    table.setSearch([
        {
            title: "搜索商家",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2"
        },
        {
            title: "域名(模糊搜索)",
            name: "search-host",
            type: "input"
        }
    ]);
    table.setState("type", "site_domain_type");
    table.onResponse(data => {
        $('.data-count .site-count').html(data.site_count);
    });
    table.onComplete((res, unique, response) => {
        if (response?.data?.list) {
            let domains = [];
            response?.data?.list.forEach(item => {
                if (item.type == 1) {
                    domains.push(item.host);
                }
            });

            util.post({
                url: "/admin/site/dnsRecord",
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
    table.render();
}();