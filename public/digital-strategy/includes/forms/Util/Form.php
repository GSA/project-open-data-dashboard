<?php
/**
 * Wrapper for the fg.HTML.Form.Input package
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.Util
 */

require_once dirname(dirname(__FILE__)) . '/load.php';

class Form{
	
/**
 * Constructor
 * 
 * This class is static only
 * 
 * @access private 
 */
	private function __construct(){
	}
	
/**
 * Creates a form tag
 * 
 * @param string $action
 * @param bool $returnData
 * @param array $fields
 * @return FG_HTML_Element_Form
 */
	public function create($action = null, $returnData = false){
		if($action === true && func_num_args() == 1){
			$returnData = true;
			unset($action);
		}
		$form = new FG_HTML_Element_Form($returnData);
		
		if(isset($action))
			$form->setAction($action);
		
		return $form;
	}
	
/**
 * Creates a form data handler. The difference between create() and init(), is that init
 * do not return a tag element like create() does (FG_HTML_Element_Form). It returns only
 * a form fields handler.
 * 
 * @param bool $returnData See FG_HTML_Form_DataHandler::__construct()
 * @return FG_HTML_Form_DataHandler
 */
	public function init($returnData = false){
		return new FG_HTML_Form_DataHandler($returnData);
	}

/**
 * Create a new text input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Text
 */
	public static function text($name){
		return self::input('text', $name);
	}
	
/**
 * Create a new submit input
 * 
 * @param string $name The value attribute
 * @return FG_HTML_Form_Input_Submit
 */
	public static function submit($value){
		return self::input('submit', $value);
	}
	
/**
 * Create a new hidden input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Hidden
 */
	public static function hidden($name){
		return self::input('hidden', $name);
	}
	
/**
 * Create a new password input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Password
 */
	public static function password($name){
		return self::input('password', $name);
	}
	
/**
 * Create a new file input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Password
 */
	public static function file($name){
		return self::input('file', $name);
	}
	
/**
 * Create a new textArea input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_TextArea
 */
	public static function textArea($name){
		return self::input('textArea', $name);
	}
	
/**
 * Create a new select input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Select
 */
	public static function select($name){
		return self::input('select', $name);
	}
	
/**
 * Create a new radio input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Radio
 */
	public static function radio($name){
		return self::input('radio', $name);
	}
	
/**
 * Create a new checkbox input
 * 
 * @param string $name The name attribute
 * @return FG_HTML_Form_Input_Checkbox
 */
	public static function checkbox($name){
		return self::input('checkbox', $name);
	}
	
/**
 * Creates a new list of checkboxes
 * 
 * @param string $name Name that all checkboxes will have
 * @return FG_HTML_Form_Input_Collection_Checkbox
 */
	public static function checkboxes($name){
		return new FG_HTML_Form_Input_Collection_Checkbox($name);
	}
	
/**
 * Creates a new list of radios
 * 
 * @param string $name Name that all radios will have
 * @return FG_HTML_Form_Input_Collection_Checkbox
 */
	public static function radios($name){
		return new FG_HTML_Form_Input_Collection_Radio($name);
	}
	
/**
 * Creates a new input
 * 
 * @param string $input The input type
 * @param string $name The name attribute
 * @return FG_HTML_Input_AbstractInput
 */
	public static function input($input, $name){			
		switch ($input) {
			default:
			case 'text':
				$in = new FG_HTML_Form_Input_Text();
			break;
			case 'textArea':
				$in = new FG_HTML_Form_Input_TextArea();
			break;
			case 'password':
				$in = new FG_HTML_Form_Input_Password();
			break;
			case 'file':
				$in = new FG_HTML_Form_Input_File();
			break;
			case 'hidden':
				$in = new FG_HTML_Form_Input_Hidden();
			break;
			case 'submit':
				$in = new FG_HTML_Form_Input_Submit();
			break;
			case 'checkbox':
				$in = new FG_HTML_Form_Input_Checkbox();
			break;
			case 'radio':
				$in = new FG_HTML_Form_Input_Radio();
			break;
			case 'select':
				$in = new FG_HTML_Form_Input_Select();
			break;
		}
		
		if($input == 'submit')
			$in->setValue($name);
		else
			$in->setName($name);
		return $in;
	}
}