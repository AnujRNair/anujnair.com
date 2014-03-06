<?php
class ZendCustom_Controller_Plugin_ActiveModule 
	extends Zend_Controller_Plugin_Abstract
{
 
	public function routeShutdown(Zend_Controller_Request_Abstract $request) { 
		$activeModuleName = preg_replace('/[^a-zA-Z0-9]+/', '', $request->getModuleName());
		if ($activeModuleName == null) {
			$activeModuleName = 'default';
		}
		$activeBootstrap = $this->_getActiveBootstrap($activeModuleName);
		if ($activeBootstrap instanceof ZendCustom_Application_Module_Bootstrap) {
			$className = ucfirst($activeModuleName) . '_Bootstrap_Initializer';
			// don't assume that every module has an initializer...
			if (class_exists($className)) {
				$intializer = new $className($activeBootstrap);
				$intializer->initialize();
			}
		}
	}
 
	/**
	 * return the default bootstrap of the app
	 * @return Zend_Application_Bootstrap_Bootstrap
	 */
	protected function _getBootstrap() {
		$frontController = Zend_Controller_Front::getInstance();
		$bootstrap =  $frontController->getParam('bootstrap');
		return $bootstrap;
	}
 
	/**
	 * return the bootstrap object for the active module
	 * @return ZendCustom_Application_Module_Bootstrap
	 */
	public function _getActiveBootstrap($activeModuleName) {
		$moduleList = $this->_getBootstrap()->getResource('modules');
		if (isset($moduleList[$activeModuleName])) {
			return $moduleList[$activeModuleName];
		}
		return null;
	}
 
}