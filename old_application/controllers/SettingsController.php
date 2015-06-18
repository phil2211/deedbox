<?php

class SettingsController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $this->view->showSidebar = false;
    }

    public function indexAction()
    {
        // dieser check muss unbedingt VOR jeglichem getStorage() aufruf sein!
        if ($this->getRequest()->getParam('doactivate') == '1') {
            $_SESSION['doActivate'] = true;
        }

        $this->view->isStorageReady = Application_Model_User::getStorage()->isReady();

    }

    public function activateAction()
    {
        if (Application_Model_User::getStorage()->isReady()) {
            $_SESSION['doActivate'] = null;
            unset($_SESSION['doActivate']);

            $this->view->success = true;
        } else {
            $this->view->success = false;
        }
    }


}

