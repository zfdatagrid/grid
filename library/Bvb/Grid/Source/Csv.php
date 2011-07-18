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

class Bvb_Grid_Source_Csv extends Bvb_Grid_Source_Array
{
    /**
     * Data source
     *
     * @var array
     */
    protected $_dataSource;

    /**
     * Source columns
     *
     * @var type
     */
    protected $_columns;

    /**
     * Source separator
     *
     * @var type
     */
    protected $_separator;

    public function __construct ($dataSource, $columns = null, $separator = ',')
    {
        $final = array();

        if (!is_file($dataSource)) {
            throw new Bvb_Grid_Exception('Could not find file: ' . $dataSource);
        }

        if (! is_readable($dataSource)) {
            throw new Bvb_Grid_Exception('Could not read file: ' . $dataSource);
        }

        $row = 0;
        $handle = fopen($dataSource, "r");
        while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
            $num = count($data);
            if (null !== $columns) {
                $field = $columns;

                for ($c = 0; $c < $num; $c ++) {
                    if ($c == 0) {
                        $final[$row]['_zfgId'] = $row;
                        $final[$row][$columns[$c]] = $data[$c];
                    } else {
                        $final[$row][$columns[$c]] = $data[$c];
                    }
                }
            } else {
                if ($row == 0) {
                    for ($c = 0; $c < $num; $c ++) {
                        $field[] = $data[$c];
                    }
                } else {
                    for ($c = 0; $c < $num; $c ++) {
                        if ($c == 0) {
                            $final[$row - 1]['_zfgId'] = $row;
                            $final[$row - 1][$field[$c]] = $data[$c];
                        } else {
                            $final[$row - 1][$field[$c]] = $data[$c];
                        }
                    }
                }
            }
            $row ++;
        }
        fclose($handle);

        $this->_dataSource = $dataSource;
        $this->_columns = $columns;
        $this->_separator = $separator;

        if ($this->_columns !== null) {
            array_unshift($this->_columns, 'zfgId');

            foreach ($final as $key => $value) {
                $final[$key] = array_combine($this->_columns, $value);
            }
        } else {
            array_unshift($field, '_zfgId');
        }

        $this->_fields = $field;
        $this->_rawResult = $final;
        $this->_sourceName = 'csv';

        unset($field);
        unset($final);

        return true;
    }

    /**
     *Insert an array of key=>values in the specified table
     * @param string $table
     * @param array $post
     */
    public function insert ($table, array $post)
    {
        $fp = fopen($this->_dataSource, 'a');
        $result = "\n" . '"' . implode('"' . $this->_separator . '"', $post) . '"';
        fwrite($fp, $result);
        fclose($fp);
    }

    /**
     *Update values in a table using the $condition clause
     *
     *The condition clause is a $field=>$value array
     *that should be escaped by YOU (if your class doesn't do that for you)
     * and using the AND operand
     *
     *Ex: array('user_id'=>'1','id_site'=>'12');
     *
     *Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $post
     * @param array $condition
     */
    public function update ($table, array $post, array $condition)
    {
        $filename = $this->_dataSource;

        $filesize = filesize($filename);

        $result = '"' . implode('"' . $this->_separator . '"', $post) . '"';
        $file = file($this->_dataSource);

        $position = $condition['_zfgId'] - 1;

        if ($this->_columns === null) {
            $position ++;
        }
        $file[$position] = $result . "\n";

        file_put_contents($this->_dataSource, implode($file, ''));
    }

    /**
     * Delete a record from a table
     *
     * The condition clause is a $field=>$value array
     * that should be escaped by YOU (if your class doesn't do that for you)
     * and using the AND operand
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $condition
     */
    public function delete ($table, array $condition)
    {
        $filename = $this->_dataSource;

        $filesize = filesize($filename);

        $file = file($this->_dataSource);
        $position = $condition['_zfgId'] - 1;
        if ($this->_columns === null) {
            $position ++;
        }
        unset($file[$position]);
        file_put_contents($this->_dataSource, implode($file, ''));
    }

     /**
     * Returns record details
     *
     * @param string $table
     * @param array  $condition
     *
     * @return mixed
     */
    public function getRecord ($table, array $condition)
    {
        $position = $condition['_zfgId'];
        if ($this->_columns === null) {
            $position--;
        }

        return $this->_rawResult[$position];
    }

    /**
     * If this adapter supports crud operations
     *
     * @return bool
     */
    public function hasCrud ()
    {
        return is_writable($this->_dataSource) ? true : false;
    }
}