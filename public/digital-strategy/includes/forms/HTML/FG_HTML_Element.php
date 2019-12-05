<?php
/**
 * Class representing a HTML element
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML
 */

require_once dirname(dirname(__FILE__)) . '/Markup/FG_Markup_Tag.php';

class FG_HTML_Element extends FG_Markup_Tag{
	
/**
* List of default void tags
*
* @var array
*/
	private static $_voids = array('input', 'img', 'hr', 'br');
	
/**
* List of slufied attrs
*
* @var array
*/
	private static $_slugifiedAttrs = array('id', 'for');
	
/**
* List of slufied attrs
*
* @var array
*/
	private static $_boolAttrs = array('checked', 'selected', 'multiple');

/**
 * Initializes the element
 * 
 * @param string $name The tag's name
 */
	public function __construct($name){
		parent::__construct($name);
		$this->setVoid(in_array($name, self::$_voids));
	}

/**
 * Sets an attribute
 * 
 * @param string $name Attribute's name. If it is ID, it will be slugified.
 * @param string $value Attribute's value
 * @return FG_Markup_Tag this object for method chaining
 */
	public function setAttribute($name, $value){
		if(in_array($name, self::$_slugifiedAttrs)){
			$value = self::slugifyAttr($value);
		}

		if(in_array($name, self::$_boolAttrs)){
			if($value === true || $value == 1){
				$value = $name;
			}else{
				$this->unsetAttribute($name);
				return $this;
			}
		}
		return parent::setAttribute($name, $value);
	}

/**
 * Returns one attribute
 * 
 * @param string $name
 * @return string|bool string or false if not exists
 */
	public function getAttribute($name){
		return isset($this->attributes[$name]) ? $this->attributes[$name] : false;
	}
	
/**
 * If a attribute is "true"
 * 
 * @param string $name
 * @return bool True if it is false otherwise
 */
	public function isAttr($name){
		$attr = $this->getAttribute($name);
		return $attr === $name;
	}
	
/**
 * Slugify $attr with $with
 * 
 * @param string $attr
 * @param string $with
 */
	public static function slugifyAttr($attr, $with = '_'){
		return preg_replace('/[^A-Za-z_0-9-]+/', $with, $attr);
	}
}