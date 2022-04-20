const versionFileUpdater = {
    VERSION_REGEX: /VERSION = '(.+)'/,

    readVersion: function (contents) {
        const version = this.VERSION_REGEX.exec(contents)[1];
        return version;
    },

    writeVersion: function (contents, version) {
        return contents.replace(this.VERSION_REGEX.exec(contents)[0], `VERSION = '${version}'`);
    }
}

module.exports = {
    bumpFiles: [{ filename: './lib/GetStream/Stream/Constant.php', updater: versionFileUpdater }],
}
