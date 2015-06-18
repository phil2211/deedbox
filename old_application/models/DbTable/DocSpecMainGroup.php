<?php

class Application_Model_DbTable_DocSpecMainGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'doc_spec_maingroup';

    protected $_primary = 'id';
    
    protected $_dependentTables = array ('Application_Model_DbTable_Document',
                                         'Application_Model_DbTable_DocSpecRecogFeats');
  
}

