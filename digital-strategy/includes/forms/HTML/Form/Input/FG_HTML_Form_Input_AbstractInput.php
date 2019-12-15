<?php
/**
 * Class representing a data input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractEntry.php';
require_once dirname(dirname(dirname(__FILE__))) . '/FG_HTML_Element.php';
require_once dirname(dirname(dirname(__FILE__))) . '/Element/FG_HTML_Element_Label.php';

abstract class FG_HTML_Form_Input_AbstractInput extends FG_HTML_Form_Input_AbstractEntry{
	
/**
 * The element
 * 
 * @var FG_HTML_Element
 */
	protected $inputElement;
	
/**
 * Initializes object
 */
	public function __construct(){
		$this->inputElement = new FG_HTML_Element('input');
	}
	
/**
 * Returns input name
 * 
 * @return string
 */
	public function getName(){
		return $this->attr('name');
	}
	
/**
 * Gets input field for render.
 * 
 * This method manages the default value property as well.
 * The default value fill the input ones, therefore the isFilled() will never again
 * returns false, and that's no the expected behavior for a default value.
 * 
 * @return FG_HTML_Element
 */
	public function getField(){
		return $this->getThis()->getInputElement();
	}
	
/**
 * Clones the neccessary properties
 */
	public function __clone(){
		$this->inputElement = clone $this->inputElement;
	}
	
/**
 * Returns the input element
 * 
 * @return FG_HTML_Element
 */
	public function getInputElement(){
		return $this->inputElement;
	}
	
/**
 * Sets, gets and deletes attributes from the input.
 * 
 * @param string|array $name Attribute's name as string or a array of attrName-value pairs
 * @param string|false $value Attribute's value or false to unset attribute
 * @return FG_HTML_Form_Input_AbstractInput|string|bool|array returns FG_HTML_Form_Input_AbstractInput
 * when you are creating a new attribute, string when you are getting one, array when you are getting all
 * or false when you failed to get one attribute.
 * @see FG_HTML_Element::attr()
 */
	public function attr($name, $value = null){
		$return = $this->inputElement->attr($name, $value);
		return is_object($return) ? $this : $return;
	}
	
/**
 * Makes setting and getting attributes magic
 * 
 * @param string $name
 * @param array $args
 * @throws RuntimeException
 */
	public function __call($name, $args){
		$return = call_user_func_array(array($this->inputElement, $name), $args);
		return is_object($return) ? $this : $return;
	}
	
/**
 * Sets this input's value
 * 
 * @param mixed $value
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function setValue($value){
		$this->setAttribute('value', $value);
		return $this;
	}
	
/**
 * Gets the input's value
 * 
 * @return mixed
 */
	public function getValue(){
		return $this->attr('value');
	}
	
/**
 * Sets the entry label
 * 
 * You can pass a string or a FG_HTML_Element_Label
 * 
 * @param FG_HTML_Element_Label|string $label The label object or the label string
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function setLabel($label){
		if(!is_a($label, 'FG_HTML_Element_Label')){
			$Label = new FG_HTML_Element_Label();
		
			$label = $Label->setContent($label);
			
			if($this->attr('id') === false && $name = $this->attr('name')){
				$this->attr('id', $name);
			}
			
			$label->attr('for', $this->attr('id'));
			$label->attr( 'class', 'control-label' );
		}
		
		$this->Label = $label;
		return $this;
	}
	
/**
 * Fills with $value.
 * 
 * This usually means fill the value's attribute of a input, but for a checkbox, for instance, means
 * set it as checked.
 * 
 * @param mixed $value The value
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function fill($value){
		return $this->setValue($value);
	}
	
/**
 * If the field is filled with some value
 * 
 * @return bool
 */
	public function isFilled(){
		return $this->getFilled() !== false;
	}
	
/**
 * Returns filled values
 * 
 * @return mixed
 */
	public function getFilled(){
		return $this->getValue();
	}
}