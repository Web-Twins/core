<?php
require_once __DIR__ . '/module.php';

class layoutParser {
    public $i18n; // Language Object
    public $bodyJs; // The javascript files will render in html
    public $bodyCss; // The css files will render in html
    public $cssFile; // CSS level, such as module level, page level, global level ...
    public $context;
    public $module; // The object of module 
    public $output = "page"; // a:page, 2:json, 3:text
    public $baseConfig;
    public $enableIndent = true;
    public $pageDom;
    public $siteDom;
    
    const OUTPUT_HTML_PAGE = 1;
    const OUTPUT_JSON = 2;
    const OUTPUT_TEXT = 3;
    
    public function __construct($i18n, $root, $baseConfig) {/*{{{*/
        $this->context = array();
        if ($i18n) {
            $this->i18n = $i18n;
        }

        $this->bodyJs = array(
            "top"    => array(),
            "bottom" => array(),
            "after"  => array(),
        );

        $this->bodyCss = array(
            "top"    => array(),
            "bottom" => array(),
        );

        $this->cssFile = array(
            "moduleLevel" => array()
        );

        if (!empty($baseConfig)) {
            $this->baseConfig = $baseConfig;
        } else {
            $baseConfig = array();
        }

        $this->context['baseConfig'] = $baseConfig;

        //$this->module = new moduleMod($root, $this->context);
    }/*}}}*/

    /**
     *
     * @param isFinalPath css url path is already final path, do not appent or prepend any text.
     */
    public function renderCss($cssText, $isFinalPath = false) {//{{{
        $indent = "";
        $list = array();

        if (empty($isFinalPath)) {
            $isFinalPath = false;
        }
        if ($this->enableIndent) {
            $indent = "    ";
        }

        if (empty($cssText)) return "";

        $cssList = preg_split('/[\r\n\s]+/', $cssText);
        $n = sizeof($cssList);

        for ($i = 0; $i < $n; $i++) {
            $cssUrl = $cssList[$i];
            if (empty($cssUrl)) continue;

            if (false === $isFinalPath) {
                $finalCssUrl = $this->getFinalStaticUrl($cssUrl, 'css');
            } else {
                $finalCssUrl = $cssUrl;
            }

            $list[] = $indent . '<link href="' . $finalCssUrl . '" rel="stylesheet" type="text/css">';
        }

        return implode("\n", $list);
    }//}}}

    public function renderJs($jsText) {//{{{
        $indent = "";
        $list = array();

        if ($this->enableIndent) {
            $indent = "    ";
        }
        $jsList = preg_split('/[\r\n\s]+/', $jsText);

        $n = sizeof($jsList);
        for ($i = 0; $i < $n; $i++) {
            $jsUrl = $jsList[$i];
            if (empty($jsUrl)) continue;
            $finalJsUrl = $this->getFinalStaticUrl($jsUrl, 'js');
            $list[] = $indent . '<script src="' . $finalJsUrl . '"></script>';
        }
        return implode("\n", $list);
    }//}}}

    public function getFinalStaticUrl($path, $type) {//{{{
        $url = "";
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        if ($this->baseConfig['urlPaths']
            && $this->baseConfig['urlPaths'][$type]
        ) {
            $url = $this->baseConfig['urlPaths'][$type];
        }

        $url .= $path;
        return $url;
    }//}}}

    public function renderHead($config) {//{{{
        $list = array();
        $child = $config->childNodes; 
        $n = $child->length;
        for ($i = 0; $i < $n; $i++) {
            $node = $child->item($i);
            $nodeName = $node->nodeName;
            $nodeName = strtolower($nodeName);
            $position = "";
            switch ($nodeName) {
                case 'css':
                    if ($node->hasAttribute("position")) {
                        $position = $node->getAttibute("position");
                    }
                    if (!empty($position) && preg_replace('/body/i', $position)) {
                        if ($position === "bottomOfBody") {
                            $this->bodyCss['bottom'][] = node;
                        } else {
                            $this->bodyCss['top'][] = node;
                        }
                    } else {
                        $list[] = $this->renderCss($node->nodeValue);
                    }

                    break;
                case 'js':
                    if ($node->hasAttribute("position")) {
                        $position = $node->getAttribute("position");
                    }

                    if ($position && preg_match('/body/i', $position)) {
                        if ($position === "topOfBody") {
                            $this->bodyJs['top'][] = node;
                        } else if ($position === "afterBody") {
                            $this->bodyJs['after'][] = $node;
                        } else {
                            $this->bodyJs['bottom'][] = $node;
                        }
                    } else {
                        $list[] = $this->renderJs($node->nodeValue);
                    }
                    break;
                case 'module':
                    $moduleHtml = $this->module->render($node);
                    if ($this->enableIndent) $moduleHtml = preg_replace('/^([\s]*<)/mg', "    $1", $moduleHtml);
                    $list[] = $moduleHtml;

                    break;

            }
        }
        return implode("\n", $list);
    }//}}}

