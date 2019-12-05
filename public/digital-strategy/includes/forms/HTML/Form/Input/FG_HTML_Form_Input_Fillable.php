<?php
/**
 * Interface of a "fillable" entity, like a colletion or input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

interface FG_HTML_Form_Input_Fillable{

	
/**
 * Fill values
 * 
 * @param mixed $value The value(s) to be filled
 * @return mixed
 */
	public function fill($value);
		
/**
 * Get filled values
 * 
 * @return mixed
 */
	public function getFilled();
		
/**
 * If was filled ones
 * 
 * @return mixed
 */
	public function isFilled();
	
/**
 * Returns the field (or field list if is a collection)
 * 
 * @return FG_HTML_Element
 */
	public function getField();
}