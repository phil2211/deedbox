<?php

class Application_Model_DocspecMapper
{
    protected $_dbTable;
 
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_DocSpecMainGroup');
        }
        return $this->_dbTable;
    }
    
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Ungueltige Gateway-Klasse fuer Tabellendaten');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
     
    public function save(Application_Model_Docspec $docspec) {
        $data = array(
            'id'    => $docspec->getId(),
            'name'  => $docspec->getName(),
            'code'  => $docspec->getCode(),
            'sort'  => $docspec->getSort()
        );
        
        if (null === $docspec->getId()) {
            unset($data['id']);
            $data['created_at']  = date('Y-m-d H:i:s');
            $data['modified_at'] = date('Y-m-d H:i:s');
            $docspec->setId($this->getDbTable()->insert($data));
        }
        else {
            $data['modified_at'] = date('Y-m-d H:i:s');
            $this->getDbTable()->update($data, array('id = ?' => $data['id']));
        }
        return $this;
    }
    
    public function find($id, Application_Model_Docspec $docspec) {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return null;
        }
        $row = $result->current();
        $docspec->setId($row->id)
                ->setCreated_at($row->created_at)
                ->setModified_at($row->modified_at)
                ->setName($row->name)
                ->setCode($row->code)
                ->setSort($row->sort);
        
        $recogFeatures = array();
        $recogFeaturesRows = $row->findManyToManyRowset(
                'Application_Model_DbTable_DocSpecRecogFeat',
                'Application_Model_DbTable_DocSpecRecogFeats'
        );
        foreach ($recogFeaturesRows as $recogFeaturesRow) {
            $recogFeature = new Application_Model_Docspecrecogfeat($recogFeaturesRow->toArray());
            $recogFeatures[] = $recogFeature;
        }
        $docspec->setRecog_features($recogFeatures);

    }
    

    public function fetchAll() {
        $resultSet = $this->getDbTable()->fetchAll();
        return $this->fetchLoop($resultSet);
    }
     
    private function fetchLoop($resultSet)
    {
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Docspec();
            $entry->setId($row->id)
                  ->setCreated_at($row->created_at)
                  ->setModified_at($row->modified_at)
                  ->setName($row->name)
                  ->setCode($row->code)
                  ->setSort($row->sort);
            $recogFeatures = array();
            $recogFeaturesRows = $row->findManyToManyRowset(
                    'Application_Model_DbTable_DocSpecRecogFeat',
                    'Application_Model_DbTable_DocSpecRecogFeats'
            );
            foreach ($recogFeaturesRows as $recogFeaturesRow) {
                $recogFeature = new Application_Model_Docspecrecogfeat($recogFeaturesRow->toArray());
                $recogFeatures[] = $recogFeature;
            }
            $entry->setRecog_features($recogFeatures);
            $entries[] = $entry;
        }
        return $entries;
    }
}

