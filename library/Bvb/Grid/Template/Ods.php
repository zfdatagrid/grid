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

class Bvb_Grid_Template_Ods
{
    public  $options;

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
	xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"
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
		<style:font-face style:name="Arial" svg:font-family="Arial"
			style:font-family-generic="swiss" style:font-pitch="variable" />
		<style:font-face style:name="Arial Unicode MS"
			svg:font-family="&apos;Arial Unicode MS&apos;"
			style:font-family-generic="system" style:font-pitch="variable" />
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma"
			style:font-family-generic="system" style:font-pitch="variable" />
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="co1" style:family="table-column">
			<style:table-column-properties
				fo:break-before="auto" style:column-width="2.267cm" />
		</style:style>
		<style:style style:name="ro1" style:family="table-row">
			<style:table-row-properties
				style:row-height="0.441cm" fo:break-before="auto"
				style:use-optimal-row-height="true" />
		</style:style>
		<style:style style:name="ta1" style:family="table"
			style:master-page-name="Default">
			<style:table-properties table:display="true"
				style:writing-mode="lr-tb" />
		</style:style>
	</office:automatic-styles>
	<office:body>
		<office:spreadsheet><table:table table:name="Sheet1" table:style-name="ta1"
	table:print="false">
	<table:table-column table:style-name="co1"  table:default-cell-style-name="Default" />';
    }

    public function globalEnd()
    {
        return '</table:table> </office:spreadsheet>
	</office:body>
</office:document-content>';
    }

    public function titlesStart()
    {
        return '<table:table-row table:style-name="ro1">';
    }

    public function titlesEnd()
    {
        return '</table:table-row>';
    }

    public function titlesLoop()
    {
        return '<table:table-cell office:value-type="string">
			<text:p><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';
    }

    public function loopStart()
    {
        return '<table:table-row table:style-name="ro1">';
    }

    public function loopEnd()
    {
        return '</table:table-row>';
    }

    public function loopLoop()
    {
        return '<table:table-cell office:value-type="string">
			<text:p><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';
    }

    public function sqlExpStart()
    {
        return '<table:table-row table:style-name="ro1">';
    }

    public function sqlExpEnd()
    {
        return '</table:table-row>';
    }

    public function sqlExpLoop()
    {
        return '<table:table-cell office:value-type="string">
			<text:p><![CDATA[{{value}}]]></text:p>
		</table:table-cell>';
    }
}