var express = require('express')
var app = express()
var php = require('phplike/module');
var xml = require('libxmljs');
var root = __dirname + "/../../";
var i18n = require('i18n');
var language = "en";

i18n.configure({
    locales:['en', 'zh_TW'],
    defaultLocale: language,
    directory: __dirname + '/../../locales/system'
});

var layoutParser = new (require('./layoutParser.js'))(i18n);


function server() {

}

var o = server.prototype;

o.start = function (host, port) {
    var loadConfigPages;
    loadConfigPages = this.loadConfigPages.bind(this);
    if (!port) port = 80;
    if (!host) host = "localhost";


    app.use('/static', express.static(root + '/static'));

    app.get('/*', loadConfigPages);

    var server = app.listen(port, function () {


      console.log('Example app listening at http://%s:%s', host, port)

    });


};

/**
 * To validate the config path(.html), if the path is not illegage then the method will reture false.
 */
o.isAllowedPageConfigPath = function (path) {//{{{

    if (path.match(/\.{2,}/)) {
        return false;
    }

    if (!path.match(/\.html$/)) {
        console.log(i18n.__('ERROR_IS_NOT_ALLOWED_FORMAT'));
        return false;
    }

    return true;
};//}}}

/**
 * loadConfigPages
 * To render HTML after parse the page config. User should create the page config first. This method will parse your page config file and output the html tags.
 *
 * @param req The request object of express module.
 * @param res The response object of Node.js
 */
o.loadConfigPages = function (req, res) {//{{{

    var content, path, html = "", pageConfig, siteConfigFilePath, siteConfig, customizedSiteConfig;
    path = root + "pageConfig/" + req.path;
    siteConfigFilePath = root + "pageConfig/base/site.html";
    if (php.is_file(path) 
        && this.isAllowedPageConfigPath(req.path)) {
        pageConfig = php.file_get_contents(path);
        pageConfig = new xml.parseXml(pageConfig);
    }

    if (!php.empty(pageConfig)) {
        customizedSiteConfig = pageConfig.get('/page').attr('siteConfig');
        if (customizedSiteConfig) {
            siteConfigFilePath = root + "pageConfig/base/" + customizedSiteConfig;
        }
        if (php.is_file(siteConfigFilePath)) {
            content = php.file_get_contents(siteConfigFilePath);
            siteConfig = new xml.parseXml(content);
        }

        html = layoutParser.render(pageConfig, siteConfig);
    }

    console.log('render html');
    res.write(html);
    res.end();

};//}}}


module.exports = server;
