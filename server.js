var server = new (require("./core/nodejs/server.js"))(__dirname);

server.start("localhost", 8080);
