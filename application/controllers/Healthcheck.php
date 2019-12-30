<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// An explicit healthcheck endpoint
class Healthcheck extends CI_Controller
{
	// Right now we just return a 200 and a simple string to confirm that
	// application logic is properly invocable.
	public function index()
	{
		$this->output
			->set_status_header(200, "We're all good in the hood")
			->set_output("dashboard is OK\n");
	}
}
/* End of file '/Healthcheck.php' */
/* Location: ./application/controllers//Healthcheck.php */
