<?php
/**
 * Wrapper for the fg.HTML package
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 */

require_once dirname(dirname(__FILE__)) . '/load.php';

class Html{
	
/**
 * Constructor
 */
	private function __construct(){
	}
	
/**
 * Creates a new tag
 * 
 * @param string $name The tag name
 * @param string $content The tag content
 * @return FG_HTML_Element
 */
	public static function tag($name, $content = null){
		$out = new FG_HTML_Element($name);
		if($content !== null)
			$out->setContent($content);

		return $out;
	}
	
/**
 * Creates a new option tag
 * 
 * @param string $value The tag value attribute
 * @param string $content The tag content attribute
 * @return FG_HTML_Element_Option
 */
	public static function option($value = null, $content = null){
		return new FG_HTML_Element_Option($value, $content);
	}
	
/**
 * Creates a new label tag
 * 
 * @param string $for The tag for attribute
 * @param string $content The tag content attribute
 * @return FG_HTML_Element_Label
 */
	public static function label($for = null, $content = null){
		return new FG_HTML_Element_Label($for, $content);
	}
}