<?php

abstract class Application_Model_Storage_Abstract
{

    protected $_options = array();
    protected $_mandatoryOptions = array();
    protected $_isLocal = false;

    public function __construct($options) {
        $this->setOptions($options);
        $this->init();
    }

    public function init()
    {
        return true;
    }

    abstract public function getInFiles();

    abstract public function putInFile(Application_Model_File $file);

    abstract public function isReady();

    abstract public function outputFile($filePath);
    
    abstract public function getFile($filePath);
    
    abstract public function deleteFile($filePath);

    abstract public function failFile(Application_Model_File $file);

    public function setOptions($options)
    {
        foreach ($this->_mandatoryOptions as $optionName) {
            if (!isset($options[$optionName])) {
                throw new Exception('Storage option '.$optionName.' not given');
            }
        }

        $this->_options = $options;
    }

    abstract public function renameFile($oldPath, &$newPath, $md5Hash=NULL);

    public function generateTempFilename()
    {
        return uniqid('', true).uniqid('', true);
    }

}