<?php
class Default_Bootstrap_Initializer
    extends ZendCustom_Application_Module_Initializer
{

    protected function _initPlaceholders() {
        $view = $this->getBootstrap()->getResource('View');
        $view->doctype('HTML5');
        $view->headTitle('AnujNair.com')
             ->setSeparator(' :: ');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8')
                         ->appendName('description', 'Welcome to AnujNair.com, a place to view my 2009-' . date('Y') . ' portfolio, visit my blog and contact me')
                         ->appendName('keywords', 'Anuj Nair, Web Developer, Web Designer, Mobile Development, Search Engine Optimization, SEO, PHP, CSS, JavaScript, London, UK')
                         ->appendName('robots', 'index, follow')
                         ->appendName('author', 'Anuj Nair')
                         ->appendName('viewport', 'width=device-width, initial-scale=1.0');
        $view->headLink()->headLink(array('rel' => 'icon',
                                          'type' => 'image/ico',
                                          'href' => '/favicon.ico'),
                                    'PREPEND')
                         ->headLink(array('rel' => 'shortcut icon',
                                          'type' => 'image/ico',
                                          'href' => '/favicon.ico'),
                                    'PREPEND');
    }

}