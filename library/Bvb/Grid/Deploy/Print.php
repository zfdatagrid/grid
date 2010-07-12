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
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Deploy_Print extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{


    public $templateInfo;


    public function __construct ($options)
    {

        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->addTemplateDir('Bvb/Grid/Template/Print', 'Bvb_Grid_Template_Print', 'print');

    }


    public function deploy ()
    {
        if ( ! in_array($this->_deployName, $this->_export) && !array_key_exists($this->_deployName,$this->_export) ) {
            throw new Bvb_Grid_Exception($this->__("You dont' have permission to export the results to this format"));
        }
        $this->setNumberRecordsPerPage(0);

        parent::deploy();


        if ( ! $this->_temp['print'] instanceof Bvb_Grid_Template_Print_Print ) {
            $this->setTemplate('print', 'print');
        }

        $titles = parent::_buildTitles();
        $wsData = parent::_buildGrid();
        $sql = parent::_buildSqlExp();

        if ( ! isset($this->deploy['title']) ) {
            $this->deploy['title'] = '';
        }

        $print = $this->_temp['print']->globalStart();
        $print .= $this->_temp['print']->header();


        //[PT] Títulos
        $print .= $this->_temp['print']->titlesStart();

        foreach ( $titles as $value ) {

            if ( (isset($value['field']) && $value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                $print .= str_replace("{{value}}", $value['value'], $this->_temp['print']->titlesLoop());

            }
        }

        $print .= $this->_temp['print']->titlesEnd();

        //Loop
        if ( is_array($wsData) ) {
            /////////////////
            if ( $this->getInfo('hRow,title') != '' ) {
                $bar = $wsData;

                $hbar = trim($this->getInfo('hRow,field'));

                $p = 0;
                foreach ( $wsData[0] as $value ) {
                    if ( $value['field'] == $hbar ) {
                        $hRowIndex = $p;
                    }

                    $p ++;
                }
                $aa = 0;
            }
            //////////////
            //////////////



            $i = 1;
            $aa = 0;
            foreach ( $wsData as $row ) {
                ////////////
                //horizontal row
                if ( $this->getInfo('hRow,title') != '' ) {
                    if ( ! isset($bar[$aa - 1][$hRowIndex]) ) {
                        $bar[$aa - 1][$hRowIndex]['value'] = '';
                    }

                    if ( $bar[$aa][$hRowIndex]['value'] != $bar[$aa - 1][$hRowIndex]['value'] ) {
                        $print .= str_replace("{{value}}", $bar[$aa][$hRowIndex]['value'], $this->_temp['print']->hRow());
                    }
                }
                ////////////
                $i ++;

                $print .= $this->_temp['print']->loopStart();
                $a = 1;
                foreach ( $row as $value ) {

                    $value['value'] = strip_tags($value['value']);

                    if ( (isset($value['field']) && $value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                        $print .= str_replace("{{value}}", $value['value'], $this->_temp['print']->loopLoop());
                    }
                }

                $print .= $this->_temp['print']->loopEnd();
                $aa ++;
                $i ++;
            }
        }

        //////////////////SQL EXPRESSIONS
        if ( is_array($sql) ) {
            $print .= $this->_temp['print']->sqlExpStart();
            foreach ( $sql as $value ) {
                $print .= str_replace("{{value}}", $value['value'], $this->_temp['print']->sqlExpLoop());
            }
            $print .= $this->_temp['print']->sqlExpEnd();
        }

        $print .= $this->_temp['print']->globalEnd();


        if ( ! isset($this->deploy['save']) ) {
            $this->deploy['save'] = false;
        }

        if ( ! isset($this->deploy['download']) ) {
            $this->deploy['download'] = false;
        }

        if ( $this->deploy['save'] != 1 && $this->deploy['download'] != 1 ) {
            header("Content-type: text/html");
        }


        if ( $this->deploy['save'] != 1 && $this->deploy['download'] != 1 ) {
            echo $print;
            die();
        }

        if ( empty($this->deploy['name']) ) {
            $this->deploy['name'] = date('H_m_d_H_i_s');
        }

        if ( substr($this->deploy['name'], - 5) == '.html' ) {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 5);
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';

        if ( ! is_dir($this->deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if ( ! is_writable($this->deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        file_put_contents($this->deploy['dir'] . $this->deploy['name'] . ".html", $print);


        if ( $this->deploy['download'] == 1 ) {
            header('Content-Disposition: attachment; filename="' . $this->deploy['name'] . '.html"');
            readfile($this->deploy['dir'] . $this->deploy['name'] . '.html');
        }


        if ( $this->deploy['save'] != 1 ) {
            unlink($this->deploy['dir'] . $this->deploy['name'] . '.html');
        }

        die();
    }

}




