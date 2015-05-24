<?php

require_once __DIR__ . "/../../../core/php/handlebar/template.php";

class testTemplate extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->tester = new handlebar("/tmp");
    }


    public function tearDown() {
        $this->tester = null;
    }

    public function providerTestGetRealPath() {/*{{{*/
        $data = array();

        $data[] = array(
            "/a/b",
            "/a/b/c/../"
        );
        $data[] = array(
            "/a/b",
            "/a/b///c/../d/d/../../"
        );
        $data[] = array(
            "/a/b/f",
            "/a/z/../b///c/../d/d/../../f"
        );

        $data[] = array(
            "/a/b/f.php",
            "/a/z/../b///c/../d/d/../../f.php"
        );



        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerTestGetRealPath
     */
    public function testGetRealPath($expect, $path) {/*{{{*/
        $result = $this->tester->getRealPath($path);
        //echo "-" ; print_r($result); echo "-";
        $this->assertEquals($expect, $result, '');
    }/*}}}*/

}
