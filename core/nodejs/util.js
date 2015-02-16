var i18n = require('i18n');
var language = "en";

i18n.configure({
    locales:['en', 'zh_TW'],
    defaultLocale: language,
    directory: __dirname + '/../../locales/system'
});


function util() {

}

var o = util.prototype;

/**
 *
 * @param type error,info,debug
 */
o.log = function (key, type) {
    console.log(i18n.__(key));
};

module.exports = util;
