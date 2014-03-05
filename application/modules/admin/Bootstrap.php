<?php

class Admin_Bootstrap extends ZendCustom_Application_Module_Bootstrap
{

    protected function _initAutoload() {
        set_include_path(get_include_path() . PATH_SEPARATOR . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .'admin' . DIRECTORY_SEPARATOR . 'controllers');
        set_include_path(get_include_path() . PATH_SEPARATOR . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .'admin' . DIRECTORY_SEPARATOR . 'models');
    }

}