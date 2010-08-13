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
 * @package    Bvb_Grid
 * @copyright  Copyright (c) Bento Vilas Boas (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Json extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    public function __construct($options)
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->checkExportRights();
    }

    public function buildTitles()
    {
        return $this->formatRow($this->_buildTitles());
    }

    public function buildSqlexp()
    {
        return $this->formatRow($this->_buildSqlExp());
    }

    public function buildGrid()
    {
        return array_map(array($this, 'formatRow'), $this->_buildGrid());
    }

    /**
     * Escape/format an array of row data
     * @param array $row
     * @return string
     */
    protected function formatRow($row)
    {
        return array_map('strip_tags', $this->arrayPluck($row));
    }

    public function deploy()
    {
        $this->setRecordsPerPage(0);
        parent::deploy();

        header('Content-Type', 'application/json');

        $grid = array(
            'titles' => $this->buildTitles(),
            'rows'   => $this->buildGrid(),
            'sqlexp' => $this->buildSqlexp()
        );

        die(Zend_Json::encode($grid));
    }
}
