<?php
/**
 * Class representing a submit input
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_AbstractInput.php';

class FG_HTML_Form_Input_Submit extends FG_HTML_Form_Input_AbstractInput{
	
/**
 * Initializes input
 */
	public function __construct(){
		parent::__construct();
		$this->inputElement->attr('type', 'submit');
	}
}