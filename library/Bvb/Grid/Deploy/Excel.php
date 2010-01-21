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
 * @version    0.4   $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Excel extends Bvb_Grid_DataGrid {

	protected $output = 'excel';

	protected $dir;

	protected $title;

	protected $options = array ();

	function __construct($title, $dir, $options = array('download')) {

		if (! in_array ( 'excel', $this->export )) {
			echo $this->__ ( "You dont' have permission to export the results to this format" );
			die ();
		}

		$this->dir = rtrim ( $dir, "/" ) . "/";
		$this->title = $title;
		$this->options = $options;


        $this->_setRemoveHiddenFields(true);
		parent::__construct ();
	}

	/**
	 * [Para podemros utiliza]
	 *
	 * @param string $var
	 * @param string $value
	 */

	function __set($var, $value) {

		parent::__set ( $var, $value );
	}

	function deploy() {

		$this->setPagination ( 0 );

		parent::deploy ();

		$titles = parent::_buildTitles ();
		$wsData = parent::_buildGrid ();
		$sql = parent::_buildSqlExp ();

		/*
        $nome = reset ( $titles );


        if($nome['field']=='id' || strpos($nome['field'],'_id')  || strpos($nome['field'],'id_')  || strpos($nome['field'],'.id')  )
        {
            @array_shift($titles);
            @array_shift($sql);
            $remove = true;
        }

        */
		$xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

		$xml .= '<Worksheet ss:Name="' . $this->title . '" ss:Description="' . $this->title . '"><ss:Table>';

		$xml .= '<ss:Row>';
		foreach ( $titles as $value ) {

			$type = ! preg_match ( "/^[0-9]+$/", $value ['value'] ) ? 'String' : 'Number';

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

		file_put_contents ( $this->dir . $this->title . ".xls", $xml );

		if (in_array ( 'download', $this->options )) {
			header ( 'Content-type: application/excel' );
			header ( 'Content-Disposition: attachment; filename="' . $this->title . '.xls"' );
			readfile ( $this->dir . $this->title . '.xls' );
		}

		if (! in_array ( 'save', $this->options )) {
			unlink ( $this->dir . $this->title . '.xls' );
		}

		die ();

	}

}




