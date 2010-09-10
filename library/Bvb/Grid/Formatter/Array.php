<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */

class Bvb_Grid_Formatter_Array implements Bvb_Grid_Formatter_FormatterInterface
{

    /**
     * Custom Callback function
     * @var mixed
     */
    protected $_callBack;

    /**
     * in form: '{{ucc.chType}}: {{chData}}<br />'
     *
     * @var string
     */
    protected $_template;

    /**
     * set of fields that are allowd to be displayed
     * @var array
     */
    protected $_displayFields = array();

    /**
     * set of fields that shouln't be displayed
     * @var array
     */
    protected $_hiddenFields = array('id', 'hDateTime', 'userID');


    /**
     * Cunstructor
     *
     * @param array $options
     *
     * @return void
     */
    public function __construct ($options = array())
    {
        foreach ( $options as $key => $data ) {
            switch ($key) {
                case 'template':
                    $this->_template = $data;
                    break;
                case 'hiddenFields':
                    $this->_hiddenFields = $data;
                    break;
                case 'displayFields':
                    $this->_displayFields = $data;
                    break;
                case 'callBack':
                    $this->_callBack = $data;
                    break;
            }
        }
    }


    /**
     * Translates a given dtring
     *
     * @param string $message
     *
     * @return string
     */
    public function __ ($message)
    {
        return Bvb_Grid_Translator::getInstance()->__($message);
    }


    /**
     * Checks if a field should be displayed
     *
     * @param string $fieldName
     *
     * @return bool
     */
    protected function _shouldDisplay ($fieldName)
    {
        // check if it should be hidden
        // @todo: check for fields names with references. e.g. id, ab.id, ucc.id, etc.
        if ( in_array($fieldName, $this->_hiddenFields) ) {
            return false;
        }

        // check if it should be displayed
        // @todo: check for fields names with references. e.g. id, ab.id, ucc.id, etc.
        if ( count($this->_displayFields) > 0 ) {
            return in_array($fieldName, $this->_displayFields);
        }

        // hide all fields that end in 'id', e.g. userID, partID, etc.
        if ( strtolower(substr($fieldName, - 2)) == 'id' and $fieldName != 'id' ) {
            return false;
        }

        return true;
    }


    /**
     * Formats a given value
     * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
     */
    public function format ($value, $indent = '')
    {
        // if callback function specified, return its result
        if ( is_callable($this->_callBack) ) {
            return call_user_func($this->_callBack, $value);
        }

        try {
            // do just for the array
            if ( is_array($value) ) {
                $ret = '';
                foreach ( $value as $field => $data ) {
                    // if template is set, replace fields with data
                    if ( isset($this->_template) ) {
                        $fields = array_map(create_function('$value', 'return "{{{$value}}}";'), array_keys($data));
                        $ret .= str_replace($fields, $data, $this->_template);
                    } else {
                        $ret .= $indent;
                        // if current data is a subarray, format it recursively
                        if ( is_array($data) ) {
                            $ret .= $this->format($data, $indent . '&nbsp;');
                        } else {
                            // display just fields that have a value and are allowed to display
                            if ( $data != '' and $this->_shouldDisplay($field) ) {
                                $ret .= $this->__($field) . ': ' . $data . '<br />';
                            }
                        }
                    }
                }
            } else {
                $ret = $value;
            }
        }
        catch (Exception $e) {
            $ret = $value;
        }
        return $ret;
    }
}