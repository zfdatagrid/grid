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
 * @version    0.4   $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */



class Bvb_Grid_Deploy_Print extends Bvb_Grid_DataGrid
{
    public $title;

    protected  $output = 'print';

    public $templateInfo;


    function __construct ($title)
    {

        if (! in_array ( 'print', $this->export ))
        {
            echo $this->__( "You dont' have permission to export the results to this format" );
            die();
        }

        $this->title = $title;


        $this->_setRemoveHiddenFields(true);
        parent::__construct();

        $this->addTemplateDir ( 'Bvb/Grid/Template/Print', 'Bvb_Grid_Template_Print', 'print' );
        if(!is_object($this->temp['print']))
        {
            $this->setTemplate('print','print',array('title'=>$title,'charEncoding'=>$this->charEncoding));
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
         $this->setPagination ( 0 );

        parent::deploy();


        if(!$this->temp['print'] instanceof Bvb_Grid_Template_Print_Print   )
        {
            $this->setTemplate('print','print');
        }


        $titles = parent::_buildTitles();

        #$nome = reset($titles);
        $wsData = parent::_buildGrid();
        $sql = parent::_buildSqlExp();



        $xml = $this->temp['print']->globalStart();
        $xml .= $this->temp['print']->header();



        //[PT] Títulos

        $xml .= $this->temp['print']->titlesStart();

        foreach ($titles as $value) {

            if((@$value['field']!=@$this->info['hRow']['field'] && @$this->info['hRow']['title'] !='') || @$this->info['hRow']['title'] =='')
            {
                $xml .= str_replace("{{value}}",$value['value'],$this->temp['print']->titlesLoop());

            }
        }


        $xml .= $this->temp['print']->titlesEnd();


        //[PT] O Loop

        if(is_array($wsData))
        {



            /////////////////
            /////////////////
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
            $aa=0;
            foreach ($wsData as $row) {


                ////////////
                ////////////
                //A linha horizontal
                if(@$this->info['hRow']['title']!='')
                {

                    if(@$bar[$aa][$hRowIndex]['value'] != @$bar[$aa-1][$hRowIndex]['value'])
                    {
                        $xml .= str_replace("{{value}}",@$bar[$aa][$hRowIndex]['value'] ,$this->temp['print']->hRow());
                    }
                }

                ////////////
                ////////////


                $i++;



                $xml .= $this->temp['print']->loopStart();
                $a=1;
                foreach ($row as $value) {

                    $value['value']  = strip_tags($value['value']);

                    if((@$value['field']!=@$this->info['hRow']['field'] && @$this->info['hRow']['title'] !='')
                    || @$this->info['hRow']['title'] =='')
                    {


                        $xml .= str_replace("{{value}}",$value['value'],$this->temp['print']->loopLoop());
                    }
                }

                $xml .= $this->temp['print']->loopEnd();
                $aa++;
                $i++;
            }
        }



        //////////////////SQL EXPRESSIONS

        if(is_array($sql))
        {
            $xml .= $this->temp['print']->sqlExpStart ();
            foreach ($sql as $value) {
                $xml .= str_replace("{{value}}",$value['value'],$this->temp['print']->sqlExpLoop());
            }
            $xml .= $this->temp['print']->sqlExpEnd();
        }

        $xml .= $this->temp['print']->globalEnd ();

        echo $xml;
        die();
    }

}




