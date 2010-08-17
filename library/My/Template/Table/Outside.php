<?php

/**
 *
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
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class My_Template_Table_Outside extends Bvb_Grid_Template_Table
{

    public $ic;

    public $insideLoop;

    public $go = 0;


    public function globalStart()
    {
        return "<table id=\"newGrid\" width=\"100%\"  align=\"center\" cellspacing=\"1\" >";
    }


    public function loopStart($values)
    {
        $this->i++;
        return "<tr  >";
    }


    public function loopLoop()
    {
        $class = $this->i % 2 ? "alt" : "";
        return "<td  class=\"$class {{class}}\" >{{value}}&nbsp;</td>";
    }

}

