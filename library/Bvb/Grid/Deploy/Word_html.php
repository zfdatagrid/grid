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
class Bvb_Grid_Deploy_Word extends Bvb_Grid_DataGrid
{
    public $title;

    public $dir ;


    protected  $output = 'word';


    function __construct ($db,$title,$dir)
    {
        if(!in_array('word',$this->export))
        {
            die('Sem permissões de exportação da grelha');
        }

        $this->title = $title;

        $this->dir = rtrim($dir,"/")."/";
        parent::__construct($db);
    }

    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */

    function __set($var,$value)
    {
        parent::__set($var,$value);
    }


    function deploy()
    {
        $this->data['pagination'][ 'per_page' ] = 10000000;

        parent::deploy();

        $titles = parent::buildTitles();

        $nome = reset($titles);
        $wsData = parent::buildGrid();
        $sql = parent::buildSqlExp();

        if($nome['field']=='id' || strpos($nome['field'],'_id')  || strpos($nome['field'],'id_') || strpos($nome['field'],'.id')  )
        {
            @array_shift($titles);
            @array_shift($sql);

            $remove = true;
        }

        $xml  = <<<EOH
			<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
xmlns:css="http://macVmlSchemaUri" xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta name=Title content="Listagem de Associações">
<meta name=Keywords content="">
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 2008">
<meta name=Originator content="Microsoft Word 2008">
<title>{$this->title}</title>
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
 <w:LatentStyles DefLockedState="false" LatentStyleCount="276">
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
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:7.5pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;}
p.small, li.small, div.small
	{mso-style-name:small;
	mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:1.0pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;}
span.SpellE
	{mso-style-name:"";
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
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
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
 <o:shapedefaults v:ext="edit" spidmax="1027">
  <o:colormenu v:ext="edit" strokecolor="none"/>
 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=PT style='tab-interval:36.0pt'>

<div class=Section1>
EOH;


        $xml .= "<table class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width='100%'
 style='width:93.8%;margin-left:-.35pt;border-collapse:collapse;border:none;
 mso-border-alt:solid black .75pt;mso-yfti-tbllook:191;mso-padding-alt:0cm 0cm 0cm 0cm;
 mso-border-insideh:.75pt solid black;mso-border-insidev:.75pt solid black'>";

        $xml .= "<tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>";
        foreach ($titles as $value) {
            if(($value['field']!=@$this->info['hRow']['field'] && @$this->info['hRow']['title'] !='') || @$this->info['hRow']['title'] =='')
            {
                $xml .=" <td style='border:solid black 1.0pt;mso-border-alt:solid black .75pt;
  background:black;mso-shading:white;mso-pattern:solid black;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:8.0pt;mso-bidi-font-size:
  10.0pt;mso-fareast-theme-font:minor-fareast'>".$value['value']."<o:p></o:p></span></b></p>
  </td>";
            }
        }
        $xml .= '</tr>';






        if(is_array($wsData))
        {


            /////////////////
            /////////////////
            /////////////////
            if(@$this->info['hRow']['title']!='')
            {
                $bar = $wsData;

                $hbar = trim($this->info['hRow']['field']);

                $p=0;
                foreach ($wsData[0] as $value)
                {
                    if($value['field'] == $hbar)
                    {
                        $hRowIndex = $p;
                    }

                    $p++;
                }
                $aa = 0;
            }

            //////////////
            //////////////
            //////////////


            $i=1;
            $aa = 0;
            foreach ($wsData as $row) {

                ////////////
                ////////////
                //A linha horizontal
                if(@$this->info['hRow']['title']!='')
                {

                    if(@$bar[$aa][$hRowIndex]['value'] != @$bar[$aa-1][$hRowIndex]['value'])
                    {
                        $xml .="<tr><td colspan=\"".$this->_colspan."\" style='border-top:none;border-left:none;border-bottom:solid black 1.0pt;   border-right:solid black 1.0pt;mso-border-top-alt:solid black .75pt;  mso-border-left-alt:solid black .75pt;mso-border-alt:solid black .75pt; background:#333333;padding:0cm 0cm 0cm 0cm'> <p class=MsoNormal><span><span style='font-size:8.0pt;font-family:Helvetica;mso-fareast-theme-font:minor-fareast'>".$bar[$aa][$hRowIndex]['value']."<o:p></o:p></span></p> </td></tr>";
                    }
                }

                ////////////
                ////////////



                $xml .= '<tr>';
                $a=1;
                foreach ($row as $value) {
                    

                    
                    $value['value']  = strip_tags($value['value']);
                    if(@$remove===true && $a==1)
                    {

                    } else{


                        if(($value['field']!=@$this->info['hRow']['field'] && !isset($this->info['hRow']['title'] ))
                        || isset($this->info['hRow']['title']))
                        {


                            if($i%2)
                            {
                                $xml .="<td style='border-top:none;border-left:none;border-bottom:solid black 1.0pt;   border-right:solid black 1.0pt;mso-border-top-alt:solid black .75pt;  mso-border-left-alt:solid black .75pt;mso-border-alt:solid black .75pt; background:#E0E0E0;padding:0cm 0cm 0cm 0cm'> <p class=MsoNormal><span><span style='font-size:8.0pt;font-family:Helvetica;mso-fareast-theme-font:minor-fareast'>".$value['value']."<o:p></o:p></span></p> </td>";

                            }else{

                                $xml .="<td style='border-top:none;border-left:none;
  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;mso-border-top-alt:
  solid black .75pt;mso-border-left-alt:solid black .75pt;mso-border-alt:solid black .75pt;
  padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><span style='font-size:8.0pt;mso-bidi-font-size:10.0pt;
  font-family:Helvetica;mso-fareast-theme-font:
  minor-fareast'>".$value['value']."<o:p></o:p></span></p>
  </td>";
                            }
                        }
                    }                        $a++;

                }
                $xml .= '</tr>';
                $aa++;
                $i++;
            }
        }


        if(is_array($sql))
        {
            $xml .= '<tr>';
            foreach ($sql as $value) {


                $xml .="<td  style='border-top:none;border-left:none;
  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;mso-border-top-alt:
  solid black .75pt;mso-border-left-alt:solid black .75pt;mso-border-alt:solid black .75pt;
  padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><span style='font-size:8.0pt;mso-bidi-font-size:10.0pt;
  font-family:Helvetica;mso-fareast-theme-font:
  minor-fareast'>".$value['value']."<o:p></o:p></span></p>
  </td>";
            }
            $xml .= '</tr>';
        }


        $xml .= '</table></div></body></html>';



        if(file_exists($this->dir.$this->title.'.doc'))
        {
            $data = date('d-m-Y H\:i\:s');
            rename($this->dir.$this->title.'.doc',$this->dir.$this->title.'-'.$data.'.doc');
        }

        file_put_contents($this->dir.$this->title.".doc",$xml);

        header('Content-type: application/word');

        // It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="'.$this->title.'.doc"');
        readfile($this->dir.$this->title.'.doc');
        unlink($this->dir.$this->title.'.doc');
        die();
    }

}




