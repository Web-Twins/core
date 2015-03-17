<?php


class moduleObj {
    public $root;
    public $templateBasePath;
    public $context;
    public $templateEngine = "handlebars";

    public function __construct($root, $context) {

        $this->root = $root;
        $this->templateBasePath = $root . '/modules';
        $this->context = $context;

    }

    public function getModuleInfo($module) {//{{{
        $info = array();
        $modulePath = $module['value'];
        $info["modulePath"] = $modulePath;
        $matches = preg_split('/\//', $modulePath);
        if (isset($matches[1])) {
            $info["moduleName"] = $matches[1];
        } else {
            $info["moduleName"] = $modulePath;
        }
        $info["moduleFullPath"] = $this->templateBasePath . '/' . $info['modulePath'];
        return $info;
    }//}}}

    public function getModel($path) {//{{{
        $extName = "";
        $fullPath = $this->templateBasePath . '/' . $path;

        if (!is_file($fullPath)) {
            error_log($fullPath . " is not exist.");
            return "";
        }

        $data = file_get_contents($fullPath);
        if ($data) {
            $splitByDot = preg_split('/\./', $path);
            $length = count($splitByDot);
            if ($length > 0) {
                $extName = $splitByDot[$length -1];
            }

            $extName = strtolower($extName);
            if (
                $extName === "yaml" 
                || $extName === "yml"
               ) {
                $data = yaml_parse($data);
            } else {
                $data = json_decode($data, true);
            }
        }
        return $data;
    }//}}}

    public function getTemplate($path) {//{{{
        $split = preg_split('/\//', $path);
        $length = count($split);

        if (!empty($split[$length - 1])) {
            $name = $split[$length - 1];
        } else {
            $name = $split[$length - 2];
        }

        if (empty($name)) return "";

        switch ($this->templateEngine) {
            case 'handlebars':
                $fullPath = $this->root . '/' . $path . '/views/' . $name . ".hb.html";
                break;
        }

        //error_log("Template full path = " + fullPath);
        if (!is_file($fullPath)) {
            error_log($fullPath . " is not exist.");
        }
        $html = file_get_contents($fullPath);
        return $html;

    }//}}}

    public function getCssPath($module) {//{{{
        $css = array();
        $info = $this->getModuleInfo($module);

        $path = $info['moduleFullPath'] . '/static/' . $info['moduleName'] . '.less';
        if (is_file($path)) {
            $css['path'] = $path;
            $css['urlPath'] = $this->context['baseConfig']['urlPaths']['template'] . '/' . $info['modulePath'] . '/static/' . $info['moduleName'] . '.less' ;
            $css['id'] = urlencode($path);
        } else {

            $path = $info['moduleFullPath'] . '/static/' . $info['moduleName'] . '.css';
            if (is_file($path)) {
                $css['path'] = $path;
                $css['urlPath'] = $this->context['baseConfig']['urlPaths']['template'] . '/' . $info['modulePath'] . '/static/' . $info['moduleName'] . '.css' ;
                $css['id'] = urlencode($path);
            }
        }
        return $css;
    }//}}}

    public function render($config) {//{{{

        //var modelName, modelPath, templateName, templatePath, templateHtml, model,  template, self;

        $self = $this;
        $templatePath = $config.nodeValue;

        $modelName = "default.json";
        if (config.attributes && config.attributes["model"]) {
            $modelName = config.attributes["model"];
        }


        $modelPath =  $templatePath . "/models/" . $modelName;
        $model = $this->getModel($modelPath);
        $templateHtml = $this->getTemplate('modules/' . $templatePath);

        //lightncandy
        $templateHtml = $templateHtml.replace(/\{\{[\s]?\>[\s]+([^\}]+)\}\}/m, function (str, fileName) {
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


}

