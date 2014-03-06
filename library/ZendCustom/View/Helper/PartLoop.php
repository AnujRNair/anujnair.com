<?php

class ZendCustom_View_Helper_PartLoop extends Zend_View_Helper_Abstract {

    public function partloop($module, $controller, $partial, $objName, $params = array()) {
        $this->view->partialLoop()->setObjectKey($objName);
        return $this->view->partialLoop($controller . '/partials/' . $partial . '.phtml', $module, $params);
    }

}