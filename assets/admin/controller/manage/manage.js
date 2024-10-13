!function () {
    let table;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/manage/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "头像",
                            name: "avatar",
                            type: "image",
                            placeholder: "请选择图片",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 100,
                            required: true
                        },
                        {
                            title: "Email",
                            name: "email",
                            type: "input",
                            placeholder: "请输入邮箱",
                            required: true
                        },
                        {title: "呢称", name: "nickname", type: "input", placeholder: "呢称", required: true},
                        {
                            title: "登录密码",
                            name: "password",
                            type: "input",
                            placeholder: "登录密码",
                            tips: "1.密码长度大于8个字符<br>2.密码必须包含字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合"
                        },
                        {
                            title: "角色",
                            name: "role",
                            type: "checkbox",
                            dict: "role",
                            required: true
                        },
                        {
                            title: "状态",
                            name: "status",
                            type: "switch"
                        },
                        {title: "备注", name: "note", type: "input", placeholder: "请输入备注"},
                    ]
                }
            ],
            autoPosition: true,
            height: "auto",
            width: "560px",
            assign: assign,
            done: () => {
                table.refresh();
            }
        });
    }


    table = new Table("/admin/manage/get", "#manage-table");
    table.setUpdate("/admin/manage/save");
    table.setColumns([
        {
            field: 'nickname', title: '呢称', formatter: (val, item) => {
                return `<span class="table-item table-item-user"><img src="${item.avatar}" class="table-item-icon"><span class="table-item-name">${item.nickname}</span></span>`;
            }
        },
        {field: 'email', title: '邮箱'},
        {field: 'login_time', title: '登录时间'},
        {field: 'login_ip', title: '登录IP'},
        {field: 'login_ua', title: '浏览器', formatter: format.browser},
        {field: 'login_ip', title: '登录IP'},
        {field: 'note', title: '备注', type: "input"},
        {field: 'status', title: '状态', type: "switch", text: "启用|关闭", width: 90},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    tips: "修改",
                    click: (event, value, row, index) => {
                        let map = Object.assign({}, row);
                        map.role = component.idObjToList(row.role);
                        delete map.password;
                        modal(util.icon("icon-biaoge-xiugai") + " 修改管理员", map);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    tips: "删除",
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/manage/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setSearch([
        {title: "邮箱", name: "equal-email", type: "input"},
        {title: "备注", name: "search-note", type: "input"},
    ]);
    table.setState("status", "manage_status");
    table.setDetail([
        {field: 'create_time', title: '创建时间'},
        {field: 'last_login_ip', title: '上次登录IP'},
        {field: 'last_login_time', title: '上次登录时间'},
        {field: 'last_login_ua', title: '上次浏览器', formatter: format.browser},
        {
            field: 'log', title: '最近操作日志', formatter: (val) => {
                let log = [];
                val.forEach(item => {
                    log.push(format.bold(item.create_time, format.property.italic) + "：" + format.primary(item.content) + " 风险评估：" + dict.result('manage_log_risk', item.risk) + " 浏览器：" + format.underline(format.browser(item.ua)));
                });
                return log.join("<br>");
            }
        },
    ]);
    table.render();

    $('.add-manage').click(() => {
        modal(`${util.icon("icon-tianjia")} 添加管理员`);
    });
}();