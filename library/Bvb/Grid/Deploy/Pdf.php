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
class Bvb_Grid_Deploy_Pdf extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{

    protected $_page;
    protected $_width;
    protected $_styles;
    protected $_height;
    protected $_cellFontSize = 8;
    protected $_font;
    protected $_pdf;
    protected $_la;
    protected $_cell;
    protected $_totalPages;
    protected $_currentPage;

    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);
    }

    /**
     * @copyright http://n4.nabble.com/Finding-width-of-a-drawText-Text-in-Zend-Pdf-td677978.html
     * @param $string
     * @param $this->_font
     * @param $this->_fontSize
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize=8)
    {
        @$drawingString = iconv('', 'UTF-16BE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $this->_font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $this->_font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    public function calculateCellSize($titles, $sqlexp,$grid)
    {


        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $i = 0;


        $larg = array();
        $fix = array();

        foreach ($titles as $titles) {
            if ((@$titles['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '')
                || $this->getInfo('hRow,title') == ''
            ) {
                $fix[$i] = ceil($this->widthForStringUsingFontSize($titles['value'], $this->_font, 14));
                $i++;
            }
        }

        $i = 0;

        if (is_array($sqlexp)) {
            foreach ($sqlexp as $sql) {
                if (($sql['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '')
                    || $this->getInfo('hRow,title') == ''
                ) {
                    if ($fix[$i] < $this->widthForStringUsingFontSize($sql['value'], $this->_font)) {
                        $fix[$i] = ceil($this->widthForStringUsingFontSize($sql['value'], $this->_font));
                    }
                    $i++;
                }
            }
        }

        if ($this->getInfo('hRow,title') != '') {
            $bar = $grid;

            $hbar = trim($this->getInfo('hRow,field'));

            $p = 0;
            foreach ($grid[0] as $value) {
                if ($value['field'] == $hbar) {
                    $hRowIndex = $p;
                }

                $p++;
            }
            $aa = 0;
        }

        foreach ($grid as $row) {
            $i = 0;
            $a = 1;
            foreach ($row as $value) {

                $value['value'] = strip_tags($value['value']);

                if ((isset($value['field']) && $value['field']
                    != $this->getInfo('hRow,field')
                    && $this->getInfo('hRow,title') != '')
                    || $this->getInfo('hRow,title') == ''
                ) {

                    if (!isset($larg[$i]) || $larg[$i] < strlen($value['value'])) {
                        $larg[$i] = strlen($value['value']);
                    }
                    $i++;
                }
                $a++;
            }
            $i++;
        }

        return array('larg'=>$larg,'fix'=>$fix);
    }


    public function buildPageStructure($titles,$firstPage = false)
    {

        if (strtoupper($this->_deploy['size'] = 'LETTER')
            && strtoupper($this->_deploy['orientation']) == 'LANDSCAPE'
        ) {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);
        } elseif (strtoupper($this->_deploy['size'] = 'LETTER')
            && strtoupper($this->_deploy['orientation']) != 'LANDSCAPE'
        ) {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        } elseif (strtoupper($this->_deploy['size'] != 'A4')
            && strtoupper($this->_deploy['orientation']) == 'LANDSCAPE'
        ) {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
        } else {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        }

        if($firstPage===true)
        {
            return;
        }

        $this->_page->setStyle($this->_styles['style']);
        $this->_pdf->pages[] = $this->_page;
        $this->_currentPage++;

        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $this->_page->setFont($this->_font, $this->getDeployOption('headerFontSize',14));

        if (file_exists($this->_deploy['logo'])) {
            $image = Zend_Pdf_Image::imageWithPath($this->_deploy['logo']);
            list ($this->_width, $this->_height, $type, $attr) = getimagesize($this->_deploy['logo']);
            $this->_page->drawImage(
                $image, 40, $this->_page->getHeight() - $this->_height - 40, 40 + $this->_width, $this->_page->getHeight() - 40
            );
        }

        $this->_page->drawText(
            $this->__($this->_deploy['title']), $this->_width + 70, $this->_page->getHeight() - 70, $this->getCharEncoding()
        );

        $this->_page->setFont($this->_font, $this->_cellFontSize);

        $this->_page->drawText(
            $this->__($this->_deploy['subtitle']), $this->_width + 70, $this->_page->getHeight() - 80, $this->getCharEncoding()
        );

        $this->_height = $this->_page->getHeight() - 120;

        $this->_page->drawText($this->__($this->_deploy['footer']), 40, 40, $this->getCharEncoding());
        if ($this->_deploy['noPagination'] != 1) {
            $this->_page->drawText(
                $this->__($this->_deploy['page']) . ' ' . $this->_currentPage . '/' . $this->_totalPages, $this->_page->getWidth() - (strlen($this->__($this->_deploy['page'])) * $this->_cellFontSize) - 50, 40, $this->getCharEncoding()
            );
        }

        reset($titles);
        $i = 0;
        $largura1 = 40;
        $this->_page->setFont($this->_font, $this->_cellFontSize + 1);
        foreach ($titles as $title) {
            if (($title['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '')
                || $this->getInfo('hRow,title') == ''
            ) {
                if ((int) $this->_la == 0) {
                    $largura1 = 40;
                } else {
                    @$largura1 = $this->_cell[$i - 1] + $largura1;
                }

                $this->_page->setStyle($this->_styles['topo']);
                $this->_page->drawRectangle($largura1, $this->_height - 8, $largura1 + $this->_cell[$i] + 1, $this->_height + 12);
                $this->_page->setStyle($this->_styles['style']);
                $this->_page->drawText($title['value'], $largura1 + 2, $this->_height, $this->getCharEncoding());
                $this->_la = $largura1;

                $i++;
            }
            $this->_page->setFont($this->_font, $this->_cellFontSize);
        }
    }


    public function deploy()
    {
        $this->checkExportRights();
        $this->setRecordsPerPage(0);
        parent::deploy();

        $this->_width = 0;

        $colors = array('title' => '#000000',
            'subtitle' => '#111111',
            'footer' => '#111111',
            'header' => '#AAAAAA',
            'row1' => '#EEEEEE',
            'row2' => '#FFFFFF',
            'sqlexp' => '#BBBBBB',
            'lines' => '#111111',
            'hrow' => '#E4E4F6',
            'text' => '#000000',
            'filters' => '#F9EDD2',
            'filtersBox' => '#DEDEDE');

        $this->_deploy['colors'] = array_merge($colors, (array) $this->_deploy['colors']);



        if (!isset($this->_deploy['save'])) {
            $this->_deploy['save'] = false;
        }

        if (!isset($this->_deploy['download'])) {
            $this->_deploy['download'] = false;
        }

        if ($this->_deploy['save'] != 1 && $this->_deploy['download'] != 1) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }

        if (empty($this->_deploy['name'])) {
            $this->_deploy['name'] = date('H_m_d_H_i_s');
        }

        if (substr($this->_deploy['name'], - 4) == '.xls') {
            $this->_deploy['name'] = substr($this->_deploy['name'], 0, - 4);
        }

        if (!isset($this->_deploy['noPagination'])) {
            $this->_deploy['noPagination'] = 0;
        }

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/';

        if (!isset($this->_deploy['dir']) || !is_dir($this->_deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not a dir');
        }

        if (!is_writable($this->_deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not writable');
        }

        $this->_la = 0;
        $titles = parent::_buildTitles();
        $grid = parent::_BuildGrid();
        $sqlExp = parent::_buildSqlExp();

        $larg = $this->calculateCellSize($titles,$sqlExp,$grid);
        $lengthTotal = array_sum($larg['larg']);

        $this->_cellFontSize = $this->getDeployOption('cellFontSize', 8);


        if (!$this->getInfo('hRow,field')) {
            $this->_info['hRow']['field'] = '';
        }

        if (strtoupper($this->_deploy['orientation']) == 'LANDSCAPE'
            && strtoupper($this->_deploy['size']) == 'A4'
        ) {
            $this->_totalPages = ceil(count($grid) / 26);
        } elseif (strtoupper($this->_deploy['orientation']) == 'LANDSCAPE'
            && strtoupper($this->_deploy['size']) == 'LETTER'
        ) {
            $this->_totalPages = ceil(count($grid) / 27);
        } else {
            $this->_totalPages = ceil(count($grid) / 37);
        }

        if ($this->_totalPages < 1) {
            $this->_totalPages = 1;
        }


        $this->_pdf = new Zend_Pdf();

        $this->_styles['style'] = new Zend_Pdf_Style();
        $this->_styles['style']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['lines']));

        $this->_styles['topo'] = new Zend_Pdf_Style();
        $this->_styles['topo']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['header']));

        $this->_styles['td'] = new Zend_Pdf_Style();
        $this->_styles['td']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['row2']));

        $this->_styles['styleFilters'] = new Zend_Pdf_Style();
        $this->_styles['styleFilters']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['filters']));

        $this->_styles['styleFiltersBox'] = new Zend_Pdf_Style();
        $this->_styles['styleFiltersBox']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['filtersBox']));

        $this->_styles['td2'] = new Zend_Pdf_Style();
        $this->_styles['td2']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['row1']));

        $this->_styles['hRowStyle'] = new Zend_Pdf_Style();
        $this->_styles['hRowStyle']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['hrow']));

        $this->_styles['styleSql'] = new Zend_Pdf_Style();
        $this->_styles['styleSql']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['sqlexp']));

        $this->_styles['styleText'] = new Zend_Pdf_Style();
        $this->_styles['styleText']->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['text']));


        $this->buildPageStructure($titles,true);


        $pageWidth = $this->_page->getWidth() - 80;


        $i = 0;
        foreach ($larg['larg'] as $final) {
            $this->_cell[$i] = ceil($final * $pageWidth / $lengthTotal);
            $i++;
        }

        $needed = 0;
        $fix = $larg['fix'];
        $larg = $larg['larg'];

        $perc = array();
        $i = 0;
        foreach ($this->_cell as $key => $value) {

            $perc[$key] = $value - $fix[$key];
            $i++;
        }


        $perc = array();
        foreach ($this->_cell as $key => $value) {

            if ($value + 2 < $fix[$key]) {

                $needed = ceil($fix[$key] - $value);
                $this->_cell[$key] = $fix[$key];
            }

            if ($value > $fix[$key] + 2 + $needed) {
                $this->_cell[$key] = $this->_cell[$key] - $needed;
                $needed = 0;
                $perc[$key] = $this->_cell[$key] - $fix[$key];

            }
        }


        if (array_sum($this->_cell) > $pageWidth) {
            $totalToRemove = array_sum($this->_cell) - $pageWidth;

            foreach ($perc as $key => $value) {
                $this->_cell[$key] = $this->_cell[$key] - round($totalToRemove * $value / array_sum($perc));
            }
        }

        $cellsCount = count($titles);
        if ($this->getInfo('hRow,title') != '') {
            $cellsCount--;
        }

        $largura = ($this->_page->getWidth() - 80) / $cellsCount;
        $this->_height = $this->_page->getHeight() - 120;

         $this->buildPageStructure($titles);

        $this->_page->setFont($this->_font, $this->_cellFontSize);
        $this->_page->setStyle($this->_styles['style']);

        if (is_array($grid)) {
            if ($this->getInfo('hRow,title') != '') {
                $bar = $grid;

                $hbar = trim($this->getInfo('hRow,field'));

                $p = 0;
                foreach ($grid[0] as $value) {
                    if ($value['field'] == $hbar) {
                        $hRowIndex = $p;
                    }

                    $p++;
                }
                $aa = 0;
            }

            $ia = 0;
            $aa = 0;
            foreach ($grid as $value) {

                if ($this->_height <= 80) {
                    $this->buildPageStructure($titles);
                }

                $this->_la = 0;
                $this->_height = $this->_height - 16;
                $i = 0;
                $tdf = $ia % 2 ? $this->_styles['td'] : $this->_styles['td2'];

                $a = 1;

                if ($this->getInfo('hRow,title') != '') {

                    if ($bar[$aa][$hRowIndex]['value'] != @$bar[$aa - 1][$hRowIndex]['value']) {

                        $centrar = $this->_page->getWidth() - 80;
                        $centrar = round($centrar / 2) + 30;

                        if ((int) $this->_la == 0) {
                            $largura1 = 40;
                        } else {
                            $largura1 = $this->_cell[$i - 1] + $largura1;
                        }

                        $this->_page->setStyle($this->_styles['hRowStyle']);
                        $this->_page->drawRectangle($largura1, $this->_height-8, $this->_page->getWidth() - 39, $this->_height + 16);
                        $this->_page->setStyle($this->_styles['styleText']);
                        $this->_page->drawText($bar[$aa][$hRowIndex]['value'], $centrar, $this->_height+2, $this->getCharEncoding());
                        $this->_la = 0;
                        $this->_height = $this->_height - 16;
                    }
                }

                $nl = 0;
                $tLines = $this->calculateNumerOfLinesForRecord($value);

                $cellPos = 0;
                foreach ($value as $value1) {

                    $value1['value'] = strip_tags(trim($value1['value']));

                    if (($value1['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '')
                        || $this->getInfo('hRow,title') == ''
                    ) {

                        if ((int) $this->_la == 0) {
                            $largura1 = 40;
                        } else {
                            $largura1 = $this->_cell[$i - 1] + $largura1;
                        }

                        $this->_page->setStyle($tdf);
                        $this->_page->drawRectangle($largura1, $this->_height + 8 , $largura1 + $this->_cell[$i] + 1, $this->_height - 8 );

                        $this->_page->setStyle($this->_styles['styleText']);

                        $textToShow = $this->getArrayForPdfRecord($value1['value'],$cellPos,$tLines);

                        $heightSupport = 0;


                        for ($ti = 0; $ti < $tLines; $ti++) {

                            if (!isset($textToShow[$ti])) {
                                continue;
                            }

                            if (count($textToShow) == 1) {
                                $extraHeight = round($tLines / count($textToShow)) + 2;
                            } else {
                                $extraHeight = 0;
                            }

                            $this->_page->drawText($textToShow[$ti], $largura1 + 2, $this->_height - $extraHeight - ($heightSupport * 8), $this->getCharEncoding());

                            $heightSupport++;
                        }


                        $this->_la = $largura1;

                        $i++;
                        $nl++;

                        $cellPos++;
                    }

                    $a++;
                }

                $aa++;
                $ia++;
            }
        }

        $this->buildSqlPdf($sqlExp);

        $this->buildShowFiltersInExport();

        $this->_pdf->save($this->_deploy['dir'] . $this->_deploy['name'] . '.pdf');

        if ($this->_deploy['download'] == 1) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $this->_deploy['name'] . '.pdf"');
            readfile($this->_deploy['dir'] . $this->_deploy['name'] . '.pdf');
        }

        if ($this->_deploy['save'] != 1) {
            unlink($this->_deploy['dir'] . $this->_deploy['name'] . '.pdf');
        }

        die();
    }

    public function buildSqlPdf($sql)
    {

        $la = 0;
        $this->_height = $this->_height - 20;
        $i = 0;

        if (is_array($sql)) {
            foreach ($sql as $value) {
                if ((int) $la == 0) {
                    $largura1 = 40;
                } else {
                    $largura1 = $this->_cell[$i - 1] + $largura1;
                }

                $this->_page->setStyle($this->_styles['styleSql']);
                $this->_page->drawRectangle($largura1, $this->_height - 4, $largura1 + $this->_cell[$i]+1, $this->_height + 12);
                $this->_page->setStyle($this->_styles['styleText']);
                $this->_page->drawText($value['value'], $largura1 + 2, $this->_height, $this->getCharEncoding());

                $la = $largura1;
                $i++;
            }
        }
    }


    public function buildShowFIltersInExport()
    {
        $this->_la = 0;
        $this->_height = $this->_height - 16;
        $i = 0;

        if (is_array($this->_showFiltersInExport) || $this->_showFiltersInExport == true) {

            if (is_array($this->_showFiltersInExport) && is_array($this->_filtersValues)) {
                $this->_showFiltersInExport = array_merge($this->_showFiltersInExport, $this->_filtersValues);
            } elseif (is_array($this->_showFiltersInExport)) {
                $this->_showFiltersInExport = $this->_showFiltersInExport;
            } elseif (is_array($this->_filtersValues)) {
                $this->_showFiltersInExport = $this->_filtersValues;
            }

            if (count($this->_showFiltersInExport) > 0) {
                $this->_page->setStyle($this->_styles['styleFilters']);
                $this->_page->drawRectangle(40, $this->_height - 4, array_sum($this->_cell) + 41, $this->_height + 12);

                $this->_page->setStyle($this->_styles['styleText']);

                $tLarg = $this->widthForStringUsingFontSize($this->__('Filtered by:'), $this->_font);

                $i = 0;
                $this->_page->setStyle($this->_styles['styleFiltersBox']);
                $this->_page->drawRectangle(40, $this->_height - 4, $tLarg + 60, $this->_height + 12);

                $this->_page->setStyle($this->_styles['styleText']);
                $text = '     ' . $this->__('Filtered by:') . '     ';

                foreach ($this->_showFiltersInExport as $key => $value) {
                    if ($keyHelper = $this->getField($key)) {
                        $key = $keyHelper['title'];
                    }

                    if (is_array($value)) {

                        foreach ($value as $newName => $newValue) {
                            $text .= $this->__($key) . ' - ' . $this->__(ucfirst($newName)) . ': '
                                . $this->__($newValue) . '    |    ';
                        }
                    } else {
                        $text .= $this->__($key) . ': ' . $this->__($value) . '    |    ';
                    }
                    $i++;
                }
                $this->_page->drawText($text, $tLarg + 3, $this->_height, $this->getCharEncoding());
            }
        }
    }


    public function calculateNumerOfLinesForRecord($value)
    {

        $value = array_slice($value, 0, count($this->_cell));


        $width = array();
        foreach ($value as $key => $field) {
            $width[$key] = round($this->widthForStringUsingFontSize($field['value'], $this->_font));
        }

        $lines = 1;
        foreach ($width as $key => $value) {
            $i = ceil($width[$key]/$this->_cell[$key]);
            if ($i > $lines) {
                $lines = $i;
            }
        }


        return $lines;
    }

    public function getArrayForPdfRecord($value, $pos)
    {

        $width = round($this->widthForStringUsingFontSize($value, $this->_font));
        $maxWidth = $this->_cell[$pos];


        if ($maxWidth > $width) {
            return array($value);
        }

        $return = array();

        $chuncks = explode(" ",$value);

        $current = 0;
        $currentCounter = 0;

        for ($i = 0; $i < count($chuncks); $i++) {

            $aux = implode(' ', array_slice($chuncks, $current, $currentCounter + 1));

            if ($this->widthForStringUsingFontSize($aux, $this->_font) > $maxWidth) {
                $return[] = implode(' ', array_slice($chuncks, $current, $currentCounter));
                $current = $i;
                $currentCounter = 0;

            }

            $currentCounter++;

            if (($i + 1) == count($chuncks)) {
                $return[] = implode(' ',array_slice($chuncks,$current+1));
            }

        }

        return $return;

    }
}
