<?php

class Application_Model_DocgroupMapper
{
    protected $_dbTable;

    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_DocGroup');
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

    public function save(Application_Model_Docgroup $docgroup) {
        $data = array(
            'id'                    => $docgroup->getId(),
            'user_id'               => $docgroup->getUser_id(),
            'name'                  => $docgroup->getName(),
            'recognition_feature'   => $docgroup->getRecognition_feature(),
            'sift_index'            => $docgroup->getSift_index()
        );

        $trans = array(' ' => '_',
                       '.' => '_',
                       'ä' => 'a',
                       'ö' => 'o',
                       'ü' => 'u',
                       'é' => 'e',
                       'è' => 'e',
                       'ê' => 'e',
                       'à' => 'a');

        $shortName = strtolower(strtr(substr($docgroup->getName(), 0, 19), $trans));
        $data['short_name'] = $shortName;

        if (null === $docgroup->getId()) {
            unset($data['id']);
            $data['created_at']  = date('Y-m-d H:i:s');
            $data['modified_at'] = date('Y-m-d H:i:s');
            $docgroup->setId($this->getDbTable()->insert($data));
        }
        else {
            $data['modified_at'] = date('Y-m-d H:i:s');
            $this->getDbTable()->update($data, array('id = ?' => $data['id']));
        }
        return $this;
    }

    public function find($id, Application_Model_Docgroup $docgroup) {
        
        $sel = $this->getDbTable()->select()
                ->where('id = ?', $id)
                ->where('user_id = ?', Application_Model_User::getId());

        $result = $this->getDbTable()->fetchAll($sel);

        if (0 == count($result)) {
            return null;
        }
        $row = $result->current();
        $docgroup->setId($row->id)
                ->setUser_id($row->user_id)
                ->setCreated_at($row->created_at)
                ->setModified_at($row->modified_at)
                ->setName($row->name)
                ->setShort_name($row->short_name)
                ->setRecognition_feature($row->recognition_feature)
                ->setSift_index($row->sift_index);
    }


    public function fetchAll() {

        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId());

        $resultSet = $this->getDbTable()->fetchAll($sel);
        return $this->fetchLoop($resultSet);
    }

    private function fetchLoop($resultSet)
    {
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Docgroup();
            $entry->setId($row->id)
                  ->setUser_id($row->user_id)
                  ->setCreated_at($row->created_at)
                  ->setModified_at($row->modified_at)
                  ->setName($row->name)
                  ->setShort_name($row->short_name)
                  ->setRecognition_feature($row->recognition_feature)
                  ->setSift_index($row->sift_index);
            $entries[] = $entry;
        }
        return $entries;
    }
}

