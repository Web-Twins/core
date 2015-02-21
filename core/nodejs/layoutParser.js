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

o.render = function (pageConfig, siteConfig) {//{{{
    var i, n;
    var list = [], key, child, nodeName, output, root, 
        siteBody, self, modules;

    self = this;
    root = pageConfig.root();
    child = root.childNodes();
    output = root.attr('output');
    if (output) output = output.value();
    this.output = this.getOutputType(output);

    n = child.length;
    switch (this.output) {
        case this.OUTPUT_HTML_PAGE:
            list.push("<!DOCTYPE html>\n<html>");
            break;
    }

    for (i = 0; i< n; i++) {
        nodeName = child[i].name();
        nodeName = nodeName.toLowerCase();
        switch (nodeName) {
            case 'head':
                if (this.output !== this.OUTPUT_HTML_PAGE) continue;
                list.push('<head>');
                if (siteConfig) {
                    var siteHead = siteConfig.get("//head");
                    if (siteHead) {
                        list.push(this.renderHead(siteHead));
                    }
                }
                list.push(this.renderHead(child[i]));

                //render Module Level Css in site.html
                this.getModuleCss(siteConfig);
                // render Module Level css in page.html
                moduleCss = this.getModuleCss(pageConfig);
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
                    siteBody = siteConfig.get("//header");
                    if (siteBody) {
                        list.push(this.renderBody(siteBody));
                    }
                }

                //render css in top body
                if (this.bodyCss['top']) {
                    this.bodyCss['top'].forEach(function (c) {
                        list.push(self.renderCss(c.text()));
                    });
                }

                //render js in top body
                if (this.bodyJs['top']) {
                    this.bodyJs['top'].forEach(function (c) {
                        list.push(self.renderJs(c.text()));
                    });
                }

                list.push(this.renderBody(child[i]));

                if (siteConfig) {
                    siteBody = siteConfig.get("//footer");
                    if (siteBody) {
                        list.push(this.renderBody(siteBody));
                    }
                }

                //render css in bottom of body
                if (this.bodyCss['bottom']) {
                    this.bodyCss['bottom'].forEach(function (c) {
                        list.push(self.renderCss(c.text()));
                    });
                }

                //render js in bottom of body
                if (this.bodyJs['bottom']) {
                    this.bodyJs['bottom'].forEach(function (c) {
                        list.push(self.renderJs(c.text()));
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
                        list.push(self.renderJs(c.text()));
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
    child = config.childNodes();
    n = child.length;
    for (i = 0; i< n; i++) {
        nodeName = child[i].name();
        nodeName = nodeName.toLowerCase();
        switch (nodeName) {
            case 'css':
                position = child[i].attr("position");
                if (position) position = position.value();

                if (position && position.search(/body/i) !== -1) {
                    if (position === "bottomOfBody") {
                        this.bodyCss['bottom'].push(child[i]);
                    } else {
                        this.bodyCss['top'].push(child[i]);
                    }
                } else {
                    list.push(this.renderCss(child[i].text()));
                }

                break;
            case 'js':
                position = child[i].attr("position");
                if (position) position = position.value();

                if (position && position.search(/body/i) !== -1) {
                    if (position === "topOfBody") {
                        this.bodyJs['top'].push(child[i]);
                    } else if (position === "afterBody") {
                        this.bodyJs['after'].push(child[i]); 
                    } else {
                        this.bodyJs['bottom'].push(child[i]);
                    }
                } else {
                    list.push(this.renderJs(child[i].text()));
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
    var i, n;
    var key, list = [], nodeName, attrs = "",
        moduleHtml;
    var child = bodyConfig.childNodes();
    if (typeof(indent) === "undefined") indent = "    ";

    n = child.length;
    for (i = 0; i< n; i++) {
        nodeName = child[i].name();
        nodeName = nodeName.toLowerCase();

        if (!nodeName) continue;

        switch (nodeName) {
            case 'text':
                list.push(indent + child[i].text());
                break;
            case "module":
                moduleHtml = this.module.render(child[i]);
                if (this.enableIndent && indent) moduleHtml = moduleHtml.replace(/^([\s]*<)/mg, indent + "$1");
                list.push(moduleHtml);
                break;
            case 'js':
                list.push(this.renderJs(child[i].text()));
                break;

            default:
                attrs = this.attributeToString(child[i].attrs());
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
    if (path.indexOf('http') === 0) {
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
    var html = "", i ,n, attr;
    n = attrs.length;
    for (i = 0; i < n; i++) {
        attr = attrs[i];
        html += " ";
        html += attr.name() + "=\"" + attr.value() + "\"";
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

o.getModuleCss = function (config) {//{{{
    var i, n;
    var head, body, css, moduleCss;

    if (!config) return [];
    head = config.get('//head');
    if (head) {
        children = head.childNodes();
        n = children.length;
        for (i = 0; i < n; i++) {
            module = children[i];
            if (module.name() === "module") {
                moduleCss = this.module.getCssPath(module);
                if (!this.isExistStaticFile(moduleCss.id, this.cssFile.moduleLevel)) {
                    this.cssFile.moduleLevel.push(moduleCss);
                }
            }
        }
    }

    body = config.get('//body');
    if (body) {
        this.getModuleCssRecursive(body, this.cssFile.moduleLevel);
    }
    return this.cssFile.moduleLevel; 
};//}}}

/**
 * get the css file recursively from page config
 *
 * @param &result
 */
o.getModuleCssRecursive = function (body, result) {//{{{
    var i, n;
    var children, module, name, moduleCss;
    children = body.childNodes();
    n = children.length;

    for (i = 0; i < n; i++) {
        module = children[i];
        name = module.name();
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
