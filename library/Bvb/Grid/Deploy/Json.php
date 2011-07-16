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

class Bvb_Grid_Deploy_Json extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{


    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->checkExportRights();
    }


    /**
     * Builds Titles for
     *
     * return string
     */
    public function buildTitles ()
    {
        return $this->formatRow($this->_buildTitles());
    }


    /**
     * Builds SQL expression
     *
     * @return string
     */
    public function buildSqlexp ()
    {
        return $this->formatRow($this->_buildSqlExp());
    }


    /**
     * Builds the grid
     *
     * @return string
     */
    public function buildGrid ()
    {
        return array_map(array($this, 'formatRow'), $this->_buildGrid());
    }


    /**
     * Escape/format an array of row data
     *
     * @param array $row
     *
     * @return string
     */
    protected function formatRow ($row)
    {
        return array_map('strip_tags', $this->arrayPluck($row));
    }


    /**
     * Deploys content
     *
     * @see library/Bvb/Bvb_Grid::deploy()
     * @return void
     */
    public function deploy ()
    {
        $this->setRecordsPerPage(0);
        parent::deploy();

        header('Content-Type', 'application/json');

        $grid = array('titles' => $this->buildTitles(), 'rows' => $this->buildGrid(), 'sqlexp' => $this->buildSqlexp());

        if(!$this->getDeployOption('return', false))
        {
            echo Zend_Json::encode($grid);
            die();
        }else{
            return Zend_Json::encode($grid);
        }

    }
}
