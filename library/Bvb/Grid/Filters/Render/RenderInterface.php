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
     * @return the $_view
     */
    public function getView ();


    public function setTranslator ($translate);


    public function getTranslator ();


    public function __ ($name);


    /**
     * @return the $_attributes
     */
    public function getAttributes ();


    public function getAttribute ($name);


    /**
     * @param $_view the $_view to set
     */
    public function setView ($_view);


    /**
     * @param $_attributes the $_attributes to set
     */
    public function setAttributes ($_attributes);


    public function setAttribute ($name, $value);


    public function removeAttribute ($name);


    public function setValues (array $options);


    public function getValues ();


    public function setDefaultValue ($value, $field = '');


    public function getDefaultValue ($name = '');


    public function setFieldName ($name);


    public function getFieldName ();


    public function normalize ($value, $part = '');


    public function setSelect ($select);


    public function getSelect ();


    public function getChilds ();


    public function buildQuery (array $filter);


    public function render ();
}