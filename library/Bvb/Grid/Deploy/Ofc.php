<?php

/**
 * Mascker
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
 * @copyright  Copyright (c) Bento Vilas Boas (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Ofc extends Bvb_Grid implements Bvb_Grid_Deploy_Interface
{

    public $deploy = array();

    public static $url;

    const OUTPUT = 'ofc';

    /**
     * Contains result of deploy() function.
     *
     * @var string
     */
    protected $_deploymentContent = null;


    /**
     * Chart Type
     * @var string
     */
    protected $_type = 'Bar';


    /**
     * Labels for X Axis
     *
     * If string, then we use the column,
     * If array, we use the array
     * @var
     */
    protected $_xLabels = null;

    /**
     * Options for x Labels
     * @var unknown_type
     */

    protected $_xLabelsOptions = array();

    /**
     * Chart Args
     * @var array
     */
    protected $_typeArgs = array();


    /**
     * Values to show
     * @var unknown_type
     */
    protected $_values = array();

    /**
     * Options to be apllied to every set of values
     * @var array
     */
    protected $_chartOptionsValues = array();

    /**
     * Chart Title
     * @var string
     */
    protected $_title = '';

    /**
     * General Options for Graphs
     * @var array
     */
    protected $_chartOptions = array();


    /**
     * Char Dimensions
     * @var array
     */
    protected $_chartDimensions = array('x' => 200, 'y' => 120);


    /**
     * Chart Id
     * @var string
     */
    protected $_chartId = null;

    /**
     * LOcation for flash and js files
     * @var array
     */
    protected $_filesLocation = array();

    /**
     * View
     * @var unknown_type
     */
    protected $_view = null;


    /*
    * @param array $data
    */
    function __construct ($options)
    {
        if ( ! in_array(self::OUTPUT, $this->_export) ) {
            echo $this->__("You dont' have permission to export the results to this format");
            die();
        }
        $this->options = $options;

        if(!Zend_Loader_Autoloader::autoload('OFC_Chart'))
        {
            die("You must have Open Flash Chart installed in order to use this deploy. Please check this page for more information: http://code.google.com/p/zfdatagrid/wiki/Bvb_Grid_Deploy");
        }

        parent::__construct($options);
    }


    function deploy ()
    {

        if ( $this->_filesLocation === null ) {
            die('Please set Javascript and Flash file locations using SetFilesLocation()');
        }

        $grid = array();
        $newData = array();
        $label = array();
        $result = array();

        parent::deploy();

        $data = parent::_buildGrid();

        foreach ( $data as $value ) {
            foreach ( $value as $final ) {
                $result[$final['field']][] = strip_tags($final['value']);
            }
        }

        if ( is_string($this->_xLabels) && isset($result[$this->_xLabels]) ) {
            $this->_xLabels = $result[$this->_xLabels];
        }

        $graph = new OFC_Chart();
        $graph->set_title(new OFC_Elements_Title($this->_title));

        foreach ( $this->_chartOptions as $key => $value ) {
            $graph->$key($value);
        }

        if ( count($this->_xLabels) > 0 ) {
            $x = new OFC_Elements_Axis_X();
            $x->set_labels_from_array($this->_xLabels);
            foreach ( $this->_xLabelsOptions as $key => $value ) {
                $x->$key($value);
            }
            $graph->set_x_axis($x);
        }


        $min = 0;
        $max = 0;

        if ( count($this->_values) == 0 ) {
            $this->setValues(key($result));
        }

        foreach ( $this->_values as $key => $value ) {

            if ( is_array($value) ) {

                $support = $value;
                sort($support);
                if ( reset($support) < $min ) {
                    $min = reset($support);
                }
                if ( end($support) > $max ) {
                    $max = end($support);
                }
                unset($support);


                $options = $this->_chartOptionsValues[$value];
                if ( isset($options['chartType']) ) {
                    $this->setChartType($options['chartType']);
                }

                $bar = new $this->_type();


                foreach ( $options as $key => $prop ) {
                    $bar->$key($prop);
                }
                $this->_type();

                $pie = array();

                if ( $this->_type == 'Pie' ) {
                    foreach ( $value as $key => $title ) {
                        $pie[] = new OFC_Charts_Pie_Value($title, '09s');
                    }
                    $bar->set_values($pie);
                } else {
                    $bar->set_values($value);
                }

                $graph->add_element($bar);

            } elseif ( is_string($value) && isset($result[$value]) ) {


                $options = $this->_chartOptionsValues[$value];
                if ( isset($options['chartType']) ) {
                    $this->setChartType($options['chartType']);
                }

                $bar = new $this->_type();

                foreach ( $options as $key => $prop ) {
                    $bar->$key($prop);
                }

                $value = array_map(create_function('$item', ' return (int)$item; '), $result[$value]);

                $support = $value;
                sort($support);
                if ( reset($support) < $min ) {
                    $min = reset($support);
                }
                if ( end($support) > $max ) {
                    $max = end($support);
                }
                unset($support);

                $pie = array();
                if ( $this->_type == 'OFC_Charts_Pie' ) {
                    foreach ( $value as $key => $title ) {
                        $pie[] = new OFC_Charts_Pie_Value($title, 'iou');
                    }
                    $bar->set_values($pie);
                } else {
                    $bar->set_values($value);
                }

                $graph->add_element($bar);
            }

        }

        $max = $max * 1.05;

        $y = new OFC_Elements_Axis_Y();
        $y->set_range($min, $max, ceil($max / 4));


        $graph->add_y_axis($y);

        $final = $graph->toPrettyString();

        if ( ! is_string($this->_chartId) ) {
            $this->_chartId = 'chart_' . rand(1, 10000);
        }


        $script = '
        swfobject.embedSWF(
        "' . $this->_filesLocation['flash'] . '", "' . $this->_chartId . '",
        "' . $this->_chartDimensions['x'] . '", "' . $this->_chartDimensions['y'] . '", "9.0.0", "expressInstall.swf",{"id":"' . $this->_chartId . '"} );

        function open_flash_chart_data()
        {
            return JSON.stringify(data);
        }

        function findSWF(movieName) {
          if (navigator.appName.indexOf("Microsoft")!= -1) {
            return window[movieName];
          } else {
            return document[movieName];
          }
        }
        var data = ' . $final . '; ';

        $final = '<div id="' . $this->_chartId . '"></div>';

        $this->getView()->headScript()->appendFile($this->_filesLocation['js']);
        $this->getView()->headScript()->appendFile($this->_filesLocation['json']);
        $this->getView()->headScript()->appendScript($script);

        $this->_deploymentContent = $final;
        return $this;
    }


    function setXLabels ($labels, $options = array())
    {
        $this->_xLabels = $labels;
        $this->_xLabelsOptions = $options;
    }


    function setChartType ($type, $args = array())
    {
        $this->_type = (string) "OFC_Charts_" . implode('_', array_map('ucwords', explode('_', $type)));
        ;
        $this->_typeArgs = $args;
        return $this;
    }


    function setValues ($values, $options = array())
    {
        if ( ! is_string($values) ) {
            $name = $values[0];
        } else {
            $name = $values;
        }

        $this->_values = array();
        $this->_values[$name] = $values;
        $this->_chartOptionsValues[$name] = $options;
        return $this;
    }


    function addValues ($values, $options = array())
    {
        if ( ! is_string($values) ) {
            $name = $values[0];
        } else {
            $name = $values;
        }
        $this->_values[$name] = $values;
        $this->_chartOptionsValues[$name] = $options;

        return $this;
    }


    function getValues ($name)
    {
        return isset($this->_values[$name]) ? $this->_values[$name] : false;
    }


    function setChartDimensions ($x, $y)
    {
        $this->_chartDimensions = array('x' => $x, 'y' => $y);
        return $this;
    }


    function setTitle ($title)
    {
        $this->_title = $title;
        return $this;
    }


    function setChartOptions (array $options = array())
    {
        $this->_chartOptions = $options;
    }


    function __toString ()
    {
        if ( is_null($this->_deploymentContent) ) {
            die('You must explicity call the deploy() method before printing the object');
        }
        return $this->_deploymentContent;
    }


    function setChartId ($id)
    {
        $htis->_id = $id;
        return $this;
    }


    function getChartId ()
    {
        return $this->_chartId;
    }


    function setFilesLocation (array $locations)
    {
        $this->_filesLocation = $locations;
        return $this;
    }


    function getFilesLocation ()
    {
        return $this->_filesLocation;
    }


    protected function _applyConfigOptions ($options)
    {

        if ( isset($options['files']['js']) ) {
            $this->_filesLocation['js'] = $options['files']['js'];
        }

        if ( isset($options['files']['json']) ) {
            $this->_filesLocation['json'] = $options['files']['json'];
        }

        if ( isset($options['files']['flash']) ) {
            $this->_filesLocation['flash'] = $options['files']['flash'];
        }

        if ( isset($options['options']) && is_array($options['options']) ) {
            $this->setChartOptions($options['options']);
        }

        if ( isset($options['title']) && is_string($options['title']) ) {
            $this->setTitle($options['title']);
        }

        if ( isset($options['type']) && is_string($options['type']) ) {
            $this->setChartType($options['type']);
        }

        if ( isset($options['dimensions']) && is_array($options['dimensions']) ) {
            $this->setChartDimensions($options['dimensions']['x'], $options['dimensions']['y']);
        }

    }

}




