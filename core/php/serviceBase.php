<?php

class serviceBase {

    public function getContentTypeAndPath($ext) {
        switch ($ext) {
            case "less": case "css":
                return array("text/css", "css");
                break;
            case "js":
                return array("application/javascript", "js");
                break;
            default:
                return array("text/plain", "");
                break;
        }
    }

}
