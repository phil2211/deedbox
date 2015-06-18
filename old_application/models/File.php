<?php

class Application_Model_File
{

    private $_localPath = false;
    private $_storagePath = false;
    private $_displayName = false;
    private $_storage = false;
    private $_id = false;
    private $_isTransient = false;

    public function __construct($filePath, $storagePath = false, $isTransient = false)
    {
        if (
            !is_file($filePath) ||
            !is_readable($filePath) ||
            !is_writable($filePath)
        ) {
            throw new Exception('File "'.$filePath.'" does not exist, can not be written or is not readable!');
        }

        $this->_localPath = $filePath;
        $this->_storagePath = $storagePath;

        if ($storagePath !== false) {
            $this->_displayName = basename($storagePath);
        } else {
            $this->_displayName = basename($filePath);
        }

        $this->_isTransient = $isTransient;
    }

    public function getLocalPath()
    {
        return $this->_localPath;
    }

    public function getStoragePath()
    {
        return $this->_storagePath;
    }

    public function getDisplayName()
    {
        return $this->_displayName;
    }

    public function setDisplayName($displayName)
    {
        $this->_displayName = $displayName;
    }

    public function exists()
    {
        return file_exists($this->getLocalPath());
    }

    public function unlink()
    {
        return unlink($this->getLocalPath());
    }

    public function setStorage(Application_Model_Storage_Abstract $storage)
    {
        $this->_storage = $storage;
    }

    public function getMd5Sum()
    {
        return md5_file($this->getLocalPath());
    }

    public function getStorage()
    {
        return $this->_storage;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function fail()
    {
        return $this->getStorage()->failFile($this);
    }

    public function __destruct() {
        // ein transient file ist flüchtig und wird gelöscht wenn es
        // nicht mehr gebraucht wird..
        if ($this->_isTransient) {
            $this->unlink();
        }
    }

}