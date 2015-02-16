var php = require('phplike/module');
var Handlebars = require('handlebars');

function moduleObj (root) {

    this.root = root;
}

var o = moduleObj.prototype;
o.root = "";

o.templateEngine = "handlebars";

o.render = function (config) {

    var modelName, modelPath, templateName, templatePath, templateHtml, model,  template, moduleNode, self;

    self = this;
    templatePath = config.text();
    modelNode = config.attr('model');
    if (modelNode) {
        modelName = modelNode.value();
    } else {
        modelName = "default.json";
    }

    modelPath = 'modules/' + templatePath + "/models/" + modelName;

    model = this.getModel(modelPath);
    templateHtml = this.getTemplate('modules/' + templatePath);


    templateHtml = templateHtml.replace(/\{\{[\s]?\>[\s]+([^\}]+)\}\}/m, function (str, fileName) {
        var path;
        path = self.root + '/' + 'modules/' + templatePath + "/views/" + fileName;
        if (php.is_file(path)) {
            return php.file_get_contents(path);
        }

        return "File " + str + "Not Found";
    });

    template = Handlebars.compile(templateHtml);

    return template(model);

};


o.getModel = function (path) {
    var html, fullPath;
    fullPath = this.root + '/' + path;
    if (!php.is_file(fullPath)) {
        console.log(fullPath + " is not exist.");
        return "";
    }

    html = php.file_get_contents(fullPath);
    if (html) {
        html = php.json_decode(html);
    }
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
            fullPath = this.root + '/' + path + '/views/' + name + ".hb.html";
            break;
    }
    //console.log("Template full path = " + fullPath);
    if (!php.is_file(fullPath)) {
        console.log(fullPath + " is not exist.");
    }
    html = php.file_get_contents(fullPath);
    return html;

}

module.exports = moduleObj;
