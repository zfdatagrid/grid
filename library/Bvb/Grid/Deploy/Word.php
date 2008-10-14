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


class Bvb_Grid_Deploy_Word extends Bvb_Grid_DataGrid
{
    public $title;

    public $dir ;


    protected  $output = 'word';


    function __construct ($db,$title,$dir)
    {
        if(!in_array('word',$this->export))
        {
            die('Sem permissões de exportação da grelha');
        }


        $this->title = $title;

        $this->dir = rtrim($dir,"/")."/";
        parent::__construct($db);


        if(!is_object($this->temp['word']))
        {
            $this->setTemplate('word','word',array('title'=>$title));
        }

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
        $this->dsetPagination(10000000);

        parent::deploy();


        $titles = parent::buildTitles();

        $nome = reset($titles);
        $wsData = parent::buildGrid();
        $sql = parent::buildSqlExp();

        /*
        if($nome['field']=='id' || strpos($nome['field'],'_id')  || strpos($nome['field'],'id_') || strpos($nome['field'],'.id')  )
        {
        @array_shift($titles);
        @array_shift($sql);

        $remove = true;
        }
        */
        
        $xml = $this->temp['word']->globalStart();

        $xml .= $this->temp['word']->titlesStart();

        foreach ($titles as $value) {
            if(($value['field']!=@$this->info['hRow']['field'] && @$this->info['hRow']['title'] !='') || @$this->info['hRow']['title'] =='')
            {
                $xml .= str_replace("{{value}}",$value['value'],$this->temp['word']->titlesLoop());
            }
        }
        $xml .= $this->temp['word']->titlesEnd();



        if(is_array($wsData))
        {
            /////////////////
            if(@$this->info['hRow']['title']!='')
            {
                $bar = $wsData;

                $hbar = trim($this->info['hRow']['field']);

                $p=0;
                foreach ($wsData[0] as $value)
                {
                    if($value['field'] == $hbar)
                    {
                        $hRowIndex = $p;
                    }

                    $p++;
                }
                $aa = 0;
            }

            //////////////
            //////////////
            //////////////


            $i=1;
            $aa = 0;
            foreach ($wsData as $row) {


                ////////////
                ////////////
                //A linha horizontal
                if(@$this->info['hRow']['title']!='')
                {

                    if(@$bar[$aa][$hRowIndex]['value'] != @$bar[$aa-1][$hRowIndex]['value'])
                    {
                        $xml .= str_replace("{{value}}",@$bar[$aa][$hRowIndex]['value'] ,$this->temp['word']->hRow());
                    }
                }

                ////////////
                ////////////


                $xml .= $this->temp['word']->loopStart();
                $a=1;
                foreach ($row as $value) {

                    $value['value']  = strip_tags($value['value']);

                    if((@$value['field']!=@$this->info['hRow']['field'] && @$this->info['hRow']['title'] !='') || @$this->info['hRow']['title'] =='')
                    {

                        $xml .= str_replace("{{value}}",$value['value'],$this->temp['word']->loopLoop(2));
                    }
                    $a++;

                }
                $xml .= $this->temp['word']->loopEnd();
                $aa++;
                $i++;
            }
        }


        if(is_array($sql))
        {
            $xml .= $this->temp['word']->sqlExpStart ();
            foreach ($sql as $value) {

                $xml .= str_replace("{{value}}",$value['value'],$this->temp['word']->sqlExpLoop());
            }
            $xml .= $this->temp['word']->sqlExpEnd();
        }


        $xml .= $this->temp['word']->globalEnd ();




        if(file_exists($this->dir.$this->title.'.doc'))
        {
            $data = date('d-m-Y H\:i\:s');
            rename($this->dir.$this->title.'.doc',$this->dir.$this->title.'-'.$data.'.doc');
        }

        file_put_contents($this->dir.$this->title.".doc",$xml);

        header('Content-type: application/word');

        // It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="'.$this->title.'.doc"');
        readfile($this->dir.$this->title.'.doc');
        unlink($this->dir.$this->title.'.doc');
        die();
    }

}




