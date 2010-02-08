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
 * @version    $Id: Json.php 525 2010-02-06 15:14:10Z pao.fresco@gmail.com $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Ofc extends Bvb_Grid_Data implements
Bvb_Grid_Deploy_Interface
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


    /*
    * @param array $data
    */
    function __construct ($options)
    {
        if (! in_array(self::OUTPUT, $this->export)) {#  echo $this->__("You dont' have permission to export the results to this format");
#  die();
        }
        $this->options = $options;

        parent::__construct($options);
    }

    function deploy ()
    {


        $final = '<script type="text/javascript" src="'.$this->_baseUrl.'/public/scripts/swfobject.js"></script>
        <script type="text/javascript">
        swfobject.embedSWF(
        "'.$this->_baseUrl.'/public/flash/open-flash-chart.swf", "my_chart",
        "900", "400", "9.0.0", "expressInstall.swf",
        {"data-file":"' . $this->_getUrl() . '/_showJs/1"} );
        </script>
        <div id="my_chart"></div>';

        $this->_deploymentContent = $final;
    }

    function ajax ()
    {
        $grid = array();
        $newData = array();
        #$this->setPagination(0);
        parent::deploy();

        $data = parent::_buildGrid();

        if (count($data[0]) == 1 || count($data[0]) == 2) {
            foreach ($data as $value) {
                $newData[] = (int) $value[0]['value'];
                $label[] = strip_tags($value[1]['value']);
            }
        }

        $chart = new OFC_Chart();

        if (count($label) > 0) {
            $x = new OFC_Elements_Axis_X();
            $x->set_labels_from_array($label);
            $chart->set_x_axis($x);
        }

        $support = $newData;

        sort($support);

        $min = reset($support);
        $max = end($support);

        $bar = new OFC_Charts_Bar_Glass();
        $bar->set_values($newData);

        $y = new OFC_Elements_Axis_Y();
        $y->set_range($min, $max, ceil($max / 4));


        $chart->add_element($bar);
        $chart->add_y_axis($y);

        $final = $chart->toPrettyString();

        if (isset($this->ctrlParams['_showJs']) && $this->ctrlParams['_showJs'] == 1) {
            $response = Zend_Controller_Front::getInstance()->getResponse();
            $response->setBody($final);
            $response->sendResponse();
            exit();
        }
    }

    function __toString ()
    {
        if (is_null($this->_deploymentContent)) {
            self::deploy();
        }
        return $this->_deploymentContent;
    }
}




