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
class Bvb_Grid_Template_Table {

    public $hasExtraRow = 0;
    public $hasFilters = 1;
    public $i = 0;
    public $insideLoop;
    public $options;
    public $export;
    
    public $buildAbstract = false;


    public $result = array();

    public function buildAttr($class, $value)
    {
        if (strlen($value) == 0) {
            return '';
        }

        return $class . "='$value'";
    }

    public function getClass($name)
    {

        if (isset($this->options['userDefined']['cssClass'][$name])) {
            return ' class="' . $this->options['userDefined']['cssClass'][$name] . '" ';
        }

        return '';
    }

    public function globalStart()
    {
        if($this->buildAbstract)
                return;
        
        return "<table " . $this->getClass('table') . " align=\"center\" ".
               "cellspacing=\"0\" cellpadding=\"0\">" . PHP_EOL;
    }

    public function globalEnd()
    {
        
        if($this->buildAbstract)
                return;
        
        return "</table>" . PHP_EOL;
    }

    public function extra($value)
    {
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL . "     <td " . $this->getClass('topRow') .
               " colspan=\"{$this->options['colspan']}\"><div >$value</div></td></tr>";
    }

    public function titlesStart()
    {
        if($this->buildAbstract)
                return;
        return "    <tr>";
    }

    public function titlesEnd()
    {
        return "    </tr>" . PHP_EOL;
    }

    public function titlesLoop($title, $colspan)
    {
        $this->result['titles'][] = func_get_args();
        
        if($this->buildAbstract)
                return;
        return  "    <th " . $this->buildAttr('colspna', $colspan) . ">$title</th>" . PHP_EOL;
    }

    public function filtersStart()
    {
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL;
    }

    public function filtersEnd()
    {
        if($this->buildAbstract)
                return;
        return "    </tr>" . PHP_EOL;
    }

    public function noResults($message)
    {
        if($this->buildAbstract)
                return;
        return "      <tr><td  colspan=\"{$this->options['colspan']}\"  " . 
               $this->getClass('noRecords') . " >$message</div></td></tr>" . PHP_EOL;
    }

