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


class Bvb_Grid_Filters_Render_RenderAbstract
{

    protected $_defaultValue;

    protected $_view;

    protected $_attributes;

    protected $_values;

    protected $_fieldName;


    /**
     * @return the $_view
     */
    public function getView ()
    {
        return $this->_view;
    }


    /**
     * @return the $_attributes
     */
    public function getAttributes ()
    {
        return $this->_attributes;
    }


    public function getAttribute ($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }


    /**
     * @param $_view the $_view to set
     */
    public function setView ($_view)
    {
        $this->_view = $_view;
    }


    /**
     * @param $_attributes the $_attributes to set
     */
    public function setAttributes ($_attributes)
    {
        $this->_attributes = $_attributes;
        return $this;
    }


    public function setAttribute ($name, $value)
    {
        $this->_attributes[$name] = $value;
        return $this;
    }


    function removeAttribute ($name)
    {
        if ( isset($this->_attributes[$name]) ) {
            unset($this->_attributes[$name]);
        }

        return $this;
    }


    public function setValues (array $options)
    {
        $this->_values = $options;
        return $this;
    }


    public function getValues ()
    {
        return $this->_values;
    }


    public function setDefaultValue ($value, $field = '')
    {
        if ( $field != '' ) {
            $this->_defaultValue[$field] = $value;
        } else {
            $this->_defaultValue = $value;
        }
        return $this;
    }


    public function getDefaultValue ($name = '')
    {
        if($name!='')
        {
            return $this->_defaultValue[$name];
        }
        return $this->_defaultValue;
    }


    public function setFieldName ($name)
    {
        $this->_fieldName = $name;
        return $this;
    }


    public function getFieldName ()
    {
        return $this->_fieldName;
    }

}