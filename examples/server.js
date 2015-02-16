var siteRoot = __dirname + "/../";
var server = new (require("./../server.js"))(siteRoot);

server.start("localhost", 8080);
