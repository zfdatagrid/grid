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
class Bvb_Grid_Deploy_Csv extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    /**
     * Directory to store output file
     *
     * @var string
     */
    protected $_dir;

    /**
     * Configuration options
     *
     * @var <type>
     */
    public $deploy = array();

    /**
     * Set true if data should be downloaded
     */
    protected $_downloadData = null;
    /**
     * Set true if data should be stored
     */
    protected $_storeData = null;
    /**
     * Storing file
     */
    protected $_outFile = null;
    /**
     * We don't want to display hidden fields
     *
     * @var $_removeHiddenFields boolean
     */
    protected $_removeHiddenFields = true;

    /**
     * Return name of file
     *
     * @return string
     */
    public function getFileName()
    {
        if (isset($this->_info['Title'])) {
            $title = $this->_info['Title'][0];
        } elseif (isset($this->_info['title'])) {
            $title = $this->_info['title'][0];
        } else {
            $title = Zend_Controller_Front::getInstance()->getRequest()->getParam('controller') . '-' . date("Ymd");
        }

        return $title . '.csv';
    }

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

    function __construct($options, $exportOptions = array('download'))
    {

    if ( ! in_array(self::OUTPUT, $this->_export) && !array_key_exists(self::OUTPUT,$this->_export) ) {
            echo $this->__ ( "You dont' have permission to export the results to this format" );
            die ();
        }

        $this->setNumberRecordsPerPage (5000);

        // TODO this needs rework
        $dir = isset($exportOptions['dir']) ? $exportOptions['dir'] : '';
        $this->_dir = rtrim($dir, "/") . "/";

        $this->deploy = $exportOptions;

        parent::__construct($options);
    }


    function buildTitltesCsv($titles)
    {

        $grid = '';
        foreach ($titles as $title) {

            $grid .= '"' . $title ['value'] . '",';
        }

        return substr($grid, 0, - 1) . "\n";
    }

    function buildSqlexpCsv($sql)
    {

        $grid = '';
        if (is_array($sql)) {

            foreach ($sql as $exp) {
                $grid .= '"' . $exp ['value'] . '",';
            }
        }

        return substr($grid, 0, - 1) . " \n";
    }

    function buildGridCsv($grids)
    {

        $grid = '';
        foreach ($grids as $value) {

            foreach ($value as $final) {
                $grid .= '"' . $final ['value'] . '",';
            }

            $grid = substr($grid, 0, - 1) . " \n";
        }

        return $grid;
    }

    /**
     * Depending on settings store to file and/or directly upload
     */
    protected function csvAddData($data)
    {
        if ($this->_downloadData) {
            // send first headers
            echo $data;
            flush();
            ob_flush();
        }
        if ($this->_storeData) {
            // open file handler
            fwrite($this->_outFile, $data);
        }
    }

    function deploy()
    {
        // prepare data
        $this->_prepareOptions();
        parent::deploy();
        if ($this->_downloadData) {

            // send first headers
            ob_end_clean();

            /* if(ini_get('zlib.output_compression')) {
              die;
              ini_set('zlib.output_compression', 'Off');
              } */
            header('Content-Description: File Transfer');
            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            // force download dialog
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
            //header("Content-Type: application/csv");
            //header ( 'Content-type: text/plain; charset=utf-8' . $this->charEncoding );

            header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');

            header('Content-Transfer-Encoding: binary');
        }
        if ($this->_storeData) {
            // open file handler
            $this->_outFile = fopen($this->_dir . $this->getFileName(), "w");
        }

        // export header
        if (!(isset($this->deploy['skipHeaders']) && $this->deploy['skipHeaders'])) {
            $this->csvAddData(self::buildTitltesCsv(parent::_buildTitles()));
        }
        $i = 0;
        do {
            $i += $this->_pagination;
            $this->csvAddData(self::buildGridCsv(parent::_buildGrid()));
            $this->csvAddData(self::buildSqlexpCsv(parent::_buildSqlExp()));
            // get next data

            $this->getSource()->buildQueryLimit($this->_pagination, $i);
            $this->_result = $this->getSource()->execute();
        } while (count($this->_result));

        if ($this->_storeData) {
            // close file handler
            fclose($this->_outFile);
        }
        if ($this->_downloadData) {
            // we set special headers and uploaded data, there is nothing more we could do
            die();
        }

        return true;
    }

    protected function _prepareOptions()
    {
        // apply options
        if (isset($this->deploy ['set_time_limit'])) {
            // script needs time to proces huge amount of data (important)
            set_time_limit($this->deploy ['set_time_limit']);
        }
        if (isset($this->deploy['memory_limit'])) {
            // adjust memory_limit if needed (not very important)
            ini_set('memory_limit', $this->deploy['memory_limit']);
        }
        // decide if we should store data to file or send directly to user
        $this->_downloadData = in_array('download', $this->deploy);
        $this->_storeData = in_array('save', $this->deploy);
    }

    public function setAjax()
    {
        if (in_array('download', $this->deploy)) {
            // if we want to upload data then we should do it now, deploy will die if needed
            $this->deploy();
        }
    }

}