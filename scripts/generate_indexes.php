<?php

require_once(dirname(__FILE__).'/bootstrap_cli.php');

// gehen wir alle files durch..
$select = Zend_Registry::get('db')
        ->select()
        ->from('document')
        ->order(array('user_id', 'created_at'));

foreach (Zend_Registry::get('db')->query($select) as $row) {
    // init user context
    Application_Model_User::initUserById($row['user_id']);

    $idxHandler = Application_Model_User::getIndexHandler();
    $idxHandler->add($row);
}
