var assert = require("assert");
var moduleObj = require("./../../core/nodejs/module");
var xml = require('libxmljs');
var root = __dirname + '/../../examples/';
var tester = new moduleObj(root);
var baseConfig = {
    "urlPaths": {
        "template": "/template"
    }
};
tester.context = {
    baseConfig: baseConfig
};
describe("Test: getCssPath", function () {
    it("get less path", function () {
        var moduleHtml = '<t><module>common/header</module></t>';
        var config = new xml.parseXml(moduleHtml);
        var module = config.get('//module');
        var expect = "header.less";
        var result = tester.getCssPath(module);
        var split = result['path'].split(/\//);
        //console.log(result);
        assert.equal(expect, split[split.length - 1]);
        assert.equal('/template/common/header/static/' + expect, result['urlPath']);
    });

    it("get css path", function () {
        var moduleHtml = '<t><module>common/meta</module></t>';
        var config = new xml.parseXml(moduleHtml);
        var module = config.get('//module');
        var expect = "meta.css";
        var result = tester.getCssPath(module);
        var split = result['path'].split(/\//);
        assert.equal(expect, split[split.length - 1]);
        assert.equal('/template/common/meta/static/' + expect, result['urlPath']);

    });



});


