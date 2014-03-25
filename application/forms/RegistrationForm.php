<?php

class Application_Form_RegistrationForm extends Zend_Form
{

    public function init()
    {
        $firstname = $this->createElement('text','firstname');
        $firstname->setLabel('First Name:')
                    ->setRequired(false);
                    
        $lastname = $this->createElement('text','lastname');
        $lastname->setLabel('Last Name:')
                    ->setRequired(false);
                    
        $email = $this->createElement('text','username');
        $email->setLabel('Email: *')
                ->setRequired(false);
                
        $password = $this->createElement('password','password');
        $password->setLabel('Password: *')
                ->setRequired(true);
                
        $confirmPassword = $this->createElement('password','confirmPassword');
        $confirmPassword->setLabel('Confirm Password: *')
                ->setRequired(true);
                
        $register = $this->createElement('submit','register');
        $register->setLabel('Sign up')
                ->setIgnore(true);
                
        $this->addElements(array(
                        $firstname,
                        $lastname,
                        $email,
                        $password,
                        $confirmPassword,
                        $register
        ));
    }
}

