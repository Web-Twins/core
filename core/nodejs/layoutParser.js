var moduleObj = new (require('./module.js'));

function layoutParser(i18n) {//{{{
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


}//}}}


var o = layoutParser.prototype;

o.output = 'htmlPage';
o.i18n = ""; //internationization

// output type: htmlPage, json, text
o.OUTPUT_HTML_PAGE = 1;
o.OUTPUT_JSON = 2;
o.OUTPUT_TEXT = 3;
o.enableIndent = true;

// The property to be rendered in body.
o.bodyJs = {
    "top": [],
    "bottom": [],
    "after": []
};

o.bodyCss = {
    "top": [],
    "bottom": []
};

o.render = function (pageConfig, siteConfig) {//{{{
    var i, n;
    var list = [], key, child, nodeName, output, root, siteBody, self;
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
    var list = [], key, child, nodeName, moduleHtml, position;

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
                moduleHtml = moduleObj.render(child[i]);
                if (this.enableIndent) moduleHtml = moduleHtml.replace(/^([\s]*<)/mg, "    $1");
                list.push(moduleHtml);

                break;

        }
    }
    return list.join("\n");
};//}}}

o.renderCss = function (cssText) {//{{{
    var i, n, indent = "";
    var cssList, cssUrl, list = [];
    if (this.enableIndent) {
        indent = "    ";
    }

    if (!cssText) return "";
    cssList = cssText.split(/[\r\n\s]+/);
    n = cssList.length;
    for (i = 0; i < n; i++) {
        cssUrl = cssList[i];
        if (!cssUrl) continue;
        list.push(indent + '<link href="' +cssUrl+ '" rel="stylesheet" type="text/css">');
    }
    return list.join("\n");
};//}}}

o.renderJs = function (jsText) {//{{{
    var i, n, indent = "";
    var jsList, jsUrl, list = [];
    if (this.enableIndent) {
        indent = "    ";
    }
    jsList = jsText.split(/[\r\n\s]+/);
    n = jsList.length;
    for (i = 0; i < n; i++) {
        jsUrl = jsList[i];
        if (!jsUrl) continue;
        list.push(indent + '<script src="' +jsUrl+ '"></script>');
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
                moduleHtml = moduleObj.render(child[i]);
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
 * convert attributes of element to string.
 */
o.attributeToString = function (attrs) {
    var html = "", i ,n, attr;
    n = attrs.length;
    for (i = 0; i < n; i++) {
        attr = attrs[i];
        html += " ";
        html += attr.name() + "=\"" + attr.value() + "\"";
    }
    return html;
};

o.getOutputType = function (type) {
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
};

module.exports = layoutParser;
