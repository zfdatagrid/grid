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
abstract class Bvb_Grid_Source_Db_DbAbstract {

    /**
     * Builds form elements based on field definition
     *
     * @param array $elements
     *
     * @return array
     */
    public function buildFormElementsFromArray(array $elements)
    {
        $form = array();

        foreach ($elements as $column => $detail) {
            $label = $detail['label'];
            $required = isset($detail['required']) ? $detail['required'] : false;
            $default = isset($detail['default']) ? $detail['default'] : '';
            $length = isset($detail['length']) ? $detail['length'] : false;

            switch ($detail['type']) {
                case 'select':
                    $form['elements'][$column] = array('select', array('multiOptions' => $default,
                            'required' => $required,
                            'label' => $label));
                    break;

                case 'multiSelect':
                    $form['elements'][$column] = array('select',
                                                        array('multiOptions' => $default,
                                                              'required' => $required,
                                                              'label' => $label));
                    break;

                case 'smallText':
                    $form['elements'][$column] = array('text',
                                                       array('validators' => array(array('stringLength',
                                                                                         false,
                                                                                         array(0, $length))),
                                                       'size' => 40,
                                                       'label' => $label,
                                                       'required' => $required,
                                                       'value' => $default));
                    break;

                case 'date':
                    $form['elements'][$column] = array('text',
                                                       array('validators' => array(array('Date')),
                                                       'size' => 10,
                                                       'label' => $label,
                                                       'required' => $required,
                                                       'value' => $default));
                    break;
                case 'time':
                    $form['elements'][$column] = array('text',
                                                       array('validators' => array(
                                                                array(new Zend_Validate_Date('H:i:s'))),
                                                       'size' => 19,
                                                       'label' => $label,
                                                       'required' => $required,
                                                       'value' => $default));
                    break;
                case 'datetime':
                    $form['elements'][$column] = array('text',
                                                       array('validators' => array(
                                                                array(new Zend_Validate_Date('Y-m-d H:i:s'))),
                                                       'size' => 19,
                                                       'label' => $label,
                                                       'required' => $required,
                                                       'value' => $default));
                    break;

                case 'longtext':
                    $form['elements'][$column] = array('textarea',
                                                       array('label' => $label,
                                                       'required' => $required));
                    break;

                case 'number':
                    $form['elements'][$column] = array('text',
                                                       array('validators' => array('Digits'),
                                                       'label' => $label,
                                                       'size' => 10,
                                                       'required' => $required,
                                                       'value' => $default));
                    break;

                case 'decimal':
                    $form['elements'][$column] = array('text',
                                                       array('validators' => array('Float'),
                                                             'size' => 10,
                                                             'label' => $label,
                                                             'required' => $required,
                                                             'value' => $default));
                    break;

                default:
                    break;
            }
        }

        return $form;
    }

}