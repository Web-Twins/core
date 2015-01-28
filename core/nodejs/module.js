var php = require('phplike/module');
var Handlebars = require('handlebars');
var root = __dirname + "/../../";

function moduleObj () {


}

var o = moduleObj.prototype;

o.templateEngine = "handlebars";

o.render = function (config) {

    var modelName, modelPath, templateName, templatePath, templateHtml, model,
        template;
    templatePath = config.text();
    modelName = config.attr('model').value();

    modelPath = 'templates/' + templatePath + "/models/" + modelName;

    model = this.getModel(modelPath);
    templateHtml = this.getTemplate('templates/' + templatePath);

    template = Handlebars.compile(templateHtml);

    return template(model);

};


o.getModel = function (path) {
    var html;
    html = php.file_get_contents(root + path);
    html = php.json_decode(html);
    return html;
};


o.getTemplate = function (path) {
    var html, fullPath, name, split;
    split = path.split('/');
    if (!php.empty(split[split.length - 1])) {
        name = split[split.length - 1];
    } else {
        name = split[split.length - 2];
    }

    if (php.empty(name)) return "";

    switch (this.templateEngine) {
        case 'handlebars':
            fullPath = root + path + 'views/' + name + ".hb.html";
            break;
    }
    html = php.file_get_contents(fullPath);
    return html;

}

module.exports = moduleObj;
