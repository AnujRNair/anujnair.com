<?php
class PageController extends Zend_Controller_Action {

    protected $_auth;
    protected $_session = null;
    protected $_useragent = null;

    public function preDispatch() {
        $controller = $this->getRequest()->getControllerName();
        $module = $this->getRequest()->getModuleName();

        // Set the session to be available in all Controllers
        $this->_session = Zend_Registry::get('session');
        $this->_useragent = Zend_Registry::get('useragent');

        // Add specific JS and CSS to page
        $this->view->headScript()
            ->appendFile('/js/libs/jquery.js', 'text/javascript')
            ->appendFile('/js/libs/bootstrap.min.js', 'text/javascript')
            ->appendFile('/js/' . $module . '/site.js', 'text/javascript')
            ->appendFile('/js/' . $module . '/' . $controller . '.js', 'text/javascript');

        $this->view->headLink()
            ->appendStylesheet('/css/bootstrap.min.css')
            ->appendStylesheet('/css/' . $module . '/site.css')
            ->appendStylesheet('/css/' . $module . '/' . $controller . '.css');

        // Add page titles for front facing controllers
        $this->view->headTitle()->prepend(ucwords($controller == 'index' ? 'Home' : $controller));
        if (strtolower($module) == 'default') {
            switch (strtolower($controller)) {
                case 'index' :
                    $this->view->headTitle()->prepend('Anuj Nair\'s Portfolio 2009-' . date('Y') . ', Blog and Contact Information');
                    break;
                case 'about' :
                    $this->view->headTitle()->prepend('All About Anuj Nair');
                    break;
                case 'portfolio' :
                    $this->view->headTitle()->prepend('Anuj Nair\'s Web Design and Development Portfolio');
                    break;
                case 'blog' :
                    $this->view->headTitle()->prepend('Anuj Nair\'s Blog');
                    break;
                case 'links' :
                    $this->view->headTitle()->prepend('Links and Resources');
                    break;
                case 'contact' :
                    $this->view->headTitle()->prepend('Contact Anuj Nair');
                    break;
            }
        }

        // If in the Admin module, make sure we are authenticated!
        if ($module == 'admin') {
            $this->_helper->layout()->setLayout('admin');
            $this->_auth = Zend_Auth::getInstance();
            if(!$this->_auth->hasIdentity() && $this->getRequest()->getActionName() != 'login') {
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("login", "index", "admin", array());
            } else {
                $this->view->userInfo = $this->_auth->getStorage()->read();
            }
        }

    }

}