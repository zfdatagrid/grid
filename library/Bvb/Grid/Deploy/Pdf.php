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

    const OUTPUT = 'pdf';

    public $deploy;

    protected $_y = 0;

    protected $_styleText = 0;

    protected $_pdf = 0;

    protected $_pageCount;

    protected $_page;

    protected $_larg;

    protected $_cell;

    protected $_totalLen;

    protected $_td;

    protected $_td2;

    protected $_hRowStyle;

    protected $_styleSql;

    protected $_styleFilters;

    protected $_font;

    protected $_cellFontSize;

    protected $_actualPage;

    protected $_titulos;

    protected $_sql;

    protected $_grid;


    public function __construct ($options)
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->_pdf = new Zend_Pdf();
        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    }


    /**
     * @copyright http://n4.nabble.com/Finding-width-of-a-drawText-Text-in-Zend-Pdf-td677978.html
     * @param $string
     * @param $font
     * @param $fontSize
     */

    public function widthForStringUsingFontSize ($string, $fontSize)
    {
        $font = $this->_font;

        @$drawingString = iconv('', 'UTF-16BE', $string);
        $characters = array();
        for ( $i = 0; $i < strlen($drawingString); $i ++ ) {
            $characters[] = (ord($drawingString[$i ++]) << 8) | ord($drawingString[$i]);
        }

        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }


    public function calculateCellSize ()
    {

        $i = 0;

        foreach ( $this->_titulos as $titulos ) {

            if ( (isset($titulos['field']) && $titulos['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                $larg[$i] = $this->widthForStringUsingFontSize($titulos['value'], 8);
                $i ++;
            }
        }

        $i = 0;

        if ( is_array($this->_sql) ) {
            foreach ( $this->_sql as $sql ) {
                if ( ($sql['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                    if ( $larg[$i] < strlen($sql['value']) ) {
                        $larg[$i] = strlen($sql['value']);
                    }
                    $i ++;
                }
            }
        }


        /////////////////
        /////////////////
        /////////////////
        if ( $this->getInfo('hRow,title') != '' ) {
            $bar = $this->_grid;

            $hbar = trim($this->getInfo('hRow,field'));

            $p = 0;
            foreach ( $this->_grid[0] as $value ) {
                if ( $value['field'] == $hbar ) {
                    $hRowIndex = $p;
                }

                $p ++;
            }
            $aa = 0;
        }


        //////////////
        //////////////
        //////////////



        foreach ( $this->_grid as $row ) {

            $i = 0;

            $a = 1;
            foreach ( $row as $value ) {

                $value['value'] = strip_tags($value['value']);

                if ( (isset($value['field']) && $value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {

                    if ( $larg[$i] < strlen($value['value']) ) {
                        $larg[$i] = strlen($value['value']);
                    }
                    $i ++;
                }
                $a ++;
            }
            $i ++;

        }

        return $larg;
    }


    protected function _newPage ()
    {

        if ( ! $this->getInfo('hRow,field') ) {
            $this->_info['hRow']['field'] = '';
        }


        if ( strtoupper($this->deploy['orientation']) == 'LANDSCAPE' && strtoupper($this->deploy['size']) == 'A4' ) {
            $this->_pageCount = ceil(count($this->_grid) / 26);

        } elseif ( strtoupper($this->deploy['orientation']) == 'LANDSCAPE' && strtoupper($this->deploy['size']) == 'LETTER' ) {
            $this->_pageCount = ceil(count($this->_grid) / 27);

        } else {
            $this->_pageCount = ceil(count($this->_grid) / 37);

        }

        if ( $this->_pageCount < 1 ) {
            $this->_pageCount = 1;
        }


        // Create new Style
        $style = new Zend_Pdf_Style();
        $style->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['lines']));

        $topo = new Zend_Pdf_Style();
        $topo->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['header']));

        $this->_td = new Zend_Pdf_Style();
        $this->_td->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['row2']));

        $this->_td2 = new Zend_Pdf_Style();
        $this->_td2->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['row1']));

        $this->_hRowStyle = new Zend_Pdf_Style();
        $this->_hRowStyle->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['hrow']));

        $this->_styleSql = new Zend_Pdf_Style();
        $this->_styleSql->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['sqlexp']));

        $this->_styleFilters = new Zend_Pdf_Style();
        $this->_styleFilters->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['filters']));

        $this->_styleText = new Zend_Pdf_Style();
        $this->_styleText->setFillColor(new Zend_Pdf_Color_Html($this->deploy['colors']['text']));

        // Add new page to the document
        if ( strtoupper($this->deploy['size'] = 'LETTER') && strtoupper($this->deploy['orientation']) == 'LANDSCAPE' ) {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);
        } elseif ( strtoupper($this->deploy['size'] = 'LETTER') && strtoupper($this->deploy['orientation']) != 'LANDSCAPE' ) {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        } elseif ( strtoupper($this->deploy['size'] != 'A4') && strtoupper($this->deploy['orientation']) == 'LANDSCAPE' ) {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
        } else {
            $this->_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        }

        $this->_page->setStyle($style);
        $this->_pdf->pages[] = $this->_page;


        $this->_page->setFont($this->_font, 14);
        //logotipo Federação $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);



        if ( is_file($this->deploy['logo']) ) {
            $image = Zend_Pdf_Image::imageWithPath($this->deploy['logo']);

            list ($width, $height, $type, $attr) = getimagesize($this->deploy['logo']);

            $this->_page->drawImage($image, 40, $this->_page->getHeight() - $height - 40, 40 + $width, $this->_page->getHeight() - 40);
        }

        $this->_page->drawText($this->__($this->deploy['title']), $width + 70, $this->_page->getHeight() - 70, $this->getCharEncoding());
        $this->_page->setFont($this->_font, $this->_cellFontSize);

        $this->_page->drawText($this->__($this->deploy['subtitle']), $width + 70, $this->_page->getHeight() - 80, $this->getCharEncoding());

        //Iniciar a contagem de páginas
        $this->_actualPage ++;


        $this->_page->drawText($this->deploy['footer'], 40, 40, $this->getCharEncoding());
        if ( ! isset($this->deploy['noPagination']) || $this->deploy['noPagination'] != 1 ) {
            $this->_page->drawText($this->__($this->deploy['page']) . ' ' . $this->_actualPage . '/' . $this->_pageCount, $this->_page->getWidth() - (strlen($this->__($this->deploy['page'])) * $this->_cellFontSize) - 50, 40, $this->getCharEncoding());
        }


        $this->_page->setFont($this->_font, $this->_cellFontSize);
        $pl = $this->_page->getWidth() - 80;


        $i = 0;

        foreach ( $this->_larg as $final ) {
            $this->_cell[$i] = round($final * $pl / $this->_totalLen);
            $i ++;
        }


        $cellsCount = count($this->_titulos);
        if ( $this->getInfo('hRow,title') != '' ) {
            $cellsCount --;
        }

        $largura = ($this->_page->getWidth() - 80) / $cellsCount;
        $this->_y = $this->_page->getHeight() - 120;


        $i = 0;
        $this->_page->setFont($this->_font, $this->_cellFontSize + 1);

        foreach ( $this->_titulos as $value ) {

            if ( ($value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {

                if ( (int) $la == 0 ) {
                    $largura1 = 40;
                } else {
                    $largura1 = $this->_cell[$i - 1] + $largura1;
                }


                $this->_page->setStyle($topo);
                $this->_page->drawRectangle($largura1, $this->_y - 4, $largura1 + $this->_cell[$i] + 1, $this->_y + 12);
                $this->_page->setStyle($this->_styleText);
                $this->_page->drawText($value['value'], $largura1 + 2, $this->_y, $this->getCharEncoding());
                $la = $largura1;

                $i ++;
            }

        }
        $this->_page->setFont($this->_font, $this->_cellFontSize);
        $this->_page->setStyle($style);

    }


    public function deploy ()
    {

        if ( ! in_array(self::OUTPUT, $this->_export) ) {
            echo $this->__("You dont' have permission to export the results to this format");
            die();
        }


        $width = 0;


        $this->setNumberRecordsPerPage(0);
        parent::deploy();
        $colors = array('title' => '#000000', 'subtitle' => '#111111', 'footer' => '#111111', 'header' => '#AAAAAA', 'row1' => '#EEEEEE', 'row2' => '#FFFFFF', 'sqlexp' => '#BBBBBB', 'lines' => '#111111', 'hrow' => '#E4E4F6', 'text' => '#000000', 'filters' => '#F9EDD2');

        $this->deploy['colors'] = array_merge($colors, $this->deploy['colors']);


        $la = '';

        if ( ! isset($this->deploy['save']) ) {
            $this->deploy['save'] = false;
        }

        if ( ! isset($this->deploy['download']) ) {
            $this->deploy['download'] = false;
        }

        if ( $this->deploy['save'] != 1 && $this->deploy['download'] != 1 ) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }


        if ( empty($this->deploy['name']) ) {
            $this->deploy['name'] = date('H_m_d_H_i_s');
        }

        if ( substr($this->deploy['name'], - 4) == '.xls' ) {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 4);
        }

        if ( ! isset($this->deploy['noPagination']) ) {
            $this->deploy['noPagination'] = 0;
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';

        if ( ! isset($this->deploy['dir']) || ! is_dir($this->deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if ( ! is_writable($this->deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);


        $this->_titulos = parent::_buildTitles();
        $this->_sql = parent::_buildSqlExp();
        $this->_grid = parent::_BuildGrid();


        $this->_larg = self::calculateCellSize();


        $this->_totalLen = array_sum($this->_larg);

        $this->_cellFontSize = 8;

        //set font
        /////////////



        $this->_newPage();

        if ( is_array($this->_grid) ) {
            /////////////////
            if ( $this->getInfo('hRow,title') != '' ) {
                $bar = $this->_grid;

                $hbar = trim($this->getInfo('hRow,field'));

                $p = 0;
                foreach ( $this->_grid[0] as $value ) {
                    if ( $value['field'] == $hbar ) {
                        $hRowIndex = $p;
                    }

                    $p ++;
                }
                $aa = 0;
            }

            //////////////
            //////////////
            $ia = 0;
            $aa = 0;
            foreach ( $this->_grid as $value ) {

                if ( $this->_y <= 80 ) {
                    $this->_newPage();
                }


                $la = 0;
                $this->_y = $this->_y - 16;
                $i = 0;
                $tdf = $ia % 2 ? $this->_td : $this->_td2;

                $a = 1;


                ////////////
                //A linha horizontal
                if ( $this->getInfo('hRow,title') != '' ) {

                    if ( $bar[$aa][$hRowIndex]['value'] != $bar[$aa - 1][$hRowIndex]['value'] ) {

                        $centrar = $this->_page->getWidth() - 80;
                        $centrar = round($centrar / 2) + 30;

                        if ( (int) $la == 0 ) {
                            $largura1 = 40;
                        } else {
                            $largura1 = $this->_cell[$i - 1] + $largura1;
                        }


                        $this->_page->setStyle($this->_hRowStyle);
                        $this->_page->drawRectangle($largura1, $this->_y - 4, $this->_page->getWidth() - 40, $this->_y + 12);
                        $this->_page->setStyle($this->_styleText);
                        $this->_page->drawText($bar[$aa][$hRowIndex]['value'], $centrar, $this->_y, $this->getCharEncoding());
                        $la = 0;
                        $this->_y = $this->_y - 16;

                    }
                }

                ////////////



                //Vamos saber qauntas linhas tem este registo
                $nlines = array();
                $nl = 0;
                foreach ( $value as $lines ) {
                    $line = $this->widthForStringUsingFontSize(strip_tags(trim($lines['value'])), 8);
                    $nlines[] = ceil($line / $this->_cell[$nl]);
                    $nl ++;
                }

                sort($nlines);
                $totalLines = end($nlines);


                $nl = 0;
                foreach ( $value as $value1 ) {

                    $value1['value'] = strip_tags(trim($value1['value']));

                    if ( ($value1['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {


                        if ( (int) $la == 0 ) {
                            $largura1 = 40;
                        } else {
                            $largura1 = $this->_cell[$i - 1] + $largura1;
                        }

                        $this->_page->setStyle($tdf);
                        $this->_page->drawRectangle($largura1, $this->_y - 4, $largura1 + $this->_cell[$i] + 1, $this->_y + 12);
                        $this->_page->setStyle($this->_styleText);
                        $this->_page->drawText($value1['value'], $largura1 + 2, $this->_y, $this->getCharEncoding());


                        $la = $largura1;

                        $i ++;
                        $nl ++;
                    }

                    $a ++;
                }

                $aa ++;
                $ia ++;
            }
        }
        /////////////



        $la = 0;
        $this->_y = $this->_y - 16;
        $i = 0;

        if ( is_array($this->_sql) ) {
            foreach ( $this->_sql as $value ) {

                if ( (int) $la == 0 ) {
                    $largura1 = 40;
                } else {
                    $largura1 = $this->_cell[$i - 1] + $largura1;
                }

                $this->_page->setStyle($this->_styleSql);
                $this->_page->drawRectangle($largura1, $this->_y - 4, $largura1 + $this->_cell[$i], $this->_y + 12);
                $this->_page->setStyle($this->_styleText);
                $this->_page->drawText($value['value'], $largura1 + 2, $this->_y, $this->getCharEncoding());
                $la = $largura1;

                $la = $largura1;
                $i ++;
            }
        }


        $this->_y = $this->_y - 16;

        if ( is_array($this->_showFiltersInExport) || $this->_showFiltersInExport == true ) {


            if ( is_array($this->_showFiltersInExport) && is_array($this->_filtersValues) ) {
                $this->_showFiltersInExport = array_merge($this->_showFiltersInExport, $this->_filtersValues);
            } elseif ( is_array($this->_showFiltersInExport) ) {
                $this->_showFiltersInExport = $this->_showFiltersInExport;
            } elseif ( is_array($this->_filtersValues) ) {
                $this->_showFiltersInExport = $this->_filtersValues;
            }

            if ( count($this->_showFiltersInExport) > 0 ) {

                $this->_page->setStyle($this->_styleFilters);
                $this->_page->drawRectangle(40, $this->_y - 4, array_sum($this->_cell) + 41, $this->_y + 12);

                $this->_page->setStyle($this->_styleText);

                $tLarg = $this->widthForStringUsingFontSize($this->__('Filtered by:'), 8);

                $this->_page->drawText($this->__('Filtered by:'), $tLarg + 2, $this->_y, $this->getCharEncoding());


                $i = 0;
                foreach ( $this->_showFiltersInExport as $key => $value ) {

                    if ( $i == 0 ) {
                        $largura1 = 40 + $tLarg + 5;
                    } else {
                        $largura1 = strlen($this->__($key) . ': ' . $this->__($value)) * 4 + $largura1;
                    }

                    $this->_page->drawText($this->__($key) . ': ' . $this->__($value), $largura1 + 3, $this->_y, $this->getCharEncoding());
                    $i ++;
                }
            }
        }


        $this->_pdf->save($this->deploy['dir'] . $this->deploy['name'] . '.pdf');

        if ( $this->deploy['download'] == 1 ) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $this->deploy['name'] . '.pdf"');
            readfile($this->deploy['dir'] . $this->deploy['name'] . '.pdf');
        }


        if ( $this->deploy['save'] != 1 ) {
            unlink($this->deploy['dir'] . $this->deploy['name'] . '.pdf');
        }

        die();
    }


}
