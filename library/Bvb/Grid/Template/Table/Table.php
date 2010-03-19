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

    public $export;


    public function globalStart ()
    {
        return "<table  width=\"100%\" class=\"borders\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\">";
    }


    public function globalEnd ()
    {
        return "</table>";
    }


    public function extra ()
    {
        return "<tr><td  colspan=\"{$this->options['colspan']}\" class=\"querySupport\"><div style=\"text-align:right;\">{{value}}</div></td></tr>";
    }


    public function titlesStart ()
    {
        return "<tr>";
    }


    public function titlesEnd ()
    {
        return "</tr>";
    }


    public function titlesLoop ()
    {
        return "<th>{{value}}</th>";
    }


    public function filtersStart ()
    {
        return "<tr>";
    }


    public function filtersEnd ()
    {
        return "</tr>";
    }


    public function noResults ()
    {
        return "<td  colspan=\"{$this->options['colspan']}\" style=\"padding:10px;text-align:center;color:brown;font-size:14px;\">{{value}}</div>";
    }


    public function filtersLoop ()
    {
        return "<td class=\"subtitulo\" >{{value}}</td>";
    }


    public function hRow ($values)
    {
        return "<td  colspan=\"{$this->options['colspan']}\" class=\"hbar\"><div>{{value}}</div></td>";
    }


    public function loopStart ($class)
    {
        $this->i ++;
        $this->insideLoop = 1;

        if ( strlen($class) > 0 ) {
            $class = " class='$class' ";
        }

        return "<tr $class>";
    }


    public function loopEnd ()
    {
        return "</tr>";
    }


    public function formMessage ($ok = false)
    {

        if ( $ok ) {
            $class = "";
        } else {
            $class = "_red";
        }
        return "<div class=\"alerta$class\">{{value}}</div>";
    }


    public function loopLoop ()
    {
        return "<td class=\"{{class}} \" style=\"{{style}}\" >{{value}}</td>";
    }


    public function sqlExpStart ()
    {
        return "<tr>";
    }


    public function sqlExpEnd ()
    {
        return "</tr>";
    }


    public function sqlExpLoop ()
    {
        return "<td class=\"sum {{class}}\">{{value}}</td>";
    }


    public function pagination ()
    {
        return "<tr><td class=\"barra_tabela\" colspan=\"{$this->options['colspan']}\"><div>
        <div style=\"float:left;width:250px;\">" . $this->export . "</div>
        <div style=\"float:left;text-align:center;width:630px;\"> <em>({{numberRecords}})</em>  | {{pagination}}</div>
        </div>
        </td></tr>";
    }


    public function images ($url)
    {
        return array('asc' => "<img src=\"" . $url . "seta_cima.gif\" border=\"0\">", 'desc' => "<img src=\"" . $url . "seta_baixo.gif\" border=\"0\">", 'delete' => "<img src=\"" . $url . "delete.png\" border=\"0\">", 'detail' => "<img src=\"" . $url . "detail.png\" border=\"0\">", 'edit' => "<img src=\"" . $url . "edit.png\"  border=\"0\">");
    }


    public function detail ()
    {
        return "<tr><td class='detailLeft'>{{field}}</td><td class='detailRight'>{{value}}</td></tr>";
    }


    public function detailEnd ()
    {
        return "<tr><td colspan='2'><a href='{{url}}'>{{return}}</a></td></tr>";
    }


    public function detailDelete ()
    {
        return "<tr><td colspan='2'>{{button}}</td></tr>";
    }


    public function export ($exportDeploy, $images, $url, $gridId)
    {

        $exp = '';
        foreach ( $exportDeploy as $export ) {
            $export['newWindow'] = isset($export['newWindow']) ? $export['newWindow'] : true;
            $class = isset($export['cssClass']) ? 'class="' . $export['cssClass'] . '"' : '';

            $blank = $export['newWindow'] == false ? '' : "target='_blank'";

            if ( strlen($images)>1) {
                $export['img'] = $images . $export['caption'] . '.gif';
            }

            if ( isset($export['img']) ) {
                $exp .= "<a title='{$export['caption'] }' $class $blank href='$url/_exportTo$gridId/{$export['caption']}'><img alt='{$export['caption']}' src='{$export ['img']}' border='0'></a>";
            } else {
                $exp .= "<a title='{$export['caption'] }'  $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>" . $export['caption'] . "</a>";
            }
        }

       $this->export = $exp;

        return $exp;
    }

}

