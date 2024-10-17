!function () {
    const group = getVar("group");

    function handleAutoReceipt() {
        const token = util.generateRandStr(16), tokenKey = "_user_task_token";
        localStorage.setItem(tokenKey, token);
        util.debug("自动收货TASK->启动..", "#98FB98");
        util.timer(() => {
            return new Promise(resolve => {
                if (token != localStorage.getItem(tokenKey)) {
                    util.debug("自动收货TASK->检测到其他窗口任务启动，已自动关闭当前任务进程..", "#00BFFF");
                    resolve(false);
                    return;
                }
                util.debug("自动收货TASK->开始更新..", "#FF8C00");
                util.post({
                    url: "/user/task/autoReceipt",
                    loader: false,
                    done: () => {
                        util.debug("自动收货TASK->更新完成!", "#00FA9A");
                        resolve(true);
                    },
                    error: () => {
                        util.debug("自动收货TASK->更新失败!", "#FF5733");
                        resolve(false);
                    }
                });
            });
        }, 60000, true);
    }


    function handLoadStoreUser() {
        const $storeUser = $(".store-user");
        util.post({
            url: "/user/store/personal/info",
            loader: false,
            done: res => {
                $storeUser.append(`<a href="/user/store" class="btn btn-sm btn-outline-dark me-1 d-none d-sm-inline-block"><div class="d-flex align-items-center">
                        <span class="fw-semibold">${res.data.username}(<span class="text-warning store-user-balance">￥${res.data.balance}</span>)</span>
                    </div></a>`);

                if (res?.data?.expire_product > 0) {
                    layer.tips(`<span style="color: #e6be2f;">您有${res?.data?.expire_product}个产品将在三天内过期，请尽快进行续费，以确保业务的持续正常运作。</span>`, $(`.store-button`), {
                        tips: 3,
                        time: 5000,
                        tipsMore: true
                    });
                }
            },
            error: () => {
            },
            fail: () => {

            }
        });
    }

    function handleSyncRemoteItems() {
        const token = util.generateRandStr(16), tokenKey = "_user_sync_item_token";
        localStorage.setItem(tokenKey, token);
        util.debug("远程商品同步->启动..", "#98FB98");
        util.timer(() => {
            return new Promise(resolve => {
                if (token != localStorage.getItem(tokenKey)) {
                    util.debug("远程商品同步->检测到其他窗口任务启动，已自动关闭当前任务进程..", "#00BFFF");
                    resolve(false);
                    return;
                }
                util.debug("远程商品同步->开始同步..", "#FF8C00");
                util.post({
                    url: "/user/plugin/ship/remote/items",
                    loader: false,
                    done: res => {
                        if (res?.data?.length > 0) {
                            util.debug(`远程商品同步->需要同步商品数量：${res?.data?.length}`, "#2fdf38");
                            util.timer(() => {
                                return new Promise(call => {
                                    const id = res?.data?.shift();
                                    if (id) {
                                        util.debug(`远程商品同步->开始同步ID：${id}`, "#ed27e9");
                                        util.post({
                                            url: "/user/plugin/ship/remote/sync",
                                            data: {id: id},
                                            loader: false,
                                            done: res => {
                                                util.debug(`远程商品同步->[${id}]同步结束`, "#2fdf38");
                                                call(true);
                                            },
                                            error: err => {
                                                util.debug(`远程商品同步->[${id}]同步失败`, "#FF5733");
                                                call(true);
                                            },
                                            fail: () => {
                                                util.debug(`远程商品同步->[${id}]网络错误，正在重连..`, "#FF5733");
                                                call(true);
                                            }
                                        });
                                        return;
                                    }
                                    resolve(true);
                                    call(false);
                                })
                            }, 1, true);
                        } else {
                            util.debug(`远程商品同步->持续检测中..`, "#2fdf38");
                            resolve(true);
                        }
                    },
                    error: err => {
                        util.debug(`远程商品同步->出错:${err.msg}`, "#FF5733");
                        resolve(true);
                    },
                    fail: () => {
                        util.debug(`远程商品同步->网络错误，正在重连..`, "#FF5733");
                        resolve(true);
                    }
                });
            });
        }, 10000, true);
    }

    handleAutoReceipt();
    handLoadStoreUser();


    if (group?.is_supplier == 1) {
        handleSyncRemoteItems();
    }
}();