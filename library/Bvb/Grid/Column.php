<?php
/**
 * Mascker
 *
 * LICENSE
 *
 * This source file is subject to the Attribution-No Derivative Works license
 * It is  available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nd/3.0/us/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com.com)
 * @license    http://creativecommons.org/licenses/by-nd/3.0/us/    Attribution-No Derivative Works license
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */

class Bvb_Grid_Column
{

    public $_field;

    function __construct($field)
    {
        $this->_field['field'] = trim($field);
    }

    function __call($name,$args)
    {

        if(substr(strtolower($name),0,3)=='set' || substr(strtolower($name),0,3)=='add')
        {
            $name = substr($name,3);
            $name[0] = strtolower($name[0]);
        }

        $this->_field[$name] = $args[0];
        return $this;
    }

}