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

class Bvb_Grid_Deploy_Excel extends Bvb_Grid  implements Bvb_Grid_Deploy_DeployInterface {


    public function __construct ($options)
    {

        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);


        if ( ! in_array($this->_deployName, $this->_export) && ! array_key_exists($this->_deployName, $this->_export) ) {
            throw new Bvb_Grid_Exception($this->__("You dont' have permission to export the results to this format"));
        }
    }


    public function deploy() {

		$this->setRecordsPerPage ( 0 );

		parent::deploy ();

		if(!isset($this->options['title']))
		{
		    $this->options['title'] = 'ZFDatagrid';
		}

		$titles = parent::_buildTitles ();
		$wsData = parent::_buildGrid ();
		$sql = parent::_buildSqlExp ();


		if (is_array ( $wsData ) && count($wsData)>65569) {
		    throw new Bvb_Grid_Exception('Maximum number of recordsa allowed is 65569');
		}

		$xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

		$xml .= '<Worksheet ss:Name="' .  $this->options['title']  . '" ss:Description="' .  $this->options['title']  . '"><ss:Table>';

		$xml .= '<ss:Row>';
		foreach ( $titles as $value ) {

			$type = ! is_numeric ($value ['value'] ) ? 'String' : 'Number';

			$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value ['value'] . '</Data></ss:Cell>';
		}
		$xml .= '</ss:Row>';

		if (is_array ( $wsData )) {
			foreach ( $wsData as $row ) {

				$xml .= '<ss:Row>';
				$a = 1;
				foreach ( $row as $value ) {

					$value ['value'] = strip_tags ( $value ['value'] );

					$type = ! is_numeric ( $value ['value'] ) ? 'String' : 'Number';
					$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value ['value'] . '</Data></ss:Cell>';

					$a ++;
				}
				$xml .= '</ss:Row>';
			}

		}

		if (is_array ( $sql )) {
			$xml .= '<ss:Row>';
			foreach ( $sql as $value ) {

				$type = ! is_numeric ( $value ['value'] ) ? 'String' : 'Number';

				$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value ['value'] . '</Data></ss:Cell>';
			}
			$xml .= '</ss:Row>';
		}

		$xml .= '</ss:Table></Worksheet>';

		$xml .= '</Workbook>';


        if (! isset($this->deploy['save'])) {
            $this->deploy['save'] = false;
        }

        if (! isset($this->deploy['download'])) {
            $this->deploy['download'] = false;
        }

        if ($this->deploy['save'] != 1 && $this->deploy['download'] != 1) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }


        if (empty($this->deploy['name'])) {
            $this->deploy['name'] = date('H_m_d_H_i_s');
        }

        if (substr($this->deploy['name'], - 4) == '.xls') {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 4);
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';

        if (! is_dir($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if (! is_writable($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        file_put_contents($this->deploy['dir'] . $this->deploy['name'] . ".xls", $xml);


        if ($this->deploy['download'] == 1) {
            header ( 'Content-type: application/excel' );
            header('Content-Disposition: attachment; filename="' . $this->deploy['name'] . '.xls"');
            readfile($this->deploy['dir'] . $this->deploy['name'] . '.xls');
        }

        if ($this->deploy['save'] != 1) {
            unlink($this->deploy['dir'] . $this->deploy['name'] . '.xls');
        }


		die ();

	}

}




