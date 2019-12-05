<?php
/**
 * Interface that marks entries as multi fillable
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Form.Input
 */

require_once dirname(__FILE__) . '/FG_HTML_Form_Input_Fillable.php';

interface FG_HTML_Form_Input_MultiFillable extends FG_HTML_Form_Input_Fillable{
	
/**
 * If is multi fillable
 * 
 * @return bool
 */
	public function isMultiFillable();
}