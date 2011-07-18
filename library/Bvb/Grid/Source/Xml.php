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
class Bvb_Grid_Source_Xml extends Bvb_Grid_Source_Array {

    public function __construct($url, $loop, $columns = null, $cache = null)
    {

        if ($cache) {
            $this->setCache($cache);
        }

        $cache = $this->getCache();

        if (strstr($url, '<?xml')) {
            $xml = simplexml_load_string($url);
        } else {

            if ($cache['enable'] == true) {
                if (($xml = $cache['instance']->load(md5($url))) === false) {
                    $xml = file_get_contents($url);
                    $cache['instance']->save($xml, md5($url), array($this->_cache['tag']));
                }
            } else {
                $xml = simplexml_load_file($url);
            }
        }

        $xml = $this->_object2array($xml);

        $cols = explode(',', $loop);
        if (is_array($cols)) {
            foreach ($cols as $value) {
                $xml = $xml[$value];
            }
        }

        //Remove possible arrays
        for ($i = 0; $i < count($xml); $i++) {
            foreach ($xml[$i] as $key => $final) {
                if (!is_string($final)) {
                    unset($xml[$i][$key]);
                }
            }
        }

        if (is_array($columns)) {
            foreach ($columns as $value) {
                $columns = $columns[$value];
            }
        } else {
            $columns = array_keys($xml[0]);
        }

        $this->_fields = $columns;
        $this->_rawResult = $xml;
        $this->_sourceName = 'xml';

        unset($columns);
        unset($xml);
    }

}