    public function filtersLoop($value, $colspan)
    {
        $this->result['filters'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return  "        <td " . $this->buildAttr('colspan', $colspan) . "" . 
                $this->getClass('filters') . "  >$value</td>" . PHP_EOL;
    }

    public function hRow($value)
    {
        if($this->buildAbstract)
                return;
        return "        <td  colspan=\"{$this->options['colspan']}\"  " . 
               $this->getClass('hBar') . "><div>$value</div></td>" . PHP_EOL;
    }

    public function loopStart($class, $style)
    {
        $this->i++;
        $this->insideLoop = 1;

        if($this->buildAbstract)
                return;
        return "<tr " . $this->buildAttr('class', $class) . " " . $this->buildAttr('style', $style) . ">";
    }

    public function loopEnd()
    {
        if($this->buildAbstract)
                return;
        return "    </tr>" . PHP_EOL;
    }

    public function formMessage($sucess, $message)
    {
        if ($sucess) {
            $class = $this->getClass('formMessageOk');
        } else {
            $class = $this->getClass('formMessageError');
        }
        return "<div $class >$message</div>";
    }

    public function loopLoop($value, $class, $style, $rowspan, $colspan)
    {
        $this->result['loop'][$this->i][] = func_get_args();
        if($this->buildAbstract)
                return;
        return "        <td " . $this->buildAttr('class', $class) . " " . $this->buildAttr('style', $style) . " " . 
                $this->buildAttr('rowspan', $rowspan) . " " . 
                $this->buildAttr('colspan', $colspan) . ">$value</td>" . PHP_EOL;
    }

    public function sqlExpStart()
    {
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL;
    }

    public function sqlExpEnd()
    {
        if($this->buildAbstract)
                return;
        return "    </tr>" . PHP_EOL;
    }

    public function sqlExpLoop($value, $class)
    {
        $this->result['sql'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return  "     <td " . $this->buildAttr('class', $class) . "" . 
                $this->getClass('sqlExp') . ">$value</td>" . PHP_EOL;
    }

    public function pagination($pagination, $numberRecords,  $perPage, $pageSelect)
    {
        $this->result['pagination'] = func_get_args();
        if($this->buildAbstract)
                return;
        return "    <tfoot><tr>" . PHP_EOL . "     <td " . $this->getClass('tableFooter') . 
                " colspan=\"{$this->options['colspan']}\"><div>
                <div " . $this->getClass('tableFooterExport') . ">" . $this->export . "</div>
                <div " . $this->getClass('tableFooterPagination') . "> <em>$numberRecords</em> $pagination  $perPage  
                        $pageSelect</div>
                </div>
                </td>" . PHP_EOL . "</tr></tfoot>" . PHP_EOL;
    }

    public function images($url)
    {
        return array('asc' => "<img src=\"" . $url . "arrow_up.gif\" border=\"0\" />",
                     'desc' => "<img src=\"" . $url . "arrow_down.gif\" border=\"0\" />", 
                     'delete' => "<img src=\"" . $url . "delete.png\" border=\"0\" />", 
                     'detail' => "<img src=\"" . $url . "detail.png\" border=\"0\" />", 
                     'edit' => "<img src=\"" . $url . "edit.png\"  border=\"0\" />");
    }

    public function startDetail($title)
    {
        
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL . "     <th colspan='2' " . $this->getClass('detailLeft') . ">$title</th>" 
                . PHP_EOL . "</tr>" . PHP_EOL;
    }

    public function detail($field, $value)
    {
        $this->result['detail'][] = func_get_args();
        
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL . "     <td " . $this->getClass('detailLeft') . ">$field</td><td  " . 
                $this->getClass('detailRight') . ">$value</td>" . PHP_EOL . "</tr>" . PHP_EOL;
    }

    public function detailEnd($url, $text)
    {
        $this->result['detailEnd'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL . "     <td colspan='2' class='detailEnd'><button type='button' class='detailReturn' 
               onclick='window.location=\"$url\"';>$text</button></td>" . PHP_EOL . " </tr>" . PHP_EOL;
    }

    public function detailDelete($button)
    {
        $this->result['detailDelete'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return "<tr><td colspan='2'>$button</td></tr>" . PHP_EOL;
    }

    public function export($exportDeploy, $images, $url, $gridId)
    {
        $exp = '';
        foreach ($exportDeploy as $export) {
            $caption = sprintf(Bvb_Grid_Translator::getInstance()->__('Export to %s format'), $export['caption']);


            $export['newWindow'] = isset($export['newWindow']) ? $export['newWindow'] : true;
            $class = isset($export['cssClass']) ? 'class="' . $export['cssClass'] . '"' : '';

            $blank = $export['newWindow'] == false ? '' : "target='_blank'";

            if (strlen($images) > 1) {
                $export['img'] = $images . $export['caption'] . '.gif';
            }

            if (isset($export['img'])) {
                $exp .= "<a title='$caption' $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>
                        <img alt='{$export['caption']}' src='{$export ['img']}' border='0'></a>";
            } else {
                $exp .= "<a title='$caption'  $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>" . 
                      $export['caption'] . "</a>";
            }
        }

        $this->exportWith = 25 * count($exportDeploy);
        $this->paginationWith = 630 + (10 - count($exportDeploy)) * 20;

        $this->export = $exp;

        return $exp;
    }

    public function scriptOnAjaxOpen($element)
    {
        return "document.getElementById(ponto).innerHTML= '<div style=\"width:'+(document.getElementById('" . $element . "').offsetWidth - 2)+'px;height:'+(document.getElementById('" . $element . "').offsetHeight - 2)+'px;\" " . $this->getClass('gridLoading') . ">&nbsp;</div>'";
    }

    public function scriptOnAjaxResponse($element)
    {
        return 'document.getElementById(ponto).innerHTML=xmlhttp.responseText';
    }

    public function scriptOnAjaxStateChange($element)
    {
        return '';
    }

}
