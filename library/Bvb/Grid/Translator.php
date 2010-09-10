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

    /**
     * Class Instance
     *
     * @var mixed
     */
    protected static $_instance = null;

    /**
     * @var Zend_Translate
     */
    protected $_translator = null;


    /**
     * Protect from instantiation
     *
     * @return void
     */
    final protected function __construct ()
    {
    }


    /**
     * Translator instante
     *
     * @return Zend_Translate
     */
    public static function getInstance ()
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Defines translator
     *
     * @param Zend_Translate $translator
     *
     * @return Zend_Translate
     */
    public function setTranslator (Zend_Translate $translator)
    {
        $this->_translator = $translator;
        return $this;
    }


    /**
     * Returns current translator
     *
     * @return Zend_Translate
     */
    public function getTranslator ()
    {
        if ( $this->_translator instanceof Zend_Translate ) {
            return $this->_translator;
        } elseif ( Zend_Registry::isRegistered('Zend_Translate') ) {
            $this->_translator = Zend_Registry::get('Zend_Translate');
        }
        return $this->_translator;
    }


    /**
     * Translate a string
     *
     * @param string $message
     *
     * @return string
     */
    public function __ ($message)
    {
        if ( strlen($message) == 0 ) {
            return $message;
        }
        if ( $this->getTranslator() ) {
            return $this->getTranslator()
                ->translate($message);
        }
        return $message;
    }


    /**
     * Checks if a string is translatable
     *
     * @param string $message
     *
     * @return mixed
     */
    public function isTranslated ($message)
    {
        if ( strlen($message) == 0 ) {
            return false;
        }
        if ( $this->getTranslator() ) {
            return $this->_translator
                ->isTranslated($message);
        }
        return false;
    }

    /**
     * Protect from instantiation
     *
     * @return void
     */
    final protected function __clone ()
    {
    }
}
