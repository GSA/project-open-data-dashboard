<?php
/**
 * Class representing a set of checkbox input collection
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input.Collection
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_Collection_AbstractCollection.php';
require_once dirname(dirname(__FILE__)) . '/FG_HTML_Form_Input_Checkbox.php';

require_once dirname(dirname(__FILE__)) . '/FG_HTML_Form_Input_MultiFillable.php';

class FG_HTML_Form_Input_Collection_Checkbox extends FG_HTML_Form_Input_Collection_AbstractCollection implements FG_HTML_Form_Input_MultiFillable{
	
/**
 * Add a new item to the collection
 * 
 * @param mixed $itemValue A string of the item's value or item's label (only if $itemLabel is null or array)
 * or a object of the input
 * @param string|FG_HTML_Element_Label $itemLabel A string with the label's text or a array of attributes
 * @param array $itemAttrs A array of attributes for the input
 * @return FG_HTML_Form_Input_Collection_Checkbox this object for method chaining
 */
	public function add($itemValue, $itemLabel = null, Array $itemAttrs = array()){
		parent::add($itemValue, $itemLabel, $itemAttrs);
		end($this->itens)->setHiddenInput(false);
		return $this;
	}
	
/**
 * Class name of the input.
 * 
 * @return string
 */
	public function getInputClass(){
		return 'FG_HTML_Form_Input_Checkbox';
	}
}