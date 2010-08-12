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
class Bvb_Grid_Deploy_Csv extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
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
        $fileName = $this->getInfo('fileName');
        if (!$fileName) {
            $fileName = $this->getInfo('title');
        }
        if (!$fileName) {
            $fileName = Zend_Controller_Front::getInstance()->getRequest()->getParam('controller') . '-' . date("Ymd");
        }
        return $fileName . '.csv';
    }

    /**
     * Constructor
     *
     * Optimize performance by setting best value for $this->setPagination(?);
     *
     * Options (deploy.csv.<option>):
     * set_time_limit - time out for php script
     * memory_limit - PHP memory limit
     * download - send data directly to user
     * save - save data to file
     * fileName - filename used as suggestion when downloading and to save data
     * dir - directory where to store data
     *
     * @param array $options options
     *
     * @see _prepareOptions
     *
     * @return void
     */
    function __construct($options)
    {
        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        // default pagination, should be adjusted based on data processed to improve speed
        $this->setRecordsPerPage(5000);

        $options = $this->_options;

        // fix configuration options
        $deploy = isset($options['deploy'][$this->_deployName]) ? $options['deploy'][$this->_deployName] : array();
        if (!isset($deploy['dir'])) {
            $deploy['dir'] = "";
        } else {
            $deploy['dir'] = rtrim($deploy['dir'], "/") . "/";
        }

        if (!isset($deploy['download']) && !isset($deploy['store'])) {
            $deploy['download'] = true;
            $deploy['store'] = false;
        } else {
            if (!isset($deploy['download'])) {
                $deploy['download'] = false;
            }
            if (!isset($deploy['store'])) {
                $deploy['store'] = false;
            }
        }
        // set the changed options
        $options['deploy'][$this->_deployName] = $deploy;

        // TODO I don't understand why parent::__constructor will not set this automaticaly,
        // what if it would be loaded from config ?
        $this->_deploy = $options['deploy'][$this->_deployName];

         if (!in_array($this->_deployName, $this->_export) && !array_key_exists($this->_deployName, $this->_export)) {
            // check if this kind of export is alowed
            throw new Bvb_Grid_Exception($this->__("You dont' have permission to export the results to this format"));
        }

    }

    /**
     * Build list of column names for header row
     *
     * @param array $titles column titles
     *
     * @return string
     */
    function buildTitltesCsv($titles)
    {

        $grid = '';
        foreach ($titles as $title) {

            $grid .= '"' . $title ['value'] . '",';
        }

        return substr($grid, 0, - 1) . "\n";
    }

    /**
     * Create line with values build based on sql expressions
     *
     * @param array $sql SQL expression values
     *
     * @return <type> string
     */
    function buildSqlexpCsv($sql)
    {

        $grid = '';
        if (is_array($sql)) {

            foreach ($sql as $exp) {
                $grid .= '"' . strip_tags($exp['value']) . '",';
            }
        }

        return substr($grid, 0, - 1) . " \n";
    }

    /**
     * Build data rows
     *
     * @param array $grids all rows
     *
     * @return string
     */
    function buildGridCsv($grids)
    {

        $grid = '';
        foreach ($grids as $value) {

            foreach ($value as $final) {
                $grid .= '"' . strip_tags($final['value']) . '",';
            }

            $grid = substr($grid, 0, - 1) . " \n";
        }

        return $grid;
    }

    /**
     * Add row to results
     *
     * Depending on settings store to file and/or directly upload
     *
     * @param array $data data
     *
     * @return void
     */
    protected function csvAddData($data)
    {
        if ($this->actionEnabled('download')) {
            // send first headers
            echo $data;
            flush();
            ob_flush();
        }
        if ($this->actionEnabled('store')) {
            // open file handler
            fwrite($this->_outFile, $data);
        }
    }

    /**
     * Deploy method
     *
     * @return boolean FALSE if error
     */
    function deploy()
    {
        // prepare data
        $this->_prepareOptions();
        parent::deploy();

        if ($this->actionEnabled('download')) {

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
        if ($this->actionEnabled('store')) {
            // open file handler
            $this->_outFile = fopen($this->_deploy['dir'] . $this->getFileName(), "w");
        }

        // export header
        if (!(isset($this->_deploy['skipHeaders']) && $this->_deploy['skipHeaders'])) {
            $this->csvAddData(self::buildTitltesCsv(parent::_buildTitles()));
        }
        $i = 0;
        do {
            $i += $this->_recordsPerPage;
            $this->csvAddData(self::buildGridCsv(parent::_buildGrid()));
            $this->csvAddData(self::buildSqlexpCsv(parent::_buildSqlExp()));
            // get next data

            $this->getSource()->buildQueryLimit($this->_recordsPerPage, $i);
            $this->_result = $this->getSource()->execute();
        } while (count($this->_result));

        if ($this->actionEnabled('store')) {
            // close file handler
            fclose($this->_outFile);
        }
        if ($this->actionEnabled('download')) {
            // we set special headers and uploaded data, there is nothing more we could do
            die();
        }

        return true;
    }

    /**
     * Set some deploy settings
     *
     * @return void
     */
    protected function _prepareOptions()
    {
        // apply options
        if (isset($this->_deploy['set_time_limit'])) {
            // script needs time to proces huge amount of data (important)
            set_time_limit($this->_deploy['set_time_limit']);
        }
        if (isset($this->_deploy['memory_limit'])) {
            // adjust memory_limit if needed (not very important)
            ini_set('memory_limit', $this->_deploy['memory_limit']);
        }
    }

    /**
     * Return file if download requested without the need to continue up to deploy() method
     *
     * @return Bvb_Grid_Deploy_Csv
     */
    public function setAjax()
    {
        if ($this->actionEnabled('download')) {
            // if we want to upload data then we should do it now, deploy will die if needed
            $this->_deploy();
        }
        return $this;
    }

    /**
     * Is action (download or store) enabled
     *
     * @param string $action name of action to test
     *
     * @return boolean
     */
    public function actionEnabled($action)
    {
        return isset($this->_deploy[$action])?$this->_deploy[$action]:false;
    }

}