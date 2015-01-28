var moduleObj = new (require('./module.js'));

function layoutParser(i18n) {
    if (i18n) {
        this.i18n = i18n;
    }
}


var o = layoutParser.prototype;

o.output = 'htmlPage';
o.i18n = ""; //internationization

// output type: htmlPage, json, text
o.OUTPUT_HTML_PAGE = 1;
o.OUTPUT_JSON = 2;
o.OUTPUT_TEXT = 3;
o.enableIndent = true;

o.render = function (pageConfig) {//{{{
    var i, n;
    var list = [], key, child, nodeName, output, root;

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
                list.push(this.renderHead(child[i]));
                break;
            case 'body':
                switch (this.output) {
                    case this.OUTPUT_HTML_PAGE:
                        list.push("<body>");
                        break;
                }

                list.push(this.renderBody(child[i], ""));
                switch (this.output) {
                    case this.OUTPUT_HTML_PAGE:
                        list.push("</body>");
                        break;
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
    var list = ['<head>'], key, child, nodeName;
    child = config.childNodes();
    n = child.length;
    for (i = 0; i< n; i++) {
        nodeName = child[i].name();
        nodeName = nodeName.toLowerCase();
        switch (nodeName) {
            case 'css':
                list.push(this.renderCss(child[i].text()));
                break;
            case 'js':
                list.push(this.renderJs(child[i].text()));
                break;
        }
    }
    list.push('</head>');
    return list.join("\n");
};//}}}

o.renderCss = function (cssText) {//{{{
    var i, n;
    var cssList, cssUrl, list = [];
    if (!cssText) return "";
    cssList = cssText.split(/[\r\n\s]+/);
    n = cssList.length;
    for (i = 0; i < n; i++) {
        cssUrl = cssList[i];
        if (!cssUrl) continue;
        list.push('<link href="' +cssUrl+ '" rel="stylesheet" type="text/css">');
    }
    return list.join("\n");
};//}}}

o.renderJs = function (jsText) {//{{{
    var i, n;
    var jsList, jsUrl, list = [];
    jsList = jsText.split(/[\r\n\s]+/);
    n = jsList.length;
    for (i = 0; i < n; i++) {
        jsUrl = jsList[i];
        if (!jsUrl) continue;
        list.push('<script src="' +jsUrl+ '"></script>');
    }
    return list.join("\n");
};//}}}

o.renderBody = function (bodyConfig, indent) {
    var i, n;
    var key, list = [], nodeName, className, attrs = "",
        moduleHtml;
    var child = bodyConfig.childNodes();
    if (typeof(indent) === "undefined") indent = "";

    n = child.length;
    for (i = 0; i< n; i++) {
        className = child[i].attrs('class');
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
            default:
                attrs = this.attributeToString(child[i].attrs());
                list.push(indent + '<' + nodeName + attrs + '>');
                list.push(this.renderBody(child[i], indent + "    "));
                list.push(indent + '</' + nodeName + '>');

                break;
        }
    }


    return list.join("\n");
};

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
