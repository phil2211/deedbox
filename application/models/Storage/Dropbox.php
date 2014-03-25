<?php

class Application_Model_Storage_Dropbox extends Application_Model_Storage_Abstract
{
    protected $_oauth = false;
    protected $_client = false;

    protected $_encrypter = false;
    protected $_keystore = false;

    private $_inDir = '/Incoming';
    private $_failDir = '/Incoming/FAIL';

    protected $_mandatoryOptions = array(
        'appname',
        'key',
        'secret',
        'salt',
        'db',
        'tokentable',
        'callback',
        'acceptedmimes'
    );

    public function init()
    {
        $this->_encrypter = new \Dropbox\OAuth\Storage\Encrypter($this->_options['salt']);

        $this->_keystore = new \Dropbox\OAuth\Storage\PDO(
            $this->_encrypter,
            Application_Model_User::getId()
        );
        $this->_keystore->connect(
            $this->_options['db']['host'],
            $this->_options['db']['dbname'],
            $this->_options['db']['username'],
            $this->_options['db']['password']
        );
        $this->_keystore->setTable($this->_options['tokentable']);

        // haben wir was?
        $accessToken = $this->_keystore->get('access_token');

        // nur wenn wir ein accesstoken haben instanzieren wir das zeugs
        // sonst leitet der client blind an dropbox auth weiter..
        if (
            $accessToken != false ||
            (isset($_SESSION['doActivate']) && $_SESSION['doActivate'] === true)
        ) {

            $this->_oauth = new \Dropbox\OAuth\Consumer\Curl(
                $this->_options['key'],
                $this->_options['secret'],
                $this->_keystore,
                $this->_options['callback']
            );

            $this->_client = new \Dropbox\API($this->_oauth);
        }
    }

    /**
     * Wir sind ready, wenn wir ein Access Token haben!
     *
     * @return boolean
     */
    public function isReady()
    {
        $ready = false;

        $accessToken = $this->_keystore->get('access_token');
        if ($accessToken != false) {
            $ready = true;
        }

        return $ready;
    }

    public function renameFile($oldPath, &$newPath, $md5Hash=NULL)
    {
        $ret = true;
        $file = $this->getFile($newPath);
        
        //checken ob das Targetfile bereits existiert
        //bei mehr als 100 Zeichen kann man davon ausgehen
        //dass ein PDF-Dokument vorliegt. Falls keine Datei gefunden wird
        //sendet Dropbox die Meldung: {"error": "File not found"}
        while (strlen($file) > 100) {  
            $newfileHash = md5($file);
            //wenn der MD5 Hash von altem und neuem File identisch ist, lösche neues File
            //wenn nicht, zähle filenamen des neuen Files hoch und lege es ab
            if ($md5Hash == $newfileHash) {
                $this->deleteFile($newPath);
            } else {
                $fileCounter = 0;
                $matches = array();
                preg_match("/(_)(\d)/ui", pathinfo($newPath, PATHINFO_FILENAME), $matches); //findet alle Zahlen vor der Filextension und nach dem letzten Unterstrich                    if (is_array($matches))
                if (count($matches) > 0) {
                    $fileCounter = $matches[2]+1;
                    $newPath = str_replace($matches[0], '', $newPath);
                }
                $newPath = pathinfo($newPath, PATHINFO_DIRNAME) . '/' 
                            . pathinfo($newPath, PATHINFO_FILENAME) . '_'
                            . $fileCounter . '.' . pathinfo($newPath, PATHINFO_EXTENSION);
                
            }
            $file = $this->getFile($newPath);
        } 



        try {
            $this->_client->move($oldPath, $newPath);
        } catch (Exception $e) {
            $ret = false;
        }

        return $ret;
    }

    public function getInFiles()
    {
        $this->ensureIncomingFolder();

        $meta = $this->_client->metaData($this->_inDir);
        $ret = new Application_Model_FileCollection();

        if (isset($meta['body']->contents) && count($meta['body']->contents) > 0) {

            foreach ($meta['body']->contents as $row) {
                if ($row->is_dir === false) {

                    // überhaupt akzeptiert?
                    // TODO Was ist mit ZIP Files???
                    if (array_search($row->mime_type, $this->_options['acceptedmimes']) !== false) {

                        // ins userlocal storage speichern!
                        $localPath = Application_Model_User::getLocalStorage()->getRandomFilePath();

                        try {
                            $this->_client->getFile($row->path, $localPath);

                            if (is_file($localPath)) {
                                $file = new Application_Model_File(
                                    $localPath,
                                    $row->path
                                );

                                $file->setStorage($this);
                                $ret->append($file);
                            }

                        } catch (Exception $e) {
                            // konnte file nicht holen!
                        }

                    } else {
                        // FAIL!
                        $this->_client->move($row->path, $this->_failDir.'/'.basename($row->path));
                    }
                }
            }
        }
        
        return $ret;
    }

    public function putInFile(Application_Model_File $file)
    {
        $ret = false;
        $putRet =  $this->_client->putFile($file->getLocalPath(), $file->getDisplayName(), $this->_inDir);

        if (isset($putRet['body']->path)) {
            $ret = $putRet['body']->path;
        }

        return $ret;
    }

    public function outputFile($filePath)
    {
        $tmpFilePath = Application_Model_User::getLocalStorage()->getRandomFilePath();
        $this->_client->getFile($filePath, $tmpFilePath);

        readfile($tmpFilePath);
        unlink($tmpFilePath);
    }
    
    public function getFile($filePath) {
        $tmpFilePath = Application_Model_User::getLocalStorage()->getRandomFilePath();
        $this->_client->getFile($filePath, $tmpFilePath);

        return file_get_contents($tmpFilePath);
        unlink($tmpFilePath);
    }

    public function failFile(Application_Model_File $file)
    {
        //$failPath = $this->_failDir.'/'.$file->getDisplayName();
        $this->_client->delete($file->getStoragePath());
        return $this->_client->putFile($file->getLocalPath(), $file->getDisplayName(), $this->_failDir);
    }
    
    public function deleteFile($filePath) {
        $this->_client->delete($filePath);
    }

    private function ensureIncomingFolder()
    {
        $this->ensureFolder($this->_inDir);
        $this->ensureFolder($this->_failDir);
    }

    private function ensureFolder($name)
    {
        try {
            if ($this->_client instanceof \Dropbox\API) {
                $this->_client->create($name);
            }
        } catch (Exception $e) {
            // ok! besteht wohl schon...
        }
    }

}