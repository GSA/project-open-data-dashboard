<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Docs extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('api');
    }

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index($page = 'main') {

        $data = array();

        $docs_path = ($this->config->item('docs_path')) ? $this->config->item('docs_path') : 'https://raw.githubusercontent.com/GSA/project-open-data-dashboard/master/documentation/';
        $docs_path = $docs_path . $page . '.md';
        $docs = @file_get_contents($docs_path);

        if ($docs) {

            $markdown_extra = new Michelf\MarkdownExtra();

            $markdown_text = $docs;

            $markdown_text = $markdown_extra->transform($markdown_text);
            $markdown_text = linkToAnchor($markdown_text);

            $data['docs_html'] = $markdown_text;
        } else {
            $data['docs_html'] = "The documentation file is unavailable";
        }

        $this->load->view('docs', $data);
    }

    public function routes($route) {

        if ($route == 'intro') {
            redirect('offices');
            //$this->load->view('welcome_message');			
        } /*else if ($route == 'export') {
            $this->load->view('export');
        }*/ else if ($route == 'user') {
            $this->load->view('user');
        } else {
            $this->index($route);
        }
    }

    /*
    public function merge() {
        $this->load->view('merge');
    }
     */

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */