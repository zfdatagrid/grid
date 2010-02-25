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
 * @version    $Id: Column.php 492 2010-01-26 17:08:02Z pao.fresco $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Source_Csv extends Bvb_Grid_Source_Array
{

    function __construct ($dataSource, $field = null, $separator = ',')
    {

        $final = array();

        $row = 0;
        $handle = fopen($dataSource, "r");
        while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
            $num = count($data);
            if (null != $field) {
                for ($c = 0; $c < $num; $c ++) {
                    $final[$row][$field[$c]] = $data[$c];
                }
            } else {
                if ($row == 0) {
                    for ($c = 0; $c < $num; $c ++) {
                        $field[] = $data[$c];
                    }
                } else {
                    for ($c = 0; $c < $num; $c ++) {
                        $final[$row - 1][$field[$c]] = $data[$c];
                    }
                }
            }
            $row ++;
        }
        fclose($handle);

        $this->_fields = $field;
        $this->_rawResult = $final;
        $this->_sourceName = 'csv';

        unset($final);
        unset($field);

        return true;
    }

}