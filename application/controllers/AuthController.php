<?php

class AuthController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->view->showSidebar = false;
    }

    public function indexAction()
    {
        // action body
    }

    public function homeAction()
    {
        $this->view->username = Application_Model_User::getIdentity();
    }

    public function loginAction()
    {
        $users = new Application_Model_DbTable_Users();
        $form = new Application_Form_LoginForm();
        $this->view->form = $form;
        if($this->getRequest()->isPost()){
            if($form->isValid($_POST)){
                $data = $form->getValues();

                $result = Application_Model_User::initByUserCredentials(
                    $data['username'],
                    $data['password']
                );

                if($result === true){
                    $this->_redirect('/');
                } else {
                    $this->view->errorMessage = "Invalid username or password. Please try again.";
                }
            }
        }
    }

    public function signupAction()
    {
        $users = new Application_Model_DbTable_Users();
        $form = new Application_Form_RegistrationForm();
        $this->view->form=$form;
        if($this->getRequest()->isPost()){
            if($form->isValid($_POST)){
                $data = $form->getValues();
                if ($data['password'] != $data['confirmPassword']){
                    $this->view->errorMessage = "Die Passwörter stimmen nicht überein. Bitte versuche es nochmals.";
                    return;
                }
                
                if ($users->checkUnique($data['username'])){
                    $this->view->errorMessage = "Unter dieser Mailadresse wurde schon eine Registrierung durchgeführt";
                    return;
                }
                
                unset($data['confirmPassword']);

                if (($newId = $users->insert($data))) {
                    
                    //Neutrale Dokumentengruppe hinzufügen
                    $docGroup = new Application_Model_Docgroup();
                    $docGroup->setUser_id($newId);
                    $docGroup->setName('Sonstige');
                    $docGroup->setShort_name('Sonstige');
                    $docGroupMapper = new Application_Model_DocgroupMapper();
                    $docGroupMapper->save($docGroup);
                
                    $activatehash = md5($newId.'-'.$data['username']);
                    
                    $mailtext = 'Guten Tag '.$data['username'].PHP_EOL.PHP_EOL;
                    $mailtext .= 'Vielen Dank für deine Anmeldung bei der DeedBox!'.PHP_EOL.PHP_EOL;
                    $mailtext .= 'Bitte klicke auf den untenstehenden Link um dein Konto zu aktivieren. '.PHP_EOL.
                        'Danach kannst du dich mit deinem Benutzernamen und Passwort anmelden.'.PHP_EOL.
                        'https://www.deedbox.ch/auth/activate/hash/'.$activatehash.'/'.PHP_EOL.PHP_EOL.
                        'Vielen Dank'.PHP_EOL.
                        'Deine DeedBox'.PHP_EOL.PHP_EOL.
                        '-----------------------------------------------------------------------'.PHP_EOL.
                        'Bitte nicht auf dieses Mail antworten, es wurde automatisch generiert.'.PHP_EOL.PHP_EOL;
   
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyText($mailtext);
                    $mail->setFrom('info@deedbox.ch', 'DeedBox');
                    $mail->addTo($data['username']);
                    $mail->setSubject('Aktivierung Benutzerkonto DeedBox');
                    $mail->send();
                }
                
                $this->_redirect('/auth/viewmail');
            }
        }
    }
    
    public function viewmailAction()
    {
    }
    
    public function activateAction()
    {
        $hash = $this->getRequest()->getParam('hash');
        
        if (strlen($hash) > 8) {
            $users = new Application_Model_DbTable_Users();
            $entry = $users->getInactiveByHash($hash);

            if (is_array($entry) && $entry['id'] > 0) {
                $users->activateByid($entry['id']); 
                Application_Model_User::initUserById($entry['id']);
                $this->view->success = true;
            } else {
                $this->view->success = false;
            }     
            
        }

    }   

    public function logoutAction()
    {
		Application_Model_User::logout();

        $this->_redirect('/');
    }
}









