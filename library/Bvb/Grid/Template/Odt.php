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

class Bvb_Grid_Template_Odt
{
    public $options;

    public $colSpan;

    public $odtOptions;

    public $i = 0;

    public function __construct($options = array())
    {
        $this->odtOptions = $options;
    }

    public function info()
    {
        $pdf = array ('logo' => 'public/images/logo.png', 'title' => 'DataGrid Zend Framework', 'subtitle' => 'Easy and powerfull - (Demo document)', 'footer' => 'Downloaded from: http://www.petala-azul.com ');

        $pdf = array_merge($pdf, $this->odtOptions);

        return $pdf;
    }

    public function globalStart()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<office:document-content
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
	xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report"
	xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:rdfa="http://docs.oasis-open.org/opendocument/meta/rdfa#"
	office:version="1.2">
	<office:scripts />
	<office:font-face-decls>
		<style:font-face style:name="Tahoma1" svg:font-family="Tahoma" />
		<style:font-face style:name="Times New Roman"
			svg:font-family="&apos;Times New Roman&apos;"
			style:font-family-generic="roman" style:font-pitch="variable" />
		<style:font-face style:name="Arial" svg:font-family="Arial"
			style:font-family-generic="swiss" style:font-pitch="variable" />
		<style:font-face style:name="Arial Unicode MS"
			svg:font-family="&apos;Arial Unicode MS&apos;"
			style:font-family-generic="system" style:font-pitch="variable" />
		<style:font-face style:name="MS Mincho"
			svg:font-family="&apos;MS Mincho&apos;" style:font-family-generic="system"
			style:font-pitch="variable" />
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma"
			style:font-family-generic="system" style:font-pitch="variable" />
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Table1" style:family="table">
			<style:table-properties style:width="16.999cm"
				table:align="margins" style:shadow="none" />
		</style:style>
		<style:style style:name="Table1.A" style:family="table-column">
			<style:table-column-properties
				style:column-width="4.249cm" style:rel-column-width="16383*" />
		</style:style>
		<style:style style:name="Table1.A1" style:family="table-cell">
			<style:table-cell-properties
				fo:background-color="#4c4c4c" fo:padding="0.097cm" fo:border-left="0.035cm solid #000000"
				fo:border-right="none" fo:border-top="0.035cm solid #000000"
				fo:border-bottom="0.035cm solid #000000">
				<style:background-image />
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Table1.D1" style:family="table-cell">
			<style:table-cell-properties
				fo:background-color="#4c4c4c" fo:padding="0.097cm" fo:border="0.035cm solid #000000">
				<style:background-image />
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Table1.A2" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm"
				fo:border-left="0.035cm solid #000000" fo:border-right="none"
				fo:border-top="none" fo:border-bottom="0.035cm solid #000000" />
		</style:style>
		<style:style style:name="Table1.D2" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm"
				fo:border-left="0.035cm solid #000000" fo:border-right="0.035cm solid #000000"
				fo:border-top="none" fo:border-bottom="0.035cm solid #000000" />
		</style:style>
		<style:style style:name="Table1.A3" style:family="table-cell">
			<style:table-cell-properties
				fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.035cm solid #000000"
				fo:border-right="none" fo:border-top="none" fo:border-bottom="0.035cm solid #000000">
				<style:background-image />
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Table1.D3" style:family="table-cell">
			<style:table-cell-properties
				fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.035cm solid #000000"
				fo:border-right="0.035cm solid #000000" fo:border-top="none"
				fo:border-bottom="0.035cm solid #000000">
				<style:background-image />
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="P1" style:family="paragraph"
			style:parent-style-name="Header">
			<style:paragraph-properties
				fo:margin-left="2.588cm" fo:margin-right="0cm" fo:text-indent="0cm"
				style:auto-text-indent="false" />
		</style:style>
		<style:style style:name="P2" style:family="paragraph"
			style:parent-style-name="Header">
			<style:paragraph-properties
				fo:margin-left="2.588cm" fo:margin-right="0cm" fo:text-indent="0cm"
				style:auto-text-indent="false" />
			<style:text-properties fo:font-size="18pt"
				style:font-size-asian="18pt" style:font-size-complex="18pt" />
		</style:style>
		<style:style style:name="P3" style:family="paragraph"
			style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center"
				style:justify-single-word="false" />
		</style:style>
		<style:style style:name="P4" style:family="paragraph"
			style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center"
				style:justify-single-word="false" />
			<style:text-properties fo:color="#ffffff" />
		</style:style>
		<style:style style:name="P5" style:family="paragraph"
			style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center"
				style:justify-single-word="false" fo:background-color="#e6e6ff">
				<style:background-image />
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-size="14pt"
				style:font-size-asian="14pt" style:font-size-complex="14pt" />
		</style:style>
		<style:style style:name="fr1" style:family="graphic"
			style:parent-style-name="Graphics">
			<style:graphic-properties
				style:vertical-pos="from-top" style:vertical-rel="paragraph"
				style:horizontal-pos="from-left" style:horizontal-rel="paragraph"
				style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)"
				draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%"
				draw:blue="0%" draw:gamma="100%" draw:color-inversion="false"
				draw:image-opacity="100%" draw:color-mode="standard" />
		</style:style>
	</office:automatic-styles>
	<office:body>
		<office:text>
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0"
					text:name="Illustration" />
				<text:sequence-decl text:display-outline-level="0"
					text:name="Table" />
				<text:sequence-decl text:display-outline-level="0"
					text:name="Text" />
				<text:sequence-decl text:display-outline-level="0"
					text:name="Drawing" />
			</text:sequence-decls>
