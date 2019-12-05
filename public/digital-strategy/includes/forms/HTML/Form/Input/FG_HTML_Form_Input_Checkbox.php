<?php
/**
 * Class representing a checkbox input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractButton.php';
require_once dirname(__FILE__) . '/FG_HTML_Form_Input_Hidden.php';

class FG_HTML_Form_Input_Checkbox extends FG_HTML_Form_Input_AbstractButton{
	
/**
 * If is to use hidden input
 * 
 * @var bool
 */
	protected $hiddenInput = true;
	
/**
 * Initializes input
 */
	public function __construct(){
		parent::__construct();
		$this->inputElement->attr('type', 'checkbox')->setValue('1');
	}
	
/**
 * Gets input's html representation
 * 
 * @return string
 */
	public function render(){
		$hidden = $this->getHiddenInput();
		return $hidden . parent::render();
	}
	
/**
 * Gets the hidden input of this element. If $this->hiddenInput is false will return null
 * 
 * @return FG_HTML_Form_Input_Hidden|null
 */
	public function getHiddenInput(){
		if($this->hiddenInput){
			$output = new FG_HTML_Form_Input_Hidden();
			$output->setValue('0')->attr('name', $this->attr('name'));
		}else{
			$output = null;
		}
		return $output;
	}
	
/**
 * Sets if is to use a hidden input before the main input, e.g:
 * 
 * With hidden input:
 * <input type="hidden" name="somename" value="0" />
 * <input type="checkbox" name="somename" value="1" />
 * 
 * Without hidden input:
 * <input type="checkbox" name="somename" value="1" />
 * 
 * @param bool $bool
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function setHiddenInput($bool){
		$this->hiddenInput = $bool;
		return $this;
	}
}