<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Merge extends CI_Controller {


	function __construct() {
		parent::__construct();

		$this->load->helper('url');
		$this->load->helper('api');

	}


	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('merge');
	}

}
