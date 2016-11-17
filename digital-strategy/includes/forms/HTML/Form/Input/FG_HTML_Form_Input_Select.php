<?php
/**
 * Class representing a select input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractInput.php';
require_once dirname(dirname(dirname(__FILE__))) . '/Element/FG_HTML_Element_Option.php';

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_MultiFillable.php';

class FG_HTML_Form_Input_Select extends FG_HTML_Form_Input_AbstractInput implements FG_HTML_Form_Input_MultiFillable{
	
/**
 * Array holding FG_HTML_Element_Option objects
 * 
 * @var array
 */
	protected $options = array();
	
/**
 * Initializes input
 */
	public function __construct(){
		$this->inputElement = new FG_HTML_Element('select');
	}
	
/**
 * Is multiple
 * 
 * @return bool
 */
	public function isMultiFillable(){
		return $this->inputElement->isAttr('multiple');
	}
	
/**
 * Add a new select option
 * 
 * @param mixed $optionValue Accepts a string or a FG_HTML_Element_Option
 * @param string $optionText If a FG_HTML_Element_Option will be used as the first argument, so this
 * is ignored
 * @return FG_HTML_Form_Input_Select this object for method chaining
 * @throws InvalidArgumentException
 */
	public function add($optionValue, $optionText = null){
		if($optionValue instanceof FG_HTML_Element_Option){
			$option = $optionValue;
		}elseif($optionText !== null){
			$option = new FG_HTML_Element_Option();
			$option->attr('value', $optionValue)->setContent($optionText);
		}else{
			throw new InvalidArgumentException('You must pass a value for $optionText or a FG_HTML_Element_Option object for $optionValue');
		}

		$this->options[] = $option;
		return $this;
	}
	
/**
 * Defines the selected values
 * 
 * @param array|string|int $value The value(s)
 * @return FG_HTML_Form_Input_Select this object for method chaining
 */
	public function setValue($value){
		$values = (array)$value;
		$optionList = array();
		
		foreach ($this->options as $option){
			if(in_array($option->attr('value'), $values)){
				$optionList[] = $option;
			}else{
				$option->attr('selected', false);
			}
		}
		
		if($this->attr('multiple')){
			foreach($optionList as $option){
				$option->attr('selected', true);
			}
		}elseif(isset($optionList[0])){
			$optionList[0]->attr('selected', true);
		}
		
		return $this;
	}
	
/**
 * Alias for setValue
 * 
 * @param array|string|int $value The value(s)
 * @return FG_HTML_Form_Input_Select this object for method chaining
 */
	public function setSelected($value){
		return $this->setValue($value);
	}

/**
 * Get the selected values
 * 
 * @return mixed A array if this select is multiple, or a string if not. Case isn't multiple and there's
 * no selected value, will return null
 */
	public function getValue(){
		$selected = array();
		foreach($this->options as $option){
			if($option->attr('selected'))
				$selected[] = $option->attr('value');
		}
		
		if($this->attr('multiple'))
			return $selected;
		elseif(isset($selected[0]))
			return $selected[0];
		else
			return null;
	}
	
/**
 * If is filled with value
 * 
 * @return bool
 */
	public function isFilled(){
		$bool = false;
		foreach($this->options as $item)
			$bool += $item->isAttr('selected');
		return $bool;
	}
	
/**
 * Gets field
 * 
 * @return string
 */
	public function getField(){
		return parent::getField()->setContent(join('', $this->options));
	}
}