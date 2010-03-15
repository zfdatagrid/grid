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

class Bvb_Grid_Deploy_Wordx extends Bvb_Grid implements Bvb_Grid_Deploy_Interface
{

    const OUTPUT = 'wordx';

    public $templateInfo;

    public $wordInfo;

    public $style;

    public $deploy;

    private $inicialDir;

    protected $templateDir;


    function __construct ($options)
    {

        if ( ! class_exists('ZipArchive') ) {
            throw new Bvb_Grid_Exception('Class ZipArchive not available. Check www.php.net/ZipArchive for more information');
        }

        if ( ! in_array(self::OUTPUT, $this->_export) ) {
            echo $this->__("You dont' have permission to export the results to this format");
            die();
        }

        $this->_setRemoveHiddenFields(true);
        parent::__construct($options);

        $this->addTemplateDir('Bvb/Grid/Template/Wordx', 'Bvb_Grid_Template_Wordx', 'wordx');

    }


    /**
     * [PT] Fazer o scan recursivo dos dir
     *
     * @param string $directory
     * @param unknown_type $filter
     * @return unknown
     */
    function scan_directory_recursively ($directory, $filter = FALSE)
    {

        // if the path has a slash at the end we remove it here
        $directory = rtrim($directory, '/');
        $directory_tree = array();

        // if the path is not valid or is not a directory ...
        if ( ! file_exists($directory) || ! is_dir($directory) ) {
            // ... we return false and exit the function
            return FALSE;

        // ... else if the path is readable
        } elseif ( is_readable($directory) ) {
            // we open the directory
            $directory_list = opendir($directory);

            // and scan through the items inside
            while (FALSE !== ($file = readdir($directory_list))) {
                // if the filepointer is not the current directory
                // or the parent directory
                if ( $file != '.' && $file != '..' && $file != '.DS_Store' ) {
                    // we build the new path to scan
                    $path = $directory . '/' . $file;

                    // if the path is readable
                    if ( is_readable($path) ) {
                        // we split the new path by directories
                        $subdirectories = explode('/', $path);

                        // if the new path is a directory
                        if ( is_dir($path) ) {
                            // add the directory details to the file list
                            $directory_tree[] = array('path' => $path . '|',

                            // we scan the new path by calling this function
                            'content' => $this->scan_directory_recursively($path, $filter));

                        // if the new path is a file
                        } elseif ( is_file($path) ) {
                            // get the file extension by taking everything after the last dot
                            $extension = end($subdirectories);
                            $extension = explode('.', $extension);
                            $extension = end($extension);

                            // if there is no filter set or the filter is set and matches
                            if ( $filter === FALSE || $filter == $extension ) {
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

    function deldir ($dir)
    {

        $current_dir = @opendir($dir);
        while ($entryname = @readdir($current_dir)) {
            if ( is_dir($dir . '/' . $entryname) and ($entryname != "." and $entryname != "..") ) {
                $this->deldir($dir . '/' . $entryname);
            } elseif ( $entryname != "." and $entryname != ".." ) {
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
    function zipPaths ($dirs)
    {

        foreach ( $dirs as $key => $value ) {
            if ( ! is_array(@$value['content']) ) {
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
    function copyDir ($source, $dest)
    {

        // Se for ficheiro
        if ( is_file($source) ) {
            $c = copy($source, $dest);
            chmod($dest, 0777);
            return $c;
        }

        // criar directorio de destino
        if ( ! is_dir($dest) ) {
            mkdir($dest, 0777, 1);
        }

        // Loop
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {

            if ( $entry == '.' || $entry == '..' || $entry == '.svn' ) {
                continue;
            }

            // copiar directorios
            if ( $dest !== "$source/$entry" ) {
                $this->copyDir("$source/$entry", "$dest/$entry");
            }
        }

        // sair
        $dir->close();
        return true;

    }


    function deploy ()
    {

        $this->setPagination(0);

        parent::deploy();

        if ( ! $this->_temp['wordx'] instanceof Bvb_Grid_Template_Wordx_Wordx ) {
            $this->setTemplate('wordx', 'wordx');
        }

        $this->templateInfo = $this->_temp['wordx']->options;


        if ( ! isset($this->deploy['title']) ) {
            $this->deploy['title'] = '';
        }

        if ( ! isset($this->deploy['subtitle']) ) {
            $this->deploy['subtitle'] = '';
        }

        if ( ! isset($this->deploy['logo']) ) {
            $this->deploy['logo'] = '';
        }

        if ( ! isset($this->deploy['footer']) ) {
            $this->deploy['footer'] = '';
        }

        if ( ! isset($this->deploy['save']) ) {
            $this->deploy['save'] = false;
        }

        if ( ! isset($this->deploy['download']) ) {
            $this->deploy['download'] = false;
        }

        if ( $this->deploy['save'] != 1 && $this->deploy['download'] != 1 ) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';


        $this->inicialDir = $this->deploy['dir'];

        if ( empty($this->deploy['name']) ) {
            $this->deploy['name'] = date('H_m_d_H_i_s');
        }

        if ( substr($this->deploy['name'], - 5) == '.docx' ) {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 5);
        }

        if ( ! is_dir($this->deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if ( ! is_writable($this->deploy['dir']) ) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        $this->templateDir = explode('/', $this->deploy['dir']);
        array_pop($this->templateDir);

        $this->templateDir = ucfirst(end($this->templateDir));

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/' . ucfirst($this->deploy['name']) . '/';

        if ( ! defined('APPLICATION_PATH') ) {
            $pathTemplate = rtrim($this->getLibraryDir(), '/') . '/' . substr($this->templateInfo['dir'], 0, - 4) . '/';
        } else {
            $pathTemplate = APPLICATION_PATH . '/../' . rtrim($this->getLibraryDir(), '/') . '/' . substr($this->templateInfo['dir'], 0, - 4) . '/';
        }


        $this->deldir($this->deploy['dir']);

        $this->copyDir($pathTemplate, $this->deploy['dir']);

        $xml = $this->_temp['wordx']->globalStart();

        $titles = parent::_buildTitles();
        $wsData = parent::_buildGrid();
        $sql = parent::_buildSqlExp();

        /////////////////////////
        /////////////////////////
        # HEADER
        if ( file_exists($this->deploy['logo']) ) {
            $data = explode("/", $this->deploy['logo']);
            copy($this->deploy['logo'], $this->deploy['dir'] . 'word/media/' . end($data));

            $logo = $this->_temp['wordx']->logo();

            file_put_contents($this->dir . "word/_rels/header1.xml.rels", $logo);

            $header = str_replace(array('{{title}}', '{{subtitle}}'), array($this->deploy['title'], $this->deploy['subtitle']), $this->_temp['wordx']->header());

        } else {

            $header = str_replace(array('{{title}}', '{{subtitle}}'), array($this->deploy['title'], $this->deploy['subtitle']), $this->_temp['wordx']->header());

        }

        file_put_contents($this->deploy['dir'] . "word/header1.xml", $header);

        /////////////////////////
        /////////////////////////
        #END HEADER



        #BEGIN FOOTER
        $footer = str_replace("{{value}}", $this->deploy['footer'], $this->_temp['wordx']->footer());
        file_put_contents($this->deploy['dir'] . "word/footer2.xml", $footer);
        #END footer



        #START DOCUMENT.XML
        $xml = $this->_temp['wordx']->globalStart();

        $xml .= $this->_temp['wordx']->titlesStart();

        foreach ( $titles as $value ) {

            if ( (isset($value['field']) && $value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {

                $xml .= str_replace("{{value}}", utf8_encode($value['value']), $this->_temp['wordx']->titlesLoop());

            }
        }
        $xml .= $this->_temp['wordx']->titlesEnd();

        if ( is_array($wsData) ) {

            /////////////////
            /////////////////
            /////////////////
            if ( $this->getInfo('hRow,title') != '' ) {
                $bar = $wsData;

                $hbar = trim($this->getInfo('hRow,title'));

                $p = 0;
                foreach ( $wsData[0] as $value ) {
                    if ( isset($value['field']) && $value['field'] == $hbar ) {
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
            foreach ( $wsData as $row ) {

                ////////////
                ////////////
                //A linha horizontal
                if ( @$this->getInfo('hRow,title') != '' ) {
                    if ( @$bar[$aa][$hRowIndex]['value'] != @$bar[$aa - 1][$hRowIndex]['value'] ) {
                        $xml .= str_replace("{{value}}", utf8_encode(@$bar[$aa][$hRowIndex]['value']), $this->_temp['wordx']->hRow());
                    }
                }
                ////////////
                ////////////



                $xml .= $this->_temp['wordx']->loopStart();

                $a = 1;

                foreach ( $row as $value ) {

                    $value['value'] = strip_tags($value['value']);

                    if ( (isset($value['field']) && $value['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '' ) {
                        $xml .= str_replace("{{value}}", utf8_encode($value['value']), $this->_temp['wordx']->loopLoop());

                    }
                    $a ++;

                }
                $xml .= $this->_temp['wordx']->loopEnd();
                $aa ++;
                $i ++;
            }
        }

        if ( is_array($sql) ) {
            $xml .= $this->_temp['wordx']->sqlExpStart();
            foreach ( $sql as $value ) {
                $xml .= str_replace("{{value}}", utf8_encode($value['value']), $this->_temp['wordx']->sqlExpLoop());
            }
            $xml .= $this->_temp['wordx']->sqlExpEnd();
        }

        $xml .= $this->_temp['wordx']->globalEnd();

        file_put_contents($this->deploy['dir'] . "word/document.xml", $xml);

        $final = $this->scan_directory_recursively($this->deploy['dir']);
        $f = explode('|', $this->zipPaths($final));
        array_pop($f);

        $zip = new ZipArchive();
        $filename = $this->deploy['dir'] . $this->deploy['name'] . ".zip";

        if ( $zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE ) {
            exit("cannot open <$filename>\n");
        }

        foreach ( $f as $value ) {
            $zip->addFile($value, str_replace($this->deploy['dir'], '', $value));
        }

        $zip->close();

        rename($filename, $this->inicialDir . $this->deploy['name'] . '.docx');

        if ( $this->deploy['download'] == 1 ) {
            header('Content-type: application/word');
            header('Content-Disposition: attachment; filename="' . $this->deploy['name'] . '.docx"');
            readfile($this->inicialDir . $this->deploy['name'] . '.docx');
        }

        if ( $this->deploy['save'] != 1 ) {
            unlink($this->inicialDir . $this->deploy['name'] . '.docx');
        }

        $this->deldir($this->deploy['dir']);

        die();
    }

}




