<?php

class Application_Model_DbTable_DocSpecRecogFeat extends Zend_Db_Table_Abstract
{

    protected $_name = 'doc_spec_recog_feat';

    protected $_primary = 'id';
    
    protected $_dependentTables = array ('Application_Model_DbTable_DocSpecRecogFeats');

}

