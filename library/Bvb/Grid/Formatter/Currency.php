<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License 2.0
 * It is  available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/gpl-2.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Formatter_Currency implements Bvb_Grid_Formatter_FormatterInterface
{

    protected $_locale = null;


    public function __construct ($options = array())
    {
        if ( Zend_Locale::isLocale($options) ) {
            $this->_locale = $options;
        } else if ( Zend_Registry::isRegistered('Zend_Locale') ) {
            $this->_locale = Zend_Registry::get('Zend_Locale');
        }
    }


    public function format ($value)
    {

        if ( $this->_locale === null || ! is_numeric($value) ) {
            return $value;
        }

        $currency = new Zend_Currency($this->_locale);
        return $currency->toCurrency($value);
    }

}