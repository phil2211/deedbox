#!/usr/bin/php
<?php

define('LOCKFILE', dirname(__FILE__).'/fdaemon.lock');

if (file_exists(LOCKFILE)) {
    exit(0);
}

file_put_contents(LOCKFILE, time() . "\n");

error_reporting(E_ERROR);

/**
 * Dies ist der Basic fDaemon..
 *
 * fDaemon steht für "File Daemon".
 * Seine einzige Aufgabe ist es, in den User Storages (= Dropboxes)
 * der User nach neuen inFiles zu schauen. Findet er was, tut er die Files
 * in die Queue. Mehr tut er nicht.. der Rest macht der wDaemon.
 */
require_once(dirname(__FILE__).'/../scripts/bootstrap_cli.php');

class fdaemon {

    public function __construct() {

        $u = new Application_Model_DbTable_Users();
        $users = $u->getWorkOnUsers();

        foreach ($users as $user) {
            Application_Model_User::initUserById($user['id']);

            if (!Application_Model_User::getStorage()->isReady()) {
                continue;
            }
        
            try {
        
                /** IN FILES HOLEN UND IN QUEUE SCHMEISSEN **/
                $inFiles = Application_Model_User::getStorage()->getInFiles();
                foreach ($inFiles as $file) {
                    Zend_Registry::get('queue')->send(
                        json_encode(array(
                            'userId' => Application_Model_User::getId(),
                            'file' => $file->getLocalPath(),
                            'storagePath' => $file->getStoragePath(),
                            'fileId' => $file->getId()
                        ))
                    );
                }
        
            } catch (Exception $e) {
                // TODO Log?
                echo $e->getMessage().PHP_EOL;
            }
        
            Application_Model_User::logout();
        }
    }

    function __destruct() {
        unlink(LOCKFILE);
    }

}

$f = new fdaemon();

exit(0);
