<?php
/**
* Controllerklasse für die Upload-Seite der DeedBox
*
* LICENSE: GPL
*
* @category   DeedBox Controller
* @package    DeedBox
* @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
* @license    GPL
* @version    $Id:$
* @since      Datei vorhanden seit Release 0.1
*/
class NewController extends Zend_Controller_Action
{

   /**
    * Steuert den Ablauf für das Anzeigen des
    * Upload Formulars
    *
    */
    public function indexAction()
    {
        $form = new Application_Form_New();
        $form->setAction('/new/save')
             ->setMethod(Zend_Form::METHOD_POST);
        $this->view->form = $form;
    }


   /**
    * Steuert den Ablauf für das Speichern eines
    * neuen Dokuments
    *
    * @throws Exception Bei negativer Formularprüfung
    */
    public function saveAction()
    {
        $files = array();
        //Verifiziert die POST Werte mit Hilfe des Formulars
        $form = new Application_Form_New();
        if (!$form->isValid($this->_request->getPost())) {
            $messages = $form->getMessages();
            $msgString = NULL;
            foreach ($messages as $message) {
                $msgString = implode("<br />", $message);
            }
            throw new Exception('Dateiupload war nicht erfolgreich: ' . $msgString);
        }

        //Empfängt die angehängte Datei
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination(APPLICATION_PATH . '/tmp')
                ->addValidator('MimeType', false, array('application/pdf', 'application/zip'));

        //Empfängt die angehängte Datei
        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            $msgString = NULL;
            foreach ($messages as $message) {
                $msgString = implode("<br />", $message);
            }

            throw new Exception('Fehler beim Empfangen der Datei: ' . $msgString);
        }



        //prüfen ob eine ZIP-Datei hochgeladen wurde. Wenn ja wird für jede
        //Datei in dem ZIP-File eine Fileprüfung gemacht
        $file = $adapter->getFileName();
        $fileType = $adapter->getMimeType();

        if ($fileType == 'application/zip') {
            $zip = zip_open($file);

            if($zip) {
                //neues FINFO Objekt um den MymeType der einzelnen Dateien im ZIP-File zu
                //identifizieren
                $finfo = new finfo(FILEINFO_MIME_TYPE);

                while ($zip_entry = zip_read($zip)) {
                    if (zip_entry_open($zip, $zip_entry, 'r')) {
                        $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        if (strlen($buf) == 0)
                            continue;
                        $file = '/tmp/' . zip_entry_name($zip_entry);
                        if (!is_dir(pathinfo($file, PATHINFO_DIRNAME)))
                            mkdir(pathinfo($file, PATHINFO_DIRNAME), 0777, TRUE);
                        file_put_contents($file, $buf);

                        //alle Dateien welche nicht im PDF-Format sind ausfiltern
                        if ($finfo->file($file) != 'application/pdf')
                            continue;

                        $files[] = $file;
                    }
                }
            }

            unlink($form->docfile->getFileName());
            zip_close($zip);
        } else {
            $files[] = $file;
        }

        foreach ($files as $filePath) {

            // in local storage verschieben!
            $newPath = Application_Model_User::getLocalStorage()->getRandomFilePath();
            rename($filePath, $newPath);

            if (!file_exists($newPath)) {
                throw new Exception('Konnte Datei nicht in LocalStorage verschieben!');
            }

            $file = new Application_Model_File($newPath);
            $file->setDisplayName(basename($filePath));
            $ret = false;

            try {
                $ret = Application_Model_User::getStorage()->putInFile($file);
            } catch (Exception $e) {
                throw new Exception('Konnte Datei nicht in Storage verschieben!');
            }

            if ($ret == false || strlen($ret) < 3) {
                throw new Exception('Konnte Datei nicht in Storage verschieben!');
            }

            Zend_Registry::get('queue')->send(
                json_encode(array(
                    'userId' => Application_Model_User::getId(),
                    'file' => $file->getLocalPath(),
                    'storagePath' => $ret,
                    'fileId' => $file->getId()
                ))
            );

        }

    }


}



