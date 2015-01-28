var assert = require("assert");
var layoutParser = new (require("./../../core/nodejs/layoutParser"));
var xml = require('./../../core/nodejs/node_modules/libxmljs');

describe("Test redner page html", function () {//{{{
    it("render page", function () {
        var page = "<page output=\"htmlPage\"><head><css>a.css</css></head></page>";

        var config = new xml.parseXml(page);
        var expect = "<!DOCTYPE html>\n<html>" +
                     "\n<head>" +
                     "\n" + '<link href="a.css" rel="stylesheet" type="text/css">' +
                     "\n</head>" + 
                     "\n</html>";
        var result = layoutParser.render(config);

        assert.equal(expect, result);

    });

    it("render default page setting", function () {
        var page = "<page><head></head></page>";

        var config = new xml.parseXml(page);
        var expect = "<!DOCTYPE html>\n<html>\n<head>\n</head>\n</html>"; 
        var result = layoutParser.render(config);

        assert.equal(expect, result);

    });



});//}}}

describe("Test redner head html", function () {//{{{

    it("render css", function () {
        var css = "<head><css>a.css\
                   b.css\
                  </css></head>";
        var config = new xml.parseXml(css);
        var expect = '<head>' + "\n" + '<link href="a.css" rel="stylesheet" type="text/css">' + "\n" 
                     + '<link href="b.css" rel="stylesheet" type="text/css">' + "\n"
                     + '</head>';
        var result = layoutParser.renderHead(config);

        assert.equal(expect, result);

    });

    it("render js", function () {
        var js = "<head><js>a.js\
                   b.js\
                  </js></head>";
        var config = new xml.parseXml(js);
        var expect = '<head>' +  "\n"
                     + '<script src="a.js"></script>' + "\n" 
                     + '<script src="b.js"></script>' + "\n"
                     + '</head>';
        var result = layoutParser.renderHead(config);

        assert.equal(expect, result);

    });

    it("render body", function () {
        var body = '<body><div class="grid">'
                   +'text1' 
                   +'<div class="col-2-3">'
                   + 'text2'
                   + '<module model="default.json">common/header/</module>'
                   + ' text3'
                   + '<script>'
                   +  'var a=4;'
                   +  'var b=1;'
                   + '</script>'
                   +'</div></div>'
                   +'</body>';

        body = new xml.parseXml(body);

        var expect = '<div class="grid">'+ "\n"
                      + '    text1' + "\n"
                      + '    <div class="col-2-3">' + "\n"
                      + '        text2' + "\n"
                      + '        <header class="template-header">' + "\n"
                      + '            <div>' + "\n"
                      + '                <div><img src="http://test" alt="logo" />Logo</div>' + "\n"
                      + '                <div>Xzzz 你好!</div>' + "\n"
                      + '            </div>' + "\n"
                      + '        </header>' + "\n\n"
                      + '         text3'  + "\n"
                      + '        <script>' + "\n"
                      + '            var a=4;var b=1;' + "\n"
                      + '        </script>' + "\n"
                      + '    </div>' + "\n"
                      + '</div>';
        layoutParser.output = 1;
        layoutParser.enableIndent = true; 
        var result = layoutParser.renderBody(body, "");
        console.log(result);
        assert.equal(expect, result);

    });


});//}}}





