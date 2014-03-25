<?php

class Application_Model_DbTable_DocSpecRecogFeats extends Zend_Db_Table_Abstract
{

    protected $_name = 'doc_spec_recog_feats';

    protected $_primary = 'id';
    
    protected $_referenceMap = array (
        'Specification'=> array (
            'columns'=>'fk_doc_spec',
            'refTableClass'=>'Application_Model_DbTable_DocSpecMainGroup',
            'refColumns'=>'id'
        ),
        
        'RecogFeat' => array (
            'columns'=>'fk_doc_spec_recog_feat',
            'refTableClass'=>'Application_Model_DbTable_DocSpecRecogFeat',
            'refColumns'=>'id'
        )
    );
}

