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

class Bvb_Grid_Template_Table
{

    public $hasExtraRow = 0;

    public $hasFilters = 1;

    public $i;

    public $insideLoop;

    public $options;

    public $export;

    public function getClass($name)
    {

        if(isset($this->options['userDefined']['cssClass'][$name])){
            return ' class="'.$this->options['userDefined']['cssClass'][$name].'" ';
        }

        return '';
    }

    public function globalStart ()
    {
        return "<table ".$this->getClass('table')." align=\"center\" cellspacing=\"0\" cellpadding=\"0\">" . PHP_EOL;
    }


    public function globalEnd ()
    {
        return "</table>" . PHP_EOL;
    }


    public function extra ()
    {
        return "    <tr>" . PHP_EOL . "     <td  ".$this->getClass('topRow')."  colspan=\"{$this->options['colspan']}\"><div >{{value}}</div></td></tr>";
    }


    public function titlesStart ()
    {
        return "    <tr>";
    }


    public function titlesEnd ()
    {
        return "    </tr>" . PHP_EOL;
    }


    public function titlesLoop ()
    {
        return "    <th  {{colspan}}>{{value}}</th>" . PHP_EOL;
    }


    public function filtersStart ()
    {
        return "    <tr>" . PHP_EOL;
    }


    public function filtersEnd ()
    {
        return "    </tr>" . PHP_EOL;
    }


    public function noResults ()
    {
        return "      <td  colspan=\"{$this->options['colspan']}\"  ".$this->getClass('noRecords')." >{{value}}</div>" . PHP_EOL;
    }


    public function filtersLoop ()
    {
        return "        <td  {{colspan}}  ".$this->getClass('filters')."  >{{value}}</td>" . PHP_EOL;
    }


    public function hRow ($values)
    {
        return "        <td  colspan=\"{$this->options['colspan']}\"  ".$this->getClass('hBar'). "><div>{{value}}</div></td>" . PHP_EOL;
    }


    public function loopStart ($class, $style)
    {
        $this->i ++;
        $this->insideLoop = 1;

        if ( strlen($class) > 0 ) {
            $class = " class='$class' ";
        }

        if ( strlen($style) > 0 ) {
            $style = " style='$style' ";
        }

        return "<tr $class $style>";
    }


    public function loopEnd ()
    {
        return "    </tr>" . PHP_EOL;
    }


    public function formMessage ($ok = false)
    {
        if ( $ok ) {
            $class = $this->getClass('formMessageOk');
        } else {
            $class = $this->getClass('formMessageError');
        }
        return "<div $class >{{value}}</div>";
    }


    public function loopLoop ()
    {
        return "        <td {{class}} {{style}} {{rowspan}} {{colspan}}>{{value}}</td>" . PHP_EOL;
    }


    public function sqlExpStart ()
    {
        return "    <tr>" . PHP_EOL;
    }


    public function sqlExpEnd ()
    {
        return "    </tr>" . PHP_EOL;
    }


    public function sqlExpLoop ()
    {
        return "     <td ".$this->getClass('sqlExp').">{{value}}</td>" . PHP_EOL;
    }


    public function pagination ()
    {
        return "    <tr>" . PHP_EOL . "     <td ".$this->getClass('tableFooter')." colspan=\"{$this->options['colspan']}\"><div>
        <div ".$this->getClass('tableFooterExport').">" . $this->export . "</div>
        <div ".$this->getClass('tableFooterPagination')."> <em>({{numberRecords}})</em> {{pagination}}  {{perPage}}  {{pageSelect}}</div>
        </div>
        </td>" . PHP_EOL . "</tr>" . PHP_EOL;
    }


    public function images ($url)
    {
        return array('asc' => "<img src=\"" . $url . "arrow_up.gif\" border=\"0\" />", 'desc' => "<img src=\"" . $url . "arrow_down.gif\" border=\"0\" />", 'delete' => "<img src=\"" . $url . "delete.png\" border=\"0\" />", 'detail' => "<img src=\"" . $url . "detail.png\" border=\"0\" />", 'edit' => "<img src=\"" . $url . "edit.png\"  border=\"0\" />");
    }


    public function detail ()
    {
        return "    <tr>" . PHP_EOL . "     <td ".$this->getClass('detailLeft').">{{field}}</td><td  ".$this->getClass('detailRight').">{{value}}</td>" . PHP_EOL . "</tr>" . PHP_EOL;
    }


    public function detailEnd ()
    {
        return "    <tr>" . PHP_EOL . "     <td colspan='2'><a href='{{url}}'>{{return}}</a></td>" . PHP_EOL . "    </tr>" . PHP_EOL;
    }


    public function detailDelete ()
    {
        return "<tr><td colspan='2'>{{button}}</td></tr>" . PHP_EOL;
    }


    public function export ($exportDeploy, $images, $url, $gridId)
    {
        $exp = '';
        foreach ( $exportDeploy as $export ) {
            $caption = sprintf(Bvb_Grid_Translator::getInstance()->__('Export to %s format'), $export['caption']);


            $export['newWindow'] = isset($export['newWindow']) ? $export['newWindow'] : true;
            $class = isset($export['cssClass']) ? 'class="' . $export['cssClass'] . '"' : '';

            $blank = $export['newWindow'] == false ? '' : "target='_blank'";

            if ( strlen($images) > 1 ) {
                $export['img'] = $images . $export['caption'] . '.gif';
            }

            if ( isset($export['img']) ) {
                $exp .= "<a title='$caption' $class $blank href='$url/_exportTo$gridId/{$export['caption']}'><img alt='{$export['caption']}' src='{$export ['img']}' border='0'></a>";
            } else {
                $exp .= "<a title='$caption'  $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>" . $export['caption'] . "</a>";
            }
        }

        $this->exportWith = 25 * count($exportDeploy);
        $this->paginationWith = 630 + (10 - count($exportDeploy)) * 20;

        $this->export = $exp;

        return $exp;
    }


    public function scriptOnAjaxOpen ($element)
    {
        return "document.getElementById(ponto).innerHTML= '<div style=\"width:'+(document.getElementById('" . $element . "').offsetWidth - 2)+'px;height:'+(document.getElementById('" . $element . "').offsetHeight - 2)+'px;\" ".$this->getClass('gridLoading').">&nbsp;</div>'";
    }


    public function scriptOnAjaxResponse ($element)
    {
        return 'document.getElementById(ponto).innerHTML=xmlhttp.responseText';
    }


    public function scriptOnAjaxStateChange ($element)
    {
        return '';
    }
}
