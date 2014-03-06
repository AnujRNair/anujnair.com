<?php

class ZendCustom_View_Helper_Part extends Zend_View_Helper_Abstract {

    public function part($module, $controller, $partial, $params = array()) {
        return $this->view->partial($controller . '/partials/' . $partial . '.phtml', $module, $params);
    }

}