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


    public function __construct ($options)
    {

        if(!class_exists('ZipArchive'))
        {
            throw new Bvb_Grid_Exception('Class ZipArchive not available. Check www.php.net/ZipArchive for more information');
        }

        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->addTemplateDir('Bvb/Grid/Template/Odt', 'Bvb_Grid_Template_Odt', 'odt');
    }


    /**
     *
     * @param string $directory
     * @param unknown_type $filter
     * @return unknown
     */
    public function scan_directory_recursively ($directory, $filter = FALSE)
    {

        // if the path has a slash at the end we remove it here
        $directory = rtrim($directory, '/');
        $directory_tree = array();

        // if the path is not valid or is not a directory ...
        if (! file_exists($directory) || ! is_dir($directory)) {
            // ... we return false and exit the function
            return FALSE;

        // ... else if the path is readable
        } elseif (is_readable($directory)) {
            // we open the directory
            $directory_list = opendir($directory);

            // and scan through the items inside
            while (FALSE !== ($file = readdir($directory_list))) {
                // if the filepointer is not the current directory
                // or the parent directory
                if ($file != '.' && $file != '..' && $file != '.DS_Store') {
                    // we build the new path to scan
                    $path = $directory . '/' . $file;

                    // if the path is readable
                    if (is_readable($path)) {
                        // we split the new path by directories
                        $subdirectories = explode('/', $path);

                        // if the new path is a directory
                        if (is_dir($path)) {
                            // add the directory details to the file list
                            $directory_tree[] = array('path' => $path . '|',

                            // we scan the new path by calling this function
                            'content' => $this->scan_directory_recursively($path, $filter));

                        // if the new path is a file
                        } elseif (is_file($path)) {
                            // get the file extension by taking everything after the last dot
                            $extension = end($subdirectories);
                            $extension = explode('.', $extension);
                            $extension = end($extension);

                            // if there is no filter set or the filter is set and matches
                            if ($filter === FALSE || $filter == $extension) {
                                // add the file details to the file list
                                $directory_tree[] = array('path' => $path . '|', 'name' => end($subdirectories));
                            }
                        }
                    }
                }
            }
            // close the directory
            closedir($directory_list);

            // return file list
            return $directory_tree;

        // if the path is not readable ...
        } else {
            // ... we return false
            return FALSE;
        }
    }


    // ------------------------------------------------------------





    /**
     * [PT] Remove direcotiros e subdirectorios
     *
     * @param string $dir
     */

    public function deldir ($dir)
    {

        $current_dir = @opendir($dir);
        while ($entryname = @readdir($current_dir)) {
            if (is_dir($dir . '/' . $entryname) and ($entryname != "." and $entryname != "..")) {
                $this->deldir($dir . '/' . $entryname);
            } elseif ($entryname != "." and $entryname != "..") {
                @unlink($dir . '/' . $entryname);
            }
        }
        @closedir($current_dir);
        @rmdir($dir);
    }


    /**
     * [PT] Ir buscar os caminhos para depois zipar
     *
     * @param unknown_type $dirs
     * @return unknown
     */
    public function zipPaths ($dirs)
    {

        foreach ($dirs as $key => $value) {
            if (! is_array(@$value['content'])) {
                @$file .= $value['path'];
            } else {
                @$file .= $this->zipPaths($value['content']);
            }
        }
        return $file;
    }


    /**
     * [PT] TEMOS que copiar os directórtio para a  loalização final
     *
     * @param unknown_type $source
     * @param unknown_type $dest
     * @return unknown
     */
    public function copyDir ($source, $dest)
    {

        // Se for ficheiro
        if (is_file($source)) {
            $c = copy($source, $dest);
            chmod($dest, 0777);
            return $c;
        }

        // criar directorio de destino
        if (! is_dir($dest)) {
            mkdir($dest, 0777, 1);
        }

        // Loop
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {

            if ($entry == '.' || $entry == '..' || $entry == '.svn') {
                continue;
            }

            // copiar directorios
            if ($dest !== "$source/$entry") {
                $this->copyDir("$source/$entry", "$dest/$entry");
            }
        }

        // sair
        $dir->close();
        return true;

    }


    public function deploy ()
    {

        if ( ! in_array($this->_deployName, $this->_export) && !array_key_exists($this->_deployName,$this->_export) ) {
            throw new Bvb_Grid_Exception($this->__("You dont' have permission to export the results to this format"));
        }

        $this->setRecordsPerPage(0);

        parent::deploy();

        if (! $this->_temp['odt'] instanceof Bvb_Grid_Template_Odt_Odt) {
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

        if (! defined('APPLICATION_PATH')) {
            $pathTemplate = substr($this->templateInfo['dir'], 0, - 4) . '/';
        } else {
            $pathTemplate = APPLICATION_PATH . '/../' . rtrim($this->getLibraryDir(), '/') . '/' . substr($this->templateInfo['dir'], 0, - 4) . '/';
        }

        $this->deldir($this->_deploy['dir']);

        $this->copyDir($pathTemplate, $this->_deploy['dir']);

        $xml = $this->_temp['odt']->globalStart();


        $titles = parent::_buildTitles();

        #$nome = reset ( $titles );
        $wsData = parent::_buildGrid();
        $sql = parent::_buildSqlExp();


        /////////////////////////
        /////////////////////////
        #HEADER

        if (file_exists($this->_deploy['logo'])) {
            $explode = explode("/", $this->_deploy['logo']);
            copy($this->_deploy['logo'], $this->dir . 'Pictures/' . end($explode));
        }


        $header = str_replace(array('{{title}}', '{{subtitle}}', '{{footer}}'), array($this->_deploy['title'], $this->_deploy['subtitle'], $this->_deploy['footer']), $this->_temp['odt']->header());

        file_put_contents($this->_deploy['dir'] . "styles.xml", $header);


        /////////////////////////
        /////////////////////////
        #END HEADER

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

            /////////////////
            /////////////////
            /////////////////
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

            //////////////
            //////////////
            //////////////



            $i = 1;
            $aa = 0;
            foreach ($wsData as $row) {

                ////////////
                ////////////
                //A linha horizontal
                if ($this->getInfo('hRow,title') != '') {
                    if (@$bar[$aa][$hRowIndex]['value'] != @$bar[$aa - 1][$hRowIndex]['value']) {
                        $xml .= str_replace("{{value}}", utf8_encode(@$bar[$aa][$hRowIndex]['value']), $this->_temp['odt']->hRow());
                    }
                }
                ////////////
                ////////////


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

        $final = $this->scan_directory_recursively($this->_deploy['dir']);
        $f = explode('|', $this->zipPaths($final));
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

        $this->deldir($this->_deploy['dir']);

        die();
    }

}




