<?php

class AjaxController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
    }

    public function indexAction()
    {
    }

    public function latestAction()
    {
        $document = new Application_Model_DocumentMapper();
        $docs = $document->fetchLatest(4);
        $count = $this->_request->getParam('count');

        if (isset($docs[$count])) {
            echo $docs[$count]->getThumbnail();
        } else {
            echo readfile(APPLICATION_PATH . '/../public/lib/img/spacer.gif');
        }
    }

    public function lastfoundAction()
    {
        $document = new Application_Model_DocumentMapper();
        $docs = $document->fetchLastFound(4);
        $count = $this->_request->getParam('count');

        if (isset($docs[$count])) {
            echo $docs[$count]->getThumbnail();
        } else {
            echo readfile(APPLICATION_PATH . '/../public/lib/img/spacer.gif');
        }
    }

    public function previewAction()
    {
        if ($this->_request->getParam('id') == 0) {
            readfile(APPLICATION_PATH . '/../public/img/noresult.png');
        } else {
            $document = new Application_Model_Document();
            $documentMapper = new Application_Model_DocumentMapper();
            $documentMapper->find($this->_request->getParam('id'), $document);

            echo $document->getPreview();
        }
    }

    /**
     * Ist für das erstellen des Editierformulars verantwortlich
     *
     * return void
     *
     *
     */
    public function editformAction()
    {
        //initialisieren des Formulars
        $form = new Application_Form_Edit();
        $form->setAction('/edit/newgroup')
             ->setMethod(Zend_Form::METHOD_POST);

        //Bestehende Werte des aktuellen Dokuments aus der DB holen
        $document = new Application_Model_Document();
        $documentMapper = new Application_Model_DocumentMapper();
        $documentMapper->find($this->_request->getParam('document'), $document);

        //setzen des selektierten Wertes der Dokumentengruppe anhand des bereits
        //gespeicherten Wertes in der Datenbank
        $form->getElement('documentGroup')->setValue('0');
        if($document->getFk_doc_group() != '')
            $form->getElement('documentGroup')->setValue($document->getFk_doc_group());

        //setzen des selektierten Wertes der Dokumentenklasse anhand des bereits
        //gespeicherten Wertes in der Datenbank
        $form->getElement('documentSpec')->setValue('0');
        if($document->getFk_doc_spec() != '')
            $form->getElement('documentSpec')->setValue($document->getFk_doc_spec());

        $date = new Zend_Date($document->getDocument_date(), Zend_Date::ISO_8601);
        $form->getElement('date')->setValue($date->toString(Zend_Date::DATES));

        //hidden Field mit dem Wert des aktuell angezeigten Dokumentes setzen
        //dieser Wert wird beim Speichern der Zuteilung wieder benötigt
        $form->getElement('documentId')->setValue($document->getId());
        echo $form;
    }

    /**
     * Ist für das Speichern der Werte des Editierformulars verantwortlich
     *
     * Dies geschieht durch einen asynchronen Aufruf pro angezeigtes Dokument
     *
     * return void 
     */
    public function editsaveAction()
    {
        //Holt die aktuellen Werte aus der Datenbank und füllt das Model
        $document = new Application_Model_Document();
        $documentMapper = new Application_Model_DocumentMapper();
        $documentMapper->find($this->_request->getParam('document'), $document);

        $docGroupMapper = new Application_Model_DocgroupMapper();
        $docGroup = new Application_Model_Docgroup();

        $docSpecMapper = new Application_Model_DocspecMapper();
        $docSpec = new Application_Model_Docspec();

        //Setzt die aus dem Form übergebenen Werte sowie die accuracy auf 100
        //da es sich hier um eine manuelle Beurteilung handelt
        if ($this->_request->getParam('group') != '0') {
            $document->fk_doc_group = $this->_request->getParam('group');
            $document->group_accuracy = 100;
        }
        if ($this->_request->getParam('spec') != '0') {
            $document->fk_doc_spec = $this->_request->getParam('spec');
            $document->spec_accuracy = 100;
        }

        // toggle ob save oder nicht!
        $doSave = true;

        if ($document->getFk_doc_spec() !== NULL and $document->getFk_doc_group() !== NULL) {

            // file umbenennen!
            $oldPath = $document->getPath();

            $docGroupMapper->find($this->_request->getParam('group'), $docGroup);
            $docSpecMapper->find($this->_request->getParam('spec'), $docSpec);

            $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));

            $newName = date('Ymd', strtotime($this->_request->getParam('date'))).' - '.
                    $docGroup->getName().'.'.$ext;

            $newPath = '/'.$docSpec->getCode() . '_' . $docSpec->getName() . '/' . $docGroup->getShort_name() . '/' .$newName;

            // umbenennen!
            if (Application_Model_User::getStorage()->renameFile($oldPath, $newPath, $document->getFile_md5())) {
                $document->setDocument_date(date('Y-m-d H:i:s', strtotime($this->_request->getParam('date'))));
                $document->setPath($newPath);
            } else {
                $doSave = false;
            }
        }

        //speichert die neuen Werte in der Datenbank
        // ein fehler beim rename ist passiert -> kein save!
        if ($doSave) {
            $documentMapper->save($document);
        }
    }

    public function documentAction()
    {
        $document = new Application_Model_Document();
        $documentMapper = new Application_Model_DocumentMapper();

        $documentMapper->find($this->_request->getParam('id'), $document);

        header("Content-type:application/pdf");
        header("Content-Disposition:inline;filename=" . $document->getOriginal_filename());

        // outputten!
        Application_Model_User::getStorage()->outputFile($document->getPath());
    }


    public function boxplotAction()
    {
        readfile(APPLICATION_PATH . '/tmp/' . $this->_request->getParam('filename'));
    }

}
