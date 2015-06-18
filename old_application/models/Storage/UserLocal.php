<?php

/**
 * Das "UserLocal" Storage ist ein Transient-Storage.
 *
 * Das heisst, es dient anderen Storages dazu, "flüchtige" Files
 * zu machen, zb via Dropbox. Man kann statisch ein File zurückgeben lassen,
 * welches _lokal_ in einem _User_spezifischem (dazu UserLocal) Verzeichnis
 * gespeichert wird.
 *
 * Im Wesentlichen sind dies Files, die über die Storages (wie Dropbox) kommen
 * und lokal zwischengespeichert werden *müssen* um damit zu arbeiten.
 */
class Application_Model_Storage_UserLocal extends Application_Model_Storage_Local
{

    private $_tmpDir = false;

    public function init()
    {
        parent::init();

        $this->_tmpDir = $this->_userBaseDir.DIRECTORY_SEPARATOR.'tmp';
        if (!is_dir($this->_tmpDir)) mkdir($this->_tmpDir);

        $this->_isLocal = true;

        return true;
    }

    public function putInFile(Application_Model_File $file)
    {
        throw new Exception('UserLocal Storage has no inFiles!');
        return false;
    }

    public function storeFileByContent($fileContent)
    {
        file_put_contents($this->getRandomFilePath(), $fileContent);

        return $newFilePath;
    }

    public function getRandomFilePath()
    {
        return $this->_tmpDir.DIRECTORY_SEPARATOR.$this->generateTempFilename();
    }


}