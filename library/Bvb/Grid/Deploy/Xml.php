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



class Bvb_Grid_Deploy_Xml extends Bvb_Grid_DataGrid
{

    protected $dir;

    protected $title;

    protected $options = array ();
    
    protected $output = 'xml';


    /**
     * [EN] The __construct function receives the db adapter. All information related to the
     * [EN] URL is also processed here
     * [EN] To edit, add, or delete records, a user must be authenticated, so we instanciate 
     * [EN] it here. Remember to uses the method write when autenticating a user, so we can know 
     * [EN] if its logged or not
     *
     * @param array $data
     */
     function __construct($db, $title, $dir, $options = array('download'))
    {

      
        if (! in_array ( 'excel', $this->export ))
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


    function buildTitltesXml($titles)
    {
        $grid = '';

        $grid .= "<fields>\n";
        
        foreach ( $titles as $title )
        {
            
            $grid .= "<" . $title ['field'] . "><![CDATA[" . $title ['value'] . "]]></" . $title ['field'] . ">\n";
        
        }
        
        $grid .= "</fields>\n";
        
        return $grid;
    
    }


    function buildSqlexpXml($sql)
    {

        if (is_array ( $sql ))
        {
            $grid .= "<sqlexp>\n";
            

            foreach ( $sql as $exp )
            {
                $grid .= "<" . $exp ['field'] . "><![CDATA[" . $exp ['value'] . "]]></" . $exp ['field'] . ">\n";
                ;
            }
            
            $grid .= "</sqlexp>\n";
        }
        
        return $grid;
    
    }


    function buildGridXml($grids)
    {

        $grid .= "<results>\n";
        

        foreach ( $grids as $value )
        {
            $i ++;
            $grid .= "<row>\n";
            foreach ( $value as $final )
            {
                
                #$final['value']  = strip_tags($final['value']);
                

                $grid .= "<" . $final ['field'] . "><![CDATA[" . $final ['value'] . "]]></" . $final ['field'] . ">\n";
            }
            $grid .= "</row>\n";
        }
        
        $grid .= "</results>\n";
        

        return $grid;
    
    }


    function deploy()
    {

        $this->setPagination ( 0 );
        parent::deploy ();
        
       
        $grid .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $grid .= "<grid>\n";
        
        $grid .= self::buildTitltesXml ( parent::buildTitles () );
        $grid .= self::buildGridXml ( parent::buildGrid () );
        $grid .= self::buildSqlexpXml ( parent::buildSqlExp () );
        
        $grid .= "</grid>";
        
        file_put_contents ( $this->dir . $this->title . ".xml", $grid );
        
        
        if (in_array ( 'download', $this->options ))
        {
            header ( "Content-type: application/xml" );
            header ( 'Content-Disposition: attachment; filename="' . $this->title . '.xml"' );
            readfile ( $this->dir . $this->title . '.xml' );
        }
        

        if (! in_array ( 'save', $this->options ))
        {
            unlink ( $this->dir . $this->title . '.xml' );
        }
        
        die ();
    }

}


