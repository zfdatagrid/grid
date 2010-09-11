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
 * @copyright 2010 unknown (web result)
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */

class Bvb_Grid_Deploy_Helper_File
{


    /**
     * @param string $directory
     * @param mixed $filter
     * @return mixed
     */
    public static function scan_directory_recursively ($directory, $filter = FALSE)
    {
        $directory = rtrim($directory, '/');
        $directory_tree = array();

        if ( ! file_exists($directory) || ! is_dir($directory) ) {
            return FALSE;

        } elseif ( is_readable($directory) ) {
            $directory_list = opendir($directory);

            while (FALSE !== ($file = readdir($directory_list))) {
                if ( $file != '.' && $file != '..' && $file != '.DS_Store' ) {
                    $path = $directory . '/' . $file;

                    if ( is_readable($path) ) {
                        $subdirectories = explode('/', $path);

                        if ( is_dir($path) ) {
                            $directory_tree[] = array('path' => $path . '|',

                            'content' => self::scan_directory_recursively($path, $filter));

                        } elseif ( is_file($path) ) {
                            $extension = end($subdirectories);
                            $extension = explode('.', $extension);
                            $extension = end($extension);

                            if ( $filter === FALSE || $filter == $extension ) {
                                $directory_tree[] = array('path' => $path . '|', 'name' => end($subdirectories));
                            }
                        }
                    }
                }
            }
            closedir($directory_list);

            return $directory_tree;
        }

        return false;
    }


    /**
     *
     * @param string $dir
     */
    public static function deldir ($dir)
    {
        $current_dir = @opendir($dir);
        while ($entryname = @readdir($current_dir)) {
            if ( is_dir($dir . '/' . $entryname) and ($entryname != "." and $entryname != "..") ) {
                self::deldir($dir . '/' . $entryname);
            } elseif ( $entryname != "." and $entryname != ".." ) {
                @unlink($dir . '/' . $entryname);
            }
        }
        @closedir($current_dir);
        @rmdir($dir);
    }


    /**
     *
     * @param array $dirs
     * @return array
     */
    public static function zipPaths ($dirs)
    {
        foreach ( $dirs as $key => $value ) {
            if ( ! is_array(@$value['content']) ) {
                @$file .= $value['path'];
            } else {
                @$file .= self::zipPaths($value['content']);
            }
        }
        return $file;
    }


    /**
     *
     * @param string $source
     * @param string $dest
     *
     * @return mixed
     */
    public static function copyDir ($source, $dest)
    {
        if ( is_file($source) ) {
            $c = copy($source, $dest);
            chmod($dest, 0777);
            return $c;
        }

        if ( ! is_dir($dest) ) {
            mkdir($dest, 0777, 1);
        }

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ( $entry == '.' || $entry == '..' || $entry == '.svn' ) {
                continue;
            }

            if ( $dest !== "$source/$entry" ) {
                self::copyDir("$source/$entry", "$dest/$entry");
            }
        }

        $dir->close();
        return true;
    }


}