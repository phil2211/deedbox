<?php

class Application_Form_Newgroup extends Zend_Form
{
    public function init()
    {
        $width = new Zend_Form_Element_Hidden('width');
        $width->setValue('0')
              ->clearDecorators()
              ->addDecorator('ViewHelper');    

        $height = new Zend_Form_Element_Hidden('height');
        $height->setValue('0')
               ->clearDecorators()
               ->addDecorator('ViewHelper');    

        $x = new Zend_Form_Element_Hidden('x');
        $x->setValue('0')
          ->clearDecorators()
          ->addDecorator('ViewHelper');    

        $y = new Zend_Form_Element_Hidden('y');
        $y->setValue('0')
          ->clearDecorators()
          ->addDecorator('ViewHelper');
        
        $document = new Zend_Form_Element_Hidden('document');
        $document->setValue(0)
                 ->clearDecorators()
                 ->addDecorator('ViewHelper');
        
        $groupname = new Zend_Form_Element_Text('groupname');
        $groupname->setLabel('Name der Dokumentengruppe')
                  ->addValidator('StringLength', false, array(2, 45))
                  ->setDecorators(array(
                    'ViewHelper',
                    'Description',
                    'Errors',
                    array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true))
              ));
        
        $submit = new Zend_Form_Element_Submit('speichern');
        $submit->setAttrib('disabled', 'disabled')
               ->setDecorators(array(
                    'ViewHelper',
                    'Description',
                    'Errors',
                    array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true))
              ));
        
        $this->addElements(array($width, $height, $x, $y, $document, $groupname, $submit));
        
        $this->setDecorators(array('FormElements', 
                                    array(array('data'=>'HtmlTag'),
                                          array('tag'=>'table')),
                             'Form'
               ));

    }


}

