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

class Bvb_Grid_Deploy_Xml extends Bvb_Grid_DataGrid
{

    protected  $output = 'xml';
    /**
     * [EN] The __construct function receives the db adapter. All information related to the
     * [EN] URL is also processed here
     * [EN] To edit, add, or delete records, a user must be authenticated, so we instanciate 
     * [EN] it here. Remember to uses the method write when autenticating a user, so we can know 
     * [EN] if its logged or not
     *
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

    function buildTitltesXml($titles)
    {

        $grid .= "<fields>\n";

        foreach ($titles as $title) {

            $grid .= "<".$title['field']."><![CDATA[" . $title['value'] . "]]></".$title['field'].">\n";

        }

        $grid .= "</fields>\n";

        return $grid;

    }

    function buildSqlexpXml($sql)
    {
        if(is_array($sql))
        {
        $grid .="<sqlexp>\n";

       
            foreach ($sql as $exp) {
                $grid .="<".$exp['field']."><![CDATA[". $exp['value'] ."]]></".$exp['field'].">\n";;
            }

        $grid .="</sqlexp>\n";
        }

        return $grid;

    }


    function buildGridXml($grids)
    {
        $grid .="<results>\n";


        foreach ($grids as $value) {
            $i++;
            $grid .="<row>\n";
            foreach ($value as $final) {

                #$final['value']  = strip_tags($final['value']);

                $grid .="<".$final['field']."><![CDATA[" . $final['value'] . "]]></".$final['field'].">\n";
            }
            $grid .="</row>\n";
        }

        $grid .="</results>\n";


        return $grid;

    }


    function deploy()
    {
         
        $this->setPagination(10000000000);
        parent::deploy();

        header("Content-type: application/xml");
        $grid .='<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $grid .="<grid>\n";

        $grid .= self::buildTitltesXml(parent::buildTitles());
        $grid .= self::buildGridXml(parent::buildGrid());
        $grid .= self::buildSqlexpXml(parent::buildSqlExp());

        $grid .="</grid>";

        echo $grid;
        die();
    }

}


