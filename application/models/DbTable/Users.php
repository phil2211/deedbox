<?php

// hier brauchen wir den passwordhasher..
require_once(dirname(__FILE__).'/../../../library/external/PasswordHash.php');

class Application_Model_DbTable_Users extends Zend_Db_Table
{
    protected $_name='users';

    public function insert($data)
    {
        // passwort hashen
        $data['password'] = $this->hashPw($data['password']);
        $data['isactive'] = 0;

        return parent::insert($data);
    }

    function checkUnique($username)
    {
        $userData = $this->getSingleUserData($username, false);
        $ret = false;

        if(is_array($userData) && isset($userData['id'])) {
            $ret = true;
        }

        return $ret;
    }
    
    /*
    * Exklusiv fur AuthController::activateAction()
    */
    public function getInactiveByHash($hash)
    {
        $select = $this->_db->select()
                            ->from($this->_name, '*')
                            ->where('isactive = 0')
                            ->where('MD5(CONCAT(id, CONCAT(\'-\', username))) = ?', $hash);
            
        return $this->getAdapter()->fetchRow($select);
    }
    
    public function activateByid($id) 
    {
        $data = array(
            'isactive' => 1
        );
 
        $where = $this->getAdapter()->quoteInto('id = ?', $id); 
        
        return $this->update($data, $where);   
    }    
    

    public function getSingleUserData($username, $onlyActive = true)
    {
        $select = $this->_db->select()
                            ->from($this->_name, '*')
                            ->where('username=?',$username);
            
        if ($onlyActive) {
            $select->where('isactive = 1');
        }
            
        return $this->getAdapter()->fetchRow($select);
    }

    public function getSingleUserDataById($id)
    {
        $select = $this->_db->select()
                            ->from($this->_name, '*')
                            ->where('isactive = 1')
                            ->where('id=?',$id);
        return $this->getAdapter()->fetchRow($select);
    }

    public function validatePassword($username, $pw)
    {
        $ret = false;
        $userData = $this->getSingleUserData($username);
        $config = Zend_Registry::get('config')->authcrypt;

        $hasher = new PasswordHash($config->waittime, FALSE);
        $hashCheck = $hasher->CheckPassword($pw, $userData['password']);

        if ($hashCheck === true) $ret = true;

        return $ret;
    }

    /**
     * Daemon-Funktion - gibt "paar" User (hier noch Methodik überlegen)
     * zum Verarbeiten zurück..
     *
     * @return type
     */
    public function getWorkOnUsers()
    {
        $select = $this->_db->select()
                            ->from($this->_name, '*');

        return $this->getAdapter()->fetchAll($select);
    }

    private function hashPw($pw)
    {
        $config = Zend_Registry::get('config')->authcrypt;
        $hasher = new PasswordHash($config->waittime, FALSE);

        return $hasher->HashPassword($pw);
    }
}

