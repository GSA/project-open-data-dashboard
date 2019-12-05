<?php
/**
 * Class representing a data input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractInput.php';

abstract class FG_HTML_Form_Input_AbstractButton extends FG_HTML_Form_Input_AbstractInput {

/**
 * Gets input's html representation (with label align to the right)
 * 
 * @return string
 */
	public function render(){
		return "{$this->getEntry()}{$this->getLabel()}";
	}
	
/**
 * Sets checkbox as checked\unchecked
 * 
 * @param bool $bool
 * @return FG_HTML_Form_Input_Checkbox this object for method chaining
 */
	public function fill($bool){
		return $this->attr('checked', $bool);
	}
	
/**
 * Gets button filled value
 * 
 * @return bool If is checked or not
 */
	public function getFilled(){
		return $this->attr('checked');
	}
}