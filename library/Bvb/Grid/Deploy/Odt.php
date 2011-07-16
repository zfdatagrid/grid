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

class Bvb_Grid_Deploy_Odt extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    public $templateInfo;

    private $inicialDir;

    protected $templateDir;

    public function __construct (array $options = array())
    {
        if(!class_exists('ZipArchive'))
        {
            throw new Bvb_Grid_Exception('Class ZipArchive not available. Check www.php.net/ZipArchive for more information');
        }

        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->addTemplateDir('Bvb/Grid/Template', 'Bvb_Grid_Template', 'odt');
    }

    public function deploy()
    {
        $this->checkExportRights();
        $this->setRecordsPerPage(0);

        parent::deploy();

        if (! $this->_temp['odt'] instanceof Bvb_Grid_Template_Odt) {
            $this->setTemplate('odt', 'odt');
        }

        $this->templateInfo = $this->_temp['odt']->options;

        if (! isset($this->_deploy['title'])) {
            $this->_deploy['title'] = '';
        }

        if (! isset($this->_deploy['subtitle'])) {
            $this->_deploy['subtitle'] = '';
        }

        if (! isset($this->_deploy['logo'])) {
            $this->_deploy['logo'] = '';
        }

        if (! isset($this->_deploy['footer'])) {
            $this->_deploy['footer'] = '';
        }

        if (! isset($this->_deploy['save'])) {
            $this->_deploy['save'] = false;
        }

        if (! isset($this->_deploy['download'])) {
            $this->_deploy['download'] = false;
        }

        if ($this->_deploy['save'] != 1 && $this->_deploy['download'] != 1) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/';

        $this->inicialDir = $this->_deploy['dir'];

        if (empty($this->_deploy['name'])) {
            $this->_deploy['name'] = date('H_m_d_H_i_s');
        }

        if (substr($this->_deploy['name'], - 5) == '.docx') {
            $this->_deploy['name'] = substr($this->_deploy['name'], 0, - 5);
        }

        if (! is_dir($this->_deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not a dir');
        }

        if (! is_writable($this->_deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->_deploy['dir'] . ' is not writable');
        }

        $this->templateDir = explode('/', $this->templateInfo['dir']);
        array_pop($this->templateDir);

        $this->templateDir = ucfirst(end($this->templateDir));

        $this->_deploy['dir'] = rtrim($this->_deploy['dir'], '/') . '/' . ucfirst($this->_deploy['name']) . '/';

        if (! defined(APPLICATION_PATH)) {
            $pathTemplate = substr($this->templateInfo['dir'], 0, - 4) . '/';
        } else {
            $pathTemplate = APPLICATION_PATH . '/../' . rtrim($this->getLibraryDir(), '/') . '/' . substr($this->templateInfo['dir'], 0, - 4) . '/';
        }

        Bvb_Grid_Deploy_Helper_File::deldir($this->_deploy['dir']);

        Bvb_Grid_Deploy_Helper_File::copyDir($pathTemplate, $this->_deploy['dir']);

        $xml = $this->_temp['odt']->globalStart();

        $titles = parent::_buildTitles();

        $wsData = parent::_buildGrid();
        $sql = parent::_buildSqlExp();

        #HEADER
        if (file_exists($this->_deploy['logo'])) {
            $explode = explode("/", $this->_deploy['logo']);
            copy($this->_deploy['logo'], $this->dir . 'Pictures/' . end($explode));
        }

        $header = str_replace(array('{{title}}', '{{subtitle}}', '{{footer}}'), array($this->_deploy['title'], $this->_deploy['subtitle'], $this->_deploy['footer']), $this->_temp['odt']->header());

        file_put_contents($this->_deploy['dir'] . "styles.xml", $header);

        #START DOCUMENT.XML
        $xml = $this->_temp['odt']->globalStart();
        $xml .= $this->_temp['odt']->titlesStart();

        foreach ($titles as $value) {
            if ((isset($value['field']) && $value['field'] !=$this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                $xml .= str_replace("{{value}}",  utf8_encode($value['value']), $this->_temp['odt']->titlesLoop());
            }
        }
        $xml .= $this->_temp['odt']->titlesEnd();

        if (is_array($wsData)) {
            if ($this->getInfo('hRow,title') != '') {
                $bar = $wsData;

                $hbar = trim($this->getInfo('hRow,field'));

                $p = 0;
                foreach ($wsData[0] as $value) {
                    if (isset($value['field']) && $value['field'] == $hbar) {
                        $hRowIndex = $p;
                    }

                    $p ++;
                }
                $aa = 0;
            }

            $i = 1;
            $aa = 0;
            foreach ($wsData as $row) {
                //A linha horizontal
                if ($this->getInfo('hRow,title') != '') {
                    if (@$bar[$aa][$hRowIndex]['value'] != @$bar[$aa - 1][$hRowIndex]['value']) {
                        $xml .= str_replace("{{value}}", utf8_encode(@$bar[$aa][$hRowIndex]['value']), $this->_temp['odt']->hRow());
                    }
                }

                $xml .= $this->_temp['odt']->loopStart();

                $a = 1;

                foreach ($row as $value) {
                    $value['value'] = strip_tags($value['value']);

                    if ((isset($value['field']) && $value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                        $xml .= str_replace("{{value}}",  utf8_encode($value['value']), $this->_temp['odt']->loopLoop());
                    }
                    $a ++;
                }
                $xml .= $this->_temp['odt']->loopEnd();
                $aa ++;
                $i ++;
            }
        }

        if (is_array($sql)) {
            $xml .= $this->_temp['odt']->sqlExpStart();
            foreach ($sql as $value) {
                $xml .= str_replace("{{value}}", utf8_encode( $value['value']), $this->_temp['odt']->sqlExpLoop());
            }
            $xml .= $this->_temp['odt']->sqlExpEnd();
        }

        $xml .= $this->_temp['odt']->globalEnd();

        file_put_contents($this->_deploy['dir'] . "content.xml", $xml);

        $final = Bvb_Grid_Deploy_Helper_File::scan_directory_recursively($this->_deploy['dir']);
        $f = explode('|', Bvb_Grid_Deploy_Helper_File::zipPaths($final));
        array_pop($f);

        $zip = new ZipArchive();
        $filename = $this->_deploy['dir'] . $this->_deploy['name'] . ".zip";

        if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
            exit("cannot open <$filename>\n");
        }

        foreach ($f as $value) {
            $zip->addFile($value, str_replace($this->_deploy['dir'], '', $value));
        }

        $zip->close();

        rename($filename, $this->inicialDir . $this->_deploy['name'] . '.odt');

        if ($this->_deploy['download'] == 1) {
            header('Content-type: application/vnd.oasis.opendocument.text');
            header('Content-Disposition: attachment; filename="' . $this->_deploy['name'] . '.odt"');
            readfile($this->inicialDir . $this->_deploy['name'] . '.odt');
        }

        if ($this->_deploy['save'] != 1) {
            unlink($this->inicialDir . $this->_deploy['name'] . '.odt');
        }

        Bvb_Grid_Deploy_Helper_File::deldir($this->_deploy['dir']);

        die();
    }
}
