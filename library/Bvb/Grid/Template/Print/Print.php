<?php
/**
 * Mascker
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
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */
class Bvb_Grid_Template_Print_Print
{

    public $i;

    public $colSpan;
    
    public $printOptions ;
    
    function __construct($options = array())
    {     
        $this->printOptions = $options;
    }

    function globalStart ()
    {
        $return = "<html><head></head><body onload='window.print()';>";
        $return .= "<table  border=1 cellspacing=0 cellpadding=0 width='100%'
 style='width:100%;margin-left:-.35pt;border-collapse:collapse;border:none;'>";

        return $return;
    }


    function header()
    {
        
        if(file_exists(@$this->printOptions['logo']))
        {
            $img = "<img align=\"left\" src=\"".$this->printOptions['baseUrl'].$this->printOptions['logo']."\" border=\"0\">";
        }else{
            $img  ='';
        }
        
        return  " <tr><td colspan=\"$this->colSpan\" style='border:solid black 1.0pt;background-color:#FFFFFF;color:#000000;padding:5px'> $img <p align=center style='text-align:center'><b><span style='font-size:10.0pt;'><o:p>".@$this->printOptions['title']."</o:p></span></b><span style='font-size:9.0pt;'><o:p><br>".@$this->printOptions['subtitle']."</o:p></span></p>
  </td></tr>";
    }


    function globalEnd ()
    {
        return "</table></div></body></html>";
    }


    function titlesStart ()
    {
        return "<tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>";
    }

    function titlesEnd ()
    {
        return "</tr>";
    }


    function titlesLoop ()
    {
        return " <td style='border:solid black 1.0pt;background-color:black;color:#FFFFFF;padding:5px'>  <p align=center style='text-align:center'><b><span style='font-size:10.0pt;'>{{value}}<o:p></o:p></span></b></p>
  </td>";
    }


    function loopStart ()
    {
        $this->i++;

        return "<tr>";
    }



    function loopEnd ()
    {
        return "</tr>";
    }



    function loopLoop ()
    {


        if($this->i%2)
        {
            return "<td style='border-top:none;border-left:solid black 1.0pt;border-bottom:solid black 1.0pt;   border-right:solid black 1.0pt; background:#E0E0E0;padding:3px'> <p><span><span style='font-size:8.0pt;font-family:Helvetica;'>{{value}}<o:p></o:p></span></p> </td>";

        }else{
            return "<td style='border-top:none;border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px'> <p class=MsoNormal><span style='font-size:8.0pt;
  font-family:Helvetica;'>{{value}}<o:p></o:p></span></p>
  </td>";
        }

    }


    function hRowLoop ()
    {
        return "<tr><td colspan=\"".$this->colSpan."\" style='border-top:none; color:#FFFFFF; border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;  font-family:Helvetica; '>{{value}}<o:p></o:p></span></p>
  </td></tr>";
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
        return "<td  style='border-top:none;border-left:none;  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:5px;'> <p><span style='font-size:8.0pt; font-family:Helvetica;'>{{value}}<o:p></o:p></span></p>
  </td>";
    }


}

