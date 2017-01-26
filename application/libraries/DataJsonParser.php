<?php 

class DataJsonParser implements \JsonStreamingParser\Listener {
    private $_json;

    private $_dataset_stack;
    private $_front_matter;    
    
    private $_stack;
    private $_key;
    // Level is required so we know how nested we are.
    private $_level;

    public $out_file;
    public $_array_count;

    public function file_position($line, $char) {

    }

    public function get_json() {
        return $this->_json;
    }

    public function start_document() {
        $this->_stack = array();
        $this->_level = 0;
        $this->_array_count = 0;
        $this->_front_matter = array();
        $this->_dataset_stack = false;
        
        // Key is an array so that we can can remember keys per level to avoid it being reset when processing child keys.
        $this->_key = array();
    }

    public function end_document() {
        
        // remove trailing comma from last object
        fseek ( $this->out_file, -2, SEEK_CUR);  

        // end file
        fwrite($this->out_file, "\n]}");
        fclose($this->out_file);
    }

    public function start_object() {
        $this->_level++;
        array_push($this->_stack, array());
        // Reset the stack when entering the second level
        if($this->_level == 2) {
            $this->_stack = array();
            $this->_key[$this->_level] = null;
        }
    }

    public function end_object() {
        $this->_level--;

        $obj = array_pop($this->_stack);
        if (empty($this->_stack)) {
            // doc is DONE!
            $this->_json = $obj;
        } else {
            $this->value($obj);                
        }


        // Output the stack when returning to the second level
        if($this->_level == 2 && $this->_key[1] == 'dataset') {
            
            $this->_array_count++;
            $json_line = '';

            // If this is the first line of the file, write the frontmatter
            if ($this->_dataset_stack === false) {
                $front_matter = json_encode($this->_front_matter);
                $front_matter = substr($front_matter, 0, strlen($front_matter) - 1);
                $json_line = $front_matter . ',"dataset":[' . "\n";
                $this->_dataset_stack = true;
            } 

            $json_line .= json_encode($this->_json) . ",\n";
            fwrite($this->out_file, $json_line);
        }
    }

    public function start_array() {
        $this->start_object();
    }

    public function end_array() {
        $this->end_object();
    }

    // Key will always be a string
    public function key($key) {
        $this->_key[$this->_level] = $key;
    }

    // Note that value may be a string, integer, boolean, null
    public function value($value) {
        $obj = array_pop($this->_stack);
        if (isset($this->_key[$this->_level])) {
            $obj[$this->_key[$this->_level]] = $value;
            
            if($this->_level == 1 && $this->_dataset_stack === false) {
                $this->_front_matter[$this->_key[$this->_level]] = $value;
            }

            $this->_key[$this->_level] = null;
        } else {
            array_push($obj, $value);
        }
        array_push($this->_stack, $obj);
    }

    public function whitespace($whitespace) {
        // do nothing
    }
}