    public function getModuleCss($dom, $config) {//{{{
        //var head, body, css, moduleCss;

        if (empty($config)) return array();
        $head = $dom->getElementsByTagName('head');
        if ($head && $head->length > 0 && $head->item(0)->childNodes) {

            $child = $head->item(0)->childNodes;
            $n = $child->length;
            for ($i = 0; $i < $n; $i++) {
                $module = $child->item($i);
                if ($module->nodeName === "module") {
                    $moduleCss = $this->module->getCssPath($module);
                    if (!$this->isExistStaticFile($moduleCss->id, $this->cssFile['moduleLevel'])) {
                        $this->cssFile['moduleLevel'][] = $moduleCss;
                    }
                }
            }
        }

        $body = $dom->getElementsByTagName('body');
        if ($body && $body->length > 0) {
            $this->getModuleCssRecursive($body->item(0), $this->cssFile['moduleLevel']);
        }
        return $this->cssFile['moduleLevel']; 
    }//}}}

    public function isExistStaticFile($id, $stack) {
        $n = count($stack);
        for ($i = 0; $i < $n; $i++) {
            if ($stack[$i]['id'] === $id) {
                return true;
            }
        }
        return false;
    }

}



//o.render = function (pageDom, siteDom) {//{{{
//    var i, n;
//    var list = [], key, child, nodeName, output = "", 
//        siteBody, self, modules, pageConfig, siteConfig;
//
//    this.pageDom = pageDom;
//    if (siteDom) this.siteDom = siteDom;
//    pageConfig = pageDom.json;
//    if (siteDom) siteConfig = siteDom.json;
//
//    self = this;
//    if (pageConfig.childNodes) child = pageConfig.childNodes;
//
//    if (pageConfig.attributes 
//        && pageConfig.attributes['output']
//       ) {
//        output = pageConfig.attributes['output'];
//    } 
//
//    this.output = this.getOutputType(output);
//
//    switch (this.output) {
//        case this.OUTPUT_HTML_PAGE:
//            list.push("<!DOCTYPE html>\n<html>");
//            break;
//    }
//
//    if (child) {
//        n = child.length;
//    } else {
//        n = 0;
//    }
//
//    for (i = 0; i< n; i++) {
//        nodeName = child[i].name;
//        nodeName = nodeName.toLowerCase();
//        switch (nodeName) {
//            case 'head':
//                if (this.output !== this.OUTPUT_HTML_PAGE) continue;
//                list.push('<head>');
//                if (siteConfig) {
//                    var siteHead = siteDom.getElementsByTagName("head");
//                    if (siteHead && siteHead[0]) {
//                        list.push(this.renderHead(siteHead[0]));
//                    }
//                }
//                list.push(this.renderHead(child[i]));
//
//                //render Module Level Css in site.html
//                this.getModuleCss(this.siteDom, siteConfig);
//                // render Module Level css in page.html
//                moduleCss = this.getModuleCss(this.pageDom, pageConfig);
//                var cssCount = moduleCss.length;
//                for (var j = 0; j < cssCount; j++) {
//                    if (!moduleCss[j] || !moduleCss[j]['urlPath']) continue;
//
//                    var isFinalPath = true;
//                    list.push(this.renderCss(moduleCss[j]['urlPath'], isFinalPath));
//                }
//                list.push('</head>');
//                break;
//            case 'body':
//                switch (this.output) {
//                    case this.OUTPUT_HTML_PAGE:
//                        list.push("<body>");
//                        break;
//                }
//
//                if (siteConfig) {
//                    siteBody = siteDom.getElementsByTagName("header");
//                    if (siteBody && siteBody[0]) {
//                        siteBody[0].value = siteBody[0].nodeValue;
//                        list.push(this.renderBody(siteBody[0]));
//                    }
//                }
//
//                //render css in top body
//                if (this.bodyCss['top']) {
//                    this.bodyCss['top'].forEach(function (c) {
//                        list.push(self.renderCss(c.value));
//                    });
//                }
//
//                //render js in top body
//                if (this.bodyJs['top']) {
//                    this.bodyJs['top'].forEach(function (c) {
//                        list.push(self.renderJs(c.value));
//                    });
//                }
//
//                list.push(this.renderBody(child[i]));
//
//                if (siteConfig) {
//                    siteBody = siteDom.getElementsByTagName("footer");
//                    if (siteBody && siteBody[0]) {
//                        // siteBody[0] is a DOMElement which only has the key nodeValue.
//                        siteBody[0].value = siteBody[0].nodeValue;
//                        list.push(this.renderBody(siteBody[0]));
//                    }
//                }
//
//                //render css in bottom of body
//                if (this.bodyCss['bottom']) {
//                    this.bodyCss['bottom'].forEach(function (c) {
//                        list.push(self.renderCss(c.value));
//                    });
//                }
//
//                //render js in bottom of body
//                if (this.bodyJs['bottom']) {
//                    this.bodyJs['bottom'].forEach(function (c) {
//                        list.push(self.renderJs(c.value));
//                    });
//                }
//
//                switch (this.output) {
//                    case this.OUTPUT_HTML_PAGE:
//                        list.push("</body>");
//                        break;
//                }
//
//                //render js after body
//                if (this.bodyJs['after']) {
//                    this.bodyJs['after'].forEach(function (c) {
//                        list.push(self.renderJs(c.value));
//                    });
//                }
//
//                break;
//            default:
//                break;
//        }
//    }
//
//    switch (this.output) {
//        case this.OUTPUT_HTML_PAGE:
//            list.push("</html>");
//            break;
//    }
//    return list.join("\n");
//
//};//}}}
//
//
//
//
//
//o.renderBody = function (bodyConfig, indent) {//{{{
//    var i, n = 0;
//    var key, list = [], nodeName, attrs = "",
//        moduleHtml, child;
//
//    if (typeof(indent) === "undefined") indent = "    ";
//
//    if (bodyConfig.childNodes) {
//        child = bodyConfig.childNodes;
//        n = child.length;
//    } else if (bodyConfig.value) {
//        list.push(indent + bodyConfig.value);
//    } 
//
//
//
//    for (i = 0; i< n; i++) {
//        nodeName = child[i].name;
//        nodeName = nodeName.toLowerCase();
//
//        if (!nodeName) continue;
//
//        switch (nodeName) {
//            case 'text':
//                list.push(indent + child[i].value);
//                break;
//            case "module":
//                moduleHtml = this.module.render(child[i]);
//                if (this.enableIndent && indent) moduleHtml = moduleHtml.replace(/^([\s]*<)/mg, indent + "$1");
//                list.push(moduleHtml);
//                break;
//            case 'js':
//                list.push(this.renderJs(child[i].value));
//                break;
//
//            default:
//                attrs = this.attributeToString(child[i].attributes);
//                list.push(indent + '<' + nodeName + attrs + '>');
//                list.push(this.renderBody(child[i], indent + "    "));
//                list.push(indent + '</' + nodeName + '>');
//
//                break;
//        }
//    }
//
//
//    return list.join("\n");
//};//}}}
//
///**
// * Get final static url to  be readered, Combine the base url config and css file path.
// */
//
///**
// * convert attributes of element to string.
// */
//o.attributeToString = function (attrs) {//{{{
//    var html = "", name, value;
//    for (name in attrs) {
//        if (!name) continue;
//        value = attrs[name];
//        html += " ";
//        html += name + "=\"" + value + "\"";
//    }
//    return html;
//};//}}}
//
//o.getOutputType = function (type) {//{{{
//    switch (type) {
//        case 'htmlPage':
//            return this.OUTPUT_HTML_PAGE;
//            break;
//        case 'json':
//            return this.OUTPUT_JSON;
//            break;
//        case 'TEXT':
//            return this.OUTPUT_TEXT;
//            break;
//    };
//    return this.OUTPUT_HTML_PAGE;
//};//}}}
//
//
///**
// * get the css file recursively from page config
// *
// * @param &result
// */
//o.getModuleCssRecursive = function (body, result) {//{{{
//    var i, n = 0;
//    var childNodes, module, name, moduleCss;
//
//    if (body.childNodes) {
//        childNodes = body.childNodes;
//        n = childNodes.length;
//    }
//    for (i = 0; i < n; i++) {
//        module = childNodes[i];
//        name = module.name;
//        if (!module) continue;
//        if (name === 'text') continue;
//        if (name === "module") {
//            moduleCss = this.module.getCssPath(module);
//            if (!this.isExistStaticFile(moduleCss.id, this.cssFile.moduleLevel)) {
//                result.push(moduleCss);
//            }
//        } else {
//            this.getModuleCssRecursive(module, result);
//        }
//    }
//
//}; //}}}
//
//

