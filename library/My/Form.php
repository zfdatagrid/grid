<?php

class My_Form extends Zend_Form
{


    public function init ()
    {
        $this->setName('Foo');
        $this->setMethod('post');

        $this->addElement('hidden', 'bug_id');

        $this->addElement('checkbox', 'bug_status');

        $this->addElement('text', 'bug_description', array('label' => 'Bugggg desc', 'required' => true, 'filters' => array('StripTags', 'StringTrim'), 'validators' => array('Digits')));

        $this->addDisplayGroup(array('bug_status', 'bug_description'), 'group1');
    }
}
