<?php

/**
 * Mascker
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
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


class Bvb_Grid_Deploy_Pdf extends Bvb_Grid_DataGrid
{

    protected $output = 'pdf';

    public $pdfInfo = array ();

    public $dir;

    protected $style;

    protected $options = array ();

    public $title;


    function __construct($db, $title, $dir, $options = array('download'))
    {

        if (! in_array ( 'pdf', $this->export ))
        {
            echo $this->__ ( "You dont' have permission to export the results to this format" );
            die ();
        }
        

        $this->dir = rtrim ( $dir, "/" ) . "/";
        $this->title = $title;
        $this->options = $options;
        
        parent::__construct ( $db );
    

    }


    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */
    
    function __set($var, $value)
    {

        parent::__set ( $var, $value );
    }


    function calculateCellSize()
    {

        $titles = parent::buildTitles ();
        $sqlexp = parent::buildSqlExp ();
        $grid = parent::buildGrid ();
        

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

        $this->setPagination ( 0 );
        parent::deploy ();
        
        $la = '';
        
        if (! $this->temp ['pdf'] instanceof Bvb_Grid_Template_Pdf_Pdf)
        {
            $this->setTemplate ( 'pdf', 'pdf' );
        }
        

        $this->pdfInfo = $this->temp ['pdf']->info ();
        $this->style = $this->temp ['pdf']->style ();
        

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
        


        $titulos = parent::buildTitles ();
        $sql = parent::buildSqlExp ();
        $grid = parent::BuildGrid ();
        

        if (strtoupper ( $this->pdfInfo ['orientation'] ) == 'LANDSCAPE' && strtoupper ( $this->pdfInfo ['size'] ) == 'A4')
        {
            $totalPaginas = ceil ( count ( $grid ) / 26 );
        
        } elseif (strtoupper ( $this->pdfInfo ['orientation'] ) == 'LANDSCAPE' && strtoupper ( $this->pdfInfo ['size'] ) == 'LETTER')
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
        $style->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['lines'] ) );
        
        $topo = new Zend_Pdf_Style ( );
        $topo->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['header'] ) );
        
        $td = new Zend_Pdf_Style ( );
        $td->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['row2'] ) );
        
        $td2 = new Zend_Pdf_Style ( );
        $td2->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['row1'] ) );
        
        $hRowStyle = new Zend_Pdf_Style ( );
        $hRowStyle->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['hrow'] ) );
        
        $styleSql = new Zend_Pdf_Style ( );
        $styleSql->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['sqlexp'] ) );
        
        $styleText = new Zend_Pdf_Style ( );
        $styleText->setFillColor ( new Zend_Pdf_Color_Html ( $this->style ['text'] ) );
        
        // Add new page to the document
        


        if (strtoupper ( $this->pdfInfo ['size'] = 'LETTER' ) && strtoupper ( $this->pdfInfo ['orientation'] ) == 'LANDSCAPE')
        {
            $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE );
        } elseif (strtoupper ( $this->pdfInfo ['size'] = 'LETTER' ) && strtoupper ( $this->pdfInfo ['orientation'] ) != 'LANDSCAPE')
        {
            $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER );
        } elseif (strtoupper ( $this->pdfInfo ['size'] != 'A4' ) && strtoupper ( $this->pdfInfo ['orientation'] ) == 'LANDSCAPE')
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
        


        if (file_exists ( $this->pdfInfo ['logo'] ))
        {
            $image = Zend_Pdf_Image::imageWithPath ( $this->pdfInfo ['logo'] );
            
            list ( $width, $height, $type, $attr ) = getimagesize ( $this->pdfInfo ['logo'] );
            
            $page->drawImage ( $image, 40, $page->getHeight () - $height - 40, 40 + $width, $page->getHeight () - 40 );
        }
        
        $page->drawText ( $this->pdfInfo ['title'], $width + 70, $page->getHeight () - 70 );
        $page->setFont ( $font, $cellFontSize );
        
        $page->drawText ( $this->pdfInfo ['subtitle'], $width + 70, $page->getHeight () - 80 );
        
        //Iniciar a contagem de páginas
        $pagina = 1;
        

        $page->drawText ( $this->pdfInfo ['footer'], 40, 40 );
        if (@$this->pdfInfo ['noPagination'] != 1)
        {
            $page->drawText ( $this->pdfInfo ['page'] . ' ' . $pagina . '/' . $totalPaginas, $page->getWidth () - (strlen ( $this->pdfInfo ['page'] ) * $cellFontSize) - 50, 40 );
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
        $altura = $page->getHeight () - 120;
        

        $i = 0;
        $page->setFont ( $font, $cellFontSize + 1 );
        foreach ( $titulos as $value )
        {
            if (($value ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
            {
                if (( int ) $la == 0)
                {
                    $largura = 40;
                } else
                {
                    $largura = $cell [$i - 1] + $largura;
                }
                

                $page->setStyle ( $topo );
                $page->drawRectangle ( $largura, $altura - 4, $largura + $cell [$i] + 1, $altura + 12 );
                $page->setStyle ( $styleText );
                $page->drawText ( $value ['value'], $largura + 2, $altura );
                $la = $largura;
                
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
                    if (strtoupper ( $this->pdfInfo ['size'] = 'LETTER' ) && strtoupper ( $this->pdfInfo ['orientation'] ) == 'LANDSCAPE')
                    {
                        $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE );
                    } elseif (strtoupper ( $this->pdfInfo ['size'] = 'LETTER' ) && strtoupper ( $this->pdfInfo ['orientation'] ) != 'LANDSCAPE')
                    {
                        $page = $pdf->newPage ( Zend_Pdf_Page::SIZE_LETTER );
                    } elseif (strtoupper ( $this->pdfInfo ['size'] != 'A4' ) && strtoupper ( $this->pdfInfo ['orientation'] ) == 'LANDSCAPE')
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
                    if (file_exists ( $this->pdfInfo ['logo'] ))
                    {
                        $image = Zend_Pdf_Image::imageWithPath ( $this->pdfInfo ['logo'] );
                        list ( $width, $height, $type, $attr ) = getimagesize ( $this->pdfInfo ['logo'] );
                        $page->drawImage ( $image, 40, $page->getHeight () - $height - 40, 40 + $width, $page->getHeight () - 40 );
                    }
                    
                    $page->drawText ( $this->pdfInfo ['title'], $width + 70, $page->getHeight () - 70 );
                    $page->setFont ( $font, $cellFontSize );
                    
                    $page->drawText ( $this->pdfInfo ['subtitle'], $width + 70, $page->getHeight () - 80 );
                    

                    //set font
                    $altura = $page->getHeight () - 120;
                    
                    $page->drawText ( $this->pdfInfo ['footer'], 40, 40 );
                    if (@$this->pdfInfo ['noPagination'] != 1)
                    {
                        $page->drawText ( $this->pdfInfo ['page'] . ' ' . $pagina . '/' . $totalPaginas, $page->getWidth () - (strlen ( $this->pdfInfo ['page'] ) * $cellFontSize) - 50, 40 );
                    }
                    

                    //Colocar novamento os títulos em cada página
                    reset ( $titulos );
                    $i = 0;
                    $largura = 40;
                    $page->setFont ( $font, $cellFontSize + 1 );
                    foreach ( $titulos as $title )
                    {
                        
                        if (($title ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
                        {
                            
                            if (( int ) $la == 0)
                            {
                                $largura = 40;
                            } else
                            {
                                @$largura = $cell [$i - 1] + $largura;
                            }
                            
                            $page->setStyle ( $topo );
                            $page->drawRectangle ( $largura, $altura - 4, $largura + $cell [$i] + 1, $altura + 12 );
                            $page->setStyle ( $style );
                            $page->drawText ( $title ['value'], $largura + 2, $altura );
                            $la = $largura;
                            
                            $i ++;
                        }
                    }
                    $page->setFont ( $font, $cellFontSize );
                }
                

                $la = 0;
                $altura -= 16;
                $i = 0;
                $tdf = $ia % 2 ? $td : $td2;
                
                $a = 1;
                
                ////////////
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
                            $largura = 40;
                        } else
                        {
                            $largura = $cell [$i - 1] + $largura;
                        }
                        
                        $page->setStyle ( $hRowStyle );
                        $page->drawRectangle ( $largura, $altura - 4, $page->getWidth () - 40, $altura + 12 );
                        $page->setStyle ( $styleText );
                        $page->drawText ( $bar [$aa] [$hRowIndex] ['value'], $centrar, $altura );
                        $la = 0;
                        $altura -= 16;
                    
                    }
                }
                
                ////////////
                ////////////
                

                $numberLines = $this->numberLines ( $value, $cell );
                
                foreach ( $value as $value1 )
                {
                    
                    $value1 ['value'] = strip_tags ( $value1 ['value'] );
                    
                    
                    if (($value1 ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
                    {
                        $largura = ( int ) $la == 0 ? 40 : $largura = $cell [$i - 1] + $largura;
                        
                        $firstHeight = $altura - $numberLines * 10;
                        

                        $page->setStyle ( $tdf );
                        $page->drawRectangle ( $largura, $firstHeight - 4, $largura + $cell [$i] + 1, $altura + 12 );
                        $page->setStyle ( $styleText );
                        
                        for($j = 0; $j < $numberLines; $j ++)
                        {
                            $page->drawText ( substr ( $value1 ['value'], $j * $this->cellLength ( $cell [$i] ), $this->cellLength ( $cell [$i] ) ), $largura + 2, $altura - $j * 11 );
                        }
                        

                        $la = $largura;
                        $i ++;
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
        
        if (is_array ( $sql ))
        {
            foreach ( $sql as $value )
            {
                
                if (( int ) $la == 0)
                {
                    $largura = 40;
                } else
                {
                    $largura = $cell [$i - 1] + $largura;
                }
                
                $page->setStyle ( $styleSql );
                $page->drawRectangle ( $largura, $altura - 4, $largura + $cell [$i], $altura + 12 );
                $page->setStyle ( $styleText );
                $page->drawText ( $value ['value'], $largura + 2, $altura );
                $la = $largura;
                
                $la = $largura;
                $i ++;
            }
        }
        

        $pdf->save ( $this->dir . $this->title . '.pdf' );
        
        if (in_array ( 'download', $this->options ))
        {
            header ( 'Content-type: application/pdf' );
            header ( 'Content-Disposition: attachment; filename="' . $this->title . '.pdf"' );
            readfile ( $this->dir . $this->title . '.pdf' );
        }
        
        if (! in_array ( 'save', $this->options ))
        {
            unlink ( $this->dir . $this->title . '.pdf' );
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


