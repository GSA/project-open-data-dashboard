<?php
/**
 * Interface of all identifiable classes
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

interface FG_HTML_Form_Input_Identifiable{
	
/**
 * Gets name
 * 
 * @return string
 */
	public function getName();
	
/**
 * Get name without the array part
 * 
 * @return string
 */
	public function getBaseName();
}