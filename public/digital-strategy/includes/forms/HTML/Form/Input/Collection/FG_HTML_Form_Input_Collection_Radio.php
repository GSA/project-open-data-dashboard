<?php
/**
 * Class representing a set of radio input collection
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input.Collection
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_Collection_AbstractCollection.php';
require_once dirname(dirname(__FILE__)) . '/FG_HTML_Form_Input_Radio.php';

class FG_HTML_Form_Input_Collection_Radio extends FG_HTML_Form_Input_Collection_AbstractCollection{
	
/**
 * If is to use hidden input
 * 
 * @var bool
 */
	protected $hiddenInput = true;
	
/**
 * Class name of the input.
 * 
 * @return string
 */
	public function getInputClass(){
		return 'FG_HTML_Form_Input_Radio';
	}
	
/**
 * Is multiple
 * 
 * @return bool
 */
	public function isMultiFillable(){
		return false;
	}
	
/**
 * Defines the checked item
 * 
 * @param mixed $value The value(s) to be checked
 * @return FG_HTML_Form_Input_Collection_AbstractCollection this object for method chaining
 */
	public function fill($value){
		$value = (string)$value;
		parent::fill($value);
		
		return $this;
	}
	
/**
 * Gets the checked value or false if no checked value exists
 * 
 * @return mixed The checked value
 */
	public function getFilled(){
		$itens = parent::getFilled();
		return isset($itens[0]) ? $itens[0] : false;
	}
}