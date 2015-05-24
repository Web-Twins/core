<?php
class_exists('LightnCandy') || require __DIR__ . "/lightncandy.php";

class handlebar {
    public $tmpDir;
    public $cache = false;
    public $baseDir = "";
    public function __construct($path, $cache = false, $baseDir = "") 
    {
        if (empty($path)) {
            $this->tmpDir = "/tmp/template_cache";
        } else {
            $this->tmpDir = $path;
        }

        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir);
        }
        if ($cache) {
            $this->cache = $cache;
        }
        $this->baseDir = $baseDir;
    }

    public function render($template, $data) 
    {
        $html = file_get_contents($template);
        $dir = $this->tmpDir . '/' . dirname($template);
        $dir = $this->getRealPath($dir);

        $cache_file = $dir . '/'. basename($template, '.html') . '.php';
        if (!$this->cache || !is_file($cache_file)) {
            $php = LightnCandy::compile($html, Array('flags' => LightnCandy::FLAG_HANDLEBARSJS, "fileext" => "", "basedir" => $this->baseDir));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!file_put_contents($cache_file, $php)) {
                error_log("Save lightncandy file failed.");
            }
            $exec = include($cache_file);

        } else {
            $exec = include($cache_file);
        }

        if ($exec) {
            return $exec($data);
        }
        //$renderer = LightnCandy::prepare($php, $this->tmpDir);
        //return $renderer($data); 
    }

    public function getRealPath($path) {
        $real = "/";
        $p = explode("/", $path);
        $n = count($p);
        for ($i = 0; $i < $n; $i++) {
            if ($p[$i] === "..") {
                for ($j = $i - 1; $j >=0; $j--) {
                    if (!empty($p[$j])) {
                        $p[$j] = ""; break;
                    } 
                }
                $p[$i] = "";
            }
        }
        $real = "/" . implode("/", $p);
        $real = preg_replace("/[\/]+/", "/", $real);
        $real = preg_replace("/[\/]+$/", "", $real);
        return $real;
    }

}

