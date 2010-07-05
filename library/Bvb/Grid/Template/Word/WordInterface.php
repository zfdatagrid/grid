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


interface Bvb_Grid_Template_Word_WordInterface

{


    public function globalStart ();


    public function globalEnd ();


    public function titlesStart ();


    public function titlesEnd ();


    public function titlesLoop ();


    public function noResults ();


    public function hRow ();


    public function loopStart ();


    public function loopEnd ();


    public function loopLoop ();


    public function sqlExpStart ();


    public function sqlExpEnd ();


    public function sqlExpLoop ();


}

