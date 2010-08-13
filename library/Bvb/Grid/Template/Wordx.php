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

class Bvb_Grid_Template_Wordx
{
    public $colSpan;

    public $wordOptions;

    public $options;

    public function __construct($options = array())
    {
        $this->wordOptions = $options;
    }

    public function info()
    {
        $pdf = array(
            'logo'=>'public/images/logo.png',
            'title'=>'DataGrid Zend Framework',
            'subtitle'=>'Easy and powerfull - (Demo document)',
            'footer'=>'Downloaded from: http://www.petala-azul.com '
        );

        $pdf = array_merge($pdf,$this->wordOptions);

        return $pdf;
    }

    public function globalStart()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:mv="urn:schemas-microsoft-com:mac:vml"
	xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
	xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
	xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
	xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word"
	xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
	xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
	xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">
	<w:body>
	<w:p w:rsidR="00147109" w:rsidRDefault="00147109" />
		<w:tbl>
			<w:tblPr>
				<w:tblStyle w:val="ColorfulGrid-Accent1" />
				<w:tblW w:w="5000" w:type="pct" />
				<w:tblLook w:val="0400" />
			</w:tblPr>
			<w:tblGrid>
				<w:gridCol w:w="2838" />
				<w:gridCol w:w="2839" />
				<w:gridCol w:w="2839" />
			</w:tblGrid>';
    }

    public function globalEnd()
    {
        return '</w:tbl>
		<w:p w:rsidR="00B65C80" w:rsidRDefault="0034373E" />
		<w:sectPr w:rsidR="00B65C80" w:rsidSect="00754DEF">
			<w:headerReference w:type="default" r:id="rId4" />
			<w:footerReference w:type="even" r:id="rId5" />
			<w:footerReference w:type="default" r:id="rId6" />
			<w:pgSz w:w="11900" w:h="16840" />
			<w:pgMar w:top="1440" w:right="1800" w:bottom="1440" w:left="1800"
				w:header="708" w:footer="708" w:gutter="0" />
			<w:cols w:space="708" />
		</w:sectPr>
	</w:body>
</w:document>';
    }

    public function logo()
    {
    	$arrayLogo = explode("/",@$this->options['logo']);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'.end($arrayLogo).'"/>
</Relationships>';
    }

    public function header()
    {
        if (isset($this->options['logo']) && is_file($this->options['logo'])) {
            $arrayLogo = explode("/",@$this->options['logo']);

            $logo = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'.end($arrayLogo).'"/>
</Relationships>';

            $header = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:hdr xmlns:mv="urn:schemas-microsoft-com:mac:vml"
	xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
	xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
	xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
	xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word"
	xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
	xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
	xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">
	<w:p w:rsidR="006C2FDC" w:rsidRPr="006C2FDC" w:rsidRDefault="006C2FDC"
		w:rsidP="006C2FDC">
		<w:pPr>
			<w:pStyle w:val="Header" />
			<w:ind w:firstLine="1276" />
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
			</w:rPr>
		</w:pPr>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:noProof />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:lang w:val="en-US" />
			</w:rPr>
			<w:drawing>
				<wp:anchor distT="0" distB="0" distL="114300" distR="114300"
					simplePos="0" relativeHeight="251658240" behindDoc="0" locked="0"
					layoutInCell="1" allowOverlap="1">
					<wp:simplePos x="0" y="0" />
					<wp:positionH relativeFrom="column">
						<wp:posOffset>0</wp:posOffset>
					</wp:positionH>
					<wp:positionV relativeFrom="paragraph">
						<wp:posOffset>-220980</wp:posOffset>
					</wp:positionV>
					<wp:extent cx="657225" cy="661035" />
					<wp:effectExtent l="25400" t="0" r="3175" b="0" />
					<wp:wrapSquare wrapText="bothSides" />
					<wp:docPr id="1" name="" descr="IconeMsn.jpg" />
					<wp:cNvGraphicFramePr>
						<a:graphicFrameLocks
							xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
							noChangeAspect="1" />
					</wp:cNvGraphicFramePr>
					<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
						<a:graphicData
							uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
							<pic:pic
								xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
								<pic:nvPicPr>
									<pic:cNvPr id="0" name="IconeMsn.jpg" />
									<pic:cNvPicPr />
								</pic:nvPicPr>
								<pic:blipFill>
									<a:blip r:embed="rId1" />
									<a:stretch>
										<a:fillRect />
									</a:stretch>
								</pic:blipFill>
								<pic:spPr>
									<a:xfrm>
										<a:off x="0" y="0" />
										<a:ext cx="657225" cy="661035" />
									</a:xfrm>
									<a:prstGeom prst="rect">
										<a:avLst />
									</a:prstGeom>
								</pic:spPr>
							</pic:pic>
						</a:graphicData>
					</a:graphic>
				</wp:anchor>
			</w:drawing>
		</w:r>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
			</w:rPr>
			<w:t><![CDATA[{{title}}]]></w:t>
		</w:r>
	</w:p>
	<w:p w:rsidR="006C2FDC" w:rsidRPr="006C2FDC" w:rsidRDefault="006C2FDC"
		w:rsidP="006C2FDC">
		<w:pPr>
			<w:pStyle w:val="Header" />
			<w:ind w:firstLine="1276" />
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="666666" w:themeColor="text2" w:themeTint="99" />
				<w:sz w:val="16" />
			</w:rPr>
		</w:pPr>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="666666" w:themeColor="text2" w:themeTint="99" />
				<w:sz w:val="16" />
			</w:rPr>
			<w:t><![CDATA[{{subtitle}}]]></w:t>
		</w:r>
	</w:p>
</w:hdr>';

        }else{

            $header = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:hdr xmlns:mv="urn:schemas-microsoft-com:mac:vml"
	xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
	xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
	xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
	xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word"
	xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
	xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
	xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">
	<w:p w:rsidR="006C2FDC" w:rsidRPr="006C2FDC" w:rsidRDefault="006C2FDC"
		w:rsidP="006C2FDC">
		<w:pPr>
			<w:pStyle w:val="Header" />
			<w:ind w:firstLine="1276" />
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
			</w:rPr>
		</w:pPr>
		<w:proofErr w:type="spellStart" />
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
			</w:rPr>
			<w:t><![CDATA[{{title}}]]></w:t>
		</w:r>
		<w:proofErr w:type="spellEnd" />
	</w:p>
	<w:p w:rsidR="006C2FDC" w:rsidRPr="006C2FDC" w:rsidRDefault="006C2FDC"
		w:rsidP="006C2FDC">
		<w:pPr>
			<w:pStyle w:val="Header" />
			<w:ind w:firstLine="1276" />
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="666666" w:themeColor="text2" w:themeTint="99" />
				<w:sz w:val="16" />
			</w:rPr>
		</w:pPr>
		<w:proofErr w:type="spellStart" />
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="666666" w:themeColor="text2" w:themeTint="99" />
				<w:sz w:val="16" />
			</w:rPr>
			<w:t><![CDATA[{{subtitle}}]]></w:t>
		</w:r>
		<w:proofErr w:type="spellEnd" />
	</w:p>
</w:hdr>';
        }

        return $header;
    }

    public function titlesStart()
    {
        return '<w:tr w:rsidR="006C2FDC">
				<w:trPr>
					<w:cnfStyle w:val="000000100000" />
				</w:trPr>';
    }

    public function titlesEnd()
    {
        return '</w:tr>';
    }

    public function titlesLoop()
    {
        return '<w:tc>
					<w:tcPr>
						<w:tcW w:w="2838" w:type="dxa" />
						<w:shd w:val="clear" w:color="auto" w:fill="3E3E3E"
							w:themeFill="background2" w:themeFillShade="40" />
					</w:tcPr>
					<w:p w:rsidR="006C2FDC" w:rsidRPr="00304298" w:rsidRDefault="006C2FDC">
						<w:pPr>
							<w:rPr>
								<w:color w:val="D9D9D9" w:themeColor="background1"
									w:themeShade="D9" />
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00304298">
							<w:rPr>
								<w:color w:val="D9D9D9" w:themeColor="background1"
									w:themeShade="D9" />
								<w:sz w:val="16" />
							</w:rPr>
							<w:t><![CDATA[{{value}}]]> </w:t>
						</w:r>
					</w:p>
				</w:tc>';
    }

    public function noResults()
    {
        return '<w:tr w:rsidR="0034373E">
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="8516" w:type="dxa" />
						<w:gridSpan w:val="'.$this->options['colspan'].'" />
						<w:shd w:val="clear" w:color="auto" w:fill="7C7C7C" />
					</w:tcPr>
					<w:p w:rsidR="0034373E" w:rsidRDefault="0034373E">
					<w:pPr>
							<w:sz w:val="16" />
							<w:jc w:val="center" />
						</w:pPr>
						<w:r>
							<w:t><![CDATA[{{value}}]]></w:t>
						</w:r>
					</w:p>
				</w:tc>
			</w:tr>';
    }

    public function hRow()
    {
        return '<w:tr w:rsidR="0034373E">
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="8516" w:type="dxa" />
						<w:gridSpan w:val="'.$this->options['colspan'].'" />
						<w:shd w:val="clear" w:color="auto" w:fill="7C7C7C" />
					</w:tcPr>
					<w:p w:rsidR="0034373E" w:rsidRDefault="0034373E">
					<w:pPr>
							<w:sz w:val="16" />
							<w:jc w:val="center" />
						</w:pPr>
						<w:r>
							<w:t><![CDATA[{{value}}]]></w:t>
						</w:r>
					</w:p>
				</w:tc>
			</w:tr>';
    }

    public function loopStart()
    {
        return '<w:tr w:rsidR="006C2FDC">
				<w:trPr>
					<w:cnfStyle w:val="000000100000" />
				</w:trPr>';
    }

    public function loopEnd()
    {
        return '</w:tr>';
    }

    public function loopLoop()
    {
        return '<w:tc>
					<w:tcPr>
						<w:tcW w:w="2838" w:type="dxa" />
					</w:tcPr>
					<w:p w:rsidR="006C2FDC" w:rsidRDefault="006C2FDC">
						<w:r>
							<w:rPr>
								<w:sz w:val="16" />
							</w:rPr>
								<w:t><![CDATA[{{value}}]]></w:t>
						</w:r>
					</w:p>
				</w:tc>';
    }

    public function sqlExpStart()
    {
        return '<w:tr w:rsidR="006C2FDC" w:rsidTr="0034373E">
				<w:trPr>
					<w:cnfStyle w:val="000000100000" />
				</w:trPr>';
    }

    public function sqlExpEnd()
    {
        return '</w:tr>';
    }

    public function sqlExpLoop()
    {
        return '
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="2839" w:type="dxa" />
						<w:shd w:val="clear" w:color="auto" w:fill="595959"
							w:themeFill="text2" w:themeFillTint="A6" />
					</w:tcPr>
					<w:p w:rsidR="006C2FDC" w:rsidRDefault="006C2FDC">
						<w:r>
							<w:rPr>
								<w:sz w:val="16" />
							</w:rPr>
							<w:t><![CDATA[{{value}}]]></w:t>
						</w:r>
					</w:p>
				</w:tc>';
    }

    public function footer()
    {
        return  '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:ftr xmlns:mv="urn:schemas-microsoft-com:mac:vml"
	xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
	xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
	xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
	xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word"
	xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
	xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
	xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">
	<w:p w:rsidR="006C2FDC" w:rsidRPr="006C2FDC" w:rsidRDefault="00042D27"
		w:rsidP="0033680A">
		<w:pPr>
			<w:pStyle w:val="Footer" />
			<w:framePr w:wrap="around" w:vAnchor="text" w:hAnchor="margin"
				w:xAlign="right" w:y="1" />
			<w:rPr>
				<w:rStyle w:val="PageNumber" />
			</w:rPr>
		</w:pPr>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rStyle w:val="PageNumber" />
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
			<w:fldChar w:fldCharType="begin" />
		</w:r>
		<w:r w:rsidR="006C2FDC" w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rStyle w:val="PageNumber" />
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
			<w:instrText xml:space="preserve">PAGE  </w:instrText>
		</w:r>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rStyle w:val="PageNumber" />
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
			<w:fldChar w:fldCharType="separate" />
		</w:r>
		<w:r w:rsidR="0034373E">
			<w:rPr>
				<w:rStyle w:val="PageNumber" />
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:noProof />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
			<w:t>1</w:t>
		</w:r>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rStyle w:val="PageNumber" />
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
			<w:fldChar w:fldCharType="end" />
		</w:r>
	</w:p>
	<w:p w:rsidR="006C2FDC" w:rsidRPr="006C2FDC" w:rsidRDefault="006C2FDC"
		w:rsidP="006C2FDC">
		<w:pPr>
			<w:pStyle w:val="Footer" />
			<w:ind w:right="360" />
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
		</w:pPr>
		<w:r w:rsidRPr="006C2FDC">
			<w:rPr>
				<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" />
				<w:color w:val="000000" w:themeColor="text2" />
				<w:sz w:val="18" />
			</w:rPr>
			<w:t><![CDATA[{{value}}]]> </w:t>
		</w:r>
	</w:p>
</w:ftr>';
    }
}
