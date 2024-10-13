!function () {
    const table = new Table("/user/security/login/log", "#user-login-log-table");
    table.setColumns([
        {field: 'create_time', title: '登录时间'},
        {field: 'ip', title: 'IP地址'},
        {field: 'ua', title: '浏览器UA'},
        {
            field: 'is_dangerous', title: '安全评估', dict: 'user_login_log_dangerous'
        }
    ]);
    table.setPagination(15, [15, 30, 50, 100, 500]);
    table.setSearch([
        {
            title: "IP地址",
            name: "equal-ip",
            type: "input"
        },
        {
            title: "登录时间",
            name: "between-create_time",
            type: "date"
        }
    ]);
    table.setState("is_dangerous", "user_login_log_dangerous_level");
    table.render();
}();