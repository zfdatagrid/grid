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
     * Set to true if setForceRecordsPerPage was called with number
     *
     * @var boolean
     */
    protected $_isRecordsPerPageForced = false;

    /**
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
     * @see _prepareOptions
     * @return void
     */
    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        // default pagination, should be adjusted based on data processed to improve speed
        $this->setForceRecordsPerPage(5000);

        // fix configuration options
        $deploy = $this->getDeployOption($this->_deployName, array());
        $defaults = array(
            'dir'       => '',
            'store'     => false,
            'download'  => (!isset($deploy['download']) && !isset($deploy['store'])),
        );
        $deploy = array_merge($defaults, $deploy);

        if (!empty($deploy['dir'])) {
            $deploy['dir'] = rtrim($deploy['dir'], "/") . "/";
        }

        // set the changed options
        $this->setDeployOption($this->_deployName, $deploy);

        // TODO I don't understand why parent::__constructor will not set this automaticaly,
        // what if it would be loaded from config ?
        $this->_deploy = $this->getDeployOption($this->_deployName);
        $this->checkExportRights();
    }
    /**
     * Force to use given value as records per page
     *
     * Csv should work with as high number of rows as possible to deliver good export speed. On the otherside high number could reach PHP memory limit.
     *
     * @param int|boolean $number will not accept any other changes made by setRecordsPerPage if not FALSE
     *
     * @return Bvb_Grid
     */
    public function setForceRecordsPerPage($number)
    {
        if (false===$number) {
            $this->_isRecordsPerPageForced = false;
        } else {
            $this->_isRecordsPerPageForced = true;
            $this->_recordsPerPage = (int) $number;
        }

        return $this;
    }
    /**
     * Number of records to show per page
     *
     * @param int $number Records to show
     *
     * @return Bvb_Grid
     */
    public function setRecordsPerPage($number = 15)
    {
        if (!$this->_isRecordsPerPageForced) {
            // will be ignore if setForceRecordsPerPage was used to set value
            return parent::setRecordsPerPage($number);
        }
        return $this;
    }
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
     * Build list of column names for header row
     *
     * @return string
     */
    public function buildTitles()
    {
        return $this->formatLine($this->_buildTitles());
    }

    /**
     * Create line with values build based on sql expressions
     *
     * @return string
     */
    public function buildSqlexp()
    {
        return $this->formatLine($this->_buildSqlExp());
    }

    /**
     * Build data rows
     *
     * @return string
     */
    public function buildGrid()
    {
        return implode("\n", array_map(array($this, 'formatLine'), $this->_buildGrid()));
    }

    /**
     * Escape/format an array of row data as a single line of CSV
     * @param array $row
     * @return string
     */
    protected function formatLine($row)
    {
        // TODO: _buildGrid should be refactored so we don't have to call arrayPluck here
        $s = implode(',', array_map(array($this, 'formatCell'), $this->arrayPluck($row)));
        return $s;
    }

    /**
     * Format CSV cell
     * @param string $value
     * @return string
     */
    protected function formatCell($value)
    {
        return '"' . strip_tags($value) . '"';
    }

    /**
     * Add row to results
     *
     * Depending on settings store to file and/or directly upload
     *
     * @param array $data data
     * @return void
     */
    protected function csvAddData($data)
    {
        if (0==strlen($data)) {
            return;
        }
        if ($this->getDeployOption('download')) {
            // send first headers
            echo $data."\n";
            flush();
            ob_flush();
        }
        if ($this->getDeployOption('store')) {
            // open file handler
            fwrite($this->_outFile, $data."\n");
        }
    }

    /**
     * Deploy method
     *
     * @return boolean FALSE if error
     */
    public function deploy()
    {
        // prepare data
        $this->_prepareOptions();
        parent::deploy();

        if ($this->getDeployOption('download')) {
            // send first headers
            ob_end_clean();

            header('Content-Description: File Transfer');
            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header("Content-Type: application/csv");
            header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');
            header('Content-Transfer-Encoding: binary');
        }
        if ($this->getDeployOption('store')) {
            // open file handler
            $this->_outFile = fopen($this->_deploy['dir'] . $this->getFileName(), "w");
        }

        // export header
        if (!$this->getDeployOption('skipHeaders')) {
            $this->csvAddData($this->buildTitles());
        }
        $i = 0;
        do {
            $i += $this->_recordsPerPage;
            $this->csvAddData($this->buildGrid());
            $this->csvAddData($this->buildSqlexp());

            // get next page
            $this->getSource()->buildQueryLimit($this->_recordsPerPage, $i);
            $this->_result = $this->getSource()->execute();
        } while (count($this->_result));

        if ($this->getDeployOption('store')) {
            // close file handler
            fclose($this->_outFile);
        }
        if ($this->getDeployOption('download')) {
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
        if ($this->getDeployOption('download')) {
            // if we want to upload data then we should do it now, deploy will die if needed
            $this->deploy();
        }
        return $this;
    }
}