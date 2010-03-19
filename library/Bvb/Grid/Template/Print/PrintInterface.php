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



interface  Bvb_Grid_Template_Print_PrintInterface

{

    function globalStart ();



    function globalEnd ();



    function header ();



    function titlesStart ();



    function titlesEnd ();




    function titlesLoop ();




    function noResults();





    function hRow();




    function loopStart ();




    function loopEnd ();




    function loopLoop ();




    function sqlExpStart ();




    function sqlExpEnd ();




    function sqlExpLoop ();


}

