<?php
require_once __DIR__ . "/serviceBase.php";
class twinsService extends serviceBase {
    public $pathStatic;
    public $isLess;

    public function __construct($config) {
        if (!isset($config['pathStatic'])) {
            $errMsg = "Please set the static path, when you construct thw object twinsService";
            error_log($errMsg);
            throw new Exception($errMsg);
        }
        $this->pathStatic = $config['pathStatic'];
    }

    public function fetchComboFiles() {/*{{{*/
        $allowedExt = array("less", "css", "js");
        $files = array_keys($_GET);

        $file = $files[0];
        $pos = strrpos($file, '_');
        if ($pos <= 0) return "";
        $finalExt = $ext = strtolower(substr($file, $pos + 1, strlen($file) - $pos - 1));
        if ($ext === "less") {
            $this->isLess = true;
            $finalExt = "css";
        }
        list($contentType, $fileTypeDir) = $this->getContentTypeAndPath($ext);

        if (!in_array($ext, $allowedExt)) return "";
        $extLen = strlen($ext);
        header("content-type: $contentType");
        $basePath = $this->pathStatic . "/" . $fileTypeDir . "/";
        foreach ($files as $file) {
            $file = preg_replace('/[\.]{2,}/', '', $file);
            $file = str_replace(chr(0), '', $file);
            $file = substr($file, 0, strlen($file) - $extLen - 1) . '.' . $finalExt;
            $path = $basePath . $file;
            if (is_file($path)) {
                echo file_get_contents($path);
            }
        }
    }/*}}}*/

}
