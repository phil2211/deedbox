<?php

/**
 * Eigener kleiner Auth Adapter
 */

class DeedBox_Auth_Adapter_DbCrypt implements Zend_Auth_Adapter_Interface
{

    private $_username;
    private $_password;

    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    public function authenticate()
    {
        $user = new Application_Model_DbTable_Users();
        $res = $user->validatePassword(
            $this->_username,
            $this->_password
        );

        $identity = $this->_username;
        $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
        $message = 'Supplied credential is invalid.';

        if ($res === true) {
            $code = Zend_Auth_Result::SUCCESS;
            $message = 'Authentication successful.';
        }

        return new Zend_Auth_Result(
            $code,
            $identity,
            array($message)
        );
    }

    public function getResultRowObject()
    {
        $user = new Application_Model_DbTable_Users();
        return $user->getSingleUserData($this->_username);
    }

}
