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


}