<table:table table:name="Table1" table:style-name="Table1">
	<table:table-column table:style-name="Table1.A"
		table:number-columns-repeated="' . $this->options['colspan']. '" />';
    }

    public function globalEnd()
    {
        return '</table:table><text:p text:style-name="Standard" />
		</office:text>
	</office:body>
</office:document-content>';
    }

    public function header()
    {
        if (isset($this->options['logo']) && is_file ($this->options['logo'])) {
            if (strpos($this->options['logo'],'/')!==false) {
                $arrayLogo = explode("/",$this->options['logo']);
            } else {
                $arrayLogo = array($this->options['logo']);
            }

            $header = '<?xml version="1.0" encoding="UTF-8"?>
<office:document-styles
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
	xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
	xmlns:rdfa="http://docs.oasis-open.org/opendocument/meta/rdfa#"
	office:version="1.2">
	<office:font-face-decls>
		<style:font-face style:name="Tahoma1" svg:font-family="Tahoma" />
		<style:font-face style:name="Times New Roman"
			svg:font-family="&apos;Times New Roman&apos;"
			style:font-family-generic="roman" style:font-pitch="variable" />
		<style:font-face style:name="Arial" svg:font-family="Arial"
			style:font-family-generic="swiss" style:font-pitch="variable" />
		<style:font-face style:name="Arial Unicode MS"
			svg:font-family="&apos;Arial Unicode MS&apos;"
			style:font-family-generic="system" style:font-pitch="variable" />
		<style:font-face style:name="MS Mincho"
			svg:font-family="&apos;MS Mincho&apos;" style:font-family-generic="system"
			style:font-pitch="variable" />
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma"
			style:font-family-generic="system" style:font-pitch="variable" />
	</office:font-face-decls>
	<office:styles>
		<style:default-style style:family="graphic">
			<style:graphic-properties
				draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm"
				draw:start-line-spacing-horizontal="0.283cm"
				draw:start-line-spacing-vertical="0.283cm"
				draw:end-line-spacing-horizontal="0.283cm"
				draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false" />
			<style:paragraph-properties
				style:text-autospace="ideograph-alpha" style:line-break="strict"
				style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
				<style:tab-stops />
			</style:paragraph-properties>
			<style:text-properties
				style:use-window-font-color="true" fo:font-size="12pt" fo:language="pt"
				fo:country="PT" style:letter-kerning="true" style:font-size-asian="12pt"
				style:language-asian="zxx" style:country-asian="none"
				style:font-size-complex="12pt" style:language-complex="zxx"
				style:country-complex="none" />
		</style:default-style>
		<style:default-style style:family="paragraph">
			<style:paragraph-properties
				fo:hyphenation-ladder-count="no-limit" style:text-autospace="ideograph-alpha"
				style:punctuation-wrap="hanging" style:line-break="strict"
				style:tab-stop-distance="1.251cm" style:writing-mode="page" />
			<style:text-properties
				style:use-window-font-color="true" style:font-name="Times New Roman"
				fo:font-size="12pt" fo:language="pt" fo:country="PT"
				style:letter-kerning="true" style:font-name-asian="Arial Unicode MS"
				style:font-size-asian="12pt" style:language-asian="zxx"
				style:country-asian="none" style:font-name-complex="Tahoma"
				style:font-size-complex="12pt" style:language-complex="zxx"
				style:country-complex="none" fo:hyphenate="false"
				fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2" />
		</style:default-style>
		<style:default-style style:family="table">
			<style:table-properties table:border-model="collapsing" />
		</style:default-style>
		<style:default-style style:family="table-row">
			<style:table-row-properties
				fo:keep-together="auto" />
		</style:default-style>
		<style:style style:name="Standard" style:family="paragraph"
			style:class="text" />
		<style:style style:name="Heading" style:family="paragraph"
			style:parent-style-name="Standard" style:next-style-name="Text_20_body"
			style:class="text">
			<style:paragraph-properties fo:margin-top="0.423cm"
				fo:margin-bottom="0.212cm" fo:keep-with-next="always" />
			<style:text-properties style:font-name="Arial"
				fo:font-size="14pt" style:font-name-asian="MS Mincho"
				style:font-size-asian="14pt" style:font-name-complex="Tahoma"
				style:font-size-complex="14pt" />
		</style:style>
		<style:style style:name="Text_20_body" style:display-name="Text body"
			style:family="paragraph" style:parent-style-name="Standard"
			style:class="text">
			<style:paragraph-properties fo:margin-top="0cm"
				fo:margin-bottom="0.212cm" />
		</style:style>
		<style:style style:name="List" style:family="paragraph"
			style:parent-style-name="Text_20_body" style:class="list">
			<style:text-properties style:font-name-complex="Tahoma1" />
		</style:style>
		<style:style style:name="Caption" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties fo:margin-top="0.212cm"
				fo:margin-bottom="0.212cm" text:number-lines="false"
				text:line-number="0" />
			<style:text-properties fo:font-size="12pt"
				fo:font-style="italic" style:font-size-asian="12pt"
				style:font-style-asian="italic" style:font-name-complex="Tahoma1"
				style:font-size-complex="12pt" style:font-style-complex="italic" />
		</style:style>
		<style:style style:name="Index" style:family="paragraph"
			style:parent-style-name="Standard" style:class="index">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0" />
			<style:text-properties style:font-name-complex="Tahoma1" />
		</style:style>
		<style:style style:name="Table_20_Contents"
			style:display-name="Table Contents" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0" />
		</style:style>
		<style:style style:name="Header" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0">
				<style:tab-stops>
					<style:tab-stop style:position="8.498cm" style:type="center" />
					<style:tab-stop style:position="16.999cm"
						style:type="right" />
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="Footer" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0">
				<style:tab-stops>
					<style:tab-stop style:position="8.498cm" style:type="center" />
					<style:tab-stop style:position="16.999cm"
						style:type="right" />
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="Graphics" style:family="graphic">
			<style:graphic-properties text:anchor-type="paragraph"
				svg:x="0cm" svg:y="0cm" style:wrap="none" style:vertical-pos="top"
				style:vertical-rel="paragraph" style:horizontal-pos="center"
				style:horizontal-rel="paragraph" />
		</style:style>
		<text:outline-style style:name="Outline">
			<text:outline-level-style text:level="1"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="0.762cm"
						fo:text-indent="-0.762cm" fo:margin-left="0.762cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="2"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm"
						fo:text-indent="-1.016cm" fo:margin-left="1.016cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="3"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm"
						fo:text-indent="-1.27cm" fo:margin-left="1.27cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="4"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm"
						fo:text-indent="-1.524cm" fo:margin-left="1.524cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="5"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm"
						fo:text-indent="-1.778cm" fo:margin-left="1.778cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="6"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm"
						fo:text-indent="-2.032cm" fo:margin-left="2.032cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="7"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm"
						fo:text-indent="-2.286cm" fo:margin-left="2.286cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="8"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm"
						fo:text-indent="-2.54cm" fo:margin-left="2.54cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="9"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.794cm"
						fo:text-indent="-2.794cm" fo:margin-left="2.794cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="10"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="3.048cm"
						fo:text-indent="-3.048cm" fo:margin-left="3.048cm" />
				</style:list-level-properties>
			</text:outline-level-style>
		</text:outline-style>
		<text:notes-configuration text:note-class="footnote"
			style:num-format="1" text:start-value="0" text:footnotes-position="page"
			text:start-numbering-at="document" />
		<text:notes-configuration text:note-class="endnote"
			style:num-format="i" text:start-value="0" />
		<text:linenumbering-configuration
			text:number-lines="false" text:offset="0.499cm" style:num-format="1"
			text:number-position="left" text:increment="5" />
	</office:styles>
	<office:automatic-styles>
		<style:style style:name="Table2" style:family="table">
			<style:table-properties style:width="17.006cm"
				fo:margin-left="0cm" fo:margin-right="-0.007cm" table:align="margins" />
		</style:style>
		<style:style style:name="Table2.A" style:family="table-column">
			<style:table-column-properties
				style:column-width="15.215cm" style:rel-column-width="8626*" />
		</style:style>
		<style:style style:name="Table2.B" style:family="table-column">
			<style:table-column-properties
				style:column-width="1.79cm" style:rel-column-width="1015*" />
		</style:style>
		<style:style style:name="Table2.A1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm"
				fo:border-left="0.002cm solid #000000" fo:border-right="none"
				fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000" />
		</style:style>
		<style:style style:name="Table2.B1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm"
				fo:border="0.002cm solid #000000" />
		</style:style>
		<style:style style:name="MP1" style:family="paragraph"
			style:parent-style-name="Header">
			<style:paragraph-properties
				fo:margin-left="2.588cm" fo:margin-right="0cm" fo:text-indent="0cm"
				style:auto-text-indent="false" />
			<style:text-properties fo:font-size="18pt"
				style:font-size-asian="18pt" style:font-size-complex="18pt" />
		</style:style>
		<style:style style:name="MP2" style:family="paragraph"
			style:parent-style-name="Header">
			<style:paragraph-properties
				fo:margin-left="2.588cm" fo:margin-right="0cm" fo:text-indent="0cm"
				style:auto-text-indent="false" />
		</style:style>
		<style:style style:name="MP3" style:family="paragraph"
			style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center"
				style:justify-single-word="false" />
		</style:style>
		<style:style style:name="MT1" style:family="text">
			<style:text-properties fo:font-size="14pt"
				style:font-size-asian="14pt" style:font-size-complex="14pt" />
		</style:style>
		<style:style style:name="Mfr1" style:family="graphic"
			style:parent-style-name="Graphics">
			<style:graphic-properties
				style:vertical-pos="from-top" style:vertical-rel="paragraph"
				style:horizontal-pos="from-left" style:horizontal-rel="paragraph"
				style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)"
				draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%"
				draw:blue="0%" draw:gamma="100%" draw:color-inversion="false"
				draw:image-opacity="100%" draw:color-mode="standard" />
		</style:style>
		<style:page-layout style:name="Mpm1">
			<style:page-layout-properties
				fo:page-width="20.999cm" fo:page-height="29.699cm" style:num-format="1"
				style:print-orientation="portrait" fo:margin-top="1.476cm"
				fo:margin-bottom="1.706cm" fo:margin-left="2cm" fo:margin-right="2cm"
				style:writing-mode="lr-tb" style:footnote-max-height="0cm">
				<style:footnote-sep style:width="0.018cm"
					style:distance-before-sep="0.101cm" style:distance-after-sep="0.101cm"
					style:adjustment="left" style:rel-width="25%" style:color="#000000" />
			</style:page-layout-properties>
			<style:header-style>
				<style:header-footer-properties
					svg:height="2.642cm" fo:margin-bottom="0.499cm" />
			</style:header-style>
			<style:footer-style>
				<style:header-footer-properties
					svg:height="1.069cm" fo:margin-top="0.499cm" />
			</style:footer-style>
		</style:page-layout>
	</office:automatic-styles>
	<office:master-styles>
		<style:master-page style:name="Standard"
			style:page-layout-name="Mpm1">
			<style:header>
				<text:p text:style-name="MP1">
					<draw:frame draw:style-name="Mfr1" draw:name="graphics2"
						text:anchor-type="paragraph" svg:x="0cm" svg:y="-0.025cm"
						svg:width="2.131cm" svg:height="2.249cm" draw:z-index="0">
						<draw:image xlink:href="Pictures/'.end($arrayLogo).'"
							xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad" />
					</draw:frame><![CDATA[{{title}}]]>
				</text:p>
				<text:p text:style-name="MP2">
					<text:span text:style-name="MT1" />
				</text:p>
				<text:p text:style-name="MP2"><![CDATA[{{subtitle}}]]></text:p>
			</style:header>
			<style:footer>
				<table:table table:name="Table2" table:style-name="Table2">
					<table:table-column table:style-name="Table2.A" />
					<table:table-row>
						<table:table-cell table:style-name="Table2.A1"
							office:value-type="string">
							<text:p text:style-name="Footer"><![CDATA[{{footer}}]]></text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Table2.A1"
							office:value-type="string">
							<text:p text:style-name="MP3">
								<text:page-number text:select-page="current">
								</text:page-number>
								/
								<text:page-count> </text:page-count>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="Footer" />
			</style:footer>
		</style:master-page>
	</office:master-styles>
</office:document-styles>';

        } else {

            $header = '<?xml version="1.0" encoding="UTF-8"?>
<office:document-styles
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
	xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
	xmlns:rdfa="http://docs.oasis-open.org/opendocument/meta/rdfa#"
	office:version="1.2">
	<office:font-face-decls>
		<style:font-face style:name="Tahoma1" svg:font-family="Tahoma" />
		<style:font-face style:name="Times New Roman"
			svg:font-family="&apos;Times New Roman&apos;"
			style:font-family-generic="roman" style:font-pitch="variable" />
		<style:font-face style:name="Arial" svg:font-family="Arial"
			style:font-family-generic="swiss" style:font-pitch="variable" />
		<style:font-face style:name="Arial Unicode MS"
			svg:font-family="&apos;Arial Unicode MS&apos;"
			style:font-family-generic="system" style:font-pitch="variable" />
		<style:font-face style:name="MS Mincho"
			svg:font-family="&apos;MS Mincho&apos;" style:font-family-generic="system"
			style:font-pitch="variable" />
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma"
			style:font-family-generic="system" style:font-pitch="variable" />
	</office:font-face-decls>
	<office:styles>
		<style:default-style style:family="graphic">
			<style:graphic-properties
				draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm"
				draw:start-line-spacing-horizontal="0.283cm"
				draw:start-line-spacing-vertical="0.283cm"
				draw:end-line-spacing-horizontal="0.283cm"
				draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false" />
			<style:paragraph-properties
				style:text-autospace="ideograph-alpha" style:line-break="strict"
				style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
				<style:tab-stops />
			</style:paragraph-properties>
			<style:text-properties
				style:use-window-font-color="true" fo:font-size="12pt" fo:language="pt"
				fo:country="PT" style:letter-kerning="true" style:font-size-asian="12pt"
				style:language-asian="zxx" style:country-asian="none"
				style:font-size-complex="12pt" style:language-complex="zxx"
				style:country-complex="none" />
		</style:default-style>
		<style:default-style style:family="paragraph">
			<style:paragraph-properties
				fo:hyphenation-ladder-count="no-limit" style:text-autospace="ideograph-alpha"
				style:punctuation-wrap="hanging" style:line-break="strict"
				style:tab-stop-distance="1.251cm" style:writing-mode="page" />
			<style:text-properties
				style:use-window-font-color="true" style:font-name="Times New Roman"
				fo:font-size="12pt" fo:language="pt" fo:country="PT"
				style:letter-kerning="true" style:font-name-asian="Arial Unicode MS"
				style:font-size-asian="12pt" style:language-asian="zxx"
				style:country-asian="none" style:font-name-complex="Tahoma"
				style:font-size-complex="12pt" style:language-complex="zxx"
				style:country-complex="none" fo:hyphenate="false"
				fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2" />
		</style:default-style>
		<style:default-style style:family="table">
			<style:table-properties table:border-model="collapsing" />
		</style:default-style>
		<style:default-style style:family="table-row">
			<style:table-row-properties
				fo:keep-together="auto" />
		</style:default-style>
		<style:style style:name="Standard" style:family="paragraph"
			style:class="text" />
		<style:style style:name="Heading" style:family="paragraph"
			style:parent-style-name="Standard" style:next-style-name="Text_20_body"
			style:class="text">
			<style:paragraph-properties fo:margin-top="0.423cm"
				fo:margin-bottom="0.212cm" fo:keep-with-next="always" />
			<style:text-properties style:font-name="Arial"
				fo:font-size="14pt" style:font-name-asian="MS Mincho"
				style:font-size-asian="14pt" style:font-name-complex="Tahoma"
				style:font-size-complex="14pt" />
		</style:style>
		<style:style style:name="Text_20_body" style:display-name="Text body"
			style:family="paragraph" style:parent-style-name="Standard"
			style:class="text">
			<style:paragraph-properties fo:margin-top="0cm"
				fo:margin-bottom="0.212cm" />
		</style:style>
		<style:style style:name="List" style:family="paragraph"
			style:parent-style-name="Text_20_body" style:class="list">
			<style:text-properties style:font-name-complex="Tahoma1" />
		</style:style>
		<style:style style:name="Caption" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties fo:margin-top="0.212cm"
				fo:margin-bottom="0.212cm" text:number-lines="false"
				text:line-number="0" />
			<style:text-properties fo:font-size="12pt"
				fo:font-style="italic" style:font-size-asian="12pt"
				style:font-style-asian="italic" style:font-name-complex="Tahoma1"
				style:font-size-complex="12pt" style:font-style-complex="italic" />
		</style:style>
		<style:style style:name="Index" style:family="paragraph"
			style:parent-style-name="Standard" style:class="index">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0" />
			<style:text-properties style:font-name-complex="Tahoma1" />
		</style:style>
		<style:style style:name="Table_20_Contents"
			style:display-name="Table Contents" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0" />
		</style:style>
		<style:style style:name="Header" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0">
				<style:tab-stops>
					<style:tab-stop style:position="8.498cm" style:type="center" />
					<style:tab-stop style:position="16.999cm"
						style:type="right" />
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="Footer" style:family="paragraph"
			style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties
				text:number-lines="false" text:line-number="0">
				<style:tab-stops>
					<style:tab-stop style:position="8.498cm" style:type="center" />
					<style:tab-stop style:position="16.999cm"
						style:type="right" />
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="Graphics" style:family="graphic">
			<style:graphic-properties text:anchor-type="paragraph"
				svg:x="0cm" svg:y="0cm" style:wrap="none" style:vertical-pos="top"
				style:vertical-rel="paragraph" style:horizontal-pos="center"
				style:horizontal-rel="paragraph" />
		</style:style>
		<text:outline-style style:name="Outline">
			<text:outline-level-style text:level="1"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="0.762cm"
						fo:text-indent="-0.762cm" fo:margin-left="0.762cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="2"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm"
						fo:text-indent="-1.016cm" fo:margin-left="1.016cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="3"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm"
						fo:text-indent="-1.27cm" fo:margin-left="1.27cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="4"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm"
						fo:text-indent="-1.524cm" fo:margin-left="1.524cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="5"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm"
						fo:text-indent="-1.778cm" fo:margin-left="1.778cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="6"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm"
						fo:text-indent="-2.032cm" fo:margin-left="2.032cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="7"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm"
						fo:text-indent="-2.286cm" fo:margin-left="2.286cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="8"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm"
						fo:text-indent="-2.54cm" fo:margin-left="2.54cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="9"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="2.794cm"
						fo:text-indent="-2.794cm" fo:margin-left="2.794cm" />
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="10"
				style:num-format="">
				<style:list-level-properties
					text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment
						text:label-followed-by="listtab" text:list-tab-stop-position="3.048cm"
						fo:text-indent="-3.048cm" fo:margin-left="3.048cm" />
				</style:list-level-properties>
			</text:outline-level-style>
		</text:outline-style>
		<text:notes-configuration text:note-class="footnote"
			style:num-format="1" text:start-value="0" text:footnotes-position="page"
			text:start-numbering-at="document" />
		<text:notes-configuration text:note-class="endnote"
			style:num-format="i" text:start-value="0" />
		<text:linenumbering-configuration
			text:number-lines="false" text:offset="0.499cm" style:num-format="1"
			text:number-position="left" text:increment="5" />
	</office:styles>
	<office:automatic-styles>
		<style:style style:name="Table2" style:family="table">
			<style:table-properties style:width="17.006cm"
				fo:margin-left="0cm" fo:margin-right="-0.007cm" table:align="margins" />
		</style:style>
		<style:style style:name="Table2.A" style:family="table-column">
			<style:table-column-properties
				style:column-width="15.215cm" style:rel-column-width="8626*" />
		</style:style>
		<style:style style:name="Table2.B" style:family="table-column">
			<style:table-column-properties
				style:column-width="1.79cm" style:rel-column-width="1015*" />
		</style:style>
		<style:style style:name="Table2.A1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm"
				fo:border-left="0.002cm solid #000000" fo:border-right="none"
				fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000" />
		</style:style>
		<style:style style:name="Table2.B1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm"
				fo:border="0.002cm solid #000000" />
		</style:style>
		<style:style style:name="MP1" style:family="paragraph"
			style:parent-style-name="Header">
			<style:paragraph-properties
				fo:margin-left="-0.048cm" fo:margin-right="0cm" fo:text-align="center"
				style:justify-single-word="false" fo:text-indent="0cm"
				style:auto-text-indent="false" />
			<style:text-properties fo:font-size="18pt"
				style:font-size-asian="18pt" style:font-size-complex="18pt" />
		</style:style>
		<style:style style:name="MP2" style:family="paragraph"
			style:parent-style-name="Header">
			<style:paragraph-properties
				fo:margin-left="-0.048cm" fo:margin-right="0cm" fo:text-align="center"
				style:justify-single-word="false" fo:text-indent="0cm"
				style:auto-text-indent="false" />
		</style:style>
		<style:style style:name="MP3" style:family="paragraph"
			style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center"
				style:justify-single-word="false" />
		</style:style>
		<style:style style:name="MT1" style:family="text">
			<style:text-properties fo:font-size="14pt"
				style:font-size-asian="14pt" style:font-size-complex="14pt" />
		</style:style>
		<style:page-layout style:name="Mpm1">
			<style:page-layout-properties
				fo:page-width="20.999cm" fo:page-height="29.699cm" style:num-format="1"
				style:print-orientation="portrait" fo:margin-top="1.476cm"
				fo:margin-bottom="1.706cm" fo:margin-left="2cm" fo:margin-right="2cm"
				style:writing-mode="lr-tb" style:footnote-max-height="0cm">
				<style:footnote-sep style:width="0.018cm"
					style:distance-before-sep="0.101cm" style:distance-after-sep="0.101cm"
					style:adjustment="left" style:rel-width="25%" style:color="#000000" />
			</style:page-layout-properties>
			<style:header-style>
				<style:header-footer-properties
					svg:height="2.642cm" fo:margin-bottom="0.499cm" />
			</style:header-style>
			<style:footer-style>
				<style:header-footer-properties
					svg:height="1.069cm" fo:margin-top="0.499cm" />
			</style:footer-style>
		</style:page-layout>
	</office:automatic-styles>
	<office:master-styles>
		<style:master-page style:name="Standard"
			style:page-layout-name="Mpm1">
			<style:header>
				<text:p text:style-name="MP1"><![CDATA[{{title}}]]></text:p>
				<text:p text:style-name="MP2">
					<text:span text:style-name="MT1" />
				</text:p>
				<text:p text:style-name="MP2"><![CDATA[{{subtitle}}]]></text:p>
			</style:header>
			<style:footer>
				<table:table table:name="Table2" table:style-name="Table2">
					<table:table-column table:style-name="Table2.A" />
					<table:table-column table:style-name="Table2.B" />
					<table:table-row>
						<table:table-cell table:style-name="Table2.A1"
							office:value-type="string">
							<text:p text:style-name="Footer"><![CDATA[{{footer}}]]></text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Table2.A1"
							office:value-type="string">
							<text:p text:style-name="MP4">
								<text:page-number text:select-page="current">1
								</text:page-number>
								/
								<text:page-count>1</text:page-count>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="Footer" />
			</style:footer>
		</style:master-page>
	</office:master-styles>
</office:document-styles>';

        }

        return $header;
    }

    public function titlesStart()
    {
        return '<table:table-row>';
    }

    public function titlesEnd()
    {
        return '</table:table-row>';
    }

    public function titlesLoop()
    {
        return '<table:table-cell table:style-name="Table1.D1"
			office:value-type="string">
			<text:p text:style-name="P4"><![CDATA[{{value}}]]> </text:p>
		</table:table-cell>';
    }

    public function noResults()
    {
        $return = '
	<table:table-row>
		<table:table-cell table:style-name="Table1.D2"
			table:number-columns-spanned="' .  $this->options['colspan'] . '" office:value-type="string">
			<text:p text:style-name="P5"><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';

        for($i = 1; $i <  $this->options['colspan']; $i ++)
        {
            $return .= '<table:covered-table-cell />';
        }

        $return .= '</table:table-row>';

        return $return;
    }

    public function hRow()
    {
        $return = '<table:table-row>
		<table:table-cell table:style-name="Table1.D2"
			table:number-columns-spanned="' .  $this->options['colspan']. '" office:value-type="string">
			<text:p text:style-name="P5"><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';

        for ($i = 1; $i <  $this->options['colspan']; $i ++) {
            $return .= '<table:covered-table-cell />';
        }

        $return .= '</table:table-row>';

        return $return;
    }

    public function loopStart()
    {
        $this->i ++;

        return '<table:table-row>';
    }

    public function loopEnd()
    {
        return '</table:table-row>';
    }

    public function loopLoop()
    {
        if ($this->i % 2) {
            return '<table:table-cell table:style-name="Table1.A2"
			office:value-type="string">
			<text:p text:style-name="Table_20_Contents"><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';
        } else {
            return '<table:table-cell table:style-name="Table1.A3"
			office:value-type="string">
			<text:p text:style-name="Table_20_Contents"><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';
        }
    }

    public function sqlExpStart()
    {
        return '<table:table-row>';
    }

    public function sqlExpEnd()
    {
        return '</table:table-row>';
    }

    public function sqlExpLoop()
    {
        return '<table:table-cell table:style-name="Table1.A2"
			office:value-type="string">
			<text:p text:style-name="Table_20_Contents"><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';
    }
}