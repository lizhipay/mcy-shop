class Broadcast {

    /**
     * 初始化语音包
     * @param selector
     * @param path
     */
    constructor(selector, path) {
        this.path = path;
        this.$handle = $(selector);

        const a = this.getPackage();
        a && this.$handle.val(a);
        this.$handle.change(function () {
            localStorage.setItem(path, this.value);
        });
    }


    /**
     * 获取当前语音包
     * @returns {boolean|string}
     */
    getPackage() {
        const c = localStorage.getItem(this.path);
        if (c != "" && typeof c == "string") {
            return c;
        }
        return false;
    }


    /**
     * 播放语音包
     * @param name
     */
    play(name) {
        const pack = this.getPackage();
        if (pack) {
            util.loadSound(`${this.path}/${pack}/${name}.mp3`);
        }
    }
}