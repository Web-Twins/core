var moduleMod = require('./module.js');
var php = require('phplike/module');


function layoutParser(i18n, root, baseConfig) {//{{{
    
    if (i18n) {
        this.i18n = i18n;
    }


    this.bodyJs = {
        "top": [],
        "bottom": [],
        "after": []
    };
    this.bodyCss = {
        "top": [],
        "bottom": []
    };
    this.cssFile = {
        "moduleLevel": []
    };
    if (!php.empty(baseConfig)) {
        this.baseConfig = baseConfig;
    } else {
        baseConfig = {};
    }
    this.context.baseConfig = baseConfig;

    this.module = new moduleMod(root, this.context);
}//}}}


var o = layoutParser.prototype;
//{{{ // properties
o.module = "";
o.output = 'htmlPage';
o.i18n = ""; //internationization
o.baseConfig = {};
o.context = {}; //Server context
// output type: htmlPage, json, text
o.OUTPUT_HTML_PAGE = 1;
o.OUTPUT_JSON = 2;
o.OUTPUT_TEXT = 3;
o.enableIndent = true;
o.cssFile = {
    "moduleLevel": []
};

o.pageDom = "";
o.siteDom = "";

// The property to be rendered in body.
o.bodyJs = {
    "top": [],
    "bottom": [],
    "after": []
};

o.bodyCss = {
    "top": [],
    "bottom": []
};//}}}

o.render = function (pageDom, siteDom) {//{{{
    var i, n;
    var list = [], key, child, nodeName, output = "", 
        siteBody, self, modules, pageConfig, siteConfig;

    this.pageDom = pageDom;
    if (siteDom) this.siteDom = siteDom;
    pageConfig = pageDom.json;
    if (siteDom) siteConfig = siteDom.json;

    self = this;
    if (pageConfig.childNodes) child = pageConfig.childNodes;

    if (pageConfig.attributes 
        && pageConfig.attributes['output']
       ) {
        output = pageConfig.attributes['output'];
    } 

    this.output = this.getOutputType(output);

    switch (this.output) {
        case this.OUTPUT_HTML_PAGE:
            var siteHtml = siteDom.getElementsByTagName("html");
            if (siteHtml && siteHtml[0] && siteHtml[0].attributes) {
                var html = "<html";
                for (var attr in siteHtml[0].attributes ) {
                    if ("-" === siteHtml[0].attributes[attr]) {
                        html += " " + attr;
                    } else {
                        html+= " " + attr + "=\"" + siteHtml[0].attributes[attr] +"\"";
                    }
                }
                html +=">";
                list.push("<!DOCTYPE html>\n" + html);

            } else {
                list.push("<!DOCTYPE html>\n<html>");
            }
            break;
    }

    if (child) {
        n = child.length;
    } else {
        n = 0;
    }

    for (i = 0; i< n; i++) {
        nodeName = child[i].name;
        nodeName = nodeName.toLowerCase();
        switch (nodeName) {
            case 'head':
                if (this.output !== this.OUTPUT_HTML_PAGE) continue;
                list.push('<head>');
                if (siteConfig) {
                    var siteHead = siteDom.getElementsByTagName("head");
                    if (siteHead && siteHead[0]) {
                        list.push(this.renderHead(siteHead[0]));
                    }
                }
                list.push(this.renderHead(child[i]));

                //render Module Level Css in site.html
                this.getModuleCss(this.siteDom, siteConfig);
                // render Module Level css in page.html
                moduleCss = this.getModuleCss(this.pageDom, pageConfig);
                var cssCount = moduleCss.length;
                for (var j = 0; j < cssCount; j++) {
                    if (!moduleCss[j] || !moduleCss[j]['urlPath']) continue;

                    var isFinalPath = true;
                    list.push(this.renderCss(moduleCss[j]['urlPath'], isFinalPath));
                }
                list.push('</head>');
                break;
            case 'body':
                switch (this.output) {
                    case this.OUTPUT_HTML_PAGE:
                        list.push("<body>");
                        break;
                }

                if (siteConfig) {
                    siteBody = siteDom.getElementsByTagName("header");
                    if (siteBody && siteBody[0]) {
                        siteBody[0].value = siteBody[0].nodeValue;
                        list.push(this.renderBody(siteBody[0]));
                    }
                }

                //render css in top body
                if (this.bodyCss['top']) {
                    this.bodyCss['top'].forEach(function (c) {
                        list.push(self.renderCss(c.value));
                    });
                }

                //render js in top body
                if (this.bodyJs['top']) {
                    this.bodyJs['top'].forEach(function (c) {
                        list.push(self.renderJs(c.value));
                    });
                }

                list.push(this.renderBody(child[i]));

                if (siteConfig) {
                    siteBody = siteDom.getElementsByTagName("footer");
                    if (siteBody && siteBody[0]) {
                        // siteBody[0] is a DOMElement which only has the key nodeValue.
                        siteBody[0].value = siteBody[0].nodeValue;
                        list.push(this.renderBody(siteBody[0]));
                    }
                }

                //render css in bottom of body
                if (this.bodyCss['bottom']) {
                    this.bodyCss['bottom'].forEach(function (c) {
                        list.push(self.renderCss(c.value));
                    });
                }

                //render js in bottom of body
                if (this.bodyJs['bottom']) {
                    this.bodyJs['bottom'].forEach(function (c) {
                        list.push(self.renderJs(c.value));
                    });
                }

                switch (this.output) {
                    case this.OUTPUT_HTML_PAGE:
                        list.push("</body>");
                        break;
                }

                //render js after body
                if (this.bodyJs['after']) {
                    this.bodyJs['after'].forEach(function (c) {
                        list.push(self.renderJs(c.value));
                    });
                }

                break;
            default:
                break;
        }
    }

    switch (this.output) {
        case this.OUTPUT_HTML_PAGE:
            list.push("</html>");
            break;
    }
    return list.join("\n");

};//}}}


