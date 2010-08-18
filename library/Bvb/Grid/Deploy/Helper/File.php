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

class Bvb_Grid_Deploy_Helper_File
{

    /**
     * @param string $directory
     * @param unknown_type $filter
     * @return unknown
     */
    public static function scan_directory_recursively ($directory, $filter = FALSE)
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
                            'content' => self::scan_directory_recursively($path, $filter));

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
            if (is_dir($dir . '/' . $entryname) and ($entryname != "." and $entryname != "..")) {
                Bvb_Grid_Deploy_Helper_File::deldir($dir . '/' . $entryname);
            } elseif ($entryname != "." and $entryname != "..") {
                @unlink($dir . '/' . $entryname);
            }
        }
        @closedir($current_dir);
        @rmdir($dir);
    }

    /**
     *
     * @param unknown_type $dirs
     * @return unknown
     */
    public static function zipPaths ($dirs)
    {
        foreach ($dirs as $key => $value) {
            if (! is_array(@$value['content'])) {
                @$file .= $value['path'];
            } else {
                @$file .= self::zipPaths($value['content']);
            }
        }
        return $file;
    }

    /**
     *
     * @param unknown_type $source
     * @param unknown_type $dest
     * @return unknown
     */
    public static function copyDir ($source, $dest)
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

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..' || $entry == '.svn') {
                continue;
            }

            // copiar directorios
            if ($dest !== "$source/$entry") {
                self::copyDir("$source/$entry", "$dest/$entry");
            }
        }

        $dir->close();
        return true;
    }


}