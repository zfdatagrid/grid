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

class Bvb_Grid_Deploy_Xml extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{

    public $templateInfo;


    /**
     * @param array $options
     */
    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);
    }


    public function buildTitles ()
    {
        $titles = $this->_buildTitles();
        $grid = "    <fields>\n";
        foreach ( $titles as $title ) {
            if ( ! isset($title['field']) ) continue;

            $grid .= "        <" . $title['field'] . "><![CDATA[" . strip_tags($title['value']) . "]]></" . $title['field'] . ">\n";
        }

        $grid .= "    </fields>\n";

        return $grid;
    }


    public function buildSqlexp ()
    {
        $sql = $this->_buildSqlExp();
        $grid = '';
        if ( is_array($sql) ) {
            $grid .= "    <sqlexp>\n";

            foreach ( $sql as $exp ) {
                if ( ! isset($exp['field']) ) continue;

                $grid .= "        <" . $exp['field'] . "><![CDATA[" . strip_tags($exp['value']) . "]]></" . $exp['field'] . ">\n";
            }

            $grid .= "    </sqlexp>\n";
        }

        return $grid;
    }


    public function buildGrid ()
    {
        $grids = $this->_buildGrid();
        $grid = "    <results>\n";
        foreach ( $grids as $value ) {
            $grid .= "        <row>\n";
            foreach ( $value as $final ) {
                if ( ! isset($final['field']) ) continue;

                $grid .= "            <" . $final['field'] . "><![CDATA[" . strip_tags($final['value']) . "]]></" . $final['field'] . ">\n";
            }
            $grid .= "        </row>\n";
        }

        $grid .= "    </results>\n";

        return $grid;
    }


    public function deploy ()
    {
        $this->checkExportRights();

        $this->setRecordsPerPage(0);
        parent::deploy();

        $grid = '<?xml version="1.0" encoding="' . $this->getCharEncoding() . '"?>' . "\n";

        $grid .= "<grid>\n";
        $grid .= $this->buildTitles();
        $grid .= $this->buildGrid();
        $grid .= $this->buildSqlexp();
        $grid .= "</grid>";

        if ( ! isset($this->_deploy['save']) ) {
            $this->_deploy['save'] = false;
        }

        if ( ! isset($this->_deploy['download']) ) {
            $this->_deploy['download'] = false;
        }

        if ( $this->_deploy['save'] != 1 && $this->_deploy['download'] != 1 ) {
            header("Content-type: application/xml");
        }

        if ( ! isset($this->_deploy['save']) && ! isset($this->options['download']) ) {
            echo $grid;
            die();
        }

        if ( empty($this->_deploy['name']) ) {
            $this->_deploy['name'] = date('H_m_d_H_i_s');
        }

        if ( substr($this->_deploy['name'], - 4) == '.xml' ) {
            $this->_deploy['name'] = substr($this->_deploy['name'], 0, - 4);
        }

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/';

        if ( ! is_dir($this->_deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not a dir');
        }

        if ( ! is_writable($this->_deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not writable');
        }

        file_put_contents($this->_deploy['dir'] . $this->_deploy['name'] . ".xml", $grid);

        if ( $this->_deploy['download'] == 1 ) {
            header('Content-Disposition: attachment; filename="' . $this->_deploy['name'] . '.xml"');
            readfile($this->_deploy['dir'] . $this->_deploy['name'] . '.xml');
        }

        if ( $this->_deploy['save'] != 1 ) {
            unlink($this->_deploy['dir'] . $this->_deploy['name'] . '.xml');
        }

        die();
    }
}
