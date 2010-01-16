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
 * @version    0.4   $
 * @author     Bento Vilas Boas <geral@petala-azul.com > 
 */

class Bvb_Grid_Formatter_Date {
	protected $locale = null;
	protected $date_format = null;
	protected $type = null;
	
	function __construct($options = array()) {
		if ($options instanceof Zend_Locale) {
			$this->locale = $options;
		} elseif (is_string ( $options )) {
			$this->date_format = $options;
		} else {
			foreach ( $options as $k => $v ) {
				switch ($k) {
					case 'locale' :
						$this->locale = $v;
						break;
					case 'date_format' :
						$this->date_format = $v;
						break;
					case 'type' :
						$this->type = $v;
						break;
					default :
						throw new Exception ( "Unknown option '$k'." );
				}
			}
		}
	}
	
	function format($value) {
		$date = new Zend_Date ( $value );
		return $date->toString ( $this->date_format, $this->type, $this->locale );
	}

}