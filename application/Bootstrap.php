<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initAutoload() {
        Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
    }

    protected function _initControllerPlugins() {
        $plugin = Zend_Controller_Front::getInstance()->registerPlugin(
            new ZendCustom_Controller_Plugin_ActiveModule()
        );
    }

    protected function _initConfig() {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
    }

    protected function _initSession() {
        Zend_Registry::set('session', new Zend_Session_Namespace('anujnair', true));
    }

    protected function _initUserAgent() {
        Zend_Registry::set('useragent', new Zend_Http_UserAgent());
    }

    protected function _initMemcached() {
        $memcachedBackend = new ZendCustom_Cache_Backend_MemcachedExtended(
            array(
                'servers' => array(
                    array(
                        'host' => 'localhost',
                        'port' => 11211,
                        'persistent' => true,
                        'weight' => 1,
                        'timeout' => 5,
                        'retry_interval' => 15,
                        'status' => true
                    )
                ),
                'compression' => true
            )
        );
        $memcachedFrontend = new ZendCustom_Cache_CoreExtended(
            array(
                'caching' => true,
                'cache_id_prefix' => 'anujnair_',
                'lifetime' => 3600,
                'logging' => false,
                'write_control' => true,
                'automatic_serialization' => true,
                'automatic_cleaning_factor' => 10,
                'ignore_user_abort' => true
            )
        );
        $memcachedCache = Zend_Cache::factory($memcachedFrontend, $memcachedBackend);
        Zend_Registry::set('cache', $memcachedCache);
    }

    protected function _initDateLocale() {
        date_default_timezone_set('Europe/London');
        $locale = new Zend_Locale('en_GB');
        Zend_Registry::set('Zend_Locale', $locale);
        Zend_Date::setOptions(array(
            'format_type' => 'php',
            'cache' => Zend_Registry::get('cache')
        ));
    }

    protected function _initRoutes() {
        $blogArticle = new Zend_Controller_Router_Route_Regex(
            'blog/(\d+)(?:-([^/\.]+))?',
            array(
                'controller'=> 'blog',
                'action'    => 'article',
                'id'        => 0
            ),
            array(
                1 => 'id',
                2 => 'title'
            ),
            'blog/%d-%s'
        );
        $blogTags = new Zend_Controller_Router_Route_Regex(
            'blog/tag-(\d+)-([^/\.]+)',
            array(
                'controller'=> 'blog',
                'action'    => 'searchtag',
                'id'        => 0
            ),
            array(
                1 => 'id',
                2 => 'name'
            ),
            'blog/tag-%d-%s'
        );
        $blogPages = new Zend_Controller_Router_Route_Regex(
            'blog/page-(\d+)',
            array(
                'controller'=> 'blog',
                'action'    => 'index',
                'page'        => 1
            ),
            array(
                1 => 'page'
            ),
            'blog/page-%d'
        );
        $site = new Zend_Controller_Router_Route_Regex(
            'portfolio/(\d+)(?:-([^/]+))?',
            array(
                'controller'=> 'portfolio',
                'action'    => 'site',
                'id'        => 0
            ),
            array(
                1 => 'id',
                2 => 'name'
            ),
            'portfolio/%d-%s'
        );
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->addRoute('blogArticle', $blogArticle);
        $router->addRoute('blogTags', $blogTags);
        $router->addRoute('blogPages', $blogPages);
        $router->addRoute('site', $site);
    }

}