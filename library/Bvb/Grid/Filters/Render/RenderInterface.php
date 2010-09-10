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
 * @version    $Id: RenderAbstract.php 1186 2010-05-21 18:16:48Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

interface Bvb_Grid_Filters_Render_RenderInterface
{


    /**
     * Returns the current View
     *
     * @return Zend_View_Abstract
     */
    public function getView ();


    /**
     * Defines the translator instance to be used
     * @param Zend_Translate $translate
     */
    public function setTranslator ($translate);


    /**
     * Returns current translator
     *
     * @return Zend_Translate
     */
    public function getTranslator ();


    /**
     * Translates a string
     *
     * @param string $name
     *
     * @return string
     */
    public function __ ($name);


    /**
     * Returns currents input attributes
     *
     * @return array
     */
    public function getAttributes ();


    /**
     * Returns the requested atttribute if it exists
     *
     * @param string $name Attributes name
     *
     * @return mixed
     */
    public function getAttribute ($name);


    /**
     * Sets the current view
     *
     * @param Zend_View_Abstract $_view
     */
    public function setView ($_view);


    /**
     * Defines multiples atttributes for the current input
     *
     * @param array $_attributes
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function setAttributes ($_attributes);


    /**
     * Defines a sinle attribute to the current input
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute ($name, $value);


    /**
     * Removes an attribute from the input
     *
     * @param string $name
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function removeAttribute ($name);


    /**
     * Defines values for current inputs
     *
     * @param array $options
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function setValues (array $options);


    /**
     * Returns currents inputs values
     *
     * @return array
     */
    public function getValues ();


    /**
     * Defines the default value for the input
     *
     * @param string $value
     * @param string $field
     */
    public function setDefaultValue ($value, $field = '');


    /**
     * Returns the default value for the input
     *
     * @param string $name
     *
     * @return string
     */
    public function getDefaultValue ($name = '');


    /**
     * Sets the current field name
     *
     * @param string $name
     */
    public function setFieldName ($name);


    /**
     * Returns the current field name
     *
     * @return string
     */
    public function getFieldName ();


    /**
     * Normalizes user's input to a compatible value (think about dates)
     *
     * @param string $value
     * @param string $part
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function normalize ($value, $part = '');


    /**
     * Sets the current data source instance
     *
     * @param object $select
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function setSelect ($select);


    /**
     * Returns the current Data Source Instance
     *
     * @return Bvb_Grid_Source_Interface
     */
    public function getSelect ();


    /**
     * Returns the current element childs.
     * In a date example could be from and to
     *
     * @return array
     */
    public function getChilds ();


    /**
     * Builds the query
     *
     * @param array $filter
     *
     * @return Bvb_Grid_Filters_Render_RenderAbstract
     */
    public function buildQuery (array $filter);


    /**
     * Renders the current input
     *
     * @return string
     */
    public function render ();
}