<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index($page = 'main')
	{

        // Docs live parallel to the app itself
        $docsPath = realpath(APPPATH . '..').'/documentation/';

        // Check that the page requested is one we actually have
        $docFiles = glob($docsPath.'*.md');
        foreach ($docFiles as $filename) {
            $availableDocs[] = basename($filename, '.md');
        }
        if (!in_array($page, $availableDocs)) {
            show_404($page . ' documentation file is unavailable');
        }

        // Get the Markdown content
		$doc = @file_get_contents($docsPath . $page . '.md');
        if (!$doc) {
            show_404($page . ' documentation file is unavailable');
        }

        // Transform the markdown
        $markdown_extra = new Michelf\MarkdownExtra();
        $markdown_text = $markdown_extra->transform($doc);

        // Turn links into anchors
        $markdown_text = linkToAnchor($markdown_text);

        // Send the HTML to the view
        $data = array();
        $data['docs_html'] = $markdown_text;
		$this->load->view('docs', $data);
	}


	public function routes($route = 'intro') {

		if ($route == 'intro') {
            if ($this->input->method(TRUE) == 'HEAD') {
				// If the HTTP method was HEAD CodeIgniter 3.1.* will return a
				// 303 status. That's bad because our load-balancer doesn't
				// understand a 303 to mean "healthy". So here we're explicitly
				// returning a 302 the same way a GET would to keep the
				// load-balancer happy.

				// This is a temporary fix until BSP's load-balancer explicitly
				// checks our new /healthcheck endpoint instead of this
				// endpoint.
				$this->output->set_status_header(302, 'Hello Mister Load Balancer Sir');
				redirect(base_url().'offices/qa', 'auto', 302);
			} else {
            redirect(base_url().'offices/qa');
			}
		} else if ($route == 'export') {
			$this->load->view('export');
		} else if ($route == 'user') {
			$this->load->view('user');
		} else {
			$this->index($route);
		}

	}


	public function merge() {
		$this->load->view('merge');
	}



}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
