<?php

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        if (Application_Model_User::isLoggedIn()) {
			$this->_helper->redirector('index', 'edit');
		}

        $this->view->showSidebar = false;
    }
}