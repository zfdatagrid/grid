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

class Bvb_Grid_Deploy_Csv extends Bvb_Grid_DataGrid {

	protected $dir;

	protected $title;

	protected $options = array ();

	protected $output = 'csv';

	/**
	 * Set true if data should be downloaded
	 */
	protected $downloadData = null;

	/**
	 * Set true if data should be stored
	 */
	protected $storeData = null;

	/**
	 * Storing file
	 */
	protected $outFile = null;

	/*
     *
     *
     * Optimize performance by setting best value for $this->setPagination(?);
     * and setting options:
     * set_time_limit
     * memory_limit
     * download: send data to directly to user
     * save: save the file
     * ?dir:
     *
     * @param array $data
     */
	function __construct($options = array('download')) {

		if (! in_array ( 'csv', $this->export )) {
			echo $this->__ ( "You dont' have permission to export the results to this format" );
			die ();
		}

		$this->setPagination ( 5000 );

		// TODO this needs rework
		$dir = isset ( $options ['dir'] ) ? $options ['dir'] : '';
		$this->dir = rtrim ( $dir, "/" ) . "/";

		$this->options = $options;

        $this->addTemplateDir ( 'Bvb/Grid/Template/Wordx', 'Bvb_Grid_Template_Wordx', 'wordx' );
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

	function buildTitltesCsv($titles) {

		$grid = '';
		foreach ( $titles as $title ) {

			$grid .= '"' . $title ['value'] . '",';
		}

		return substr ( $grid, 0, - 1 ) . "\n";

	}

	function buildSqlexpCsv($sql) {

		$grid = '';
		if (is_array ( $sql )) {

			foreach ( $sql as $exp ) {
				$grid .= '"' . $exp ['value'] . '",';
			}
		}

		return substr ( $grid, 0, - 1 ) . " \n";

	}

	function buildGridCsv($grids) {

		$grid = '';
		foreach ( $grids as $value ) {

			foreach ( $value as $final ) {
				$grid .= '"' . $final ['value'] . '",';
			}

			$grid = substr ( $grid, 0, - 1 ) . " \n";
		}

		return $grid;

	}

	/**
	 * Depending on settings store to file and/or directly upload
	 */
	protected function csvAddData($data) {
		if ($this->downloadData) {
			// send first headers
			echo $data;
			flush ();
			ob_flush ();
		}
		if ($this->storeData) {
			// open file handler
			fwrite ( $this->outFile, $data );
		}
	}
	function deploy() {
		// apply options
		if (isset ( $this->options ['set_time_limit'] )) {
			// script needs time to proces huge amount of data (important)
			set_time_limit ( $this->options ['set_time_limit'] );
		}
		if (isset ( $this->options ['memory_limit'] )) {
			// adjust memory_limit if needed (not very important)
			ini_set ( 'memory_limit', $this->options ['memory_limit'] );
		}
		// decide if we should store data to file or send directly to user
		$this->downloadData = in_array ( 'download', $this->options );
		$this->storeData = in_array ( 'save', $this->options );

		// prepare data
		parent::deploy ();

		if ($this->downloadData) {
			// send first headers
			header ( 'Content-type: text/plain; charset=utf-8' . $this->charEncoding );
			header ( 'Content-Disposition: attachment; filename="' . $this->title . '.csv"' );
		}
		if ($this->storeData) {
			// open file handler
			$this->outFile = fopen ( $this->dir . $this->title . ".csv", "w" );
		}

		// export header
		$this->csvAddData ( self::buildTitltesCsv ( parent::_buildTitles () ) );
		$i = 0;
		do {
			$i += $this->pagination;
			$this->csvAddData ( self::buildGridCsv ( parent::_buildGrid () ) );
			$this->csvAddData ( self::buildSqlexpCsv ( parent::_buildSqlExp () ) );
			// get next data
			$this->_select->limit ( $this->pagination, $i );
			$stmt = $this->_db->query ( $this->_select );
			$this->_result = $stmt->fetchAll ();
		} while ( count ( $this->_result ) );

		if ($this->storeData) {
			// close file handler
			fclose ( $this->outFile );
		} else {
			die ();
		}

		return true;
	}

}
