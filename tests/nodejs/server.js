var assert = require("assert");
var serverObj = require("./../../core/nodejs/server");
var root = __dirname + '/../../examples/';
var tester = new serverObj(root);

var cssResult = ""
var baseRes = {
    callback: null,
    write: function(text) {
        cssResult = text;
    },
    end: function() {
        if (this.callback) this.callback();
    },
    contentType: function() {}
};
var baseReq = {
};

describe("Test: Compile less", function () {
    before(function(done) {
        var req = baseReq;
        req["baseUrl"] = "/static/css/simple.less";
        baseRes.callback = done;
        tester.loadLess(req, baseRes);
    });

    it("simple less to css", function () {
        var expect = ".class {\n" +
                     "  width: \"2px\";\n" + 
                     "}\n";

        assert.equal(expect, cssResult);
    });
});

describe("Test: Compile less2", function () {

    it("file not found", function () {
        var req = baseReq;
        var expect = ".fileNotFound{}";
        req["baseUrl"] = "/xxxx.less";
        baseRes.callback = null;

        var result = tester.loadLess(req, baseRes);
        assert.equal(expect, cssResult);
    });

});


describe("Test: loadConfigPages", function () {

    it("simple", function () {
        var req = baseReq;
        req.path = "/common/homepage.html"; 
        baseRes.callback = null;
        var result = tester.loadConfigPages(req, baseRes);
        //console.log(result);

    });

});



