<?php

class ZendCustom_View_Helper_UrlTitle extends Zend_View_Helper_Abstract {

    public function urltitle($title) {
        return preg_replace('/\s+/', '-', $title);
    }

}