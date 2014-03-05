<?php

abstract class Social_Model implements Social_Interface {

    protected $_blog = null;
    protected $_ip = null;
    protected $_socialId = 0;

    final public function __construct($blog, $ip) {
        $this->_blog = $blog;
        $this->_ip = $ip;
        if (!isset($this->_socialId) || $this->_socialId == 0) {
            throw new exception("Please define a Social ID in your Social Model");
        }
        Factory_Social::logSocialShare($this->_socialId, $this->_blog->blogId, $this->_ip);
    }

    final public function getView() {
        return Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
    }

    final public function getBaseUrl() {
        return $_SERVER['HTTP_HOST'];
    }

}