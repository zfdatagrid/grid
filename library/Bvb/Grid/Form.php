<?php
/**
 * Mascker
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License 2.0
 * It is  available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/gpl-2.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */

class Bvb_Grid_Form
{
    public  $options;

    public $fields;


    function __call($name,$args)
    {
        $this->options[$name] = $args[0];
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