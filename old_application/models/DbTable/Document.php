<?php

class Application_Model_DbTable_Document extends Zend_Db_Table_Abstract
{

    protected $_name = 'document';

    protected $_primary = 'id';
    
    protected $_referenceMap = array (
        'Specification'=> array (
            'columns'=>'fk_doc_spec',
            'refTableClass'=>'Application_Model_DbTable_DocSpecMainGroup',
            'refColumns'=>'id'
        ),
        
        'Group' => array (
            'columns'=>'fk_doc_group',
            'refTableClass'=>'Application_Model_DbTable_DocGroup',
            'refColumns'=>'id'
        )
    );


}

