<?php

/**
 * Kurzbeschreibung
 *
 * Langbeschreibung (wenn vorhanden) 
 *
 * @category   
 * @package    
 * @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
 * @license    GPL
 * @version    Release: @package_version@
 * @since      Klasse vorhanden seit Release 0.1
 * @deprecated 
 */
 class DeedBox_Controller_Plugin_AclHandler extends Zend_Controller_Plugin_Abstract {
    
	// hier sind controller (key) + action (value) erwähnt die man besuchen kann ohne identity
	private $_allowedPlaces = array(
		'index' => array(
			'index' => ''
		),
		'auth' => array(
			'login' => '',
			'signup' => '',
			'viewmail' => '',
			'activate' => ''
		)
	);
	
     public function preDispatch(Zend_Controller_Request_Abstract $request)
     {
         if (!Application_Model_User::isLoggedIn() &&
            !isset(
                $this->_allowedPlaces[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()]
            )) {
             $this->getRequest()->setControllerName('auth')
                                ->setActionName('login')
                                ->setModuleName('default');
         }
     }
     
     
     
}
