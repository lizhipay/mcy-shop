const common = new class Common {

    renderTableUser(user) {
        if (user) {
            return format.avatar(user.avatar) + " " + user.username + ' (ID: <b>' + user.id + '</b>)';
        }
        return util.icon('icon-guanfang');
    }


    renderTableBr() {
        return "<div style='margin-top: 6px;'></div>";
    }

    renderTableList(data) {
        let list = [];
        data.forEach(item => {
            list.push(item.title + item.value);
        })
        return list.join(this.renderTableBr());
    }
}