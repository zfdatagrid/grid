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
 * @package    Bvb_Grid
 * @copyright  Copyright (c) Bento Vilas Boas (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Json extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{


    public function __construct ($options)
    {

        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);


        if ( ! in_array($this->_deployName, $this->_export) && ! array_key_exists($this->_deployName, $this->_export) ) {
            throw new Bvb_Grid_Exception($this->__("You don't have permission to export the results to this format"));
        }
    }

    public function buildTitlesJson ($titles)
    {

        $grid = array();
        foreach ($titles as $title) {

            $grid[] = strip_tags($title['value']);
        }
        return $grid;
    }

    public function buildSqlexpJson ($sql)
    {

        $grid = array();
        if (is_array($sql)) {

            foreach ($sql as $exp) {
                $grid[] = strip_tags($exp['value']);
            }
        }
        return $grid;
    }

    public function buildGridJson ($grids)
    {

        $grid = array();
        $i = 0;
        foreach ($grids as $value) {

            $grid1 = array();
            foreach ($value as $final) {
                $grid1[] = strip_tags($final['value']);
            }

            $grid[] = $grid1;
            $i ++;
        }

        return $grid;

    }

    public function deploy ()
    {

        $grid = array();
        $this->setRecordsPerPage(0);
        parent::deploy();

        header('Content-Type', 'application/json');

        $grid['titles'] = self::buildTitlesJson(parent::_buildTitles());
        $grid['rows'] = self::buildGridJson(parent::_buildGrid());
        $grid['sqlexp'] = self::buildSqlexpJson(parent::_buildSqlExp());

        echo Zend_Json::encode($grid);

        die();
    }

}




