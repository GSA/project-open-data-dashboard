<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // load gearman library
        $this->load->library('lib_gearman');
    }

    public static function run_validate_json($job) {
        $data = unserialize($job->workload());
        print_r($data);
        sleep(2);
        echo "JSON validation run is done.\n\n";
    }

    public static function run_check_links($job) {
        $data = unserialize($job->workload());
        sleep(10);
        print_r($data);
        sleep(10);
        echo "Link checking run is done.\n\n";
    }

    public function client() {
        $this->lib_gearman->gearman_client();

        $urls_to_check = array(
            'http://data.gov',
            'http://data.gov',
            'http://data.gov',
            'http://data.gov',
            'http://data.gov',
            'http://data.gov',
            'http://data.gov',
            'http://data.gov'
        );

        $total_links = count($urls_to_check);
        $batch_size = 2;

        $url_batched = array_chunk($urls_to_check, $batch_size);

        foreach ($url_batched as $url_batch) {
            $this->lib_gearman->do_job_background('check_links', serialize($url_batch));
        }
    }

    public function worker() {
        $worker = $this->lib_gearman->gearman_worker();

        $this->lib_gearman->add_worker_function('check_links', 'Jobs::run_check_links');

        while ($this->lib_gearman->work()) {
            if (!$worker->returnCode()) {
                echo "worker done successfully \n";
            }
            if ($worker->returnCode() != GEARMAN_SUCCESS) {
                echo "return_code: " . $this->lib_gearman->current('worker')->returnCode() . "\n";
                break;
            }
        }
    }

}
