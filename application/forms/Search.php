<?php

class Application_Form_Search extends Zend_Form
{

    public function init()
    {
        $documents = new Application_Model_DbTable_Document();
        $select = $documents->select();
        $select->from(
            $documents,
            array('unix_timestamp(max(document_date)) as max', 'unix_timestamp(min(document_date)) as min')
        );
        $select->where('user_id = ?', Application_Model_User::getId());
        $row = $documents->fetchRow($select);

        $from = new Zend_Form_Element_Hidden('from');
        $from->setValue($row->min+3600)
          ->clearDecorators()
          ->addDecorator('ViewHelper');

        $to = new Zend_Form_Element_Hidden('to');
        $to->setValue($row->max+3600)
          ->clearDecorators()
          ->addDecorator('ViewHelper');

        $min = new Zend_Form_Element_Hidden('min');
        $min->setValue($row->min+3600)
          ->clearDecorators()
          ->addDecorator('ViewHelper');

        $max = new Zend_Form_Element_Hidden('max');
        $max->setValue($row->max+3600)
          ->clearDecorators()
          ->addDecorator('ViewHelper');


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
        $rowset = $docGroup->fetchAll(NULL, 'name');

        $group->addMultiOption('0', 'Alle');
        foreach ($rowset as $row) {
            $group->addMultiOption($row->id, $row->name);
        }


        //SelectBox für die Dokumentklassen
        $spec = new Zend_Form_Element_Select('documentSpec');
        $spec->setLabel('Dokumentenklasse')
             ->setDecorators(array(
                  'ViewHelper',
                  'Description',
                  'Errors',
                  array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                  array('Label', array('tag' => 'td')),
                  array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true))
              ));

        $docSpec = new Application_Model_DbTable_DocSpecMainGroup();
        $rowset = $docSpec->fetchAll(NULL, 'name');

        $spec->addMultiOption('0', 'Alle');
        foreach($rowset as $row) {
            $spec->addMultiOption($row->id, (string) $row->name);
        }

        $searchText = new Zend_Form_Element_Text('suchbegriff');
        $searchText->setLabel('Suchbegriff eingeben')
                   ->setDecorators(array(
                  'ViewHelper',
                  'Description',
                  'Errors',
                  array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                  array('Label', array('tag' => 'td')),
                  array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true))
              ));

        $submit = new Zend_Form_Element_Submit('suchen');
        $submit->setDecorators(array(
                  'ViewHelper',
                  'Description',
                  'Errors',
                  array(array('data'=>'HtmlTag'), array('tag' => 'td', 'colspan' => '2')),
                  array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true))
              ));

        //Elemente dem Formular hinzufügen
        $this->addElements(array($from, $to, $min, $max, $group, $spec, $searchText, $submit));

        $this->setDecorators(array('FormElements',
                                    array(array('data'=>'HtmlTag'),
                                          array('tag'=>'table', 'id' => 'formtable')),
                             'Form'
               ));

    }


}

