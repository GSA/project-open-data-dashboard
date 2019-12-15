<?php
/**
 * Class representing a set of data input collection
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input.Collection
 */

require_once dirname(dirname(__FILE__)) . '/FG_HTML_Form_Input_AbstractEntry.php';
require_once dirname(dirname(__FILE__)) . '/FG_HTML_Form_Input_Hidden.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/FG_HTML_Element.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/Element/FG_HTML_Element_Label.php';

abstract class FG_HTML_Form_Input_Collection_AbstractCollection extends FG_HTML_Form_Input_AbstractEntry{
	
/**
 * This itens of this collection
 * 
 * @var array of objects (e.g: checkbox or radio)
 */
	protected $itens = array();
	
/**
 * If is to use hidden input
 * 
 * @var bool
 */
	protected $hiddenInput = false;
	
/**
 * Each input's wrapper
 * 
 * If the value is null, then there's no wrapper
 * 
 * @var FG_HTML_Element|null
 */
	protected $wrapper;
	
/**
 * Number of times that the add method was called
 * 
 * This is used for that ID's attributes do not repeat each other
 * 
 * @var int
 */
	private $times = 0;
	
/**
 * The attribute's name of all inputs (e.g: options[])
 * 
 * @var string
 */
	protected $name;
	
/**
 * Initializes the object
 * 
 * @param string Attribute's name that all inputs will have
 */
	public function __construct($name){
		$this->setWrapper(new FG_HTML_Element('div'));
		$this->name = $name;
	}
	
/**
 * Is multiple
 * 
 * @return bool
 */
	public function isMultiFillable(){
		return true;
	}
	
/**
 * Class name of the input.
 * 
 * The class must be a type of FG_HTML_Form_Input_AbstractButton
 * 
 * @return string
 */
	abstract public function getInputClass();
	
/**
 * Add a new item to the collection
 * 
 * @param mixed $itemValue A string of the item's value or item's label (only if $itemLabel is null or array)
 * or a object of the input
 * @param string|FG_HTML_Element_Label $itemLabel A string with the label's text or a array of attributes
 * @param array $itemAttrs A array of attributes for the input
 * @return FG_HTML_Form_Input_Collection_AbstractCollection this object for method chaining
 */
	public function add($itemValue, $itemLabel = null, Array $itemAttrs = array()){
		$this->times++;		
		$type = $this->getInputClass();
	
		// no item value passed
		if(is_null($itemLabel)){
			$itemLabel = $itemValue;
			$itemValue = $this->times;
			
		// attrs
		}elseif(is_array($itemLabel)){
			$itemAttrs = $itemLabel;
			$itemLabel = $itemValue;
			$itemValue = $this->times;
		}
		
		// specific type
		if(is_a($itemLabel, 'FG_HTML_Element_Label')){
			$label = $itemLabel;
			$isLabelSpecific = true;
		// no label
		}elseif($itemLabel === false){
			$label = $itemLabel;
		// string that will be converted to label object
		}else{
			$label = new FG_HTML_Element_Label();
			$label->setContent($itemLabel);
			$isLabelSpecific = false;
		}
		
		// create item
		if(is_a($itemValue, $type)){
			$item = $itemValue;
		}else{
			$item = new $type();
			
			if(!isset($itemAttrs['value']))
				$itemAttrs['value'] = $itemValue;
			
			if(!isset($itemAttrs['id']))
				$itemAttrs['id'] = $this->name . $this->times;
				
			if(!isset($itemAttrs['name']))
				$itemAttrs['name'] = $this->name;
				
			$item->attr($itemAttrs);
		}
				
		// insert label to item
		if(isset($label) && $label !== false){
			if(!$isLabelSpecific){
				$label->attr('for', $itemAttrs['id']);
			}
			$item->setLabel($label);
		}
		
		$this->itens[] = $item;
		return $this;
	}
	
/**
 * Get inputs names
 * 
 * @return string
 */
	public function getName(){
		return $this->name;
	}

/**
 * Gets the hidden input of this element. If $this->hiddenInput is false will return null
 * 
 * @return FG_HTML_Form_Input_Hidden|null
 */
	public function getHiddenInput(){
		if($this->hiddenInput){
			$output = new FG_HTML_Form_Input_Hidden();
			$output->setValue('')->attr('name', strrev(preg_replace('/\] *\[/', '', strrev($this->name), 1)));
		}else{
			$output = null;
		}
		return $output;
	}
	
/**
 * Sets if is to use a hidden input before the inputs, e.g:
 * 
 * @param bool $bool
 * @return FG_HTML_Form_Input_Collection_AbstractCollection this object for method chaining
 */
	public function setHiddenInput($bool){
		$this->hiddenInput = $bool;
		return $this;
	}

/**
 * Sets the wrapper
 * 
 * @param mixed $wrapper FG_HTML_Element or null for no wrapper
 * @return FG_HTML_Form_Input_Collection_AbstractCollection this object for method chaining
 * @throws InvalidArgumentException
 */
	public function setWrapper($wrapper){
		if(!is_null($wrapper) && !is_a($wrapper, 'FG_HTML_Element')){
			throw new InvalidArgumentException('You must pass null or a FG_HTML_Element object for the wrapper');
		}
		$this->wrapper = $wrapper;
		return $this;
	}

/**
 * Gets the wrapper
 * 
 * @return FG_HTML_Element
 */
	public function getWrapper(){
		return $this->wrapper;
	}

/**
 * Sets the collection's label as a div
 * 
 * You can pass a string or a FG_HTML_Element object
 * 
 * @param FG_HTML_Element|string $label The label object or the label string
 * @return FG_HTML_Form_Input_Collection_AbstractCollection this object for method chaining
 */
	public function setLabel($label){
		if(!is_a($label, 'FG_HTML_Element')){
			$Label = new FG_HTML_Element('div');
			$label = $Label->setContent($label);
			$label->setClass( 'control-label' );
		}
		
		$this->Label = $label;
		return $this;
	}
	
/**
 * Defines the checked itens
 * 
 * @param mixed $value The value(s) to be checked
 * @return FG_HTML_Form_Input_Collection_AbstractCollection this object for method chaining
 */
	public function fill($value){
		$values = (array)$value;
		$itemList = array();
		
		foreach ($this->itens as $item){
			$item->fill(in_array($item->getValue(), $values));
		}
		
		return $this;
	}
	
/**
 * Gets the checked values
 * 
 * @return mixed The checked values
 */
	public function getFilled(){
		$itens = array();
		
		foreach($this->itens as $item){
			if($item->getFilled())
				$itens[] = $item->getValue();
		}
		
		return $itens;
	}
	
/**
 * If is filled with value
 * 
 * @return bool
 */
	public function isFilled(){
		$bool = false;
		foreach($this->itens as $item)
			$bool += $item->isFilled();
		return $bool;
	}
	
/**
 * Renders all itens in a html string
 * 
 * @return string
 */
	public function getField(){
		$output = $this->getHiddenInput();
		
		foreach($this->getThis()->itens as $item){
			if($this->wrapper !== null){
				$wrapper = clone $this->wrapper;
				$output .= $wrapper->setContent($item);
			}else{
				$output .= $item;
			}
		}
		
		return $output;
	}
}