<?php

class DashboardController extends Zend_Controller_Action
{

    public function indexAction()
    {

        if (!Application_Model_User::getStorage()->isReady()) {
            $this->_helper->redirector('index', 'settings');
        }


        //echo get_include_path(); die;

        /*
        $config = Zend_Registry::get('config');

       $oauth = new Dropbox_OAuth_PHP($config->storage->dropbox->key, $config->storage->dropbox->secret);


        $dropbox = new Dropbox_API($oauth);

        var_dump($dropbox);

        var_dump($oauth->getRequestToken()); die;

        var_dump($oauth->getAuthorizeUrl());
*/
        //var_dump($config->storage);

        /*
        $ls = Application_Model_User::getLocalStorage();

        $storage = Application_Model_User::getStorage()->getInFiles();

        foreach ($storage as $file) {

            $content = file_get_contents($file->getLocalPath());

            $newFile = $ls->storeFileByContent($content);

        }

        die;
         *
         */
        // action body
    }


}

