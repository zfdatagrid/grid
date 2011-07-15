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
class Bvb_Grid_Extra_Column {

    /**
     * Columns to be added
     * @var array
     */
    protected $_column;

    /**
     * Add new extra columns
     *
     * @param string $name
     * @param mixed  $args
     */
    public function __call($name, $args)
    {

        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);
        }

        $this->_column[$name] = $args[0];
        return $this;
    }

    public function __construct($name='', $args='')
    {
        if (is_string($args))
            return $this;

        foreach ($args as $key => $value) {
            $this->_column[$key] = $value;
        }

        $this->_column['name'] = $name;
        return $this;
    }

    /**
     * Fecths a column
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return isset($this->_column[$option]) ? $this->_column[$option] : false;
    }

    /**
     * Returns column
     *
     */
    public function getColumn()
    {
        return $this->_column;
    }

    /**
     * Defined a new option for the column
     *
     * @param string     $name
     * @param string|int $value
     * 
     * @return Bvb_Grid_Extra_Column
     */
    public function setOption($name, $value)
    {
        $this->_column[$name] = $value;
        return $this;
    }

}