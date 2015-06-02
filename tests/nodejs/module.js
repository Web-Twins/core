var php = require('phplike/module');
var assert = require("assert");
var moduleObj = require("./../../core/nodejs/module");
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
        var dom = new php.DOMDocument();
        var moduleHtml = '<t><module>common/header</module></t>';
        var config = dom.loadXML(moduleHtml);
        var module = config.childNodes[0];
        var expect = "header.less";
        var result = tester.getCssPath(module);
        var split = result['path'].split(/\//);
        //console.log(result);
        assert.equal(expect, split[split.length - 1]);
        assert.equal('/template/common/header/static/' + expect, result['urlPath']);
    });

    it("get css path", function () {
        var dom = new php.DOMDocument();
        var moduleHtml = '<t><module>common/meta</module></t>';
        var config = dom.loadXML(moduleHtml);
        var module = config.childNodes[0];
        var expect = "meta.css";
        var result = tester.getCssPath(module);
        var split = result['path'].split(/\//);
        assert.equal(expect, split[split.length - 1]);
        assert.equal('/template/common/meta/static/' + expect, result['urlPath']);

    });

});


describe("Test: get models", function () {
    it("load json file", function () {
        var modelFilePath = "common/meta/models/default.json";
        var result = tester.getModel(modelFilePath);
        //console.log(result);
        assert.equal("Web Twins", result['title']);
    });

    it("load yaml file", function () {
        var modelFilePath = "common/meta/models/default.yaml";
        var result = tester.getModel(modelFilePath);
        //console.log(result);
        assert.equal("Web Twins", result['title']);
    });

    it("no file extension (The default extension is json format)", function () {
        var modelFilePath = "common/meta/models/default";
        var result = tester.getModel(modelFilePath);
        //console.log(result);
        assert.equal("Web Twins", result['title']);
    });


});
describe("Test: Render", function () {
    it("Normal Handlebars templates", function () {
        var config = {}, expect;
        config['value'] = "common/header";
        expect = '<header class="template-header">' + "\n"
                +"    <div>\n"
                +"        <div>Welcome Joe!</div>\n"
                +"    </div>\n"
                +"</header>\n";
        tester.templateEngine = "handlebars";
        var result = tester.render(config);
        //console.log(result);
        assert.equal(expect, result);
    });

    it("Normal Jade templates", function () {
        var config = {}, expect;
        config['value'] = "common/header";
        expect = '<header class="template-header"><div><div>Welcome Joe!</div></div></header>';
        tester.templateEngine = "jade";
        var result = tester.render(config);
        //console.log(result);
        assert.equal(expect, result);
    });

});

