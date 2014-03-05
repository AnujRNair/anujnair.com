<?php
class Admin_Bootstrap_Initializer
    extends ZendCustom_Application_Module_Initializer
{

    protected function _initPlaceholders() {
        $view = $this->getBootstrap()->getResource('View');
        $view->doctype('HTML5');
        $view->headTitle('AnujNair.com')
             ->setSeparator(' :: ');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8')
                         ->appendName('description', 'AnujNair.com Administration Panel')
                         ->appendName('keywords', '')
                         ->appendName('robots', 'noindex, nofollow, noarchive')
                         ->appendName('author', 'Anuj Nair');
        $view->headLink()->headLink(array('rel' => 'icon',
                                          'type' => 'image/png',
                                          'href' => '/img/favicon.png'),
                                    'PREPEND')
                         ->headLink(array('rel' => 'shortcut icon',
                                          'type' => 'image/png',
                                          'href' => '/img/favicon.png'),
                                    'PREPEND');
    }

}