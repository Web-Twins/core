<?php

require_once __DIR__ . "/../../core/php/layoutParser.php";

class testLayoutParser extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $root = __DIR__ . "/../../examples";
        $baseConfig = array(
            "urlPaths" => array(
                "template" => "/modules",
                "css" => "",
                "js" => "",
            )
        );

        $this->tester = new layoutParser(array(), $root, $baseConfig);
    }


    public function tearDown() {
        $this->tester = null;
    }

    public function testRenderCss() {/*{{{*/
        $text = <<<HTML
        a.css
        b.css
HTML;

        $expect = <<< HTML
    <link href="a.css" rel="stylesheet" type="text/css">
    <link href="b.css" rel="stylesheet" type="text/css">
HTML;

        $result = $this->tester->renderCss($text);
        //print_r($result);
        $this->assertEquals($expect, $result, '');
    }/*}}}*/

    public function providerTestRenderHead() {/*{{{*/
        $data = array();

        // Simple test
        $html = <<<HTML
        <page>
            <head>
                <css>
                    a.css
                    b.css
                </css>
                <js>
                    a.js
                </js>
            </head>
        </page>
HTML;
        $data[] = array(
            $html,
            '' .
'    <link href="a.css" rel="stylesheet" type="text/css">' . "\n" .
'    <link href="b.css" rel="stylesheet" type="text/css">' . "\n" .
'    <script src="a.js"></script>'
        );


        // Absolute url and multi tags 
        $html = <<<HTML
        <page>
            <head>
                <css>
                    http://www.com/a.css
                </css>
                <css>b.css</css>
                <js>
                    a.js
                </js>
                <js>
                    b.js
                </js>
            </head>
        </page>
HTML;
        $data[] = array(
            $html,
            '' .
'    <link href="http://www.com/a.css" rel="stylesheet" type="text/css">' . "\n" .
'    <link href="b.css" rel="stylesheet" type="text/css">' . "\n" .
'    <script src="a.js"></script>' . "\n" .
'    <script src="b.js"></script>'

        );

        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestRenderHead
     */
    public function testRenderHead($html, $expect) {/*{{{*/
        $d = new DOMDocument();
        $d->loadXML($html);
        $config = $d->getElementsByTagName("head");
        $result = $this->tester->renderHead($config->item(0));
        //echo "-" ; print_r($result); echo "-";
        $this->assertEquals($expect, $result, '');
    }/*}}}*/


    public function providerTestGetOutputType() {/*{{{*/
        $data = array();
        //array(type, expect)            
        $data[] = array("htmlPage", 1);
        $data[] = array("json", 2);
        $data[] = array("text", 3);
 
        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestGetOutputType
     */
    public function testGetOutputType($type, $expect) {/*{{{*/
        $result = $this->tester->getOutputType($type);
        $this->assertEquals($expect, $result);
    }/*}}}*/


    public function providerTestAttributeToString() {/*{{{*/
        $data = array();
        //array(attr, expect)            
        $data[] = array(
                   array("class" => "a"),
                   "class=\"a\""
                  );
        $data[] = array(
                   array(
                    "class" => "ab",
                    "width" => 100
                   ),
                   "class=\"ab\" width=\"100\""
                  );
 
        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestAttributeToString
     */
    public function testAttributeToString($attr, $expect) {/*{{{*/
        $result = $this->tester->attributeToString($attr);
        $this->assertEquals($expect, $result);
    }/*}}}*/

    public function providerTestGetModuleCssRecursive() {/*{{{*/
        $data = array();
        $html = '<body>'
               .'    <module models="default.json">common/header</module>'
               .'</body>' ;
        $expect = array(
            array(
                'id' => '%2Fwww%2Fdev%2Ftwins%2Ftests%2Fphp%2F..%2F..%2Fexamples%2Fmodules%2Fcommon%2Fheader%2Fstatic%2Fheader.less',
                'path' => __DIR__ . '/../../examples/modules/common/header/static/header.less',
                'urlPath' => '/modules/common/header/static/header.less',
            ),
        );
        $data[] = array($html, $expect);
 
        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestGetModuleCssRecursive
     */
    public function testGetModuleCssRecursive($html, $expect) {/*{{{*/

        $dom = new DOMDocument();
        $dom->loadXML($html);
        $body = $dom->getElementsByTagName("body");
        $result = array();
        $this->tester->getModuleCssRecursive($body->item(0), $result);
        $n = count($expect);
        for ($i = 0; $i < $n; $i++) {
            foreach($expect[$i] as $key => $val){
                $this->assertEquals($val, $result[$i][$key]);
            }
        }
    }/*}}}*/



}
