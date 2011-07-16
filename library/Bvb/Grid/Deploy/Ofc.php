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
 * @copyright  Copyright (c) Bento Vilas Boas (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Ofc extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{

    public static $url;

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
     *
     * @var mixed
     */
    protected $_xLabels = null;

    /**
     * Options for x Labels
     * @var array
     */
    protected $_xLabelsOptions = array();

    /**
     * Chart Args
     * @var array
     */
    protected $_typeArgs = array();

    /**
     * Values to show
     * @var array
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
     * Chart Title Style
     * @var string
     */
    protected $_style = '';

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
     * flag to indicate if you will be having more than one chart on a page
     * @var bool
     */
    protected $_multiple = true;

    protected $_xAxisOptions = array();


    /**
     * @param array $data
     */
    public function __construct (array $options = array())
    {
        $this->options = $options;

        if ( ! Zend_Loader_Autoloader::autoload('OFC_Chart') ) {
            die("You must have Open Flash Chart installed in order to use this deploy. Please check this page for more information: http://code.google.com/p/zfdatagrid/wiki/Bvb_Grid_Deploy");
        }

        parent::__construct($options);
    }


    /**
     * To use multiples instances per page
     * @param $flag
     */
    public function setMultiple ($flag)
    {
        $this->_multiple = $flag;
    }


    public function deploy ()
    {
        $this->checkExportRights();

        if ( $this->_filesLocation === null ) {
            throw new Bvb_Grid_Exception($this->__("Please set Javascript and Flash file locations using SetFilesLocation()"));
        }

        $grid = array();
        $newData = array();
        $label = array();
        $result = array();

        parent::deploy();

        $data = parent::_buildGrid();

        if ( count($data) == 0 ) {
            $this->_deploymentContent = '';
            return;
        }

        foreach ( $data as $value ) {
            foreach ( $value as $final ) {
                $result[$final['field']][] = is_numeric($final['value']) ? $final['value'] : strip_tags($final['value']);
            }
        }

        if ( is_string($this->_xLabels) && isset($result[$this->_xLabels]) ) {
            $this->_xLabels = $result[$this->_xLabels];
        }

        $graph = new OFC_Chart();
        $title = new OFC_Elements_Title($this->_title);
        $title->set_style($this->_style);
        $graph->set_title($title);

        foreach ( $this->_chartOptions as $key => $value ) {
            $graph->$key($value);
        }

        if ( count($this->_xLabels) > 0 ) {
            $x = new OFC_Elements_Axis_X();
            $x_axis_labels = new OFC_Elements_Axis_X_Label_Set();
            foreach ( $this->_xAxisOptions as $key => $value ) {
                $x_axis_labels->$key($value);
            }
            $x_axis_labels->set_labels($this->_xLabels);
            $x->set_labels($x_axis_labels);
            foreach ( $this->_xLabelsOptions as $key => $value ) {
                $x->$key($value);
            }
            $graph->set_x_axis($x);
        }

        if ( ! empty($this->_xLegendText) && ! empty($this->_xLegendStyle) ) {
            $x_legend = new OFC_Elements_Legend_X($this->_xLegendText);
            $x_legend->set_style($this->_xLegendStyle);
            $graph->set_x_legend($x_legend);
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
                        $pie[] = array('value'=>$title,'label'=> $this->_xLabels[$key]);
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

                $value = array_map(create_function('$item', ' return (float)$item; '), $result[$value]);

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
                        $pie[] = array('value'=>$title,'label'=> $this->_xLabels[$key]);
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
        "' . $this->_chartDimensions['x'] . '", "' . $this->_chartDimensions['y'] . '", "9.0.0", "expressInstall.swf",{"id":"' . $this->_chartId . '"},{"z-index":"1","wmode":"transparent"} );

        function open_flash_chart_data(id)
        {
            return JSON.stringify(window[id]);
        }

        function findSWF(movieName) {
          if (navigator.appName.indexOf("Microsoft")!= -1) {
            return window[movieName];
          } else {
            return document[movieName];
          }
        }
        var ' . $this->_chartId . ' = ' . $final . ';';

        $final = '<div id="' . $this->_chartId . '" >
        loading...
        <br/>
        <p>
        Please note that this content requires flash player 9.0.0</br>
        To test for your version of flash, <a href="http://www.bobbyvandersluis.com/swfobject/testsuite_2_1/test_api_getflashplayerversion.html" target="_blank">click here</a>
        </p>
        </div>';
        if ( ! $this->_multiple ) $final = '<div style="width: 100%;text-align: center">' . $final . '</div>';

        $this->getView()
            ->headScript()
            ->appendFile($this->_filesLocation['js']);
        $this->getView()
            ->headScript()
            ->appendFile($this->_filesLocation['json']);
        $this->getView()
            ->headScript()
            ->appendScript($script);

        $this->_deploymentContent = $final;
        return $this;
    }


    public function setXLabels ($labels, $options = array())
    {
        $this->_xLabels = $labels;
        $this->_xLabelsOptions = $options;
    }


    public function setChartType ($type, $args = array())
    {
        $this->_type = (string) "OFC_Charts_" . implode('_', array_map('ucwords', explode('_', $type)));

        $this->_typeArgs = $args;
        return $this;
    }


    public function setValues ($values, $options = array())
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


    public function addValues ($values, $options = array())
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


    public function getValues ($name)
    {
        return isset($this->_values[$name]) ? $this->_values[$name] : false;
    }


    public function setChartDimensions ($x, $y)
    {
        $this->_chartDimensions = array('x' => $x, 'y' => $y);
        return $this;
    }


    public function setTitle ($title)
    {
        $this->_title = $title;
        return $this;
    }


    public function setTitleStyle ($style)
    {
        $this->_style = $style;
        return $this;
    }


    public function setChartOptions (array $options = array())
    {
        $this->_chartOptions = $options;
    }


    public function __toString ()
    {
        if ( is_null($this->_deploymentContent) ) {
            die('You must explicity call the deploy() method before printing the object');
        }
        return $this->_deploymentContent;
    }


    public function setChartId ($id)
    {
        $htis->_id = $id;
        return $this;
    }


    public function getChartId ()
    {
        return $this->_chartId;
    }


    public function setFilesLocation (array $locations)
    {
        $this->_filesLocation = $locations;
        return $this;
    }


    public function getFilesLocation ()
    {
        return $this->_filesLocation;
    }


    public function setFlashParams ($flashParams)
    {
        $this->_flashParams = $flashParams;
        return $this;
    }


    public function setXLegend ($text, $style)
    {
        $this->_xLegendText = $text;
        $this->_xLegendStyle = $style;
        return $this;
    }


    public function setXAxisOptions ($xAxisOptions)
    {
        $this->_xAxisOptions = $xAxisOptions;
        return $this;
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

        if ( isset($options['style']) && is_string($options['style']) ) {
            $this->setTitleStyle($options['style']);
        }

        if ( isset($options['type']) && is_string($options['type']) ) {
            $this->setChartType($options['type']);
        }

        if ( isset($options['dimensions']) && is_array($options['dimensions']) ) {
            $this->setChartDimensions($options['dimensions']['x'], $options['dimensions']['y']);
        }

        if ( isset($options['flashParams']) && is_string($options['flashParams']) ) {
            $this->setFlashParams($options['flashParams']);
        }

        if ( isset($options['xLegend']) && is_array($options['xLegend']) ) {
            $this->setXLegend($options['xLegend']['text'], $options['xLegend']['style']);
        }
    }
}
