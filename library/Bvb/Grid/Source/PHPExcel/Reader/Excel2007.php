<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */

class Bvb_Grid_Source_PHPExcel_Reader_Excel2007 extends Bvb_Grid_Source_Array
{


    public function __construct ($file, $sheet = '', $titles = null)
    {
        if ( ! Zend_Loader_Autoloader::autoload('PHPExcel_Reader_Excel2007') ) {
            die("You must have PHPExcel installed in order to use this deploy. Please check this page for more information: http://www.phpexcel.net ");
        }

        if ( ! $file instanceof PHPExcel_Reader_Excel2007 ) {
            $objReader = new PHPExcel_Reader_Excel2007();
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file);

            $result = $objPHPExcel->getSheetByName($sheet)
                ->toArray();
        } else {
            $result = $file->toArray();
        }

        $empty = array();
        foreach ( reset($result) as $key => $hasContent ) {
            if ( $hasContent == '' ) {
                $empty[$key] = $key;
            }
        }

        foreach ( $result as $c => $hasContent ) {
            foreach ( $hasContent as $key => $cell ) {
                if ( array_key_exists($key, $empty) ) {
                    unset($hasContent[$key]);
                }
            }

            $result[$c] = $hasContent;
        }

        foreach ( $result as $key => $value ) {
            $r = 0;

            foreach ( $value as $c => $cell ) {
                if ( $cell == '' ) {
                    $r ++;
                }
            }

            if ( $r == count($value) ) {
                unset($result[$key]);
            }
        }

        if ( $titles === null || count($titles) != count(reset($result)) ) {
            $this->_fields = array_keys(reset($result));
        } else {
            $this->_fields = $titles;
            foreach ( $result as $key => $value ) {
                $result[$key] = array_combine($titles, $value);
            }
        }

        $this->_fields = array_keys(reset($result));
        $this->_rawResult = $result;
        $this->_sourceName = 'excel';
    }
}