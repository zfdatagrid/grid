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
class Bvb_Grid_Source_Json extends Bvb_Grid_Source_Array {

    public function __construct($array, $loop = null, $columns = null, $cache = null)
    {

        $this->setCache($cache);

        $cache = $this->getCache();

        $array = trim($array);

        if ($array[0] != '[' && $array[0] != '{') {

            if ($cache['enable'] == true) {
                if (($result = $cache['instance']->load(md5($array))) === false) {
                    $result = file_get_contents($array);
                    $cache['instance']->save($result, md5($array), array($this->_cache['tag']));
                }
            } else {
                $result = file_get_contents($array);
            }
        } else {
            $result = $array;
        }


        $xml = Zend_Json::decode($result, true);

     
        $cols = explode(',', $loop);

        if (is_array($cols) && count($cols) > 1) {
            foreach ($cols as $value) {
                $xml = $xml[$value];
            }
        }

        //Remove possible arrays
        foreach ($xml as $x1 => $f1) {

            foreach ($f1 as $key => $final) {
                if (!is_scalar($final)) {
                    unset($xml[$x1][$key]);
                }
            }
        }

        if (is_array($columns) && count($columns) == count($xml[0])) {
            foreach ($columns as $value) {
                if (is_string($value))
                    $columns = $columns[$value];
            }
        } else {
            $columns = array_keys(reset($xml));
        }

        $this->_fields = $columns;
        $this->_rawResult = $xml;
        $this->_sourceName = 'json';

        return $this;
    }

}