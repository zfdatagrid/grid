<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Form
{

    protected $_form;

    public $options;

    public $fields;

    protected $_groupDecorator = array('FormElements', array('HtmlTag', array('tag' => 'td', 'colspan' => '2', 'class' => 'buttons')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_elementDecorator = array('ViewHelper', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')), array(array('label' => 'Label'), array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_buttonHiddenDecorator = array('ViewHelper');

    protected $_formDecorator = array('FormElements', array('HtmlTag', array('tag' => 'table', 'style' => 'width:99%', 'class' => 'borders')), 'Form');


    function getForm ()
    {
        return $this->_form;
    }


    function __call ($name, $args)
    {
        if ( method_exists($this->getForm(), $name) ) {
            return call_user_func_array(array($this->getForm(), $name), $args);
        }

        if ( substr(strtolower($name), 0, 3) == 'set' ) {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);

            $decorator = '_' . $name;

            if ( isset($this->$decorator) ) {
                $this->$decorator = $args[0];
                return $this;
            }

            $this->options[$name] = $args[0];
            return $this;
        }

        if ( substr(strtolower($name), 0, 3) == 'get' ) {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);

            $decorator = '_' . $name;

            if ( isset($this->$decorator) ) {
                return $this->$decorator;
            }

            return $this;
        }

    }


    function __construct ($formClass = 'Zend_Form', $formOptions = array())
    {
        $this->_form = new $formClass($formOptions);
    }


    function setCallbackBeforeDelete ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }
        $this->options['callbackBeforeDelete'] = $callback;

        return $this;
    }


    function setCallbackBeforeUpdate ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeUpdate'] = $callback;

        return $this;
    }


    function setCallbackBeforeInsert ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeInsert'] = $callback;

        return $this;
    }


    function setCallbackAfterDelete ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterDelete'] = $callback;

        return $this;
    }


    function setCallbackAfterUpdate ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterUpdate'] = $callback;

        return $this;
    }


    function setCallbackAfterInsert ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterInsert'] = $callback;

        return $this;
    }

}