o.renderHead = function (config) {//{{{
    var i, n;
    var list = [], key, child, nodeName, moduleHtml, position, moduleCss;

    if (config.childNodes) {
        child = config.childNodes;
        n = child.length;
    }

    for (i = 0; i< n; i++) {
        nodeName = child[i].name;
        nodeName = nodeName.toLowerCase();
        position = "";
        switch (nodeName) {
            case 'css':
                if (child[i].attributes && child[i].attributes["position"]) {
                    position = child[i].attributes["position"];
                }
                if (position && position.search(/body/i) !== -1) {
                    if (position === "bottomOfBody") {
                        this.bodyCss['bottom'].push(child[i]);
                    } else {
                        this.bodyCss['top'].push(child[i]);
                    }
                } else {
                    list.push(this.renderCss(child[i].value));
                }

                break;
            case 'js':
                if (child[i].attributes && child[i].attributes["position"]) {
                    position = child[i].attributes["position"];
                }
                if (position && position.search(/body/i) !== -1) {
                    if (position === "topOfBody") {
                        this.bodyJs['top'].push(child[i]);
                    } else if (position === "afterBody") {
                        this.bodyJs['after'].push(child[i]); 
                    } else {
                        this.bodyJs['bottom'].push(child[i]);
                    }
                } else {
                    list.push(this.renderJs(child[i].value));
                }
                break;
            case 'module':
                moduleHtml = this.module.render(child[i]);
                if (this.enableIndent) moduleHtml = moduleHtml.replace(/^([\s]*<)/mg, "    $1");
                list.push(moduleHtml);

                break;

        }
    }
    return list.join("\n");
};//}}}

/**
 *
 * @param isFinalPath css url path is already final path, do not appent or prepend any text.
 */
o.renderCss = function (cssText, isFinalPath) {//{{{
    var i, n, indent = "";
    var cssList, cssUrl, finalCssUrl, list = [];

    if (php.empty(isFinalPath)) {
        isFinalPath = false;
    }
    if (this.enableIndent) {
        indent = "    ";
    }

    if (!cssText) return "";
    cssList = cssText.split(/[\r\n\s]+/);
    n = cssList.length;
    for (i = 0; i < n; i++) {
        cssUrl = cssList[i];
        if (!cssUrl) continue;
        if (isFinalPath === false) {
            finalCssUrl = this.getFinalStaticUrl(cssUrl, 'css');
        } else {
            finalCssUrl = cssUrl;
        }
        list.push(indent + '<link href="' +finalCssUrl+ '" rel="stylesheet" type="text/css">');
    }
    return list.join("\n");
};//}}}

o.renderJs = function (jsText) {//{{{
    var i, n, indent = "";
    var jsList, jsUrl, finalJsUrl, list = [];
    if (this.enableIndent) {
        indent = "    ";
    }
    jsList = jsText.split(/[\r\n\s]+/);
    n = jsList.length;
    for (i = 0; i < n; i++) {
        jsUrl = jsList[i];
        if (!jsUrl) continue;
        finalJsUrl = this.getFinalStaticUrl(jsUrl, 'js');
        list.push(indent + '<script src="' +finalJsUrl+ '"></script>');
    }
    return list.join("\n");
};//}}}

