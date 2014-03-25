<?php

/**
 * Eigener dummy Auth Adapter
 */

class DeedBox_Auth_Adapter_Dummy implements Zend_Auth_Adapter_Interface
{

    private $_userId;
    private $_userdata = array();

    public function __construct($userId)
    {
        $this->_userId = $userId;
    }

    public function authenticate()
    {
        $users = new Application_Model_DbTable_Users();
        $userdata = $users->getSingleUserDataById($this->_userId);

        $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
        $identity = false;
        $message = 'Supplied credential is invalid.';

        if (is_array($userdata) && isset($userdata['username'])) {
            $code = Zend_Auth_Result::SUCCESS;
            $identity = $userdata['username'];
            $message = 'Authentication successful.';
            $this->_userdata = $userdata;
        }

        return new Zend_Auth_Result(
            $code,
            $identity,
            array($message)
        );
    }

    public function getResultRowObject()
    {
        return $this->_userdata;
    }

}
