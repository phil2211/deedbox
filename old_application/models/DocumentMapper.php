<?php

class Application_Model_DocumentMapper
{
    protected $_dbTable;

    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Document');
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

    public function save(Application_Model_Document $document) {

        $indexHandler = Application_Model_User::getIndexHandler();

        $data = array(
            'id'                    => $document->getId(),
            'user_id'               => Application_Model_User::getId(),
            'last_found'            => $document->getLast_found(),
            'original_filename'     => $document->getOriginal_filename(),
            'file_md5'              => $document->getFile_md5(),
            'path'                  => $document->getPath(),
            'file_name'             => $document->getFile_name(),
            'tag'                   => $document->getTag(),
            'content'               => $document->getContent(),
            'thumbnail'             => $document->getThumbnail(),
            'preview'               => $document->getPreview(),
            'fk_doc_spec'           => $document->getFk_doc_spec(),
            'fk_doc_group'          => $document->getFk_doc_group(),
            'group_accuracy'        => $document->getGroup_accuracy(),
            'spec_accuracy'         => $document->getSpec_accuracy(),
            'document_date'         => $document->getDocument_date(),
            'document_IBAN'         => $document->getDocument_IBAN(),
            'document_amount'       => $document->getDocument_amount(),
            'document_stats'        => $document->getDocument_stats()
        );

        if (null === $document->getId()) {
            unset($data['id']);
            $data['created_at']  = date('Y-m-d H:i:s');
            $data['modified_at'] = date('Y-m-d H:i:s');
            $document->setId($this->getDbTable()->insert($data));
            $data['id'] = $document->getId();

            //Daten dem Indexhandler übergeben
            $indexHandler->add($data);
        }
        else {
            $data['modified_at'] = date('Y-m-d H:i:s');
            $this->getDbTable()->update($data, array('id = ?' => $data['id']));

            //Daten dem Indexhandler übergeben
            $indexHandler->update($data);
        }
        return $this;
    }

    public function find($id, Application_Model_Document $document)
    {
        $sel = $this->getDbTable()->select()
                ->where('id = ?', $id)
                ->where('user_id = ?', Application_Model_User::getId());

        $result = $this->getDbTable()->fetchAll($sel);

        if (0 == count($result)) {
            return null;
        }

        $row = $result->current();
        $document->setId($row->id)
                ->setUser_id($row->user_id)
                ->setCreated_at($row->created_at)
                ->setModified_at($row->modified_at)
                ->setLast_found($row->last_found)
                ->setOriginal_filename($row->original_filename)
                ->setFile_md5($row->file_md5)
                ->setPath($row->path)
                ->setFile_name($row->file_name)
                ->setTag($row->tag)
                ->setContent($row->content)
                ->setThumbnail($row->thumbnail)
                ->setPreview($row->preview)
                ->setFk_doc_spec($row->fk_doc_spec)
                ->setFk_doc_group($row->fk_doc_group)
                ->setGroup_accuracy($row->group_accuracy)
                ->setSpec_accuracy($row->spec_accuracy)
                ->setDocument_date($row->document_date)
                ->setDocument_IBAN($row->document_IBAN)
                ->setDocument_amount($row->document_amount)
                ->setDocument_stats($row->document_stats);
        $groupRow = $row->findParentRow('Application_Model_DbTable_DocGroup');
        if ($groupRow) {
            $document->setGroup_name($groupRow->name);
        }

        $specRow = $row->findParentRow('Application_Model_DbTable_DocSpecMainGroup');
        if ($specRow) {
            $document->setSpec_name($specRow->name);
        }

    }
    
    public function getByStoragePath($storagePath)
    {
        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->where('path = ?', $storagePath);

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);    
    }

    public function fetchLatest($count)
    {
        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->order('created_at desc')
                ->limit($count);

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);
    }

    public function fetchLastFound($count)
    {
        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->order('last_found desc')
                ->limit($count);

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);
    }

    public function fetchAll() {

        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId());

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);
    }

    public function getUngroupedDocuments()
    {
        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->where('tag is null')
                ->where('(group_accuracy < 50 or group_accuracy IS NULL)')
                ->order('modified_at desc');

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);
    }

    public function getUnrecognizedDocuments()
    {
        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->where('(group_accuracy < 50 or group_accuracy is NULL or spec_accuracy < 30 or spec_accuracy IS NULL)')
                ->order('modified_at desc');

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);
    }

    public function getSpecSamples($specId)
    {

        $sel = $this->getDbTable()->select()
                ->where('user_id = ?', Application_Model_User::getId())
                ->where('fk_doc_spec = ?', $specId)
                ->where('spec_accuracy >= 100')
                ->order('RAND()')
                ->limit(10, 0);

        $resultSet = $this->getDbTable()->fetchAll($sel);

        return $this->fetchLoop($resultSet);
    }

    private function fetchLoop($resultSet)
    {
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Document();
            $entry->setId($row->id)
                  ->setUser_id($row->user_id)
                  ->setCreated_at($row->created_at)
                  ->setModified_at($row->modified_at)
                  ->setOriginal_filename($row->original_filename)
                  ->setLast_found($row->last_found)
                  ->setFile_md5($row->file_md5)
                  ->setPath($row->path)
                  ->setFile_name($row->file_name)
                  ->setTag($row->tag)
                  ->setContent($row->content)
                  ->setThumbnail($row->thumbnail)
                  ->setPreview($row->preview)
                  ->setFk_doc_spec($row->fk_doc_spec)
                  ->setFk_doc_group($row->fk_doc_group)
                  ->setGroup_accuracy($row->group_accuracy)
                  ->setSpec_accuracy($row->spec_accuracy)
                  ->setDocument_date($row->document_date)
                  ->setDocument_IBAN($row->document_IBAN)
                  ->setDocument_amount($row->document_amount)
                  ->setDocument_stats($row->document_stats);
            $groupRow = $row->findParentRow('Application_Model_DbTable_DocGroup');
            if ($groupRow) {
                $entry->setGroup_name($groupRow->name);
            }

            $specRow = $row->findParentRow('Application_Model_DbTable_DocSpecMainGroup');
            if ($specRow) {
                $entry->setSpec_name($specRow->name);
            }

            $entries[] = $entry;
        }
        return $entries;
    }
}

