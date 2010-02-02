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

class Bvb_Grid_Deploy_Csv extends Bvb_Grid_Data implements Bvb_Grid_Deploy_Interface
{

    protected $dir;

    const OUTPUT = 'csv';

    public $deploy;

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
    function __construct ($options)
    {

        if (! in_array(self::OUTPUT, $this->export)) {
            echo $this->__("You dont' have permission to export the results to this format");
            die();
        }

        $this->setPagination(500);

        parent::__construct($options);
    }

    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */

    function __set ($var, $value)
    {

        parent::__set($var, $value);
    }

    function buildTitltesCsv ($titles)
    {

        $grid = '';
        foreach ($titles as $title) {

            $grid .= '"' . $title['value'] . '",';
        }

        return substr($grid, 0, - 1) . "\n";

    }

    function buildSqlexpCsv ($sql)
    {

        $grid = '';
        if (is_array($sql)) {

            foreach ($sql as $exp) {
                $grid .= '"' . $exp['value'] . '",';
            }
        }

        return substr($grid, 0, - 1) . " \n";

    }

    function buildGridCsv ($grids)
    {

        $grid = '';
        foreach ($grids as $value) {

            foreach ($value as $final) {
                $grid .= '"' . $final['value'] . '",';
            }

            $grid = substr($grid, 0, - 1) . " \n";
        }

        return $grid;

    }

    /**
     * Depending on settings store to file and/or directly upload
     */
    protected function csvAddData ($data)
    {
        if ($this->downloadData) {
            // send first headers
            echo $data;
            flush();
            ob_flush();
        }
        if ($this->storeData) {
            // open file handler
            fwrite($this->outFile, $data);
        }
    }
    function deploy ()
    {
        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';
        // apply options
        if (isset($this->deploy['set_time_limit'])) {
            // script needs time to proces huge amount of data (important)
            set_time_limit($this->deploy['set_time_limit']);
        }
        if (isset($this->deploy['memory_limit'])) {
            // adjust memory_limit if needed (not very important)
            ini_set('memory_limit', $this->deploy['memory_limit']);
        }

        if (empty($this->deploy['name'])) {
            $this->deploy['name'] = date('H_m_d_H_i_s');
        }

        if (substr($this->deploy['name'], - 4) == '.csv') {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 4);
        }



        // decide if we should store data to file or send directly to user
        $this->downloadData = $this->deploy['download'] == 1 ? 1 : false;
        $this->storeData = $this->deploy['save'] == 1 ? 1 : false;

        // prepare data
        parent::deploy();


        if ($this->downloadData) {
            // send first headers
            header('Content-type: text/plain; charset=' . $this->charEncoding);
            header('Content-Disposition: attachment; filename="' .$this->deploy['name'] . '.csv"');
        }
        if ($this->storeData) {
            // open file handler
            $this->outFile = fopen($this->deploy['dir']. $this->deploy['name']. ".csv", "w");
        }

        // export header
        $this->csvAddData(self::buildTitltesCsv(parent::_buildTitles()));
        $i = 0;
        do {
            $i += $this->pagination;
            $this->csvAddData(self::buildGridCsv(parent::_buildGrid()));
            $this->csvAddData(self::buildSqlexpCsv(parent::_buildSqlExp()));
            // get next data
            $this->_select->limit($this->pagination, $i);
            $stmt = $this->_db->query($this->_select);
            $this->_result = $stmt->fetchAll();
        } while (count($this->_result));

        if ($this->storeData) {
            // close file handler
            fclose($this->outFile);
        } else {
            die();
        }

        return true;
    }

}
