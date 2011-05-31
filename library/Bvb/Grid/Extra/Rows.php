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
class Bvb_Grid_Extra_Rows {

    /**
     * Rows to be added
     * @var array
     */
    public   $_rows;

    /**
     * Adds a new row
     *
     * @param string $position Row position
     * @param array  $rows Rows properties
     */
    public function addRow($position,$rows)
    {
        $this->_rows[] = array('position'=>$position,'values'=>$rows);
    }

}