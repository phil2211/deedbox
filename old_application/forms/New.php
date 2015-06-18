<?php
/**
* Formularklasse für das Upload Formular
*
* LICENSE: GPL
*
* @category   DeedBox Forms
* @package    DeedBox
* @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
* @license    GPL
* @version    $Id:$
* @since      Datei vorhanden seit Release 0.1
*/
class Application_Form_New extends Zend_Form
{

   /**
    * Erstellt das Formular für den Dateiupload mit
    * seinen spezifischen Validatoren
    *  
    */
    public function init()
    {
        $file = new Zend_Form_Element_File('docfile');
        $file->setLabel('')
             ->setDestination(APPLICATION_PATH . '/tmp')
             ->setMaxFileSize(104857600);
        // Nur PDFs und ZIPs
        $file->addValidator('Extension', false, array('pdf','zip'));
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Dokument ablegen');
        
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->addElements(array($file, $submit));
    }

}

