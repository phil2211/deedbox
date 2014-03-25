<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initDeedbox()
    {
    	//Initialisieren der View und Setzen des Dokumenttyps
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('HTML5');
        $view->setEncoding('UTF-8');
        $view->headMeta()->appendHttpEquiv('Content-Type',
                                           'text/html; charset=utf-8');

        //Setzen des Seitentitels
        $view->headTitle('DeedBox')
        	 ->setSeparator(' :: ');

		$view->addHelperPath(APPLICATION_PATH.'/views/helpers','Application_View_Helper');

        $controller = Zend_Controller_Front::getInstance();
        $controller->registerPlugin(new DeedBox_Controller_Plugin_AclHandler);

        // autoloader für dropbox-client
        Zend_Loader_Autoloader::getInstance()->pushAutoloader(function($class){
            $class = str_replace('\\', '/', $class);
            require_once($class . '.php');
        }, 'Dropbox');

        // config in registry stellen
        Zend_Registry::set(
            'config',
            new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV)
        );
    }

    protected function _initDatabase()
    {
        $dbConfig = $this->getOption('database');
        $db = Zend_Db::factory($dbConfig['adapter'], $dbConfig['params']);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);

        Zend_Registry::set('db', $db);
    }

    protected function _initQueue()
    {
        $options = array(
        'name'          => 'docqueue',
        'driverOptions' => array(
        'host'      => zend_registry::get('config')->database->params->host,
        'port'      => '3306',
        'username'  => zend_registry::get('config')->database->params->username,
        'password'  => zend_registry::get('config')->database->params->password,
        'dbname'    => zend_registry::get('config')->database->params->dbname,
        'type'      => 'pdo_mysql')
        );

        // Erstellt eine Datenbank Queue.
        // Zend_Queue fügt vorne Zend_Queue_Adapter_ an 'Db' für den Klassennamen an.
        $queue = new Zend_Queue('Db', $options);
        Zend_Registry::set('queue', $queue);
    }
}

