var assert = require("assert");
var serverObj = require("./../../core/nodejs/server");
var root = __dirname + '/../../examples/';
var tester = new serverObj(root);

var cssResult = ""
var baseRes = {
    write: function(text) {
        cssResult = text;
    },
    end: function() {},
    contentType: function() {}
};
var baseReq = {
};

describe("Test: Compile less", function () {
    it("simple less to css", function () {
        var req = baseReq;
        var expect = ".class {\n" +
                     "  width: \"2px\";\n" + 
                     "}\n";
        req["baseUrl"] = "/static/css/simple.less";

        var result = tester.loadLess(req, baseRes);
        assert.equal(expect, cssResult);
    });

    it("file not found", function () {
        var req = baseReq;
        var expect = ".fileNotFound{}";
        req["baseUrl"] = "/xxxx.less";

        var result = tester.loadLess(req, baseRes);
        assert.equal(expect, cssResult);
    });

});


