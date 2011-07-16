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

class Bvb_Grid_Deploy_Excel extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{


    /**
     * Constructor
     *
     * @param array $options
     *
     * @return void
     */
    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);
    }


    /**
     * Deploys
     *
     * @return void
     * @see library/Bvb/Bvb_Grid::deploy()
     */
    public function deploy ()
    {

        $this->checkExportRights();
        $this->setRecordsPerPage(0);
        header("Expires: 0");
        header("Cache-Control: maxage=1"); //In seconds
        header("Pragma: public");


        parent::deploy();

        if ( ! isset($this->_deploy['title']) ) {
            $this->_deploy['title'] = 'ZFDatagrid';
        }

        $titles = parent::_buildTitles();
        $wsData = parent::_buildGrid();
        $sql = parent::_buildSqlExp();

        if ( is_array($wsData) && count($wsData) > 65569 ) {
            throw new Bvb_Grid_Exception('Maximum number of records allowed is 65569');
        }

        $xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

        $xml .= '<Worksheet ss:Name="' . $this->_deploy['title'] . '" ss:Description="' . $this->_deploy['title'] . '"><ss:Table>';

        $xml .= '<ss:Row>';
        foreach ( $titles as $value ) {
            $type = ! is_numeric($value['value']) ? 'String' : 'Number';

            $xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value['value'] . '</Data></ss:Cell>';
        }
        $xml .= '</ss:Row>';

        if ( is_array($wsData) ) {
            foreach ( $wsData as $row ) {
                $xml .= '<ss:Row>';
                $a = 1;
                foreach ( $row as $value ) {
                    $value['value'] = strip_tags($value['value']);

                    $type = ! is_numeric($value['value']) ? 'String' : 'Number';
                    $xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value['value'] . '</Data></ss:Cell>';

                    $a ++;
                }
                $xml .= '</ss:Row>';
            }
        }

        if ( is_array($sql) ) {
            $xml .= '<ss:Row>';
            foreach ( $sql as $value ) {
                $type = ! is_numeric($value['value']) ? 'String' : 'Number';

                $xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value['value'] . '</Data></ss:Cell>';
            }
            $xml .= '</ss:Row>';
        }

        $xml .= '</ss:Table></Worksheet>';

        $xml .= '</Workbook>';

        if ( ! isset($this->_deploy['save']) ) {
            $this->_deploy['save'] = false;
        }

        if ( ! isset($this->_deploy['download']) ) {
            $this->_deploy['download'] = false;
        }

        if ( $this->_deploy['save'] != 1 && $this->_deploy['download'] != 1 ) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }

        if ( empty($this->_deploy['name']) ) {
            $this->_deploy['name'] = date('H_m_d_H_i_s');
        }

        if ( substr($this->_deploy['name'], - 4) == '.xls' ) {
            $this->_deploy['name'] = substr($this->_deploy['name'], 0, - 4);
        }

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/';

        if ( ! is_dir($this->_deploy['dir']) && $this->_deploy['save'] == 1 ) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not a dir');
        }

        if ( ! is_writable($this->_deploy['dir']) && $this->_deploy['save'] == 1 ) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not writable');
        }

        if ( $this->_deploy['save'] == 1 ) {
            file_put_contents($this->_deploy['dir'] . $this->_deploy['name'] . ".xls", $xml);
        }

        if ( $this->_deploy['download'] == 1 ) {
            header('Content-type: application/excel');
            header('Content-Disposition: attachment; filename="' . $this->_deploy['name'] . '.xls"');
            echo $xml;
        }
        die();
    }
}
