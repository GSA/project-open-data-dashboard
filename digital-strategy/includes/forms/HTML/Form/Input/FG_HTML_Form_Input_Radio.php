<?php
/**
 * Class representing a radio input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractButton.php';

class FG_HTML_Form_Input_Radio extends FG_HTML_Form_Input_AbstractButton{
	
/**
 * Initializes input
 */
	public function __construct(){
		parent::__construct();
		$this->inputElement->attr('type', 'radio');
	}
}