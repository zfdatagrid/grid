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
 * @version    $Id: Outside.php 1513 2010-12-11 00:12:31Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */
class My_Template_Table_Horizontal extends Bvb_Grid_Template_Table {

    public $buildAbstract = true;

    public function globalEnd()
    {

        $print = '';
        $print .= "<table " . $this->getClass('table') . " align=\"center\" cellspacing=\"0\" cellpadding=\"0\">";


        if (isset($this->result['titles'])) {

            for ($a = 0; $a < count($this->result['titles']); $a++) {

                $print .= "<tr>";

                $print .= "<th>" . $this->result['titles'][$a][0] . "</th>";

                if (isset($this->result['filters'])) {
                    $print .= "<td " . $this->getClass('filters') . " >" . $this->result['filters'][$a][0] . "</td>";
                }

                for ($i = 1; $i <= count($this->result['loop']); $i++) {
                    $print .= "<td " . $this->buildAttr('class', $this->result['loop'][$i][$a][1]) . "" . 
                            $this->buildAttr('style', $this->result['loop'][$i][$a][2]) . " >" . 
                                    $this->result['loop'][$i][$a][0] . "</td>";
                }

                if (isset($this->result['sql'])) {
                    $print .= "<td  " . $this->buildAttr('class', $this->result['sql'][$a][1]) . "" . 
                            $this->getClass('sqlExp') . ">" . $this->result['sql'][$a][0] . "</td>";
                }


                $print .= "</tr>";
            }

            $print .= " <tr> ";

            $print .= "<td " . $this->getClass('tableFooter') . " colspan='" . 
                    (count($this->result['loop']) + 1) . "'><div>
                    <div " . $this->getClass('tableFooterExport') . ">" . $this->export . "</div>
                    <div " . $this->getClass('tableFooterPagination') . "> <em>{$this->result['pagination'][1]}</em> 
                    {$this->result['pagination'][0]}  {$this->result['pagination'][2]}  
                    {$this->result['pagination'][3]}</div>
                    </div>
                    </td>";

            $print .= " </tr> ";
        }

        if (isset($this->result['detail'])) {

            $this->i++;
            if ($this->i != 1)
                return;

            foreach ($this->result['detail'] as $value) {
                $print .= "<tr><th>{$value[0]}</th><td>{$value[1]}</td></tr>";
            }

            if (isset($this->result['detailEnd'])) {

                $print .= "<tr><td colspan='2'><button type='button' class='detailReturn' 
                onclick='window.location=\"{$this->result['detailEnd'][0][0]}\"';>
                {$this->result['detailEnd'][0][1]}</button></td></td></tr>";
            }
        }

        $print .= "</table>";


        return $print;
    }

}