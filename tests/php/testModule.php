<?php

require_once __DIR__ . "/../../core/php/module.php";

class testModule extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $urlPaths = array(
                     'template' => __DIR__,
                    );
        $context = array(
                    "baseConfig" => array(
                                     'urlPaths' => $urlPaths,
                                    )
                   );
        
        $this->tester = new moduleObj("data", $context);
    }


    public function tearDown() {
        $this->tester = null;
    }


    public function providerGetModuleInfo() {/*{{{*/
        $data = array();

        $data[] = array(
            array("value" => "common/test"),
            array(
             'moduleFullPath' => 'data/modules/test',
             'modulePath' => 'common/test',
             'moduleFullPath' => 'data/modules/common/test',
             'moduleName' => 'test',
            ),
        );



        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerGetModuleInfo
     */
    public function testGetModuleInfo($module, $expect) {/*{{{*/

        $result = $this->tester->getModuleInfo($module);
        //print_r($result);
        $this->assertEquals($expect, $result, '');
    }/*}}}*/

    public function providerGetModel() {/*{{{*/
        $data = array();
        $data[] = array(
                   "test.yml",
                   array("page" => array("value" => 10)),
                  );

        $data[] = array(
                   "test.json",
                   array("page" => array("value" => 10)),
                  );

        $data[] = array(
                   "test.jsonxxx",
                   "",
                  );


        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerGetModel
     */
    public function testGetModel($file, $expect) {/*{{{*/

        $result = $this->tester->getModel($file);
        //print_r($result);
        $this->assertEquals($expect, $result, '');
    }/*}}}*/

    public function providerGetTemplate() {/*{{{*/
        $data = array();
        $data[] = array(
                   "modules/test",
                   "<div>test</div>\n",
                  );
        return $data;
    }/*}}}*/


    /**
     * @dataProvider providerGetTemplate
     */
    public function testGetTemplate($file, $expect) {/*{{{*/

        $result = $this->tester->getTemplate($file);
        print_r($result);
        $this->assertEquals($expect, $result, '');

    }/*}}}*/

    public function providerGetCssPath() {/*{{{*/
        $data = array();
        $data[] = array(
                   array("value" => "test"),
                   array(
                        'path' => 'data/modules/test/static/test.less',
                        'urlPath' => '/www/dev/twins/tests/php/test/static/test.less',
                        'id' => 'data%2Fmodules%2Ftest%2Fstatic%2Ftest.less',
                   ),
                  );

        $data[] = array(
                   array("value" => "test2"),
                   array(
                        'path' => 'data/modules/test2/static/test2.css',
                        'urlPath' => '/www/dev/twins/tests/php/test2/static/test2.css',
                        'id' => 'data%2Fmodules%2Ftest2%2Fstatic%2Ftest2.css',
                   ),
                  );

        return $data;
    }/*}}}*/

    /**
     * @dataProvider providerGetCssPath
     */
    public function testGetCssPath($module, $expect) {/*{{{*/

        $result = $this->tester->getCssPath($module);
        //print_r($result);
        $this->assertEquals($expect, $result, '');

    }/*}}}*/

}
