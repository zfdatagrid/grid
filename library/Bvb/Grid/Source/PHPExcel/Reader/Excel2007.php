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
 * @version    $Id: Csv.php 685 2010-02-25 20:20:43Z pao.fresco $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Source_PHPExcel_Reader_Excel2007 extends Bvb_Grid_Source_Array
{


    function __construct ($file, $sheet = '')
    {
        if ( ! $file instanceof PHPExcel_Reader_Excel2007 ) {
            $objReader = new PHPExcel_Reader_Excel2007();
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file);
            $result = $objPHPExcel->getSheetByName($sheet)->toArray();
        } else {
            $result = $file->toArray();
        }

        $this->_fields = array_keys($result[0]);
        $this->_rawResult = $result;
        $this->_sourceName = 'excel';
    }

}