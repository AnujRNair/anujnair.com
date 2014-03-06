<?php

class ZendCustom_View_Helper_Route extends Zend_View_Helper_Abstract {

    public static function route($module, $controller, $action = null, $urlOptions = array()) {
        $urlOptions['module'] = $module;
        $urlOptions['controller'] = $controller;
        $urlOptions['action'] = $action;
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble($urlOptions, 'default', true, true);
    }

}