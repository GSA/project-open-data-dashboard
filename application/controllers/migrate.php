<?php

class Migrate extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if (php_sapi_name() != 'cli') {
            exit("Execute via command line: php index.php migrate");
        }
    }

    public function index() {

        $this->load->library('migration');

        if ($this->migration->current() === FALSE) {
            show_error($this->migration->error_string());
        } else {
            echo 'The migration was run' . PHP_EOL;
        }
    }

}
