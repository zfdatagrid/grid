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


class Bvb_Grid_Column
{

    public $_field;


    public function __construct ($field)
    {
        $this->_field['field'] = trim($field);
    }


    public function __call ($name, $args)
    {

        if ( substr(strtolower($name), 0, 3) == 'set' || substr(strtolower($name), 0, 3) == 'add' ) {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);
        }

        $this->_field[$name] = $args[0];
        return $this;
    }

}
