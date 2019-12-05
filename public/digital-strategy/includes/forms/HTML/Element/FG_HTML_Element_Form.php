<?php
/**
 * Class representing a HTML form element
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com>
 * @package fg.HTML.Element
 */

require_once dirname(dirname(__FILE__)) . '/FG_HTML_Element.php';
require_once dirname(dirname(__FILE__)) . '/Form/FG_HTML_Form_DataHandler.php';

class FG_HTML_Element_Form extends FG_HTML_Element{
	
/**
 * @var FG_HTML_Form_DataHandler
 */
	private $dataHandler;
	
/**
 * Initializes the element
 * 
 * @param bool $returnData
 */
	public function __construct($returnData = false){
		if($returnData)
			$this->setHideEndTag(true);
			
		$this->dataHandler = new FG_HTML_Form_DataHandler($returnData);
		parent::__construct('form');
	}
	
/**
 * Adds content to the form
 * 
 * @param FG_HTML_Form_Input_Fillable|string $content
 * @return mixed
 */
	public function add($content){
		if(is_a($content, 'FG_HTML_Form_Input_File') && !$this->attr('enctype')){
			$this->setMultipart(true);
		}
		$return = $this->dataHandler->add($content);
		return $return === $this->dataHandler ? $this : $return;
	}
	
/**
 * get form html
 * 
 * @return string
 */
	public function render(){
		if(!$this->dataHandler->isToReturnData())
			$this->setContent($this->dataHandler->render());
		return parent::render();
	}
	
/**
 * Sets form with attr enctype = multipart/form-data
 * 
 * @param boolean $bool
 * @return FG_HTML_Element_Form this object for method chaining
 */
	public function setMultipart($bool){
		$enc = 'multipart/form-data';
		if($bool){
			$this->setAttribute('enctype', $enc);
		}else{
			if($this->attr('enctype') == $enc){
				$this->attr('enctype', false);
			}
		}
		return $this;
	}
	
/**
 * Populates the form fields with $data
 * 
 * @param array $data
 * @return FG_HTML_Element_Form this object for method chaining
 */
	public function populate(Array $data){
		$this->dataHandler->populate($data);
		return $this;
	}
}