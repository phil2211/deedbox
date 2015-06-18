<?php

class Application_Form_Edit extends Zend_Form
{
    public function init()
    {
        //Hidden Field für die DokumentId
        $docId = new Zend_Form_Element_Hidden('documentId');
        $docId->setValue('0');

        //SelectBox für die Dokumentgruppen
        $group = new Zend_Form_Element_Select('documentGroup');
        $group->setLabel('Dokumentengruppe')
              ->setDecorators(array(
                  'ViewHelper',
                  'Description',
                  'Errors',
                  array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                  array('Label', array('tag' => 'td')),
                  array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true))
              ));

        $docGroup = new Application_Model_DbTable_DocGroup();
        $rowset = $docGroup->fetchAll(
            $docGroup->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->order('name ASC')
        );

        $group->addMultiOption('0', 'Bitte auswählen');
        foreach ($rowset as $row) {
            $group->addMultiOption($row->id, $row->name);
        }

        //Button um eine neue Dokumentengruppe zu erfassen
        $newGroup = new Zend_Form_Element_Submit('newGroup');
        $newGroup->setLabel('Neu')
                 ->setDecorators(array(
                     'ViewHelper',
                     'Description',
                     'Errors',
                     array(array('data'=>'HtmlTag'), array('tag' => 'td'))
                 ));

        //SelectBox für die Dokumentklassen
        $spec = new Zend_Form_Element_Select('documentSpec');
        $spec->setLabel('Dokumentenklasse')
             ->setDecorators(array(
                  'ViewHelper',
                  'Description',
                  'Errors',
                  array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                  array('Label', array('tag' => 'td'))
                  ));


        $docSpec = new Application_Model_DbTable_DocSpecMainGroup();
        $rowset = $docSpec->fetchAll(NULL, 'name');

        $spec->addMultiOption('0', 'Bitte auswählen');
        foreach($rowset as $row) {
            $spec->addMultiOption($row->id, $row->name);
        }

        $dateField = new Zend_Form_Element_Text('date');
        $dateField->setLabel('Datum')
                  ->setValue('dd.mm.yyyy')
                  ->setDecorators(array(
                    'ViewHelper',
                    'Description',
                    'Errors',
                    array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td'))
                ));

        $confirm = new Zend_Form_Element_Image('confirm');
        $confirm->setImage('/img/Ok-icon.png')
                ->setDecorators(array(
                    'ViewHelper',
                    'Description',
                    'Errors',
                    array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true))
                ));

        //Elemente dem Formular hinzufügen
        $this->addElements(array($group, $newGroup, $spec, $dateField, $confirm, $docId));
    }


}

