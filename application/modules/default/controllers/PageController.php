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

        // Add specific JS and CSS to page. Offset 2, 3, 4 left for other jQuery dependant plugins
        $this->view->headScript()
            ->offsetSetFile(0, '/js/libs/jquery.js', 'text/javascript')
            ->offsetSetFile(1, '/js/libs/bootstrap.min.js', 'text/javascript')
            ->offsetSetFile(5, '/js/' . $module . '/site.js', 'text/javascript')
            ->offsetSetFile(6, '/js/' . $module . '/' . $controller . '.js', 'text/javascript');

        $this->view->headLink()
            ->appendStylesheet('/css/libs/bootstrap.min.css')
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