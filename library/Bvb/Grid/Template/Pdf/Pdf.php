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
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


class Bvb_Grid_Template_Pdf_Pdf
{

    public  $pdfOptions;

    function __construct($options = array())
    {
        $this->pdfOptions = $options;
    }


    function info()
    {
        $pdf = array(
        'logo'=>'public/images/logo.png',
        'title'=>'DataGrid Zend Framework',
        'subtitle'=>'Easy and powerfull - (Demo document)',
        'footer'=>'Downloaded from: http://www.petala-azul.com ',
        'size'=>'a4', #letter
        'orientation'=>'', #landscape
        'page'=>'Page N.');

        $pdf = array_merge($pdf,$this->pdfOptions);

        return $pdf;
    }


    function style()
    {

        $style = array(
        'title'=>'#000000',
        'subtitle'=>'#111111',
        'footer'=>'#111111',
        'header'=>'#AAAAAA',
        'row1'=>'#EEEEEE',
        'row2'=>'#FFFFFF',
        'sqlexp'=>'#BBBBBB',
        'lines'=>'#111111',
        'hrow'=>'#E4E4F6',
        'text'=>'#000000'
        );


        return $style;
    }

}


