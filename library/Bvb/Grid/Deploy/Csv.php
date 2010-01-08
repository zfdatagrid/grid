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



class Bvb_Grid_Deploy_Csv extends Bvb_Grid_DataGrid {

    protected $dir;

    protected $title;

    protected $options = array ();

    protected $output = 'csv';


    /*
    * @param array $data
    */
    function __construct( $dir, $options = array('download')) {

        if (! in_array ( 'csv', $this->export )) {
            echo $this->__ ( "You dont' have permission to export the results to this format" );
            die ();
        }
        
        $this->dir = rtrim ( $dir, "/" ) . "/";
        $this->options = $options;
        
        parent::__construct (  );
    }


    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */
    
    function __set($var, $value) {

        parent::__set ( $var, $value );
    }


    function buildTitltesCsv($titles) {

        $grid = '';
        foreach ( $titles as $title ) {
            
            $grid .= '"' . $title ['value'] . '",';
        }
        
        return substr ( $grid, 0, - 1 ) . "\n";
    
    }


    function buildSqlexpCsv($sql) {

        $grid = '';
        if (is_array ( $sql )) {
            
            foreach ( $sql as $exp ) {
                $grid .= '"' . $exp ['value'] . '",';
            }
        }
        

        return substr ( $grid, 0, - 1 ) . " \n";
    
    }


    function buildGridCsv($grids) {

        $grid = '';
        foreach ( $grids as $value ) {
            
            foreach ( $value as $final ) {
                $grid .= '"' . $final ['value'] . '",';
            }
            
            $grid = substr ( $grid, 0, - 1 ) . " \n";
        }
        
        return $grid;
    
    }


    function deploy() {

        $grid = '';
        $this->setPagination ( 0 );
        
        parent::deploy ();
        
        $grid .= self::buildTitltesCsv ( parent::buildTitles () );
        $grid .= self::buildGridCsv ( parent::buildGrid () );
        $grid .= self::buildSqlexpCsv ( parent::buildSqlExp () );
        
        file_put_contents ( $this->dir . $this->title . ".csv", $grid );
        

        if (in_array ( 'download', $this->options )) {
            header ( 'Content-type: text/plain; charset='.$this->charEncoding );
            header ( 'Content-Disposition: attachment; filename="' . $this->title . '.csv"' );
            readfile ( $this->dir . $this->title . '.csv' );
        }
        

        if (! in_array ( 'save', $this->options )) {
            unlink ( $this->dir . $this->title . '.csv' );
        }
        
        die ();
    
    }

}