o.renderBody = function (bodyConfig, indent) {//{{{
    var i, n = 0;
    var key, list = [], nodeName, attrs = "",
        moduleHtml, child;

    if (typeof(indent) === "undefined") indent = "    ";

    if (bodyConfig.childNodes) {
        child = bodyConfig.childNodes;
        n = child.length;
    } else if (bodyConfig.value) {
        list.push(indent + bodyConfig.value);
    } 



    for (i = 0; i< n; i++) {
        nodeName = child[i].name;
        nodeName = nodeName.toLowerCase();

        if (!nodeName) continue;

        switch (nodeName) {
            case 'text':
                list.push(indent + child[i].value);
                break;
            case "module":
                moduleHtml = this.module.render(child[i]);
                if (this.enableIndent && indent) moduleHtml = moduleHtml.replace(/^([\s]*<)/mg, indent + "$1");
                list.push(moduleHtml);
                break;
            case 'js':
                list.push(this.renderJs(child[i].value));
                break;

            default:
                attrs = this.attributeToString(child[i].attributes);
                list.push(indent + '<' + nodeName + attrs + '>');
                list.push(this.renderBody(child[i], indent + "    "));
                list.push(indent + '</' + nodeName + '>');

                break;
        }
    }


    return list.join("\n");
};//}}}

/**
 * Get final static url to  be readered, Combine the base url config and css file path.
 */
o.getFinalStaticUrl = function (path, type) {//{{{
    var url = "";
    if (path.indexOf('http') === 0 || path.indexOf('//') === 0) {
        return path;
    }
    if (this.baseConfig['urlPaths']
        && this.baseConfig['urlPaths'][type]
    ) {
        url = this.baseConfig['urlPaths'][type];
    }

    url += path;
    return url;
};//}}}

/**
 * convert attributes of element to string.
 */
o.attributeToString = function (attrs) {//{{{
    var html = "", name, value;
    for (name in attrs) {
        if (!name) continue;
        value = attrs[name];
        html += " ";
        html += name + "=\"" + value + "\"";
    }
    return html;
};//}}}

o.getOutputType = function (type) {//{{{
    switch (type) {
        case 'htmlPage':
            return this.OUTPUT_HTML_PAGE;
            break;
        case 'json':
            return this.OUTPUT_JSON;
            break;
        case 'TEXT':
            return this.OUTPUT_TEXT;
            break;
    };
    return this.OUTPUT_HTML_PAGE;
};//}}}

o.getModuleCss = function (dom, config) {//{{{
    var i, n;
    var head, body, css, moduleCss;

    if (!config) return [];
    head = dom.getElementsByTagName('head');
    if (head && head[0] && head[0].childNodes) {
        childNodes = head[0].childNodes;
        n = childNodes.length;
        for (i = 0; i < n; i++) {
            module = childNodes[i];
            if (module.name === "module") {
                moduleCss = this.module.getCssPath(module);
                if (!this.isExistStaticFile(moduleCss.id, this.cssFile.moduleLevel)) {
                    this.cssFile.moduleLevel.push(moduleCss);
                }
            }
        }
    }

    body = dom.getElementsByTagName('body');
    if (body && body[0]) {
        this.getModuleCssRecursive(body[0], this.cssFile.moduleLevel);
    }
    return this.cssFile.moduleLevel; 
};//}}}

/**
 * get the css file recursively from page config
 *
 * @param &result
 */
o.getModuleCssRecursive = function (body, result) {//{{{
    var i, n = 0;
    var childNodes, module, name, moduleCss;

    if (body.childNodes) {
        childNodes = body.childNodes;
        n = childNodes.length;
    }
    for (i = 0; i < n; i++) {
        module = childNodes[i];
        name = module.name;
        if (!module) continue;
        if (name === 'text') continue;
        if (name === "module") {
            moduleCss = this.module.getCssPath(module);
            if (!this.isExistStaticFile(moduleCss.id, this.cssFile.moduleLevel)) {
                result.push(moduleCss);
            }
        } else {
            this.getModuleCssRecursive(module, result);
        }
    }

}; //}}}

o.isExistStaticFile = function (id, stack) {
    var i, n;
    n = stack.length;
    for (i = 0; i< n; i++) {
        if (stack[i].id === id) {
            return true;
        }
    }
    return false;
};


module.exports = layoutParser;
