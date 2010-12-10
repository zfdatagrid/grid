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

abstract class Bvb_Grid_Filters_Render_RenderAbstract implements Bvb_Grid_Filters_Render_RenderInterface
{

    /**
     * Default valud for the field
     * @var string
     */
    protected $_defaultValue;

    /**
     * View Instance
     * @var Zend_View_Abstract
     */
    protected $_view;

    /**
     * Translator interface
     * @var mixed
     */
    protected $_translator = false;

    /**
     * Input attributes
     * @var array
     */
    protected $_attributes;

    /**
     * Input(s) attributes
     * @var mixed
     */
    protected $_values;

    /**
     * Input name
     * @var string
     */
    protected $_fieldName;

    /**
     * Data Source Instance
     * @var object
     */
    protected $_select;

    /**
     * Grid's id
     * @var string
     */
    protected $_gridId;


    /**
     * Returns the current grid Id
     *
     * @return string
     */
    public function getGridId ()
    {
        return $this->_gridId;
    }


    /**
     * sets grid Id
     * @param string $id
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function setGridId ($id)
    {
        $this->_gridId = $id;
        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getView()
     */
    public function getView ()
    {
        return $this->_view;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setTranslator()
     */
    public function setTranslator ($translate)
    {
        $this->_translator = $translate;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getTranslator()
     */
    public function getTranslator ()
    {
        return $this->_translator;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::__()
     */
    public function __ ($name)
    {
        if ( $this->getTranslator() ) return $this->getTranslator()
            ->translate($name);

        return $name;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getAttributes()
     */
    public function getAttributes ()
    {
        return $this->_attributes;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getAttribute()
     */
    public function getAttribute ($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setView()
     */
    public function setView ($_view)
    {
        $this->_view = $_view;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setAttributes()
     */
    public function setAttributes ($_attributes)
    {
        $this->_attributes = $_attributes;
        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setAttribute()
     */
    public function setAttribute ($name, $value)
    {
        $this->_attributes[$name] = $value;
        return $this;
    }


    /**
     * Checks if a given attribute exists
     * @param bool $name
     */
    public function hasAttribute ($name)
    {
        return isset($this->_attributes[$name]) ? true : false;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::removeAttribute()
     */
    public function removeAttribute ($name)
    {
        if ( isset($this->_attributes[$name]) ) {
            unset($this->_attributes[$name]);
        }

        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setValues()
     */
    public function setValues (array $options)
    {
        $this->_values = $options;
        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getValues()
     */
    public function getValues ()
    {
        return $this->_values;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setDefaultValue()
     */
    public function setDefaultValue ($value, $field = '')
    {
        if ( $field != '' ) {
            $this->_defaultValue[$field] = $value;
        } else {
            $this->_defaultValue = $value;
        }
        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getDefaultValue()
     */
    public function getDefaultValue ($name = '')
    {
        if ( $name != '' ) {
            return isset($this->_defaultValue[$name]) ? $this->_defaultValue[$name] : null;
        }
        return $this->_defaultValue;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setFieldName()
     */
    public function setFieldName ($name)
    {
        $this->_fieldName = $name;
        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getFieldName()
     */
    public function getFieldName ()
    {
        return $this->_fieldName . $this->getGridId();
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::normalize()
     */
    public function normalize ($value, $part = '')
    {
        return $value;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::setSelect()
     */
    public function setSelect ($select)
    {
        $this->_select = $select;
        return $this;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getSelect()
     */
    public function getSelect ()
    {
        return $this->_select;
    }


    /**
     * If this input has conditions to be added to the query
     *
     * @return bool
     */
    public function hasConditions ()
    {
        return true;
    }


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::buildQuery()
     */
    public function buildQuery (array $filter)
    {}


    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::getChilds()
     */
    public function getChilds ()
    {}
}