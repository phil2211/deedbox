<?php

/**
 *
 * WIRD NICHT MEHR BENUTZT!
 *
 */

class Application_Model_Storage_Local extends Application_Model_Storage_Abstract
{

    protected $_userBaseDir = false;
    protected $_inDir = false;
    protected $_outDir = false;
    protected $_failDir = false;

    protected $_mandatoryOptions = array(
        'baseDir'
    );

    public function init()
    {
        // lokale struktur sicherstellen!
        $baseDir = $this->_options['baseDir'];

        if (!is_dir($baseDir)) {
            throw new Exception('Storage basedir "'.$baseDir.'" does not exist!');
        }

        if (!is_writable($baseDir)) {
            throw new Exception('Storage basedir "'.$baseDir.'" is not writable!');
        }

        $this->_userBaseDir = realpath($baseDir).DIRECTORY_SEPARATOR.Application_Model_User::getHashId();
        if (!is_dir($this->_userBaseDir)) mkdir($this->_userBaseDir);

        $this->_inDir = $this->_userBaseDir.DIRECTORY_SEPARATOR.'incoming';
        if (!is_dir($this->_inDir)) mkdir($this->_inDir);

        $this->_outDir = $this->_userBaseDir.DIRECTORY_SEPARATOR.'outgoing';
        if (!is_dir($this->_outDir)) mkdir($this->_outDir);

        $this->_failDir = $this->_userBaseDir.DIRECTORY_SEPARATOR.'fail';
        if (!is_dir($this->_failDir)) mkdir($this->_failDir);
    }

    public function getInFiles()
    {
        return $this->readDir($this->_inDir);
    }

    public function putInFile(Application_Model_File $file)
    {
        throw new Exception('localStorage hat keine inFiles!');
    }

    public function renameFile($oldPath, &$newPath, $md5Hash=NULL)
    {
        $ret = true;
        $file = $this->getFile($newPath);
        
        //checken ob das Targetfile bereits existiert
        while (strlen($file) > 0) {  
            $newfileHash = md5($file);
            //wenn der MD5 Hash von altem und neuem File identisch ist, lösche neues File
            //wenn nicht, zähle filenamen des neuen Files hoch und lege es ab
            if ($md5Hash == $newfileHash) {
                $this->deleteFile($newPath);
            } else {
                $fileCounter = 0;
                $matches = array(); 
                preg_match("/(_)(\\d)/ui", pathinfo($newPath, PATHINFO_FILENAME), $matches); //findet alle Zahlen vor der Filextension und nach dem letzten Unterstrich                    if (is_array($matches))
                $fileCounter = $matches[0]+1;
                $newPath = pathinfo($newPath, PATHINFO_DIRNAME) . '/' 
                            . pathinfo($newPath, PATHINFO_FILENAME) . '_'
                            . $fileCounter . pathinfo($newPath, PATHINFO_EXTENSION);
                
            }
            $file = $this->getFile($newPath);
        } 
        
        return rename($oldPath, $newPath);
    }
    
    public function deleteFile($filePath) {
        unlink($filePath);
    }

    public function outputFile($filePath)
    {
        readfile($filePath);
    }
    
    public function getFile($filePath) {
        return file_get_contents($filePath);
    }

    public function failFile(Application_Model_File $file)
    {
        return true;
    }

    public function isReady()
    {
        $ret = false;
        if (is_dir($this->_userBaseDir)) {
            $ret = true;
        }

        return $ret;
    }

    private function readDir($dir)
    {
        $ret = new Application_Model_FileCollection();

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {

                $fullPath = $dir.DIRECTORY_SEPARATOR.$entry;

                if (is_file($fullPath)) {
                    $file = new Application_Model_File(
                        $fullPath
                    );

                    $file->setStorage($this);
                    $ret->append($file);
                }
            }

            closedir($handle);
        }

        return $ret;
    }


}