<?php

/**
 * Mascker
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
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.4  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */



class Bvb_Grid_Form
{
    public  $options;

    public $fields;
    
    public $cascadeDelete;


    function __call($name,$args)
    {
        $this->options[$name] = $args[0];
        return $this;
    }
    
    function onDeleteCascade($options)
    {
        $this->cascadeDelete[] = $options;
        return $this;
        
    }


    function addColumns()
    {
        $columns = func_get_args();

        $final = array();

        foreach ( $columns as $value ) {

            if($value instanceof Bvb_Grid_Form_Column )
            {
                array_push($final,$value);
            }
        }

        $this->fields = $final;
        

        return $this;

    }


}