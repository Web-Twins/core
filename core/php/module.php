<?php

var Handlebars = require('handlebars');
var util = new (require('./util'))();
var checksum = require('checksum');

function moduleObj (root, context) {

    this.root = root;
    this.templateBasePath = root + '/modules';
    this.context = context;
}

var o = moduleObj.prototype;
o.root = "";

o.context = {};
o.templateEngine = "handlebars";
o.templateBasePath = "";

o.moduleList = {
    yaml: ""
};

o.render = function (config) {//{{{

    var modelName, modelPath, templateName, templatePath, templateHtml, model,  template, self;

    self = this;
    templatePath = config.value;


    modelName = "default.json";
    if (config.attributes && config.attributes["model"]) {
        modelName = config.attributes["model"];
    }


    modelPath =  templatePath + "/models/" + modelName;
    model = this.getModel(modelPath);
    templateHtml = this.getTemplate('modules/' + templatePath);


    templateHtml = templateHtml.replace(/\{\{[\s]?\>[\s]+([^\}]+)\}\}/m, function (str, fileName) {
        var path;
        path = self.templateBasePath + '/' + templatePath + "/views/" + fileName;
        if (php.is_file(path)) {
            return php.file_get_contents(path);
        }

        return "File " + str + "Not Found";
    });

    template = Handlebars.compile(templateHtml);

    return template(model);

};//}}}


o.getModel = function (path) {//{{{
    var data, fullPath, splitByDot, extName = "";
    fullPath = this.templateBasePath + '/' + path;

    if (!php.is_file(fullPath)) {
        console.log(fullPath + " is not exist.");
        return "";
    }

    data = php.file_get_contents(fullPath);
    if (data) {
        splitByDot = path.split(/\./);
        if (splitByDot.length > 0) {
            extName = splitByDot[splitByDot.length -1];
        }
        if (extName.toLowerCase() === "yaml") {
            if (!this.moduleList.YAML) {
                this.moduleList.YAML = require('yamljs'); 
            }
            data = this.moduleList.YAML.parse(data);
        } else {
            data = php.json_decode(data);
        }
    }
    return data;
};//}}}


o.getTemplate = function (path) {//{{{
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

};//}}}

o.getCssPath = function (module) {//{{{
    var path, info, css;
    css = {};
    info = this.getModuleInfo(module);
    path = info['moduleFullPath'] + '/static/' + info.moduleName + '.less';
    if (php.is_file(path)) {
        css.path = path;
        css.urlPath = this.context.baseConfig.urlPaths.template + '/' + info.modulePath + '/static/' +info.moduleName + '.less' ;
        css.id = checksum(path);
    } else {

        path = info['moduleFullPath'] + '/static/' + info.moduleName + '.css';
        if (php.is_file(path)) {
            css.path = path;
            css.urlPath = this.context.baseConfig.urlPaths.template +'/' + info.modulePath + '/static/' +info.moduleName + '.css' ;
            css.id = checksum(path);
        }
    }
    return css;

};//}}}

o.getModuleInfo = function (module) {//{{{
    var info = {}, modulePath, matches;
    modulePath = module.value;
    info["modulePath"] = modulePath;
    matches = modulePath.split(/\//);
    if (matches && matches[1]) {
        info["moduleName"] = matches[1];
    } else {
        util.log('ERROR_MODULE_MISSING_NAME','error');
    }
    info["moduleFullPath"] = this.templateBasePath + '/' + info.modulePath;
    return info;
};//}}}

module.exports = moduleObj;
