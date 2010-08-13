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

class Bvb_Grid_Translator
{
    protected static $_instance = null;

    /**
     * @var Zend_Translate
     */
    protected $_translator = null;

    final protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$_instance === NULL) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function setTranslator(Zend_Translate $translator)
    {
        $this->_translator = $translator;
        return $this;
    }

    public function getTranslator()
    {
        if ($this->_translator instanceof Zend_Translate) {
            return $this->_translator;
        } elseif (Zend_Registry::isRegistered('Zend_Translate')) {
            $this->_translator = Zend_Registry::get('Zend_Translate');
        }

        return $this->_translator;
    }

    public function __($message)
    {
        if (strlen($message) == 0) {
            return $message;
        }

        if ($this->getTranslator()) {
            return $this->getTranslator()->translate($message);
        }

        return $message;
    }

    public function isTranslated($message)
    {
        if (strlen($message) == 0) {
            return false;
        }

        if ($this->getTranslator()) {
            return $this->_translator->isTranslated($message);
        }

        return false;
    }

    final protected function __clone()
    {
    }
}
