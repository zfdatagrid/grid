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

class Bvb_Grid_Deploy_Pdf extends Bvb_Grid implements Bvb_Grid_Deploy_Interface
{
    public function __construct ($options)
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);
    }

    /**
     * @copyright http://n4.nabble.com/Finding-width-of-a-drawText-Text-in-Zend-Pdf-td677978.html
     * @param $string
     * @param $font
     * @param $fontSize
     */
    public function widthForStringUsingFontSize ($string, $font, $fontSize=8)
    {
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
        $titles = parent::_buildTitles();
        $sqlexp = parent::_buildSqlExp();
        $grid = parent::_buildGrid();

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $i = 0;

        foreach ( $titles as $titulos ) {
            if ( (@$titulos['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                $larg[$i] = $this->widthForStringUsingFontSize($titulos['value'], $font, 8);
                $i ++;
            }
        }

        $i = 0;

        if ( is_array($sqlexp) ) {
            foreach ( $sqlexp as $sql ) {
                if ( ($sql['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                    if ( $larg[$i] < strlen($sql['value']) ) {
                        $larg[$i] = strlen($sql['value']);
                    }
                    $i ++;
                }
            }
        }

        if ( $this->getInfo('hRow,title') != '' ) {
            $bar = $grid;

            $hbar = trim($this->getInfo('hRow,field'));

            $p = 0;
            foreach ( $grid[0] as $value ) {
                if ( $value['field'] == $hbar ) {
                    $hRowIndex = $p;
                }

                $p ++;
            }
            $aa = 0;
        }

        foreach ( $grid as $row ) {
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

    public function deploy()
    {
        $this->checkExportRights();
        $this->setRecordsPerPage(0);
        parent::deploy();

        $width = 0;

        $colors = array('title' => '#000000', 'subtitle' => '#111111', 'footer' => '#111111', 'header' => '#AAAAAA', 'row1' => '#EEEEEE', 'row2' => '#FFFFFF', 'sqlexp' => '#BBBBBB', 'lines' => '#111111', 'hrow' => '#E4E4F6', 'text' => '#000000', 'filters' => '#F9EDD2', 'filtersBox' => '#DEDEDE');

        $this->_deploy['colors'] = array_merge($colors, (array)$this->_deploy['colors']);

        $la = '';

        if ( ! isset($this->_deploy['save']) ) {
            $this->_deploy['save'] = false;
        }

        if ( ! isset($this->_deploy['download']) ) {
            $this->_deploy['download'] = false;
        }

        if ( $this->_deploy['save'] != 1 && $this->_deploy['download'] != 1 ) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }

        if ( empty($this->_deploy['name']) ) {
            $this->_deploy['name'] = date('H_m_d_H_i_s');
        }

        if ( substr($this->_deploy['name'], - 4) == '.xls' ) {
            $this->_deploy['name'] = substr($this->_deploy['name'], 0, - 4);
        }

        if ( ! isset($this->_deploy['noPagination']) ) {
            $this->_deploy['noPagination'] = 0;
        }

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/';

        if ( ! isset($this->_deploy['dir']) || ! is_dir($this->_deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not a dir');
        }

        if ( ! is_writable($this->_deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not writable');
        }

        $larg = self::calculateCellSize();
        $total_len = array_sum($larg);

        $cellFontSize = 8;

        //set font
        $titulos = parent::_buildTitles();
        $sql = parent::_buildSqlExp();
        $grid = parent::_BuildGrid();

        if ( ! $this->getInfo('hRow,field') ) {
            $this->_info['hRow']['field'] = '';
        }

        if ( strtoupper($this->_deploy['orientation']) == 'LANDSCAPE' && strtoupper($this->_deploy['size']) == 'A4' ) {
            $totalPaginas = ceil(count($grid) / 26);
        } elseif ( strtoupper($this->_deploy['orientation']) == 'LANDSCAPE' && strtoupper($this->_deploy['size']) == 'LETTER' ) {
            $totalPaginas = ceil(count($grid) / 27);
        } else {
            $totalPaginas = ceil(count($grid) / 37);
        }

        if ( $totalPaginas < 1 ) {
            $totalPaginas = 1;
        }

        $pdf = new Zend_Pdf();

        // Create new Style
        $style = new Zend_Pdf_Style();
        $style->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['lines']));

        $topo = new Zend_Pdf_Style();
        $topo->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['header']));

        $td = new Zend_Pdf_Style();
        $td->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['row2']));

        $styleFilters = new Zend_Pdf_Style();
        $styleFilters->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['filters']));

        $styleFiltersBox = new Zend_Pdf_Style();
        $styleFiltersBox->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['filtersBox']));

        $td2 = new Zend_Pdf_Style();
        $td2->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['row1']));

        $hRowStyle = new Zend_Pdf_Style();
        $hRowStyle->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['hrow']));

        $styleSql = new Zend_Pdf_Style();
        $styleSql->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['sqlexp']));

        $styleText = new Zend_Pdf_Style();
        $styleText->setFillColor(new Zend_Pdf_Color_Html($this->_deploy['colors']['text']));

        // Add new page to the document
        if ( strtoupper($this->_deploy['size'] = 'LETTER') && strtoupper($this->_deploy['orientation']) == 'LANDSCAPE' ) {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);
        } elseif ( strtoupper($this->_deploy['size'] = 'LETTER') && strtoupper($this->_deploy['orientation']) != 'LANDSCAPE' ) {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        } elseif ( strtoupper($this->_deploy['size'] != 'A4') && strtoupper($this->_deploy['orientation']) == 'LANDSCAPE' ) {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
        } else {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        }

        $page->setStyle($style);
        $pdf->pages[] = $page;

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 14);
        //logotipo FederaÃ§Ã£o $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        if ( file_exists($this->_deploy['logo']) ) {
            $image = Zend_Pdf_Image::imageWithPath($this->_deploy['logo']);

            list ($width, $height, $type, $attr) = getimagesize($this->_deploy['logo']);

            $page->drawImage($image, 40, $page->getHeight() - $height - 40, 40 + $width, $page->getHeight() - 40);
        }

        $page->drawText($this->__($this->_deploy['title']), $width + 70, $page->getHeight() - 70, $this->getCharEncoding());
        $page->setFont($font, $cellFontSize);

        $page->drawText($this->__($this->_deploy['subtitle']), $width + 70, $page->getHeight() - 80, $this->getCharEncoding());

        //Iniciar a contagem de pÃ¡ginas
        $pagina = 1;

        $page->drawText($this->_deploy['footer'], 40, 40, $this->getCharEncoding());
        if ( @$this->_deploy['noPagination'] != 1 ) {
            $page->drawText($this->__($this->_deploy['page']) . ' ' . $pagina . '/' . $totalPaginas, $page->getWidth() - (strlen($this->__($this->_deploy['page'])) * $cellFontSize) - 50, 40, $this->getCharEncoding());
        }

        $page->setFont($font, $cellFontSize);
        $pl = $page->getWidth() - 80;

        $i = 0;

        foreach ( $larg as $final ) {
            $cell[$i] = round($final * $pl / $total_len);
            $i ++;
        }

        $total_celulas = count($titulos);
        if ( $this->getInfo('hRow,title') != '' ) {
            $total_celulas --;
        }
        $largura = ($page->getWidth() - 80) / $total_celulas;
        $altura = $page->getHeight() - 120;

        $i = 0;
        $page->setFont($font, $cellFontSize + 1);
        foreach ( $titulos as $value ) {
            if ( ($value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                if ( (int) $la == 0 ) {
                    $largura1 = 40;
                } else {
                    $largura1 = $cell[$i - 1] + $largura1;
                }

                $page->setStyle($topo);
                $page->drawRectangle($largura1, $altura - 4, $largura1 + $cell[$i] + 1, $altura + 12);
                $page->setStyle($styleText);
                $page->drawText($value['value'], $largura1 + 2, $altura, $this->getCharEncoding());
                $la = $largura1;

                $i ++;
            }
        }

        $page->setFont($font, $cellFontSize);
        $page->setStyle($style);

        if ( is_array($grid) ) {
            if ( $this->getInfo('hRow,title') != '' ) {
                $bar = $grid;

                $hbar = trim($this->getInfo('hRow,field'));

                $p = 0;
                foreach ( $grid[0] as $value ) {
                    if ( $value['field'] == $hbar ) {
                        $hRowIndex = $p;
                    }

                    $p ++;
                }
                $aa = 0;
            }

            $ia = 0;
            $aa = 0;
            foreach ( $grid as $value ) {
                if ( $altura <= 80 ) {
                    // Add new page to the document
                    if ( strtoupper($this->_deploy['size'] = 'LETTER') && strtoupper($this->_deploy['orientation']) == 'LANDSCAPE' ) {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);
                    } elseif ( strtoupper($this->_deploy['size'] = 'LETTER') && strtoupper($this->_deploy['orientation']) != 'LANDSCAPE' ) {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
                    } elseif ( strtoupper($this->_deploy['size'] != 'A4') && strtoupper($this->_deploy['orientation']) == 'LANDSCAPE' ) {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                    } else {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                    }

                    $page->setStyle($style);
                    $pdf->pages[] = $page;
                    $pagina ++;

                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

                    $page->setFont($font, 14);

                    //logotipo FederaÃ§Ã£o $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    if ( file_exists($this->_deploy['logo']) ) {
                        $image = Zend_Pdf_Image::imageWithPath($this->_deploy['logo']);
                        list ($width, $height, $type, $attr) = getimagesize($this->_deploy['logo']);
                        $page->drawImage($image, 40, $page->getHeight() - $height - 40, 40 + $width, $page->getHeight() - 40);
                    }

                    $page->drawText($this->__($this->_deploy['title']), $width + 70, $page->getHeight() - 70, $this->getCharEncoding());
                    $page->setFont($font, $cellFontSize);

                    $page->drawText($this->__($this->_deploy['subtitle']), $width + 70, $page->getHeight() - 80, $this->getCharEncoding());

                    //set font
                    $altura = $page->getHeight() - 120;

                    $page->drawText($this->__($this->_deploy['footer']), 40, 40, $this->getCharEncoding());
                    if ( $this->_deploy['noPagination'] != 1 ) {
                        $page->drawText($this->__($this->_deploy['page']) . ' ' . $pagina . '/' . $totalPaginas, $page->getWidth() - (strlen($this->__($this->_deploy['page'])) * $cellFontSize) - 50, 40, $this->getCharEncoding());
                    }

                    //Colocar novamento os tÃ­tulos em cada pÃ¡gina
                    reset($titulos);
                    $i = 0;
                    $largura1 = 40;
                    $page->setFont($font, $cellFontSize + 1);
                    foreach ( $titulos as $title ) {
                        if ( ($title['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                            if ( (int) $la == 0 ) {
                                $largura1 = 40;
                            } else {
                                $largura1 = $cell[$i - 1] + $largura1;
                            }

                            $page->setStyle($topo);
                            $page->drawRectangle($largura1, $altura - 4, $largura1 + $cell[$i] + 1, $altura + 12);
                            $page->setStyle($style);
                            $page->drawText($title['value'], $largura1 + 2, $altura, $this->getCharEncoding());
                            $la = $largura1;

                            $i ++;
                        }
                    }
                    $page->setFont($font, $cellFontSize);
                }

                $la = 0;
                $altura = $altura - 16;
                $i = 0;
                $tdf = $ia % 2 ? $td : $td2;

                $a = 1;

                //A linha horizontal
                if ( $this->getInfo('hRow,title') != '' ) {

                    if ( $bar[$aa][$hRowIndex]['value'] != $bar[$aa - 1][$hRowIndex]['value'] ) {

                        $centrar = $page->getWidth() - 80;
                        $centrar = round($centrar / 2) + 30;

                        if ( (int) $la == 0 ) {
                            $largura1 = 40;
                        } else {
                            $largura1 = $cell[$i - 1] + $largura1;
                        }

                        $page->setStyle($hRowStyle);
                        $page->drawRectangle($largura1, $altura - 4, $page->getWidth() - 40, $altura + 12);
                        $page->setStyle($styleText);
                        $page->drawText($bar[$aa][$hRowIndex]['value'], $centrar, $altura, $this->getCharEncoding());
                        $la = 0;
                        $altura = $altura - 16;

                    }
                }

                ////////////

                //Vamos saber qauntas linhas tem este registo
                $nlines = array();
                $nl = 0;
                foreach ( $value as $lines ) {
                    $line = $this->widthForStringUsingFontSize(strip_tags(trim($lines['value'])), $font, 8);
                    $nlines[] = ceil($line / $cell[$nl]);
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
                            $largura1 = $cell[$i - 1] + $largura1;
                        }

                        $page->setStyle($tdf);
                        $page->drawRectangle($largura1, $altura - 4, $largura1 + $cell[$i] + 1, $altura + 12);
                        $page->setStyle($styleText);
                        $page->drawText($value1['value'], $largura1 + 2, $altura, $this->getCharEncoding());

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

        $la = 0;
        $altura = $altura - 16;
        $i = 0;

        if ( is_array($sql) ) {
            foreach ( $sql as $value ) {
                if ( (int) $la == 0 ) {
                    $largura1 = 40;
                } else {
                    $largura1 = $cell[$i - 1] + $largura1;
                }

                $page->setStyle($styleSql);
                $page->drawRectangle($largura1, $altura - 4, $largura1 + $cell[$i], $altura + 12);
                $page->setStyle($styleText);
                $page->drawText($value['value'], $largura1 + 2, $altura, $this->getCharEncoding());
                $la = $largura1;

                $la = $largura1;
                $i ++;
            }
        }

        $la = 0;
        $altura = $altura - 16;
        $i = 0;

        if ( is_array($this->_showFiltersInExport) || $this->_showFiltersInExport == true ) {
            if ( is_array($this->_showFiltersInExport) && is_array($this->_filtersValues) ) {
                $this->_showFiltersInExport = array_merge($this->_showFiltersInExport, $this->_filtersValues);
            } elseif ( is_array($this->_showFiltersInExport) ) {
                $this->_showFiltersInExport = $this->_showFiltersInExport;
            } elseif ( is_array($this->_filtersValues) ) {
                $this->_showFiltersInExport = $this->_filtersValues;
            }

            if ( count($this->_showFiltersInExport) > 0 ) {
                $page->setStyle($styleFilters);
                $page->drawRectangle(40, $altura - 4, array_sum($cell) + 41, $altura + 12);

                $page->setStyle($styleText);

                $tLarg = $this->widthForStringUsingFontSize($this->__('Filtered by:'), $font);

                $i = 0;
                $page->setStyle($styleFiltersBox);
                $page->drawRectangle(40, $altura - 4, $tLarg + 50, $altura + 12);

                $page->setStyle($styleText);
                $text = $this->__('Filtered by:     ');

                foreach ( $this->_showFiltersInExport as $key => $value ) {
                    if ( $keyHelper = $this->getField($key) ) {
                        $key = $keyHelper['title'];
                    }

                    $text .= $this->__($key) . ': ' . $this->__($value).'    |    ';
                    $i ++;
                }
                $page->drawText($text, $tLarg + 3, $altura, $this->getCharEncoding());
            }
        }

        $pdf->save($this->_deploy['dir'] . $this->_deploy['name'] . '.pdf');

        if ( $this->_deploy['download'] == 1 ) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $this->_deploy['name'] . '.pdf"');
            readfile($this->_deploy['dir'] . $this->_deploy['name'] . '.pdf');
        }

        if ( $this->_deploy['save'] != 1 ) {
            unlink($this->_deploy['dir'] . $this->_deploy['name'] . '.pdf');
        }

        die();
    }
}
