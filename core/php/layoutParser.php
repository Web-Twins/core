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

    /******* Features ********/
    /**
     * Developed mode: 
    */
    // Combine the files of css into only one url
    public $enableCssCombo = true;
    // Combine the files of JavaScript into only one url
    public $enableJsCombo = true;
    // Display the data of models

    const MAX_URL_LENGTH = 2000;
    const OUTPUT_HTML_PAGE = 1;
    const OUTPUT_JSON = 2;
    const OUTPUT_TEXT = 3;
    /**
     * i18n International language
     * root the path of templates 
     * baseConfig config for different project.
     */ 
    public function __construct($i18n, $root, $baseConfig) {/*{{{*/
        $this->context = array();
        $this->staticVersion = "20160101";
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
        if (!empty($baseConfig['staticVersion'])) $this->staticVersion = $baseConfig['staticVersion'];
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
            if ($attr->value == "-") {
                $html[] = $attr->name;
            } else {
                $html[] = $attr->name . "=\"" . $attr->value . "\"";
            }
        }
        if (empty($html)) return "";

        return " " . implode(" ", $html);
    }//}}}

    /**
     * Combine multi file into one url combo
    */ 
    public function combineFiles($base, $list) {/*{{{*/
        $base .= "?";
        $res = array();
        $url = ""; $urlLen = 0;
        $baseLen = strlen($base);
        foreach ($list as $file) {
            $tmpLen = strlen($file) + 1;
            if ($tmpLen == 1) continue;
            if ($tmpLen >= 4) $protocol = strtolower(substr($file, 0, 4));
            if ($protocol === "http" || substr($file, 0, 2) === "//") {
                if ($urlLen > 0) $res[] = $url;
                $res[] = $file;
                $url = "";
                $urlLen = 0;
                continue;
            }
            if ($tmpLen + $urlLen < self::MAX_URL_LENGTH) {
                if ($urlLen === 0) {
                    $url = $base;  
                    $urlLen = $baseLen;
                }
                $url .= $file . '&';
                $urlLen += $tmpLen;
            } else {
                $res[] = $url;
                $url = $base . $file . '&';
                $urlLen = $baseLen + $tmpLen;
            }
        }
        if ($urlLen >0 ) $res[] = $url;
        return $res;
    }/*}}}*/

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
        $path = preg_replace('/\.less$/', '.css', $path);
        if ($this->baseConfig['urlPaths']
            && $this->baseConfig['urlPaths'][$type]
        ) {
            $url = $this->baseConfig['urlPaths'][$type];
        }

        $url .= '/'.$path;
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

    public function setData($key, $data) {
        $this->context[$key] = $data;
    }

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

        if (true === $this->enableCssCombo) {
            $cssList = $this->combineFiles($this->baseConfig['urlPaths']['cssCombo'], $cssList);
        }
        
        foreach ($cssList as $url) {
            $list[] = $indent . '<link href="' . $url . '" rel="stylesheet" type="text/css">';
        }
        return implode("\n", $list);
    }//}}}

    public function renderJs($jsText) {//{{{
        $indent = "";
        $list = array();
        if ($this->enableIndent) {
            $indent = "    ";
        }
        if (is_string($jsText)) {
            $jsList = preg_split('/[\r\n\s]+/', $jsText);
        } else {
            foreach ($jsText as $node) {
                $jsList[] = $node->nodeValue;
            }
        }

        if (true === $this->enableJsCombo) {
            $jsList = $this->combineFiles($this->baseConfig['urlPaths']['jsCombo'], $jsList);
        }

        foreach ($jsList as $url) {
            $url .= "?v=" . $this->staticVersion;
            $list[] = $indent . '<script src="' . $url . '"></script>';
        }
        return implode("\n", $list);
    }//}}}

    public function render($pageXML, $siteXML = "") {/*{{{*/
        $siteDom = "";
        if (!is_file($pageXML)) return "";
        $pageDom = new DOMDocument();
        $pageDom->load($pageXML);
        $pageDom = $pageDom->getElementsByTagName("page");
        if ($pageDom->length <= 0) return "";
        $pageDom = $pageDom->item(0);

        // Load site config, if page has set
        if ($pageDom->hasAttribute('siteConfig')) {
            $siteConfig = $pageDom->getAttribute('siteConfig');
            $siteXML = preg_replace('/[^\/]+$/', '', $siteXML);
            $siteXML .= $siteConfig;
        }
        if (is_file($siteXML)) {
            $siteDom = new DOMDocument();
            $siteDom->load($siteXML);
            $siteDom = $siteDom->getElementsByTagName("site"); 
            if ($siteDom->length > 0) {
                $siteDom = $siteDom->item(0);
            } else {
                $siteDom = "";
            }
        }
        return $this->renderByDom($pageDom, $siteDom);
    }/*}}}*/

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
                $htmlNode = $siteDom->getElementsByTagName("html");
                if ($htmlNode->length > 0) {
                    $html = "<!DOCTYPE html>\n";
                    $attrs = $this->attributeToString($htmlNode->item(0)->attributes);
                    $html .= "<html" . $attrs .">";
                    $list[] = $html;
                } else {
                    $list[] = "<!DOCTYPE html>\n<html>";
                }
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

                    $list[] = $this->renderBody($node);

                    if ($siteDom) {
                        $siteBody = $siteDom->getElementsByTagName("footer");
                        if ($siteBody->length > 0) {
                            // siteBody[0] is a DOMElement which only has the key nodeValue.
                            $list[] = $this->renderBody($siteBody->item(0));
                        }
                    }


                    switch ($this->output) {
                        case $this::OUTPUT_HTML_PAGE:
                            $list[] = "</body>";
                            break;
                    }


                    break;
                default:
                    break;
            }
        }
        if (!empty($this->bodyJs['after'])) {
            foreach ($this->bodyJs['after'] as $node) {
                $list[] = $this->renderJs($node->nodeValue);
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
                    $moduleHtml = $this->renderModule($node, true);
                    if ($this->enableIndent && $moduleHtml) $moduleHtml = preg_replace('/^([\s]*<)/m', "    $1", $moduleHtml);
                    $list[] = $moduleHtml;

                    break;

            }
        }
        return implode("\n", $list);
    }//}}}

    public function renderBody($bodyConfig, $indent = "") {//{{{
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
                    $moduleHtml = $this->renderModule($elm, true);
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

    public function renderModule($node, $isReal = true) {
        $model = true;
        if ($node->hasAttribute('dataKey')) {
            $key = $node->getAttribute('dataKey');
            if ($key) $model = $this->context[$key];
        }
        return $this->module->render($node, $model);
    }

}

