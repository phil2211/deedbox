#!/usr/bin/php
<?php

/**
* der mdaemon mailt.. mails..
*/

define('LOCKFILE', dirname(__FILE__).'/mdaemon.lock');

if (file_exists(LOCKFILE)) {
    exit(0);
}

file_put_contents(LOCKFILE, time() . "\n");

error_reporting(E_ERROR);

require_once(dirname(__FILE__).'/../scripts/bootstrap_cli.php');

class mdaemon {

    public function __construct() {

        $u = new Application_Model_DbTable_Users();
        $users = $u->getWorkOnUsers();
        
        foreach ($users as $user) {
        
            Application_Model_User::initUserById($user['id']);

            try {
        
                $docMapper = new Application_Model_DocumentMapper();
                $docs = $docMapper->getUnrecognizedDocuments();
                
                if (is_array($docs) && count($docs) > 0) {

                    $username = Application_Model_User::getIdentity(); 
                    
                    $mailtext = 'Guten Tag '.$username.PHP_EOL.PHP_EOL;
                    $mailtext .= 'Du hast aktuell >> '.count($docs).' << nicht zugeordnete Dokumente in deiner DeedBox.'.PHP_EOL;
                    $mailtext .= 'Indem du deine ersten Dokumente kategorisierst, hilfst du der DeedBox zu lernen und '.
                        'so zukünftig die kommenden Dokumente selbst zuteilen zu können. Bitte klick auf den untenstehenden '.
                        'Link um die Übersicht der nicht zugeteilten Dokumente zu sehen:'.PHP_EOL.
                        'https://www.deedbox.ch/edit'.PHP_EOL.PHP_EOL.
                        'Vielen Dank'.PHP_EOL.
                        'Deine DeedBox'.PHP_EOL.PHP_EOL.
                        '-----------------------------------------------------------------------'.PHP_EOL.
                        'Bitte nicht auf dieses Mail antworten, es wurde automatisch generiert.'.PHP_EOL.PHP_EOL;
   
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyText($mailtext);
                    //$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
                    $mail->setFrom('info@deedbox.ch', 'DeedBox');
                    $mail->addTo($username);
                    $mail->setSubject('Dokumentzuteilung DeedBox notwendig');
                    $mail->send();                      
                                
                
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

$f = new mdaemon();

exit(0);
