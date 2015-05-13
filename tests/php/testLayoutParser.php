<?php

require_once __DIR__ . "/../../core/php/layoutParser.php";

class testLayoutParser extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $root = __DIR__ . "/../../examples";
        $root = realpath($root);
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
        $html = "<div class=\"a\"></div>";
        $data[] = array(
                   $html,
                   " class=\"a\""
                  );
        $html = "<div class=\"ab\" width=\"100\"></div>";

        $data[] = array(
                   $html,
                   " class=\"ab\" width=\"100\""
                  );
 
        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestAttributeToString
     */
    public function testAttributeToString($html, $expect) {/*{{{*/
        $dom = new DOMDocument();   
        $dom->loadXML($html);
        $d = $dom->getElementsByTagName('div');
        $result = $this->tester->attributeToString($d->item(0)->attributes);
        $this->assertEquals($expect, $result);
    }/*}}}*/

    public function providerTestGetModuleCssRecursive() {/*{{{*/
        $data = array();
        $html = '<body>'
               .'    <module models="default.json">common/header</module>'
               .'</body>' ;
        $expect = array(
            array(
                'id' => '%2Fwww%2Fdev%2Ftwins%2Fexamples%2Fmodules%2Fcommon%2Fheader%2Fstatic%2Fheader.less',
                'path' => realpath(__DIR__ . '/../../examples/modules/common/header/static/header.less'),
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

    public function providerTestRenderBody() {/*{{{*/
        $data = array();
        $html = '<body>'
               .'    <module models="default.json">common/header</module>'
               .'</body>' ;
        $expect = "    \n" . 
            '<header class="template-header">' . "\n".
            '    <div>' . "\n".
            '        <div>Welcome Joe!</div>' . "\n".
            '    </div>' . "\n".
            '</header>' . "\n".
            '';
        $data[] = array($html, $expect);

        // --------
         $html = '<body>'
               . 'test123'
               .'    <module models="default.json">common/header</module>'
               .'    <div class="test">aa</div>'
               .'    <script>var s = "1";</script>'
               .'</body>' ;
        $expect = "" . 
            'test123    ' . "\n" .
            '<header class="template-header">' . "\n".
            '    <div>' . "\n".
            '        <div>Welcome Joe!</div>' . "\n".
            '    </div>' . "\n".
            '</header>' . "\n".
            "\n    \n".
            '<div class="test">' . "\n" .
            '    aa' . "\n" .
            '</div>' . "\n    \n".
            '<script>' . "\n" .
            '    var s = "1";' . "\n" . 
            '</script>' .
            '';
        $data[] = array($html, $expect);

        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestRenderBody
     */
    public function testRenderBody($html, $expect) {/*{{{*/

        $dom = new DOMDocument();
        $dom->loadXML($html);
        $body = $dom->getElementsByTagName("body");
        $result = $this->tester->renderBody($body->item(0), "");
        //echo "result = ";print_r($result);
        $this->assertEquals($expect, $result);
    }/*}}}*/


}
