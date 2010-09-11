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

class Bvb_Grid_Template_Word
{
    /**
     * [PT] A colspan das td's. Este valor é recebido pelo classe mão.
     *
     * @var int
     */
    public $colSpan;

    /**
     * [PT] U array com as opções do documento
     *
     * possiveis: title
     *
     * @var array
     */
    public  $wordOptions;

    /**
     * [PT] Valor para depois poder-mos fazer zebra na listagem de resultados
     *
     * @var array
     */
    public $i;

    /**
     * Options
     * @var array
     */
    public $options;

    public function globalStart()
    {
        $xml  = "<html xmlns:v=\"urn:schemas-microsoft-com:vml\"
xmlns:o=\"urn:schemas-microsoft-com:office:office\"
xmlns:w=\"urn:schemas-microsoft-com:office:word\"
xmlns:m=\"http://schemas.microsoft.com/office/2004/12/omml\"
xmlns:css=\"http://macVmlSchemaUri\" xmlns=\"http://www.w3.org/TR/REC-html40\">

<head>
<meta name=Title content=\"{$this->options['title']}\">
<meta name=Keywords content=\"\">
<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">
<meta name=ProgId content=Word.Document>
<meta name=Generator content=\"Microsoft Word 2008\">
<meta name=Originator content=\"Microsoft Word 2008\">
<title>{$this->options['title']}</title>
<!--[if gte mso 9]><xml>
 <o:OfficeDocumentSettings>
  <o:AllowPNG/>
 </o:OfficeDocumentSettings>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>BestFit</w:Zoom>
  <w:SpellingState>Clean</w:SpellingState>
  <w:GrammarState>Clean</w:GrammarState>
  <w:TrackMoves>false</w:TrackMoves>
  <w:TrackFormatting/>
  <w:DoNotHyphenateCaps/>
  <w:PunctuationKerning/>
  <w:DrawingGridHorizontalSpacing>9,35 pt</w:DrawingGridHorizontalSpacing>
  <w:DrawingGridVerticalSpacing>9,35 pt</w:DrawingGridVerticalSpacing>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:Compatibility>
   <w:SplitPgBreakAndParaMark/>
   <w:DontVertAlignCellWithSp/>
   <w:DontBreakConstrainedForcedTables/>
   <w:DontVertAlignInTxbx/>
   <w:Word11KerningPairs/>
   <w:CachedColBalance/>
   <w:UseFELayout/>
  </w:Compatibility>
 </w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState=\"false\" LatentStyleCount=\"276\">
 </w:LatentStyles>
</xml><![endif]-->
<style>
<!--p.MSONORMAL
	{mso-bidi-font-size:8pt;}
li.MSONORMAL
	{mso-bidi-font-size:8pt;}
div.MSONORMAL
	{mso-bidi-font-size:8pt;}
p.SMALL
	{mso-bidi-font-size:1pt;}

 /* Font Definitions */
@font-face
	{font-family:Times;
	panose-1:2 0 5 0 0 0 0 0 0 0;
	mso-font-charset:0;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:3 0 0 0 1 0;}
@font-face
	{font-family:Verdana;
	panose-1:2 11 6 4 3 5 4 4 2 4;
	mso-font-charset:0;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:3 0 0 0 1 0;}
@font-face
	{font-family:Cambria;
	panose-1:2 4 5 3 5 4 6 3 2 4;
	mso-font-charset:0;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:3 0 0 0 1 0;}
 /* Style Definitions */
p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:\"\";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:7.5pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:\"Times New Roman\";
	mso-bidi-theme-font:minor-bidi;}
p.small, li.small, div.small
	{mso-style-name:small;
	mso-style-parent:\"\";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:1.0pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:\"Times New Roman\";
	mso-bidi-theme-font:minor-bidi;}
span.SpellE
	{mso-style-name:\"\";
	mso-spl-e:yes;}
@page Section1
	{size:612.0pt 792.0pt;
	margin:72.0pt 90.0pt 72.0pt 90.0pt;
	mso-header-margin:35.4pt;
	mso-footer-margin:35.4pt;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
table.MsoNormalTable
	{mso-style-name:\"Table Normal\";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:\"\";
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:Cambria;
	mso-ascii-font-family:Cambria;
	mso-ascii-theme-font:minor-latin;
	mso-hansi-font-family:Cambria;
	mso-hansi-theme-font:minor-latin;}
</style>
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext=\"edit\" spidmax=\"1027\">
  <o:colormenu v:ext=\"edit\" strokecolor=\"none\"/>
 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext=\"edit\">
  <o:idmap v:ext=\"edit\" data=\"1\"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=PT style='tab-interval:36.0pt'>

<div class=Section1>
<table  border=1 cellspacing=0 cellpadding=0 width='100%'
 style='width:100%;margin-left:-.35pt;border-collapse:collapse;border:none;'>
<tr><td colspan=\"{$this->options['colspan']}\" style='border-top:none; color:#FFFFFF; border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;  font-family:Helvetica; '>{$this->options['title']}<o:p></o:p></span></p>
  </td></tr>";

        return $xml;
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
        return " <td style='border:solid black 1.0pt;background-color:black;color:#FFFFFF;padding:5px'>  <p align=center style='text-align:center'><b><span style='font-size:10.0pt;'>{{value}}<o:p></o:p></span></b></p>
  </td>";
    }

    public function noResults()
    {
        return "<tr><td colspan=\"{$this->options['colspan']}\" style='border-top:none; color:#FFFFFF; border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;  font-family:Helvetica; '>{{value}}&nbsp;<o:p></o:p></span></p>
  </td></tr>";
    }

    public function hRow()
    {
        return "<tr><td colspan=\"{$this->options['colspan']}\" style='border-top:none; color:#FFFFFF; border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;  font-family:Helvetica; '>{{value}}<o:p></o:p></span></p>
  </td></tr>";
    }

    public function loopStart()
    {
        $this->i++;

        return "<tr>";
    }

    public function loopEnd()
    {
        return "</tr>";
    }

    public function loopLoop()
    {
        if ($this->i % 2) {
            return "<td style='border-top:none;border-left:solid black 1.0pt;border-bottom:solid black 1.0pt; border-right:solid black 1.0pt; background:#E0E0E0;padding:3px'> <p><span><span style='font-size:8.0pt;font-family:Helvetica;'>{{value}}<o:p></o:p></span></p> </td>";
        } else {
            return "<td style='border-top:none;border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px'> <p class=MsoNormal><span style='font-size:8.0pt; font-family:Helvetica;'>{{value}}<o:p></o:p></span></p> </td>";
        }
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
        return "<td  style='border-top:none;border-left:none;  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:5px;'> <p><span style='font-size:8.0pt; font-family:Helvetica;'>{{value}}<o:p></o:p></span></p>
  </td>";
    }
}
