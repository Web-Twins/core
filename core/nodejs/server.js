var express = require('express')
var app = express()
var php = require('phplike/module');
var i18n = require('i18n');
var language = "en";
var less = require('less');
var sass = require('node-sass');
//var Q = require('q');
var fs = require('fs');

global.TWINS = {};
global.TWINS.util = require('util');


i18n.configure({
    locales:['en', 'zh_TW'],
    defaultLocale: language,
    directory: __dirname + '/../../locales/system'
});

var layoutParserMod = require('./layoutParser.js');

function server(root) {//{{{
    if (root) {
        this.root = root;
    } else {
        this.root = __dirname + "/../../";
    }
}//}}}

var o = server.prototype;

o.root = "";

o.start = function (host, port) {//{{{
    var loadConfigPages, loadLess, loadSass, loadJs;
    loadConfigPages = this.loadConfigPages.bind(this);
    loadLess = this.loadLess.bind(this);
    loadSass = this.loadSass.bind(this);
    loadJs = this.loadJs.bind(this);

    if (!port) port = 80;
    if (!host) host = "localhost";

    app.use('/static/**.less', loadLess);
    app.use('/static/**.sass', loadSass);
    app.use('/static/**.scss', loadSass);



    app.use('/static', express.static(this.root + '/static'));
    app.use('/modules/**.less', loadLess);
    app.use('/modules/**.css', express.static(this.root + '/static'));
    app.use('/modules/**.js', loadJs);
    app.use('/modules/**.scss', loadSass);
    app.use('/modules/**.sass', loadSass);




    app.get('/*', loadConfigPages);

    var server = app.listen(port, function () {


      console.log('Example app listening at http://%s:%s', host, port)

    });


};//}}}

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

    var layoutParser, dom;
    var path, html = "", pageConfig, siteConfigFilePath, siteConfig, customizedSiteConfig, baseConfig, pageDom, siteDom;

    // baseConfig is a global setting of a website.
    baseConfig = this.loadBaseConfig();

    layoutParser = new layoutParserMod(i18n, this.root, baseConfig);
    path = this.root + "/pageConfig/" + req.path;

    siteConfigFilePath = this.root + "/pageConfig/base/site.html";
    if (php.is_file(path) 
        && this.isAllowedPageConfigPath(req.path)) {
        //pageConfig = php.file_get_contents(path);
        pageDom = new php.DOMDocument();
        pageConfig = pageDom.load(path);
    }

    if (!php.empty(pageConfig)) {
        if (pageConfig['attributes']['siteConfig']) {
            customizedSiteConfig = pageConfig['attributes']['siteConfig'];
        }
        if (customizedSiteConfig) {
            siteConfigFilePath = this.root + "/pageConfig/base/" + customizedSiteConfig;
        }
        if (php.is_file(siteConfigFilePath)) {
            siteDom = new php.DOMDocument();
            siteCofnig = siteDom.load(siteConfigFilePath);
        }

        html = layoutParser.render(pageDom, siteDom);
    }

    console.log('render html');
    res.write(html);
    res.end();

};//}}}

o.loadBaseConfig = function () {//{{{
    var x, baseConfig, pathNodes, pathNode,
        name, value, i, n;
    var baseFile = this.root + "/pageConfig/base/base.html";
    var dom = new php.DOMDocument();
    baseConfig = {
        "urlPaths": {} ,
        "paths": {}
    };
    if (php.is_file(baseFile)) {
        dom.load(baseFile);
    }

    var xmlToJson = function (key, refResult) {
        pathNodes = dom.getElementsByTagName(key);

        if (!pathNodes || !pathNodes[0]) {
            return "";
        }
        if (!pathNodes[0].childNodes) {
            return "";
        }

        pathNodes = pathNodes[0].childNodes;
        n = pathNodes.length;
        for (i = 0; i < n; i++) {
            pathNode = pathNodes[i];
            name = pathNode.name;
            if (name === "text") continue;
            value = pathNode.value;
            refResult[key][name] = value;
        }

    };

    xmlToJson("urlPaths", baseConfig);
    xmlToJson("paths", baseConfig);

    return baseConfig;
};//}}}

o.loadLess = function (req, res) {//{{{
    var html = "", path;
    path = this.root + "/"  + req.baseUrl;
    TWINS.util.log("Less Compiler: " + path, "debug");
    if (!php.is_file(path)) {
       res.write(".fileNotFound{}"); 
       res.end();
    } else {
        res.contentType("text/css");
        fs.readFile(path, function (err, content) {
            less.render(content.toString(), 
                {
                    paths: ['.', './static/css'],
                },
                function (e, output) {
                   if (e) console.log(e);
                   res.write(output.css);
                   res.end();
                }
            );
        }); 
    }
};//}}}

o.loadJs = function (req, res) {//{{{
    var html = "", path;
    path = this.root + "/"  + req.baseUrl;
    TWINS.util.log("Load JavaScript: " + path, 'debug');
    if (!php.is_file(path)) {
       res.write(".fileNotFound{}"); 
       res.end();
    } else {
        fs.readFile(path, function (err, content) {
            res.write(content);
            res.end();
        }); 
    }
};//}}}


o.loadSass = function (req, res) {//{{{
    var html = "", path;

    path = this.root + "/"  + req.baseUrl;
    if (!php.is_file(path)) {
       res.write(".fileNotFound{}"); 
       res.end();
    } else {
        res.contentType("text/css");
        sass.render({
           file: path
        }, function (e, output) {
           if (e) console.log(e);
           res.write(output.css);
           res.end();
        });
    }
};//}}}

module.exports = server;
