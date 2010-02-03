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


class Bvb_Grid_Template_Table_Table
{

    public $hasExtraRow = 0;

    public $hasFilters = 1;

    public $i;

    public $insideLoop;

    public $options;


    function globalStart ()
    {
        return "<table  width=\"100%\" class=\"borders\" align=\"center\" cellspacing=\"0\" celpadding=\"0\">";
    }


    function globalEnd ()
    {
        return "</table>";
    }


    function extra ()
    {
        return "<tr><td  colspan=\"{$this->options['colspan']}\" class=\"querySupport\"><div style=\"text-align:right;\">{{value}}</div></td></tr>";
    }

    function titlesStart ()
    {
        return "<tr>";
    }

    function titlesEnd ()
    {
        return "</tr>";
    }



    function titlesLoop ()
    {
        return "<th>{{value}}</th>";
    }



    function filtersStart ()
    {
        return "<tr>";
    }



    function filtersEnd ()
    {
        return "</tr>";
    }


    function noResults ()
    {
        return "<td  colspan=\"{$this->options['colspan']}\" style=\"padding:10px;text-align:center;color:brown;font-size:14px;\">{{value}}</div>";
    }


    function filtersLoop ()
    {
        return "<td class=\"subtitulo\" >{{value}}</td>";
    }


    function hRow ($values)
    {
        return "<td  colspan=\"{$this->options['colspan']}\" class=\"hbar\"><div>{{value}}</div></td>";
    }



    function loopStart ($values)
    {
        $this->i ++;

        $this->insideLoop = 1;

        return "<tr>";
    }



    function loopEnd ($values)
    {
        return "</tr>";
    }


    function formMessage ($ok = false)
    {

        if ($ok) {
            $class = "";
        } else {
            $class = "_red";
        }
        return "<div class=\"alerta$class\">{{value}}</div>";
    }

    function loopLoop ($values)
    {
        $class = $this->i % 2 ? "alt" : "";
        return "<td class=\"$class {{class}} \" >{{value}}</td>";
    }



    function sqlExpStart ()
    {
        return "<tr>";
    }



    function sqlExpEnd ()
    {
        return "</tr>";
    }



    function sqlExpLoop ()
    {
        return "<td class=\"sum {{class}}\">{{value}}</td>";
    }


    function pagination ()
    {
        return "<tr><td class=\"barra_tabela\" colspan=\"{$this->options['colspan']}\"><div>
        <div style=\"float:left;width:220px;\">{{export}}</div>
        <div style=\"float:left;text-align:center;width:520px;\"> <em>({{numberRecords}})</em>  | {{pagination}}</div>
        <div style=\"float:right;width:50px;\">{{pageSelect}}</div>
        </div>
        </td></tr>";
    }


    function images ($url)
    {

        return array('asc' => "<img src=\"" . $url . "seta_cima.gif\" border=\"0\">", 'desc' => "<img src=\"" . $url . "seta_baixo.gif\" border=\"0\">", 'delete' => "<img src=\"" . $url . "delete.png\" border=\"0\">", 'edit' => "<img src=\"" . $url . "edit.png\"  border=\"0\">", 'excel' => "<img title=\"Microsoft Excel\"  src=\"" . $url . "excel.gif\"  border=\"0\">", 'word' => "<img  title=\"Microsoft Office 2003\" src=\"" . $url . "word.gif\"  border=\"0\">", 'wordx' => "<img title=\"Microsoft Office 2007\" src=\"" . $url . "wordx.gif\"  border=\"0\">", 'pdf' => "<img title=\"PDF\"  src=\"" . $url . "pdf.gif\"  border=\"0\">", 'csv' => "<img src=\"" . $url . "csv.gif\"  border=\"0\">", 'ods' => "<img src=\"" . $url . "ods.gif\" title=\"OpenOffice Spreadsheet\"  border=\"0\">", 'odt' => "<img src=\"" . $url . "odt.gif\"  title=\"OpenOffice Document Text\"  border=\"0\">", 'xml' => "<img src=\"" . $url . "xml.gif\"  border=\"0\">", 'print' => "<img  src=\"" . $url . "print.gif\"  border=\"0\">");
    }


}

