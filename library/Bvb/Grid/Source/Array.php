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


class Bvb_Grid_Source_Array implements Bvb_Grid_Source_SourceInterface
{

    protected $_fields;

    protected $_rawResult;

    protected $_offset;

    protected $_start;

    protected $_totalRecords = 0;

    protected $_sourceName;

    protected $_cache;


    public function __construct (array $array, $titles = null)
    {
        $min = min(array_keys($array));
        if ( count($array) > 0 ) {
            if ( $titles === null || count($titles) != count($array[$min]) ) {
                $this->_fields = array_keys($array[$min]);
            } else {
                $this->_fields = $titles;
                foreach ( $array as $key => $value ) {
                    $array[$key] = array_combine($titles, $value);
                }
            }
        } elseif ( $titles !== null ) {
            $this->_fields = $titles;
        }


        $this->_rawResult = $array;
        $this->_sourceName = 'array';
    }



    public function resetOrder ()
    {
        return true;
    }


    public function getSourceName ()
    {
        return $this->_sourceName;
    }


    public function hasCrud ()
    {
        return false;
    }


    public function buildFields ()
    {
        $fields = $this->_fields;

        $final = array();

        foreach ( $fields as $value ) {


            if ( $value == '_zfgId' ) {
                $final[$value] = array('title' => ucfirst(str_replace('_', ' ', $value)), 'field' => $value, 'remove' => 1);
            } else {
                $final[$value] = array('title' => ucfirst(str_replace('_', ' ', $value)), 'field' => $value);
            }

        }

        return $final;
    }


    public function buildQueryLimit ($offset, $start)
    {
        $this->_offset = $offset;
        $this->_start = $start;
        return true;
    }


    public function addCondition ($filter, $op, $completeField)
    {
        $explode = explode('.', $completeField['field']);
        $field = end($explode);

        foreach ( $this->_rawResult as $key => $result ) {

            foreach ( $result as $fieldKey => $fieldValue ) {
                if ( strlen($filter) > 0 && $fieldKey == $field ) {
                    if ( ! $this->_applySearchType($fieldValue, $filter, $op) ) {
                        unset($this->_rawResult[$key]);
                    }
                }
            }
        }

        return true;
    }


    /**
     * Apply the search to a give field when the adaptar is an array
     */
    protected function _applySearchType ($final, $filtro, $op)
    {

        switch ($op) {
            case 'equal':
            case '=':
                if ( $filtro == $final ) return true;
                break;
            case 'REGEXP':
                if ( preg_match($filtro, $final) ) return true;
                break;
            case 'rlike':
                if ( substr($final, 0, strlen($filtro)) == $filtro ) return true;
                break;
            case 'llike':
                if ( substr($final, - strlen($filtro)) == $filtro ) return true;
                break;
            case '>=':
                if ( $final >= $filtro ) return true;
                break;
            case '>':
                if ( $final > $filtro ) return true;
                break;
            case '<>':
            case '!=':
                if ( $final != $filtro ) return true;
                break;
            case '<=':
                if ( $final <= $filtro ) return true;
                break;
            case '<':
                if ( $final < $filtro ) return true;
                break;
            default:
                $enc = stripos((string) $final, $filtro);
                if ( $enc !== false ) {
                    return true;
                }
                break;
        }

        return false;

    }


    public function getSelectOrder ()
    {
        return array();
    }


    public function getDescribeTable ()
    {
        return false;
    }


    public function getFilterValuesBasedOnFieldDefinition ($field)
    {
        return 'text';
    }


    public function getTotalRecords ()
    {
        return $this->_totalRecords;
    }


    public function execute ()
    {
        $this->_totalRecords = count($this->_rawResult);


        if ( $this->_offset == 0 ) {
            return array_slice($this->_rawResult, $this->_start);
        }


        return array_slice($this->_rawResult, $this->_start, $this->_offset);

    }


    public function getMainTable ()
    {
        return true;
    }


    public function getTableList ()
    {
        return array();
    }


    public function buildQueryOrder ($field, $order, $reset = false)
    {

        if ( strtolower($order) == 'desc' ) {
            $sort = SORT_DESC;
        } else {
            $sort = SORT_ASC;
        }

        // Obtain a list of columns
        foreach ( $this->_rawResult as $key => $row ) {
            $result[$key] = $row[$field];
        }

        array_multisort($result, $sort, $this->_rawResult);

        unset($result);
    }


    protected function _object2array ($data)
    {

        if ( ! is_object($data) && ! is_array($data) ) return $data;

        if ( is_object($data) ) $data = get_object_vars($data);

        return array_map(array($this, '_object2array'), $data);

    }


    public function getSqlExp (array $value, $where = array())
    {
        if ( is_array($value['functions']) ) {

            $i = 0;
            foreach ( $value['functions'] as $final ) {

                if ( $i == 0 ) {
                    $valor = $this->_applySqlExpToArray($value['value'], $final,null,$where);
                } else {
                    $valor = $this->_applySqlExpToArray($value['value'], $final, $valor,$where);
                }

                $i ++;
            }

        } else {
            $valor = $this->_applySqlExpToArray($valor['value'], $value['functions'],null,$where);
        }


        return $valor;
    }


    /**
     * Applies the SQL EXP options to an array
     * @param $field
     * @param $operation
     * @param $option
     */
    protected function _applySqlExpToArray ($field, $operation, $value = null,$where = array())
    {
        $field = trim($field);
        $array = array();

        if ( null === $value ) {
            foreach ( $this->_rawResult as $value ) {

                if( (count($where)>0 && $value[key($where)] == reset($where)) || count($where)==0)
                $array[] = $value[$field];
            }
        } else {
            $array = array($value);
        }

        $operation = trim(strtolower($operation));

        switch ($operation) {
            case 'product':
                return array_product($array);
                break;
            case 'sum':
                return array_sum($array);
                break;
            case 'count':
                return count($array);
                break;
            case 'min':
                sort($array);
                return array_shift($array);
                break;
            case 'max':
                sort($array);
                return array_pop($array);
                break;
            case 'avg':
                return round(array_sum($array) / count($array));
                break;
            default:
                throw new Bvb_Grid_Exception('Operation not found');
                break;
        }
    }


    public function getDistinctValuesForFilters ($field, $fieldValue,$order = 'name ASC')
    {

        $filter = array();
        foreach ( $this->_rawResult as $value ) {
            $filter[$value[$field]] = $value[$fieldValue];
        }

        return array_unique($filter);
    }


    public function fetchDetail (array $where)
    {

    }


    public function getFieldType ($field)
    {

    }


    public function getSelectObject ()
    {

    }


    public function addFullTextSearch ($filter, $field)
    {

    }


    public function getRecord ($table, array $condition)
    {

    }


    public function insert ($table, array $post)
    {

    }


    public function update ($table, array $post, array $condition)
    {

    }


    public function delete ($table, array $condition)
    {

    }


    public function setCache ($cache)
    {
        if ( ! is_array($cache) ) {
            $cache = array('use' => 0);
        }

        $this->_cache = $cache;
    }


     public function buildForm($fields = array())
    {

        $form = array();

        foreach ($this->_fields as $elements)
        {
            if($elements =='_zfgId')
            continue;

            $label = ucwords(str_replace('_', ' ', $elements));
            $form['elements'][$elements] = array('text', array( 'size' => 10, 'label' => $label));
        }


        return $form;
    }


    public function getPrimaryKey ($table)
    {
        if ( in_array('_zfgId', $this->_fields) ) {
            return array('_zfgId');
        }
        return false;
    }

    public function getMassActionsIds ($table)
    {

        throw new Exception('Not yet Implemented');

    }
}