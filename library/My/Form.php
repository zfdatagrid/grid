<?php

class My_Form extends Zend_Form
{


    public function init ()
    {
        $this->setName('Foo');
        $this->setMethod('post');
        $this->addElement('checkbox', 'next');$this->addElement('text', 'bug_description', array(
                    'label'      => 'Bugggg desc',
                    'required'   => true,
                    'value'=>'sss',
                        'filters'    => array('StripTags', 'StringTrim'),
//                  'validators' => array('Digits')
                ));

    }
}
