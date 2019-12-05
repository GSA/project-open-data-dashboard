<?php
/**
 * Class representing a tag
 * 
 * Examples of tags: 
 * <pre>
 *    <div>content</div>
 *    <span attr="value"></span>
 *    <br />
 * </pre>
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.Markup
 */

class FG_Markup_Tag{
	
/**
 * The tag's name
 * 
 * @var string
 */
	protected $name;
	
/**
 * If this tag is a void one.
 * 
 * A void tag have no content, e.g: <br />
 * 
 * @var boolean
 */
	protected $isVoid;
	
/**
 * The tag's content
 * 
 * @var mixed
 */
	protected $content = false;
	
/**
 * Is to hide finish tag
 * 
 * @var mixed
 */
	protected $hideEndTag = false;
	
/**
 * The tag's attributes
 * 
 * @var array
 */
	protected $attributes = array();
	
/**
 * Initializes the tag
 * 
 * @param string $name The tag's name
 */
	public function __construct($name){
		$this->name = $name;
	}
	
/**
 * Returns tag's name
 * 
 * @return string
 */
	public function getName(){
		return $this->name;
	}
	
/**
 * Sets this tag's content
 * 
 * @param string|array $content The content. If you pass a array, it will be joined by a empty string
 * @return FG_Markup_Tag this object for method chaining
 */
	public function setContent($content){
		if(is_array($content))
			$content = join('', $content);
			
		$this->content = (string)$content;
		return $this;
	}
	
/**
 * Returns the tag's content
 * 
 * @return string
 */
	public function getContent(){
		return $this->content;
	}

/**
 * Sets an attribute
 * 
 * @param string $name Attribute's name
 * @param string $value Attribute's value
 * @return FG_Markup_Tag this object for method chaining
 */
	public function setAttribute($name, $value){
		$this->attributes[$name] = (string)$value;
		return $this;
	}
	
/**
 * Set hide finish tag
 * 
 * @param bool $bool
 * @return FG_Markup_Tag this object for method chaining
 */
	public function setHideEndTag($bool){
		$this->hideEndTag = $bool;
		return $this;
	}
	
/**
 * Sets a list of attributes
 * 
 * @param array $attrs The list, with the format array(attrName => attrValue)
 * @return FG_Markup_Tag this object for method chaining
 */
	public function setAttributes(Array $attrs){
		foreach($attrs as $k => $v){
			$this->setAttribute($k, $v);
		}
		return $this;
	}
	
/**
 * Alias for setAttribute(), setAttributes(), getAttribute() and getAttributes() methods
 * 
 * With this method you can easily write, read or delete an attribute.
 * 
 * Writing:
 * $tag->attr('name', 'value');
 * 
 * Reading:
 * $tag->attr('name');
 * $tag->attr(); // all attributes
 * 
 * Deleting:
 * $tag->attr('name', false);
 * 
 * @param string|array|null $name Attribute's name as string, array of attrName-value pairs or nothing.
 * @param string|bool|null $value Attribute's value or false to unset attribute
 * @return FG_HTML_Element|string|bool|array returns FG_HTML_Element when you are creating a new attribute, string
 * when you are getting one, array when you are getting all or false when you failed to get one attribute.
 */
	public function attr($name = null, $value = null){
		if($name === null){
			return $this->getAttributes();
		}elseif(is_array($name)){
			$this->setAttributes($name);
		}elseif($value === false){ 
			$this->unsetAttribute($name);
		}elseif($value !== null){
			$this->setAttribute($name, $value);
		}else{
			return $this->getAttribute($name);
		}
		return $this;
	}
	
/**
 * Returns the list of attributes
 * 
 * @return array
 */
	public function getAttributes(){
		return $this->attributes;
	}
	
/**
 * Returns one attribute
 * 
 * @param string $name
 * @return string|bool string or false if attribute not exists
 */
	public function getAttribute($name){
		return isset($this->attributes[$name]) ? $this->attributes[$name] : false;
	}

/**
 * Unset one attribute
 * 
 * @param string $name Attribute's name
 * @return FG_Markup_Tag this object for method chaining
 */
	public function unsetAttribute($name){
		if(isset($this->attributes[$name]))
			unset($this->attributes[$name]);
			
		return $this;
	}
	
/**
 * Parses the attributes
 * 
 * @return string
 */
	private function parseAttributes(){
		$output = '';
		foreach($this->attributes as $name => $value){
			$output .= sprintf(' %s="%s"', $name, self::escape($value));
		}
		return $output;
	}
	
/**
 * Escapes a attribute value
 * 
 * @param string $value The value
 * @return string The escaped value
 */
	public static function escape($value){
		return htmlspecialchars($value, ENT_QUOTES);
	}
	
/**
 * Sets this object as void. A void tag have no ending: <br />
 * 
 * @param bool $bool If is void
 * @return FG_Markup_Tag this object for method chaining
 */
	public function setVoid($bool){
		$this->isVoid = $bool;
		return $this;
	}
	
/**
 * Checks if this object is void
 * 
 * @return bool
 */
	public function isVoid(){
		return $this->isVoid;
	}
	
/**
 * Gets string representation of this tag
 * 
 * @return string
 */
	public function render(){
		if($this->isVoid()){
			$output = sprintf('<%s%s />', $this->name, $this->parseAttributes());
		}else{
			$output = sprintf('<%s%s>%s' . ($this->hideEndTag ? '' : $this->end()), $this->name, $this->parseAttributes(), $this->content);
		}
		return $output;
	}
	
/**
 * Gets finish tag
 * 
 * @return string
 */
	public function end(){
		return '</' . $this->name . '>';
	}
	
/**
 * Makes setting and getting attributes magic
 * 
 * @param string $name
 * @param array $args
 * @throws RuntimeException
 */
	public function __call($name, $args){
		$subname = substr($name, 0, 3);
		$attr = strtolower(substr($name, 3));
		
		if($subname == 'set'){
			if(is_array($args[0]))
				return $this->attr($args[0]);
			else
				return $this->attr($attr, $args[0]);
		}elseif($subname == 'get'){
			return $this->attr($attr);
		}else{
			throw new RuntimeException(sprintf('Method %s not found', $name));
		}
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