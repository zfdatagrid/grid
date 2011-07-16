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
class Bvb_Grid_Deploy_Word extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->addTemplateDir('Bvb/Grid/Template', 'Bvb_Grid_Template', 'word');
    }

    public function deploy()
    {
        $this->checkExportRights();
        $this->setRecordsPerPage(0);

        parent::deploy();

        if (!$this->_temp['word'] instanceof Bvb_Grid_Template_Word) {
            $this->setTemplate('word', 'word');
        }

        $titles = parent::_buildTitles();
        $wsData = parent::_buildGrid();
        $sql    = parent::_buildSqlExp();

        $xml = $this->_temp['word']->globalStart();

        $xml .= $this->_temp['word']->titlesStart();

        foreach ($titles as $value) {
            if (($value ['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                $xml .= str_replace("{{value}}", $value ['value'], $this->_temp['word']->titlesLoop());
            }
        }
        $xml .= $this->_temp['word']->titlesEnd();

        if (is_array($wsData)) {
            if ($this->getInfo('hRow,title') != '') {
                $bar = $wsData;

                $hbar = trim($this->getInfo('hRow,title'));

                $p = 0;
                foreach ($wsData [0] as $value) {
                    if ($value ['field'] == $hbar) {
                        $hRowIndex = $p;
                    }

                    $p++;
                }
                $aa = 0;
            }

            $i = 1;
            $aa = 0;
            foreach ($wsData as $row) {
                //A linha horizontal
                if ($this->getInfo('hRow,title') != '') {
                    if (!isset($bar[$aa - 1][$hRowIndex])) {
                        $bar[$aa - 1][$hRowIndex]['value'] = '';
                    }

                    if ($bar [$aa][$hRowIndex]['value'] != $bar [$aa - 1][$hRowIndex]['value']) {
                        $xml .= str_replace("{{value}}", $bar [$aa][$hRowIndex]['value'], $this->_temp['word']->hRow());
                    }
                }

                $xml .= $this->_temp['word']->loopStart();
                $a = 1;
                foreach ($row as $value) {
                    $value ['value'] = strip_tags($value ['value']);

                    if ((@$value ['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                        $xml .= str_replace("{{value}}", $value ['value'], $this->_temp['word']->loopLoop(2));
                    }
                    $a++;
                }
                $xml .= $this->_temp['word']->loopEnd();
                $aa++;
                $i++;
            }
        }

        if (is_array($sql)) {
            $xml .= $this->_temp['word']->sqlExpStart();
            foreach ($sql as $value) {
                $xml .= str_replace("{{value}}", $value ['value'], $this->_temp['word']->sqlExpLoop());
            }
            $xml .= $this->_temp['word']->sqlExpEnd();
        }

        $xml .= $this->_temp['word']->globalEnd();

        if (!isset($this->_deploy['save'])) {
            $this->_deploy['save'] = false;
        }

        if (!isset($this->_deploy['download'])) {
            $this->_deploy['download'] = false;
        }

        if ($this->_deploy['save'] != 1 && $this->_deploy['download'] != 1) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }

        if (empty($this->_deploy['name'])) {
            $this->_deploy['name'] = date('H_m_d_H_i_s');
        }

        if (substr($this->_deploy['name'], - 4) == '.doc') {
            $this->_deploy['name'] = substr($this->_deploy['name'], 0, - 4);
        }

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/';

        if (!is_dir($this->_deploy['dir']) && $this->_deploy['save']==1) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not a dir');
        }

        if (!is_writable($this->_deploy['dir']) && $this->_deploy['save']==1) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not writable');
        }


        if ( $this->_deploy['save'] == 1 ) {
            file_put_contents($this->_deploy['dir'] . $this->_deploy['name'] . ".doc", $xml);
        }


        if ($this->_deploy['download'] == 1) {
            header('Content-type: application/word');
            header('Content-Disposition: attachment; filename="' . $this->_deploy['name'] . '.doc"');
            echo $xml;
        }

        die ();
    }
}
