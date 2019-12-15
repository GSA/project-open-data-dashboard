<?php
/**
 * Class representing a abstract identifiable class
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_Identifiable.php';

abstract class FG_HTML_Form_Input_AbstractIdentifiable implements FG_HTML_Form_Input_Identifiable{

/**
 * Get name without the array part
 * 
 * @return string
 */
	public function getBaseName(){
		$pieces = explode('[', $this->getName());
		return $pieces[0];
	}
}