<?php

/**
 * Description of eZPublish
 *
 * @author gmorel
 */
class eZPublish {
    
    CONST DIRECTORY_CLASSES = 'classes';
    CONST DIRECTORY_CRONJOBS = 'cronjobs';
    CONST DIRECTORY_DESIGN = 'design';
    CONST DIRECTORY_MODULE = 'modules';
    CONST DIRECTORY_SETTINGS = 'settings';
    CONST DIRECTORY_TESTS = 'tests';
    CONST DIRECTORY_TRANSLATIONS = 'translations';
    
    CONST DIRECTORY_TEMPLATES = 'templates';
    CONST DIRECTORY_CONTENT = 'content';
    
    CONST FILE_MODULE_PHP = 'module.php';
    
    protected $_extension_name = null;
    protected $_module_name = null;
    
    public function __construct($extension_name, $module_name) {
        $this->_extension_name = $extension_name;
        $this->_module_name = $module_name;
    }
    
    /**
     * Return the path where .tpl have to be put
     * @return string path 
     */
    public function getTemplatesPath()
    {
        return '/' . $this->_extension_name . '/' . self::DIRECTORY_DESIGN . '/standard/' . self::DIRECTORY_TEMPLATES . '/' . $this->_extension_name . '/' . $this->_module_name;
    }
    
    /**
     * Return the path where Classes have to be put
     * @return string path 
     */
    public function getClassesPath()
    {
        return '/' . $this->_extension_name . '/' . self::DIRECTORY_CLASSES;
    }
    
    /**
     * Return the path where controllers have to be put
     * @return string path 
     */
    public function getControllersPath()
    {
        return '/' . $this->_extension_name . '/' . self::DIRECTORY_MODULE . '/' . $this->_module_name;
    }
    
    /**
     * Return the path where .ini have to be put
     * @return string path 
     */
    public function getIniPath()
    {
        return '/' . $this->_extension_name . '/' . self::DIRECTORY_SETTINGS;
    }
    
}

?>
