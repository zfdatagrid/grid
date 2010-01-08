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
 * @package    Bvb_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.4  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */



class Bvb_Grid_Deploy_Word extends Bvb_Grid_DataGrid
{

    public $title;

    public $dir;
    
    public $templateInfo;

    protected $options = array ();

    
    protected $output = 'word';


    function __construct( $title, $dir, $options = array('download'))
    {

        if (! in_array ( 'word', $this->export ))
        {
            echo $this->__ ( "You dont' have permission to export the results to this format" );
            die ();
        }
        
        $this->dir = rtrim ( $dir, "/" ) . "/";
        $this->title = $title;
        $this->options = $options;
        

        parent::__construct (  );
        

        if (! is_object ( $this->temp ['word'] ))
        {
            $this->setTemplate ( 'word', 'word', array ('title' => $title ) );
        }
    
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


    function deploy()
    {

        $this->setPagination ( 0 );
        
        parent::deploy ();
        

        $titles = parent::buildTitles ();
        
        #$nome = reset($titles);
        $wsData = parent::buildGrid ();
        $sql = parent::buildSqlExp ();
        
        /*
        if($nome['field']=='id' || strpos($nome['field'],'_id')  || strpos($nome['field'],'id_') || strpos($nome['field'],'.id')  )
        {
        @array_shift($titles);
        @array_shift($sql);

        $remove = true;
        }
        */
        
        $xml = $this->temp ['word']->globalStart ();
        
        $xml .= $this->temp ['word']->titlesStart ();
        
        foreach ( $titles as $value )
        {
            if (($value ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
            {
                $xml .= str_replace ( "{{value}}", $value ['value'], $this->temp ['word']->titlesLoop () );
            }
        }
        $xml .= $this->temp ['word']->titlesEnd ();
        

        if (is_array ( $wsData ))
        {
            /////////////////
            if (@$this->info ['hRow'] ['title'] != '')
            {
                $bar = $wsData;
                
                $hbar = trim ( $this->info ['hRow'] ['field'] );
                
                $p = 0;
                foreach ( $wsData [0] as $value )
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
            


            $i = 1;
            $aa = 0;
            foreach ( $wsData as $row )
            {
                

                ////////////
                ////////////
                //A linha horizontal
                if (@$this->info ['hRow'] ['title'] != '')
                {
                    
                    if (@$bar [$aa] [$hRowIndex] ['value'] != @$bar [$aa - 1] [$hRowIndex] ['value'])
                    {
                        $xml .= str_replace ( "{{value}}", @$bar [$aa] [$hRowIndex] ['value'], $this->temp ['word']->hRow () );
                    }
                }
                
                ////////////
                ////////////
                


                $xml .= $this->temp ['word']->loopStart ();
                $a = 1;
                foreach ( $row as $value )
                {
                    
                    $value ['value'] = strip_tags ( $value ['value'] );
                    
                    if ((@$value ['field'] != @$this->info ['hRow'] ['field'] && @$this->info ['hRow'] ['title'] != '') || @$this->info ['hRow'] ['title'] == '')
                    {
                        
                        $xml .= str_replace ( "{{value}}", $value ['value'], $this->temp ['word']->loopLoop ( 2 ) );
                    }
                    $a ++;
                
                }
                $xml .= $this->temp ['word']->loopEnd ();
                $aa ++;
                $i ++;
            }
        }
        

        if (is_array ( $sql ))
        {
            $xml .= $this->temp ['word']->sqlExpStart ();
            foreach ( $sql as $value )
            {
                
                $xml .= str_replace ( "{{value}}", $value ['value'], $this->temp ['word']->sqlExpLoop () );
            }
            $xml .= $this->temp ['word']->sqlExpEnd ();
        }
        

        $xml .= $this->temp ['word']->globalEnd ();
        

        if (file_exists ( $this->dir . $this->title . '.doc' ))
        {
            $data = date ( 'd-m-Y H\:i\:s' );
            rename ( $this->dir . $this->title . '.doc', $this->dir . $this->title . '-' . $data . '.doc' );
        }
        

        file_put_contents ( $this->dir . $this->title . ".doc", $xml );
        

        if (in_array ( 'download', $this->options ))
        {
            header ( 'Content-type: application/word' );
            header ( 'Content-Disposition: attachment; filename="' . $this->title . '.doc"' );
            readfile ( $this->dir . $this->title . '.doc' );
        }
        

        if (! in_array ( 'save', $this->options ))
        {
            unlink ( $this->dir . $this->title . '.doc' );
        }
        

        die ();
    }

}




