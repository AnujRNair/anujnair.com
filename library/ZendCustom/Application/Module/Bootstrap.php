<?

class ZendCustom_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function __construct($application) {
        parent::__construct($application);
        $this->_loadModuleConfig();
        $this->_loadInitializer();
    }

    protected function _loadModuleConfig() {
        // would probably better to use
        // Zend_Controller_Front::getModuleDirectory() ?
        $configFile = APPLICATION_PATH
            . '/modules/'
            . strtolower($this->getModuleName())
            . '/configs/module.ini';
        if (!file_exists($configFile)) {
            return;
        }
        $config = new Zend_Config_Ini($configFile, $this->getEnvironment());
        $this->setOptions($config->toArray());
    }

    public function _loadInitializer() {
        $this->getResourceLoader()->addResourceType(
            'Bootstrap_Initializer', 'bootstrap', 'Bootstrap'
        );
    }

}