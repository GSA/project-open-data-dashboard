<?php
/**
 * Class representing an abstract entry
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_Fillable.php';

abstract class FG_HTML_Form_Input_AbstractEntry implements FG_HTML_Form_Input_Fillable{

/**
 * Before's content
 * 
 * @var string
 */
	protected $contentBefore = '';
	
/**
 * After's content
 * 
 * @var string
 */
	protected $contentAfter = '';
	
/**
 * Default value. This is used when the entry is not filled.
 * 
 * @var mixed
 */
	protected $default;
	
/**
 * Label object
 * 
 * @var FG_HTML_Element
 */
	protected $Label;
	
/**
 * Gets entry name (e.g: name[array][part])
 * 
 * @return string
 */
	public abstract function getName();
	
/**
 * Returns the field (or field list, if a collection) with before and after content
 * 
 * @return string
 */
	public function getEntry(){
		return $this->getContentBefore() . $this->getField() . $this->getContentAfter();
	}
	
/**
 * Returns a clone of self object if a default value was inserted and can be displayed, a reference
 * to the current otherwise.
 * 
 * Is necessary use this method because of the "default" value that a entry can have.
 * 
 * @return bool|FG_HTML_Form_Input_AbstractEntry
 */
	protected function getThis(){
		if(!$this->isFilled() && !is_null($this->default)){
			$that = clone $this;
			$that->fill($this->default);
			return $that;
		}
		return $this;
	}
	
/**
 * Sets default "value" (fill) of this input
 * 
 * The use of fill() method overwrite this one.
 * 
 * @param string $value
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function setDefault($value){
		$this->default = $value;
		return $this;
	}
	
/**
 * Get default value
 * 
 * @return mixed
 */
	public function getDefault(){
		return $this->default;
	}

/**
 * Get name without the array part (e.g: name[array][part])
 * 
 * @return string
 */
	public function getBaseName(){
		$pieces = explode('[', $this->getName());
		return $pieces[0];
	}
	
/**
 * Sets the entry label
 * 
 * You can pass a string or a FG_HTML_Element
 * 
 * @param FG_HTML_Elementl|string $label The label object or the label string
 * @return FG_HTML_Form_Input_AbstractEntry this object for method chaining
 */
	public abstract function setLabel($label);
	
/**
 * Gets the input label
 * 
 * @return FG_HTML_Element or null in case there's no defined label
 */
	public function getLabel(){
		return $this->Label;
	}

/**
 * Sets a HTML content before the input
 * 
 * @param string $content
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function setContentBefore($content){
		$this->contentBefore = $content;
		return $this;
	}
	
/**
 * Returns the HTML content that is before this input
 * 
 * @return string
 */
	public function getContentBefore(){
		return $this->contentBefore;
	}
	
/**
 * Sets a HTML content after the input
 * 
 * @param string $content
 * @return FG_HTML_Form_Input_AbstractInput this object for method chaining
 */
	public function setContentAfter($content){
		$this->contentAfter = $content;
		return $this;
	}
	
/**
 * Returns the HTML content that is after this input
 * 
 * @return string
 */
	public function getContentAfter(){
		return $this->contentAfter;
	}
	
/**
 * Gets entry's html representation
 * 
 * @return string
 */
	public function render(){
		
		return (string) Html::tag( 'div' )->setClass( 'control-group' )->setContent( $this->getLabel() . Html::tag( 'div' )->setClass( 'controls' )->setContent( $this->getEntry() ) );

	}
	
/**
 * Alias for render()
 * 
 * @return string
 */
	public function __toString(){
		return $this->render();
	}
}