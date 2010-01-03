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
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.4  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */

class Bvb_Grid_Form {
	public $options;
	
	public $fields;
	
	public $cascadeDelete;
	
	function __call($name, $args) {
		$this->options [$name] = $args [0];
		return $this;
	}
	
	function setCallbackBeforeDelete($callback) {
		
		if (! is_callable ( $callback )) {
			throw new Exception ( $callback . ' not callable' );
		}
		
		$this->options ['callbackBeforeDelete'] = $callback;
		
		return $this;
	}
	
	function setCallbackBeforeUpdate($callback) {
		
		if (! is_callable ( $callback )) {
			throw new Exception ( $callback . ' not callable' );
		}
		
		$this->options ['callbackBeforeUpdate'] = $callback;
		
		return $this;
	}
	
	function setCallbackBeforeInsert($callback) {
		
		if (! is_callable ( $callback )) {
			throw new Exception ( $callback . ' not callable' );
		}
		
		$this->options ['callbackBeforeInsert'] = $callback;
		
		return $this;
	}
	
	
	function setCallbackAfterDelete($callback) {
		
		if (! is_callable ( $callback )) {
			throw new Exception ( $callback . ' not callable' );
		}
		
		$this->options ['callbackAfterDelete'] = $callback;
		
		return $this;
	}
	
	function setCallbackAfterUpdate($callback) {
		
		if (! is_callable ( $callback )) {
			throw new Exception ( $callback . ' not callable' );
		}
		
		$this->options ['callbackAfterUpdate'] = $callback;
		
		return $this;
	}
	
	function setCallbackAfterInsert($callback) {
		
		if (! is_callable ( $callback )) {
			throw new Exception ( $callback . ' not callable' );
		}
		
		$this->options ['callbackAfterInsert'] = $callback;
		
		return $this;
	}
	
	function onDeleteCascade($options) {
		$this->cascadeDelete [] = $options;
		return $this;
	
	}
	
	function addColumns() {
		
		$columns = func_get_args ();
		
		$final = array ();
		
		if (is_array ( $columns [0] )) {
			$columns = $columns [0];
		}
		
		foreach ( $columns as $value ) {
			
			if ($value instanceof Bvb_Grid_Form_Column) {
				array_push ( $final, $value );
			}
		}
		
		$this->fields = $final;
		
		return $this;
	
	}

}