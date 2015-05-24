<?php
class_exists('moduleObj ') || require __DIR__ . '/module.php';

class layoutParser {
    public $i18n; // Language Object
    public $bodyJs; // The javascript files will render in html
    public $bodyCss; // The css files will render in html
    public $cssFile; // CSS level, such as module level, page level, global level ...
    public $context;
    public $module; // The object of module 
    public $output = "page"; // 1:page, 2:json, 3:text
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

        $this->module = new moduleObj($root, $this->context);
    }/*}}}*/

    /**
     * convert attributes of element to string.
     */
    public function attributeToString($attrs) {//{{{
        $html = array();
        if (empty($attrs)) return "";
        $n = $attrs->length;
        for ($i = 0 ;$i < $n; $i++) {
            $attr = $attrs->item($i);
            $html[] = $attr->name . "=\"" . $attr->value . "\"";
        }
        if (empty($html)) return "";

        return " " . implode(" ", $html);
    }//}}}

    public function getOutputType($type) {//{{{
        $type = strtolower($type);
        switch ($type) {
            case 'htmlpage':
                return self::OUTPUT_HTML_PAGE;
                break;
            case 'json':
                return self::OUTPUT_JSON;
                break;
            case 'text':
                return self::OUTPUT_TEXT;
                break;
        };
        return self::OUTPUT_HTML_PAGE;
    }//}}}

    /**
     * Get final static url to  be readered, Combine the base url config and css file path.
     */
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

    public function getModuleCss($dom) {//{{{
        //var head, body, css, moduleCss;
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

    /**
     * get the css file recursively from page config
     *
     * @param &result
     */
    public function getModuleCssRecursive($bodyDom, &$result) {//{{{
        $n = 0;
        $childNodes = "";
        if ($bodyDom->childNodes) {
            $childNodes = $bodyDom->childNodes;
            $n = $childNodes->length;
        }

        for ($i = 0; $i < $n; $i++) {
            $module = $childNodes->item($i);
            $name = $module->nodeName;
            if (!$module) continue;
            if ($name === 'text') continue;
            if ($name === "module") {
                $moduleCss = $this->module->getCssPath($module);
                if (!$this->isExistStaticFile($moduleCss['id'], $this->cssFile['moduleLevel'])) {
                    $result[] = $moduleCss;
                }
            } else {
                $this->getModuleCssRecursive($module, $result);
            }
        }
    }//}}}


    public function isExistStaticFile($id, $stack) {/*{{{*/
        $n = count($stack);
        for ($i = 0; $i < $n; $i++) {
            if ($stack[$i]['id'] === $id) {
                return true;
            }
        }
        return false;
    }/*}}}*/

    /**
     * 
     * @param $isFinalPath css url path is already final path, do not appent or prepend any text.
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

    public function render($pageXML, $siteXML = "") {
        $siteDom = "";
        if (!is_file($pageXML)) return "";
        $pageDom = new DOMDocument();
        $pageDom->load($pageXML);
        $pageDom = $pageDom->getElementsByTagName("page");
        if ($pageDom->length <= 0) return "";
        $pageDom = $pageDom->item(0);
        if (is_file($siteXML)) {
            $siteDom = new DOMDocument();
            $siteDom->load($siteXML);
            $siteDom = $siteDom->getElementsByTagName("page"); 
            if ($siteDom->length > 0) {
                $siteDom = $siteDom->item(0);
            } else {
                $siteDom = "";
            }
        }
        return $this->renderByDom($pageDom, $siteDom);
    }

    public function renderByDom($pageDom, $siteDom = "") {//{{{
        $list = array();
        $output = "";
        $this->pageDom = $pageDom;
        if ($siteDom) $this->siteDom = $siteDom;
        $self = $this;
        if ($pageDom->childNodes) $child = $pageDom->childNodes;

        if ($pageDom->hasAttribute('output')) {
            $output = $pageDom->getAttribute('output');
        } 

        $this->output = $this->getOutputType($output);

        switch ($this->output) {
            case $this::OUTPUT_HTML_PAGE:
            default:
                $list[] = "<!DOCTYPE html>\n<html>";
                break;
        }
        if ($child) {
            $n = $child->length;
        } else {
            $n = 0;
        }

        for ($i = 0; $i< $n; $i++) {
            $node = $child->item($i);
            $nodeName = strtolower($node->nodeName);
            switch ($nodeName) {
                case 'head':
                    if ($this->output !== $this::OUTPUT_HTML_PAGE) continue;
                    $list[] = '<head>';
                    if ($siteDom) {
                        $siteHead = $siteDom->getElementsByTagName("head");
                        if ($siteHead->length > 0) {
                            $list[] = $this->renderHead($siteHead->item(0));
                        }
                    }
                    $list[] = $this->renderHead($node);

                    //render Module Level Css in site.html
                    if ($this->siteDom)
                        $this->getModuleCss($this->siteDom);
                    // render Module Level css in page.html
                    $moduleCss = $this->getModuleCss($this->pageDom);
                    if ($moduleCss) {
                        $cssCount = $moduleCss->length;
                        for ($j = 0; $j < $cssCount; $j++) {
                            if (!$moduleCss[$j] || !$moduleCss[$j]['urlPath']) continue;

                            $isFinalPath = true;
                            $list[] = $this->renderCss($moduleCss[$j]['urlPath'], $isFinalPath);
                        }
                    }
                    $list[] = '</head>';
                    break;
                case 'body':
                    switch ($this->output) {
                        case $this::OUTPUT_HTML_PAGE:
                            $list[] = "<body>";
                            break;
                    }

                    if ($siteDom) {
                        $siteBody = $siteDom->getElementsByTagName("header");
                        if ($siteBody->length > 0) {
                            $list[] = $this->renderBody($siteBody->item(0));
                        }
                    }

                    //render css in top body
                    if ($this->bodyCss['top']) {
                        foreach ($this->bodyCss['top'] as $c) {
                            $list[] = $this->renderCss($c->value);
                        }
                    }

                    //render js in top body
                    //if ($this.bodyJs['top']) {
                    //    this.bodyJs['top'].forEach(function (c) {
                    //        list.push(self.renderJs(c.value));
                    //    });
                    //}

                    $list[] = $this->renderBody($node);

                    if ($siteDom) {
                        $siteBody = $siteDom->getElementsByTagName("footer");
                        if ($siteBody->length > 0) {
                            // siteBody[0] is a DOMElement which only has the key nodeValue.
                            $list[] = $this->renderBody($siteBody->item(0));
                        }
                    }

                    //render css in bottom of body
                    //if (this.bodyCss['bottom']) {
                    //    this.bodyCss['bottom'].forEach(function (c) {
                    //        list.push(self.renderCss(c.value));
                    //    });
                    //}

                    ////render js in bottom of body
                    //if (this.bodyJs['bottom']) {
                    //    this.bodyJs['bottom'].forEach(function (c) {
                    //        list.push(self.renderJs(c.value));
                    //    });
                    //}

                    switch ($this->output) {
                        case $this::OUTPUT_HTML_PAGE:
                            $list[] = "</body>";
                            break;
                    }

                    //render js after body
                    //if (this.bodyJs['after']) {
                    //    this.bodyJs['after'].forEach(function (c) {
                    //        list.push(self.renderJs(c.value));
                    //    });
                    //}

                    break;
                default:
                    break;
            }
        }

        switch ($this->output) {
            case $this::OUTPUT_HTML_PAGE:
                $list[] = "</html>";
                break;
        }
        return implode("\n", $list);

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

    public function renderBody($bodyConfig, $indent = "") {//{{{
        //var i, n = 0;
        //var key, list = [], nodeName, attrs = "",
        //    moduleHtml, child;
        $list = array();
        $n = 0;
        if (!isset($indent)) $indent = "    ";
        if ($bodyConfig->childNodes) {
            $child = $bodyConfig->childNodes;
            $n = $child->length;
        } else if ($bodyConfig->nodeValue) {
            $list[] = $indent . $bodyConfig->nodeValue;
        } 

        for ($i = 0; $i< $n; $i++) {
            $elm = $child->item($i);
            $nodeName = $elm->nodeName;
            $nodeName = strtolower($nodeName);

            if (!$nodeName) continue;

            switch ($nodeName) {
                case '#text':case 'text':
                    $list[] = $indent . $elm->nodeValue;
                    break;
                case "module":
                    $moduleHtml = $this->module->render($elm);
                    if ($this->enableIndent && !empty($this->indent)) $moduleHtml = preg_replace('/^([\s]*<)/', $indent . "$1", $moduleHtml);
                    $list[] = $moduleHtml;
                    break;
                case 'js':
                    $list[] = $this->renderJs($elm->nodeValue);
                    break;

                default:
                    $attrs = $this->attributeToString($elm->attributes);
                    $list[] = $indent . '<' . $nodeName . $attrs . '>';
                    $list[] = $this->renderBody($elm, $indent . "    ");
                    $list[] = $indent . '</' . $nodeName . '>';

                    break;
            }
        }
        return implode("\n", $list);
    }//}}}

}

