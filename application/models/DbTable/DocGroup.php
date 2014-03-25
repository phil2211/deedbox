<?php

class Application_Model_DbTable_DocGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'doc_group';
    
    protected $_primary = 'id';
    
    protected $_dependentTables = array ('Application_Model_DbTable_Document');


}

