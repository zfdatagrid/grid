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
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */

class Bvb_Grid_Deploy_Print extends Bvb_Grid_DataGrid
{
    public $title;


    /**
     * [PT] o template
     *
     * @var object
     */
    public $temp ;

    protected  $output = 'print';


    function __construct ($db,$title)
    {
        if(!in_array('print',$this->export))
        {
            die('Sem permissões de exportação da grelha');
        }

        $this->title = $title;
        parent::__construct($db);


        $this->addTemplateDir('Bvb/Grid/Template/Print','Bvb_Grid_Template_Print','print');
        $this->setTemplate('print','print');

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

        $nome = reset($titles);
        $wsData = parent::buildGrid();
        $sql = parent::buildSqlExp();

        if($nome['field']=='id' || strpos($nome['field'],'_id')  || strpos($nome['field'],'id_') || strpos($nome['field'],'.id')  )
        {
            @array_shift($titles);
            @array_shift($sql);

            $remove = true;
        }


        $xml .= $this->temp->globalStart();
        $xml .= $this->temp->header();



        //[PT] Títulos

        $xml .= $this->temp->titlesStart();

        foreach ($titles as $value) {

            if(($value['field']!=$this->info['hRow']['field'] && $this->info['hRow']['title'] !='') || $this->info['hRow']['title'] =='')
            {
                $xml .= str_replace("{{value}}",$value['value'],$this->temp->titlesLoop());

            }
        }
        $xml .= $this->temp->titlesEnd();


        //[PT] O Loop

        if(is_array($wsData))
        {

            if($this->info['hRow']['title']!='')
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



            $i=1;
            foreach ($wsData as $row) {

                //A linha horizontal
                if($this->info['hRow']['title']!='')
                {

                    if($bar[$aa][$hRowIndex]['value'] != $bar[$aa-1][$hRowIndex]['value'])
                    {
                        $i++;

                        $xml .= str_replace("{{value}}",$bar[$aa][$hRowIndex]['value'] ,$this->temp->hRowLoop());
                    }
                }

                $i++;



                $xml .= $this->temp->loopStart();
                $a=1;
                foreach ($row as $value) {

                    $value['value']  = strip_tags($value['value']);

                    if(($value['field']!=$this->info['hRow']['field'] && $this->info['hRow']['title'] !='')
                    || $this->info['hRow']['title'] =='')
                    {

                        if($remove===true && $a==1)
                        {

                        } else{
                            $xml .= str_replace("{{value}}",$value['value'],$this->temp->loopLoop());
                        }
                    }
                }

                $xml .= $this->temp->loopEnd();
                $aa++;
                $i++;
            }
        }


        if(is_array($sql))
        {
            $xml .= $this->temp->sqlExpStart ();
            foreach ($sql as $value) {
                $xml .= str_replace("{{value}}",$value['value'],$this->temp->sqlExpLoop());
            }
            $xml .= $this->temp->sqlExpEnd();
        }

        $xml .= $this->temp->globalEnd ();

        echo $xml;
        die();
    }



    /**
     * [PT]Definir o template para a grid
     * [PT] por defeito ele tenta bvb/grid/template/table/table
     *
     * @param unknown_type $template
     * @return unknown
   

    function setTemplate($template)
    {
        $temp = array_reverse($this->_templates['print']);


        foreach ($temp  as $find) {

            $file = $find['dir'].ucfirst($template).'.php';
            $class = $find['prefix'].'_'.ucfirst($template);

            require_once($file);

            if(class_exists($class))
            {
                $this->temp = new $class();
            }

            return true;

        }

        throw new Exception('No templates found');

    }
*/
}




