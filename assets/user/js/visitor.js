const visitor = new class Visitor {
    constructor() {
        this.init();
    }

    init() {
        const cookie = util.getCookie("client_id");
        const localClientId = this.getId();
        if (cookie && localClientId && cookie !== localClientId) {
            util.setCookie("client_id", cookie);
        } else if (!cookie && !localClientId) {
            this.create();
        } else if (!cookie && localClientId && cookie !== localClientId) {
            util.setCookie("client_id", localClientId);
        } else if (cookie && !localClientId && cookie !== localClientId) {
            localStorage.setItem("_visitor_client_id", cookie);
        }

        //创建邀请人id
        const inviteId = util.getParam("invite");
        if (typeof inviteId == "string" && inviteId > 0) {
            util.setCookie("invite_id", inviteId);
        }
    }


    create() {
        const id = util.generateRandStr(32);
        localStorage.setItem("_visitor_client_id", id);
        util.setCookie("client_id", id);
        return id;
    }


    getId() {
        return localStorage.getItem("_visitor_client_id");
    }
}