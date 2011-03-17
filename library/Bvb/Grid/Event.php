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
 * @version   $Id: Select.php 1645 2011-03-08 20:08:43Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */
class Bvb_Grid_Event
{

    /**
     * @var mixed Object Name
     */
    protected $_object = null;
    /**
     *
     * @var string
     */
    protected $_name = null;
    /**
     *
     * @var mixed Event Args
     */
    protected $_params = null;

    public function __construct($name, $object, $params)
    {
        $this->_name = $name;
        $this->_object = $object;
        $this->_params = $params;
    }

    /**
     * Returns current event name
     *
     * @return string Event Name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns current event Params
     *
     * @return mixed Event Params
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Returns current event Params
     *
     * @return mixed Event Params
     */
    public function getParam($name)
    {
        return isset($this->_params['$name']) ? $this->_params[$name] : null;
    }

    /**
     * Returns current event Params
     *
     * @return mixed Class where event was called
     */
    public function getObject()
    {
        return $this->_object;
    }

}