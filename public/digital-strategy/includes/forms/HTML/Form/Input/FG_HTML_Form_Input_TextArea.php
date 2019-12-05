<?php
/**
 * Class representing a textarea entry
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractInput.php';
require_once dirname(dirname(dirname(__FILE__))) . '/FG_HTML_Element.php';

class FG_HTML_Form_Input_TextArea extends FG_HTML_Form_Input_AbstractInput{
	
/**
 * Initializes input
 */
	public function __construct(){
		$this->inputElement = new FG_HTML_Element('textarea');
	}
	
/**
 * Sets textarea value
 * 
 * @param string $value
 * @return FG_HTML_Form_Input_TextArea this object for method chaining
 */
	public function setValue($value){
		$this->inputElement->setContent($value);
		return $this;
	}
	
/**
 * Gets textarea value
 * 
 * @return string the value
 */
	public function getValue(){
		return $this->inputElement->getContent();
	}
}