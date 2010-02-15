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

class Bvb_Grid_Deploy_Pdf extends Bvb_Grid_Data implements Bvb_Grid_Deploy_Interface
{

    const OUTPUT = 'pdf';

    public $deploy;


    function __construct($options)
    {

        if (! in_array ( self::OUTPUT, $this->export ))
        {
            echo $this->__ ( "You dont' have permission to export the results to this format" );
            die ();
        }

        $this->_setRemoveHiddenFields(true);
        parent::__construct ($options  );

    }


    function calculateCellSize()
    {

        $titles = parent::_buildTitles ();
        $sqlexp = parent::_buildSqlExp ();
        $grid = parent::_buildGrid ();


        $i = 0;

        foreach ( $titles as $titulos )
        {

            if ((@$titulos ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
            {
                $larg [$i] = strlen ( $titulos ['value'] );
                $i ++;
            }
        }


        $i = 0;

        if (is_array ( $sqlexp ))
        {
            foreach ( $sqlexp as $sql )
            {
                if (($sql ['field'] != $this->info ['hRow'] ['field'] && $this->info ['hRow'] ['title'] != '') || $this->info ['hRow'] ['title'] == '')
                {
                    if ($larg [$i] < strlen ( $sql ['value'] ))
                    {
                        $larg [$i] = strlen ( $sql ['value'] );
                    }
                    $i ++;
                }
            }
        }


        /////////////////
        /////////////////
        /////////////////
        if (@$this->info ['hRow'] ['title'] != '')
        {
            $bar = $grid;

            $hbar = trim ( $this->info ['hRow'] ['field'] );

            $p = 0;
            foreach ( $grid [0] as $value )
            {
                if ($value ['field'] == $hbar)
                {
                    $hRowIndex = $p;
                }

                $p ++;
            }
            $aa = 0;
        }


        //////////////
        //////////////
        //////////////



        foreach ( $grid as $row )
        {

            $i = 0;

            $a = 1;
            foreach ( $row as $value )
            {


                $value ['value'] = strip_tags ( $value ['value'] );


                if ((@$value ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
                {

                    if ($larg [$i] < strlen ( $value ['value'] ))
                    {
                        $larg [$i] = strlen ( $value ['value'] );
                    }
                    $i ++;
                }
                $a ++;
            }
            $i ++;

        }

        return $larg;

    }


    function deploy()
    {
        $width= 0;


        $this->setPagination ( 0 );
        parent::deploy ();

        $colors = array('title' =>'#000000','subtitle'=>'#111111','footer' => '#111111', 'header' => '#AAAAAA', 'row1' => '#EEEEEE', 'row2' => '#FFFFFF',
'sqlexp' => '#BBBBBB', 'lines' => '#111111', 'hrow' => '#E4E4F6', 'text' => '#000000');

        $this->deploy['colors'] = array_merge($colors,$this->deploy['colors']);


        $la = '';

        if (! isset($this->deploy['save'])) {
            $this->deploy['save'] = false;
        }

        if (! isset($this->deploy['download'])) {
            $this->deploy['download'] = false;
        }

        if ($this->deploy['save'] != 1 && $this->deploy['download'] != 1) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }


        if (empty($this->deploy['name'])) {
            $this->deploy['name'] = date('H_m_d_H_i_s');
        }

        if (substr($this->deploy['name'], - 4) == '.xls') {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 4);
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';

        if (!isset($this->deploy['dir']) || !is_dir($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if (! is_writable($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        $larg = self::calculateCellSize ();
        $total_len = array_sum ( $larg );


        if ($total_len < 50)
        {
            $cellFontSize = 8;
        } else
        {
            $cellFontSize = 7;
        }

        //set font

        /////////////

        $titulos = parent::_buildTitles ();
        $sql = parent::_buildSqlExp ();
        $grid = parent::_BuildGrid ();


        if (strtoupper ( $this->deploy ['orientation'] ) == 'LANDSCAPE' && strtoupper ( $this->deploy ['size'] ) == 'A4')
        {
            $totalPaginas = ceil ( count ( $grid ) / 26 );

        } elseif (strtoupper ( $this->deploy ['orientation'] ) == 'LANDSCAPE' && strtoupper ( $this->deploy ['size'] ) == 'LETTER')
        {
            $totalPaginas = ceil ( count ( $grid ) / 27 );

        } else
        {
            $totalPaginas = ceil ( count ( $grid ) / 37 );

        }

        if ($totalPaginas < 1)
        {
            $totalPaginas = 1;
        }


        $pdf = new Zend_Pdf ( );

        // Create new Style
        $style = new Zend_Pdf_Style ( );
        $style->setFillColor ( new Zend_Pdf_Color_Html ( $this->deploy['colors'] ['lines'] ) );

        $topo = new Zend_Pdf_Style ( );
        $topo->setFillColor ( new Zend_Pdf_Color_Html ($this->deploy['colors'] ['header'] ) );

        $td = new Zend_Pdf_Style ( );
        $td->setFillColor ( new Zend_Pdf_Color_Html ( $this->deploy['colors'] ['row2'] ) );

        $td2 = new Zend_Pdf_Style ( );
        $td2->setFillColor ( new Zend_Pdf_Color_Html ( $this->deploy['colors'] ['row1'] ) );

        $hRowStyle = new Zend_Pdf_Style ( );
        $hRowStyle->setFillColor ( new Zend_Pdf_Color_Html ( $this->deploy['colors'] ['hrow'] ) );

        $styleSql = new Zend_Pdf_Style ( );
        $styleSql->setFillColor ( new Zend_Pdf_Color_Html ( $this->deploy['colors'] ['sqlexp'] ) );

        $styleText = new Zend_Pdf_Style ( );
        $styleText->setFillColor ( new Zend_Pdf_Color_Html ( $this->deploy['colors']['text'] ) );

        // Add new page to the document



        if (strtoupper ( $this->deploy ['size'] = 'LETTER' ) && strtoupper ( $this->deploy ['orientation'] ) == 'LANDSCAPE')
        {
            $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE );
        } elseif (strtoupper ( $this->deploy ['size'] = 'LETTER' ) && strtoupper ( $this->deploy ['orientation'] ) != 'LANDSCAPE')
        {
            $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER );
        } elseif (strtoupper ( $this->deploy ['size'] != 'A4' ) && strtoupper ( $this->deploy ['orientation'] ) == 'LANDSCAPE')
        {
            $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_A4_LANDSCAPE );
        } else
        {
            $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_A4 );
        }


        $page->setStyle ( $style );
        $pdf->pages [] = $page;


        $font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_HELVETICA );
        $page->setFont ( $font, 14 );
        //logotipo Federação $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);


        if (file_exists ( $this->deploy ['logo'] ))
        {
            $image = Zend_Pdf_Image::imageWithPath ( $this->deploy ['logo'] );

            list ( $width, $height, $type, $attr ) = getimagesize ( $this->deploy ['logo'] );

            $page->drawImage ( $image, 40, $page->getHeight () - $height - 40, 40 + $width, $page->getHeight () - 40 );
        }

        $page->drawText (  $this->__($this->deploy ['title']), $width + 70, $page->getHeight () - 70, $this->charEncoding );
        $page->setFont ( $font, $cellFontSize );

        $page->drawText ( $this->__($this->deploy ['subtitle']), $width + 70, $page->getHeight () - 80 , $this->charEncoding);

        //Iniciar a contagem de páginas
        $pagina = 1;


        $page->drawText ( $this->deploy ['footer'], 40, 40 , $this->charEncoding);
        if (@$this->deploy ['noPagination'] != 1)
        {
            $page->drawText (  $this->__($this->deploy ['page'] ). ' ' . $pagina . '/' . $totalPaginas, $page->getWidth () - (strlen (  $this->__($this->deploy ['page']) ) * $cellFontSize) - 50, 40, $this->charEncoding );
        }


        $page->setFont ( $font, $cellFontSize );
        $pl = $page->getWidth () - 80;


        $i = 0;

        foreach ( $larg as $final )
        {
            $cell [$i] = round ( $final * $pl / $total_len );
            $i ++;
        }


        $total_celulas = count ( $titulos );
        if (@$this->info ['hRow'] ['title'] != '')
        {
            $total_celulas --;
        }
        $largura = ($page->getWidth () - 80) / $total_celulas;
        $altura = $page->getHeight () - 120;


        $i = 0;
        $page->setFont ( $font, $cellFontSize + 1 );
        foreach ( $titulos as $value )
        {


            if (($value ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
            {

                if (( int ) $la == 0)
                {
                    $largura1 = 40;
                } else
                {
                    $largura1 = $cell [$i - 1] + $largura1;
                }


                $page->setStyle ( $topo );
                $page->drawRectangle ( $largura1, $altura - 4, $largura1 + $cell [$i] + 1, $altura + 12 );
                $page->setStyle ( $styleText );
                $page->drawText ( $value ['value'], $largura1 + 2, $altura , $this->charEncoding);
                $la = $largura1;

                $i ++;
            }

        }
        $page->setFont ( $font, $cellFontSize );


        $page->setStyle ( $style );


        /////////////



        if (is_array ( $grid ))
        {


            /////////////////
            /////////////////
            /////////////////
            if (@$this->info ['hRow'] ['title'] != '')
            {
                $bar = $grid;

                $hbar = trim ( $this->info ['hRow'] ['field'] );

                $p = 0;
                foreach ( $grid [0] as $value )
                {
                    if ($value ['field'] == $hbar)
                    {
                        $hRowIndex = $p;
                    }

                    $p ++;
                }
                $aa = 0;
            }

            //////////////
            //////////////
            //////////////



            $ia = 0;

            $aa = 0;
            foreach ( $grid as $value )
            {

                if ($altura <= 80)
                {
                    // Add new page to the document
                    if (strtoupper ( $this->deploy ['size'] = 'LETTER' ) && strtoupper ( $this->deploy ['orientation'] ) == 'LANDSCAPE')
                    {
                        $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE );
                    } elseif (strtoupper ( $this->deploy ['size'] = 'LETTER' ) && strtoupper ( $this->deploy ['orientation'] ) != 'LANDSCAPE')
                    {
                        $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER );
                    } elseif (strtoupper ( $this->deploy ['size'] != 'A4' ) && strtoupper ( $this->deploy ['orientation'] ) == 'LANDSCAPE')
                    {
                        $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_A4_LANDSCAPE );
                    } else
                    {
                        $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_A4 );
                    }


                    $page->setStyle ( $style );
                    $pdf->pages [] = $page;
                    $pagina ++;

                    $font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_HELVETICA );

                    $page->setFont ( $font, 14 );


                    //logotipo Federação $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    if (file_exists ( $this->deploy ['logo'] ))
                    {
                        $image = Zend_Pdf_Image::imageWithPath ( $this->deploy ['logo'] );
                        list ( $width, $height, $type, $attr ) = getimagesize ( $this->deploy ['logo'] );
                        $page->drawImage ( $image, 40, $page->getHeight () - $height - 40, 40 + $width, $page->getHeight () - 40 );
                    }

                    $page->drawText (  $this->__($this->deploy ['title']), $width + 70, $page->getHeight () - 70, $this->charEncoding );
                    $page->setFont ( $font, $cellFontSize );

                    $page->drawText (  $this->__($this->deploy ['subtitle']), $width + 70, $page->getHeight () - 80, $this->charEncoding );


                    //set font
                    $altura = $page->getHeight () - 120;

                    $page->drawText (  $this->__($this->deploy ['footer']), 40, 40, $this->charEncoding);
                    if ($this->deploy ['noPagination'] != 1)
                    {
                        $page->drawText (  $this->__($this->deploy ['page'] ). ' ' . $pagina . '/' . $totalPaginas, $page->getWidth () - (strlen (  $this->__($this->deploy ['page']) ) * $cellFontSize) - 50, 40 , $this->charEncoding);
                    }


                    //Colocar novamento os títulos em cada página
                    reset ( $titulos );
                    $i = 0;
                    $largura1 = 40;
                    $page->setFont ( $font, $cellFontSize + 1 );
                    foreach ( $titulos as $title )
                    {

                        if (($title ['field'] != $this->info ['hRow'] ['field'] && $this->info ['hRow'] ['title'] != '') || $this->info ['hRow'] ['title'] == '')
                        {

                            if (( int ) $la == 0)
                            {
                                $largura1 = 40;
                            } else
                            {
                                $largura1 = $cell [$i - 1] + $largura1;
                            }

                            $page->setStyle ( $topo );
                            $page->drawRectangle ( $largura1, $altura - 4, $largura1 + $cell [$i] + 1, $altura + 12 );
                            $page->setStyle ( $style );
                            $page->drawText ( $title ['value'], $largura1 + 2, $altura, $this->charEncoding );
                            $la = $largura1;

                            $i ++;
                        }
                    }
                    $page->setFont ( $font, $cellFontSize );
                }


                $la = 0;
                $altura = $altura - 16;
                $i = 0;
                $tdf = $ia % 2 ? $td : $td2;

                $a = 1;


                ////////////
                //A linha horizontal
                if (@$this->info ['hRow'] ['title'] != '')
                {

                    if ($bar [$aa] [$hRowIndex] ['value'] != $bar [$aa - 1] [$hRowIndex] ['value'])
                    {

                        $centrar = $page->getWidth () - 80;
                        $centrar = round ( $centrar / 2 ) + 30;

                        if (( int ) $la == 0)
                        {
                            $largura1 = 40;
                        } else
                        {
                            $largura1 = $cell [$i - 1] + $largura1;
                        }

                        $page->setStyle ( $hRowStyle );
                        $page->drawRectangle ( $largura1, $altura - 4, $page->getWidth () - 40, $altura + 12 );
                        $page->setStyle ( $styleText );
                        $page->drawText ( $bar [$aa] [$hRowIndex] ['value'], $centrar, $altura , $this->charEncoding);
                        $la = 0;
                        $altura = $altura - 16;

                    }
                }

                ////////////

                foreach ( $value as $value1 )
                {

                    $value1 ['value'] = strip_tags ( $value1 ['value'] );


                    if (($value1 ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
                    {


                        if (( int ) $la == 0)
                        {
                            $largura1 = 40;
                        } else
                        {
                            $largura1 = $cell [$i - 1] + $largura1;
                        }

                        $page->setStyle ( $tdf );
                        $page->drawRectangle ( $largura1, $altura - 4, $largura1 + $cell [$i] + 1, $altura + 12 );
                        $page->setStyle ( $styleText );
                        $page->drawText ( $value1 ['value'], $largura1 + 2, $altura , $this->charEncoding);

                        $la = $largura1;
                        $i ++;
                    }

                    $a ++;
                }

                $aa ++;
                $ia ++;
            }
        }


        /////////////



        $la = 0;
        $altura = $altura - 16;
        $i = 0;

        if (is_array ( $sql ))
        {
            foreach ( $sql as $value )
            {

                if (( int ) $la == 0)
                {
                    $largura1 = 40;
                } else
                {
                    $largura1 = $cell [$i - 1] + $largura1;
                }

                $page->setStyle ( $styleSql );
                $page->drawRectangle ( $largura1, $altura - 4, $largura1 + $cell [$i], $altura + 12 );
                $page->setStyle ( $styleText );
                $page->drawText ( $value ['value'], $largura1 + 2, $altura, $this->charEncoding );
                $la = $largura1;

                $la = $largura1;
                $i ++;
            }
        }


        $pdf->save ($this->deploy['dir'] . $this->deploy['name'].  '.pdf' );

        if ($this->deploy['download'] == 1) {
            header ( 'Content-type: application/pdf' );
            header ( 'Content-Disposition: attachment; filename="' . $this->deploy['name']. '.pdf"' );
            readfile ( $this->deploy['dir'] . $this->deploy['name']. '.pdf' );
        }


        if ($this->deploy['save'] != 1) {
            unlink($this->deploy['dir'] . $this->deploy['name'] . '.pdf');
        }

        die ();
    }



    /////////////



    function numberLines($value, $cell)
    {

        $j = 0;
        foreach ( $value as $linhas )
        {
            $linha [$j] = strlen ( strip_tags ( $linhas ['value'] ) );
            $nl = 230 * $cell [$j] / 790;
            $numberLines [] = $linha [$j] / $nl;
            $j ++;
        }

        sort ( $numberLines );

        return ceil ( end ( $numberLines ) );

    }


    /////////////



    function cellLength($cellValue)
    {

        if ($cellValue > 500)
        {
            $value = 175;
        } elseif ($cellValue > 300)
        {
            $value = 165;
        } elseif ($cellValue > 100)
        {
            $value = 160;
        } else
        {
            $value = 150;
        }

        return $value * $cellValue / 553;

    }

}


