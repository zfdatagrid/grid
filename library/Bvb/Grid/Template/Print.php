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

class Bvb_Grid_Template_Print
{
    public $i;

    /**
     * Options
     * @var array
     */
    public $options = array();

    public function globalStart()
    {
        $return = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $this->options['charEncoding']. "\" />
        </head><body onload='window.print()';>";
        $return .= "<table  border=1 cellspacing=0 cellpadding=0 width='100%'
 style='width:100%;margin-left:-.35pt;border-collapse:collapse;border:none;'>";

        return $return;
    }

    public function header()
    {
        if (isset($this->options['logo']) && is_file ($this->options['logo'])){
            $img = "<img align=\"left\" src=\"" . $this->options['logo'] . "\" border=\"0\">";
        } else {
            $img = '';
        }

        $this->options['title'] = isset($this->options['title'])?$this->options['title']:'';
        $this->options['subtitle'] = isset($this->options['subtitle'])?$this->options['subtitle']:'';

        return " <tr><td colspan=\"{$this->options['colspan']}\" style='border:solid black 1.0pt;background-color:#FFFFFF;color:#000000;padding:5px'> $img <p align=center style='text-align:center'><b><span style='font-size:10.0pt;'><o:p>" . $this->options['title'] . "</o:p></span></b><span style='font-size:9.0pt;'><o:p><br>" . $this->options['subtitle'] . "</o:p></span></p>
  </td></tr>";
    }

    public function globalEnd()
    {
        return "</table></div></body></html>";
    }

    public function titlesStart()
    {
        return "<tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>";
    }

    public function titlesEnd()
    {
        return "</tr>";
    }

    public function titlesLoop()
    {
        return " <td style='border:solid black 1.0pt;background-color:black;color:#FFFFFF;padding:5px'>
          <p align=center style='text-align:center'><b><span style='font-size:10.0pt;'>{{value}}<o:p></o:p></span></b></p>
  </td>";
    }

    public function loopStart()
    {
        $this->i ++;

        return "<tr>";
    }

    public function loopEnd()
    {
        return "</tr>";
    }

    public function loopLoop()
    {
        if ($this->i % 2) {
            return "<td style='border-top:none;border-left:solid black 1.0pt;border-bottom:solid black 1.0pt;
             border-right:solid black 1.0pt; background:#E0E0E0;padding:3px'> <p><span>
             <span style='font-size:8.0pt;font-family:Helvetica;'>{{value}}<o:p></o:p></span></p> </td>";
        } else {
            return "<td style='border-top:none;border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;
            border-right:solid black 1.0pt; padding:3px'> <p class=MsoNormal><span style='font-size:8.0pt;
  font-family:Helvetica;'>{{value}}<o:p></o:p></span></p>
  </td>";
        }
    }

    public function hRow()
    {
        return "<tr><td colspan=\"" . $this->options['colspan']. "\" style='border-top:none; color:#FFFFFF;
        border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;
        padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;
        font-family:Helvetica; '>{{value}}<o:p></o:p></span></p>
  </td></tr>";
    }

    public function noResults()
    {
        return "<tr><td colspan=\"" . $this->options['colspan'] . "\" style='border-top:none; color:#FFFFFF;
        border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;
        padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;
        font-family:Helvetica; '>{{value}}<o:p></o:p></span></p>
  </td></tr>";
    }

    public function sqlExpStart()
    {
        return "<tr>";
    }

    public function sqlExpEnd()
    {
        return "</tr>";
    }

    public function sqlExpLoop()
    {
        return "<td  style='border-top:none;border-left:none;  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;
        padding:5px;'> <p><span style='font-size:8.0pt; font-family:Helvetica;'>{{value}}<o:p></o:p></span></p>
  </td>";
    }
}
