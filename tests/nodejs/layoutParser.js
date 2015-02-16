var assert = require("assert");
var layoutParserObj = require("./../../core/nodejs/layoutParser");
var xml = require('libxmljs');
var root = __dirname + '/../../examples/';
var layoutParser = new layoutParserObj({}, root);


describe("Test redner page html: ", function () {//{{{
    it("render page", function () {
        var page = "<page output=\"htmlPage\"><head><css>a.css</css></head></page>";

        var config = new xml.parseXml(page);
        var expect = "<!DOCTYPE html>\n<html>" +
                     "\n<head>\n" +
                     '    <link href="a.css" rel="stylesheet" type="text/css">' + "\n" + 
                     "</head>\n" + 
                     "</html>";
        var result = layoutParser.render(config);

        assert.equal(expect, result);

    });

    it("render default page setting", function () {
        var page = "<page><head></head></page>";

        var config = new xml.parseXml(page);
        var expect = "<!DOCTYPE html>\n<html>\n<head>\n\n</head>\n</html>"; 
        var result = layoutParser.render(config);

        assert.equal(expect, result);

    });

    it("render js in bottom of body", function () {
        var page = "<page><head></head><body><js>angular.js</js></body></page>";

        var config = new xml.parseXml(page);
        var expect = "<!DOCTYPE html>\n<html>\n<head>\n\n</head>\n" +
                     "<body>" + "\n" + 
                     "    <script src=\"angular.js\"></script>" + "\n" +
                     "</body>" + "\n" + 
                     "</html>"; 
        
        var result = layoutParser.render(config);
        //console.log(result);
        assert.equal(expect, result);

    });



});//}}}

describe("Test redner head html:", function () {//{{{

    it("render css", function () {
        var css = "<head><css>a.css\
                   b.css\
                  </css></head>";
        var config = new xml.parseXml(css);
        var expect = '    <link href="a.css" rel="stylesheet" type="text/css">' + "\n" 
                     + '    <link href="b.css" rel="stylesheet" type="text/css">';
        var result = layoutParser.renderHead(config);

        assert.equal(expect, result);

    });

    it("render js", function () {
        var js = "<head><js>a.js\
                   b.js\
                  </js></head>";
        var config = new xml.parseXml(js);
        var expect = '    <script src="a.js"></script>' + "\n" 
                     + '    <script src="b.js"></script>';
        var result = layoutParser.renderHead(config);

        assert.equal(expect, result);

    });

    it("render js and css in any position of body", function () {
        var js = "<page><head>"+
                    "<css>head.css</css>" + 
                    "<css position='body'>body.css</css>" + 
                    "<css position='bottomOfBody'>bottomOfBody.css</css>" + 
                    "<js position='topOfBody'>top_of_body.js</js>" + 
                    "<js position='body'>body.js</js>" +
                    "<js position='bottomOfBody'>bottomOfBody.js</js>" +
                    "<js position='afterBody'>afterBody.js</js>" +
                    "<js>head.js</js>" +
                 "</head><body>test</body></page>";

        var config = new xml.parseXml(js);
        var expect = '<!DOCTYPE html>' + "\n" +
                     "<html>\n" +
                     "<head>\n" +
                     '    <link href="head.css" rel="stylesheet" type="text/css">' + "\n" +

                     "    <script src=\"head.js\"></script>\n" + 
                     "</head>\n" +
                     "<body>\n" +
                     '    <link href="body.css" rel="stylesheet" type="text/css">' + "\n" +
                     "    <script src=\"top_of_body.js\"></script>\n" +
                     "    test\n" +
                     '    <link href="bottomOfBody.css" rel="stylesheet" type="text/css">' + "\n" +
                     "    <script src=\"body.js\"></script>\n" +
                     "    <script src=\"bottomOfBody.js\"></script>\n" +
                     "</body>\n" +
                     "    <script src=\"afterBody.js\"></script>\n" + 
                     "</html>";
 
        var result = layoutParser.render(config);
        //console.log(result);
        assert.equal(expect, result);

    });


    it("should render body correctly", function () {
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
                      + '                <div>Welcome Joe!</div>' + "\n"
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
        //console.log(result);
        assert.equal(expect, result);

    });


});//}}}

describe("Test redner site html: ", function () {//{{{
    it("render site css", function () {
        var page = "<page output=\"htmlPage\"><head><css>a.css</css></head></page>";
        var site = "<site><head><css>reset.css</css></head></site>";
        var config = new xml.parseXml(page);
        var siteConfig = new xml.parseXml(site);


        var expect = "<!DOCTYPE html>\n<html>" +
                     "\n<head>" +
                     "\n" + '    <link href="reset.css" rel="stylesheet" type="text/css">' +
                     "\n" + '    <link href="a.css" rel="stylesheet" type="text/css">' +
                     "\n</head>" + 
                     "\n</html>";
        var result = layoutParser.render(config, siteConfig);
        assert.equal(expect, result);

    });

    it("render site js", function () {
        var page = "<page output=\"htmlPage\"><head><css>a.css</css></head></page>";
        var site = "<site><head><js>jquery.js</js></head></site>";
        var config = new xml.parseXml(page);
        var siteConfig = new xml.parseXml(site);


        var expect = "<!DOCTYPE html>\n<html>" +
                     "\n<head>\n" +
                     '    <script src="jquery.js"></script>' + "\n" +
                     '    <link href="a.css" rel="stylesheet" type="text/css">' + "\n" +
                     "</head>\n" + 
                     "</html>";
        var result = layoutParser.render(config, siteConfig);
        //console.log(result);
        assert.equal(expect, result);

    });

    it("Render site meta module in head.", function () {
        var page = "<page output=\"htmlPage\"><head></head></page>";
        var site = "<site><head><module model=\"default.json\">common/meta</module></head></site>";
        var config = new xml.parseXml(page);
        var siteConfig = new xml.parseXml(site);


        var expect = "<!DOCTYPE html>\n<html>\n" +
                     "<head>\n" +
                     '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' + "\n" +
                     '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' + "\n" +
                     '    <META HTTP-EQUIV="CONTENT-LANGUAGE" CONTENT="zh-tw" />'  + "\n" +
                     '    <META NAME="AUTHOR" CONTENT="" />'  + "\n" +
                     '    <title>Web Twins</title>'  + "\n" +
                     '    <meta name="keywords" content="Web Development, Node.js, Local development, Template" />'  + "\n" +
                     '    <meta name="description" content="Web Twins description" />'  + "\n" +
                     '    <meta http-equiv="Pragma" content="no-cache" />'  + "\n" +
                     '    <meta http-equiv="Cache-Control" content="private" />'  + "\n" +
                     "\n\n"+
                     '</head>'  +  "\n" +
                     "</html>";
        var result = layoutParser.render(config, siteConfig);
        assert.equal(expect, result);

    });

    it("Render site header and footer module in head.", function () {
        var page = "<page output=\"htmlPage\"><body>pagebody</body></page>";
        var site = "<site><body><header>test</header><footer>test_footer</footer></body></site>";
        var config = new xml.parseXml(page);
        var siteConfig = new xml.parseXml(site);
        var layoutParser = new layoutParserObj({}, root);
        var expect = "<!DOCTYPE html>\n<html>\n" +
                     "<body>\n" +
                     '    test' + "\n" +
                     "    pagebody"+ "\n" + 
                     '    test_footer' + "\n" + 
                     '</body>'  +  "\n" +
                     "</html>";
        var result = layoutParser.render(config, siteConfig);
        //console.log(result);
        assert.equal(expect, result);

    });

});//}}}





