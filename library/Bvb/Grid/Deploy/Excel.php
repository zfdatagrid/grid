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

class Bvb_Grid_Deploy_Excel extends Bvb_Grid_DataGrid
{


    protected  $output = 'excel';
    public $title;

    public $dir ;

    function __construct ($db,$title,$dir)
    {

        if(!in_array('excel',$this->export))
        {
            die('Sem permissões de exportação da grelha');
        }

        $this->dir = rtrim($dir,"/")."/";

        $this->title = $title;
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


    function deploy()
    {
        $this->data['pagination'][ 'per_page' ] = 10000000;

        parent::deploy();

        $titles = parent::buildTitles();
        $wsData = parent::buildGrid();
        $sql = parent::buildSqlExp();


        $nome = reset($titles);

        if($nome['field']=='id' || strpos($nome['field'],'_id')  || strpos($nome['field'],'id_')  || strpos($nome['field'],'.id')  )
        {
            @array_shift($titles);
            @array_shift($sql);
            $remove = true;
        }
        $xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'.'>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';




        $xml .= '<Worksheet ss:Name="Barcelos" ss:Description="Sempre contigo"><ss:Table>';

        $xml .= '<ss:Row>';
        foreach ($titles as $value) {

            $type = !is_numeric($value['value'])?'String':'Number';

            $xml .= '<ss:Cell><Data ss:Type="'.$type.'">'.$value['value'].'</Data></ss:Cell>';
        }
        $xml .= '</ss:Row>';


        if(is_array($wsData))
        {
            foreach ($wsData as $row) {

                $xml .= '<ss:Row>';
                $a=1;
                foreach ($row as $value) {

                    $value['value']  = strip_tags($value['value']);
                    
                    
                    if(@$remove===true && $a==1)
                    {

                    } else{

                        $type = !is_numeric($value['value'])?'String':'Number';
                        $xml .= '<ss:Cell><Data ss:Type="'.$type.'">'.$value['value'].'</Data></ss:Cell>';
                    }
                    $a++;
                }
                $xml .= '</ss:Row>';
            }

        }


        if(is_array($sql))
        {
            $xml .= '<ss:Row>';
            foreach ($sql as $value) {

                $type = !is_numeric($value['value'])?'String':'Number';

                $xml .= '<ss:Cell><Data ss:Type="'.$type.'">'.$value['value'].'</Data></ss:Cell>';
            }
            $xml .= '</ss:Row>';
        }


        $xml .= '</ss:Table></Worksheet>';


        $xml .= '</Workbook>';


        
        if(file_exists($this->dir.$this->title.'.xls'))
        {
            $data = date('d-m-Y H\:i\:s');
            rename($this->dir.$this->title.'.xls',$this->dir.$this->title.'-'.$data.'.xls');
        }

        file_put_contents($this->dir.$this->title.".xls",$xml);

        header('Content-type: application/excel');

        // It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="'.$this->title.'.xls"');
        readfile($this->dir.$this->title.'.xls');
        unlink($this->dir.$this->title.'.xls');
        die();
    }

}




