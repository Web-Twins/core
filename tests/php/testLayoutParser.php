<?php

require_once __DIR__ . "/../../core/php/layoutParser.php";

class testLayoutParser extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->tester = new layoutParser(array(), "", "");
    }


    public function tearDown() {
        $this->tester = null;
    }

    public function testRenderCss() {
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
    }

    /**
     * @dataProvider provider_testRenderHead
     */
    public function testRenderHead($html, $expect) {/*{{{*/
        $d = new DOMDocument();
        $d->loadXML($html);
        $config = $d->getElementsByTagName("head");
        $result = $this->tester->renderHead($config->item(0));
        //echo "-" ; print_r($result); echo "-";
        $this->assertEquals($expect, $result, '');
    }/*}}}*/

    public function provider_testRenderHead() {
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
    }


}
