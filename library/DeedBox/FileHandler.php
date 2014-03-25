<?php

/**
* Beinhaltet die Klasse File Handler und seine Konstanten
*
* LICENSE: GPL
*
* @category   DeedBox Library
* @package    DeedBox
* @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
* @license    GPL
* @version    $Id:$
* @since      Datei vorhanden seit Release 0.1
*/
define('IN_DIR', '/tmp/in/');
define('OK_DIR', '/tmp/ok/');
define('FAIL_DIR', '/failed/');
define('DESTINATION_DIR', '/documents/');

/**
* Kopiert und verschiebt Dateien und prueft diese auf gueltigen Inhalt
*
* @category   DeedBox Library
* @package    DeedBox
* @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
* @license    GPL
* @version    Release: @package_version@
* @since      Klasse vorhanden seit Release 0.1
* @deprecated 
*/
class DeedBox_FileHandler {
    
    
    private $_fileName = NULL;
    
    private $_basePath = '/tmp';
        
    /**
     * Klassen Konstruktor
     *
     * @param string $fileName Pfad und Dateiname der zu pruefenden Datei
     * @param string $basePath Basispfad fuer das Ablageverzeichnis der geprueften Dateien
     */
    public function __construct($fileName, $basePath) {
        $this->_fileName = $fileName;
        $this->_basePath = $basePath;
    }
    
    /**
     * Prueft den Inhalt der Datei auf eine PDF Datei welche geoeffnet werden kann
     *
     * @return boolean
     * @throws Exception Wenn die Datei keine Gueltige PDF-Datei ist
     */
    public function checkFileContent()
    {
        //Datei unabhaengig von Extension auf Mime-Type application/pdf pruefen
        $finfo = new finfo(FILEINFO_MIME);
        $fileType = $finfo->file($this->_fileName);
        if (!strpos($fileType, 'pdf')) {
            $this->moveFile(FAIL_DIR);
            throw new Exception('Filetyp nicht erlaubt: '. $fileType . ' ' . $this->_fileName);
        //Versuche PDF Datei zu laden. Zend_Pdf wird eine Exception werfen falls dies nicht moeglich ist
        } else {
            $image = new Imagick($this->_fileName);
            if($image->getimageformat() == 'PDF') {
                $this->moveFile(OK_DIR);
                return TRUE;
            } else {
                throw new Exception('Datei kann nicht als PDF geoeffnet werden');
            }
            
        }
        return $this;
        
    }
    
    
    /**
     * Verschiebt eine Datei an einen gewuenschten Zielort. Wenn der Zielort
     * nicht existiert wird dieser angelegt
     *
     * @param string $destination Zielort der Datei 
     * @throws Exception Wenn der Zielort nicht angelegt werden kann oder die Datei nicht verschoben werden kann
     */
    public function moveFile($destination, $fileName = NULL)
    {
        //falls kein Dateiname uebergeben wurde, wird der alte Dateiname verwendet
        if ($fileName === NULL)
            $fileName = pathinfo($this->_fileName, PATHINFO_BASENAME);
        
        //Pruefen ob Zielverzeichnis existiert. Falls nicht, neu anlegen
        if (!is_dir($this->_basePath . $destination)) {
            if (!mkdir($this->_basePath . $destination, 0777, TRUE)) {
                throw new Exception('Verzeichnis konnte nicht angelegt werden: ' . $this->_basePath . $destination);
            }
        }
        
        //Datei an Zielordner verschieben und Destination nachfuehren
        $fileTmpName = pathinfo(tempnam($this->_basePath.$destination, 
                                        pathinfo($fileName, PATHINFO_FILENAME)), 
                       PATHINFO_FILENAME) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        $dest = $this->_basePath . $destination . $fileTmpName;
        
        if (is_uploaded_file($this->_fileName)) {
            move_uploaded_file($this->_fileName, $dest);
        } else {
            if (!rename($this->_fileName, pathinfo($dest, PATHINFO_DIRNAME) . '/' . $fileTmpName)) {
            throw new Exception('Datei konnte nicht verschoben werden '. $this->_fileName . '=>' . pathinfo($dest, PATHINFO_DIRNAME) . '/' . $fileTmpName);
            
            }
        $this->_fileName = $dest;
        unlink(pathinfo($this->_fileName, PATHINFO_DIRNAME). '/' . pathinfo($this->_fileName, PATHINFO_FILENAME));
        }
        return $this;
    }  
    
    
    /**
     * gibt den aktuell gueltigen Dateinamen zurueck
     *
     * @return type string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }   
}
