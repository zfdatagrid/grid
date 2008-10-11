<?php
/**
 * Mascker
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License 2.0
 * It is  available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/gpl-2.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */

class Bvb_Grid_Deploy_Csv extends Bvb_Grid_DataGrid
{


    protected  $output = 'csv';

    /*
    * @param array $data
    */
    function __construct ($db)
    {
        parent::__construct($db);

    }

    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */

    function __set($var,$value)
    {
        parent::__set($var,$value);
    }

    function buildTitltesCsv($titles)
    {

        foreach ($titles as $title) {

            $grid .=  $title['value'] . ",";
        }

        return $grid."\n";

    }

    function buildSqlexpCsv($sql)
    {
        if(is_array($sql))
        {

            foreach ($sql as $exp) {
                $grid .= $exp['value'] .",";
            }
        }


        return $grid."\n";

    }


    function buildGridCsv($grids)
    {


        foreach ($grids as $value) {
            
            $totalFields = count($value);
            
            $i=2;
            foreach ($value as $final) {

                $final['value']  = strip_tags($final['value']);

                /*
                if(strpos ( ",",  $final['value']) || strpos ( '""',  $final['value'] ) || strpos ( '\n',  $final['value'] ))
                {
                $grid .= '"'.$final['value'].'"';
                }else{
                $grid .= $final['value'];
                }

                */
                
                $grid .= '"'.$final['value'].'",';

            }

            $grid .= $grid ."\n";
        }
        return $grid."\n";

    }


    function deploy()
    {

        $this->setPagination(100);
        
        parent::deploy();

        $grid .= self::buildTitltesCsv(parent::buildTitles());
        $grid .= self::buildGridCsv(parent::buildGrid());
        $grid .= self::buildSqlexpCsv(parent::buildSqlExp());
        echo $grid;
        die();
    }

}




