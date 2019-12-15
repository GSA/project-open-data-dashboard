<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed gearman');
/**
 *
 * Class to utilize Gearman http://gearman.org/
 * @author Aniruddha Kale
 * @author Sunil Sadasivan <sunil@fancite.com>
 * @author Bo-Yi Wu <appleboy.tw@gmail.com>
 */
class Lib_gearman
{

    public $gearman_host = array();
    public $gearman_port = array();
    protected $ci;
    public $errors = array();
    public $client = null;
    public $worker = null;
    public $priority = array('high', 'low', 'normal');

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->ci =& get_instance();

        if (!$this->is_supported()) {
            return false;
        }
    }

    /**
     * Is supported
     *
     * Returns false if Gearman is not supported on the system.
     * If it is, we setup the gearman object & return true
     */
    public function is_supported()
    {
        if ( ! extension_loaded('gearman')) {
            log_message('error', 'The Gearman Extension must be loaded to use Gearman client.');

            return false;
        }

        return true;
    }

    /**
     * Function to create a gearman client
     *
     * @access public
     * @return void
     */
    public function gearman_client()
    {
        $this->client = new GearmanClient();
        $this->_auto_connect($this->client);

        return $this->client;
    }

    /**
     * Function to create a gearman worker
     *
     * @access public
     * @return void
     */
    public function gearman_worker()
    {
        $this->worker = new GearmanWorker();
        $this->_auto_connect($this->worker);

        return $this->worker;
    }

    /**
     * get worker or client obj
     *
     * @access public
     * @param string
     * @return object
     */
    public function current($obj = 'client')
    {
        return (isset($this->{$obj})) ? $this->{$obj} : false;
    }

    /**
     * Function to assign a function name against an identifier
     *
     * @access public
     * @param string
     * @param string
     * @return void
     */
    public function add_worker_function($identifier,$function_name)
    {
        $this->worker->addFunction($identifier, $function_name);
        log_message('debug', "Gearman Library: Successfully added worker function with identifier $identifier with function $function_name");

    }
    /**
     * Listen for a job
     *
     * @access public
     * @return bool true on sucess, false on failure
     */
    public function work()
    {
        return $this->worker->work();
    }

    /**
     * Perform a job in background for a client
     *
     * @access public
     * @param string
     * @param string
     * @param string [high|low]
     * @return void
     */
    public function do_job_background($function, $param, $priority = 'normal')
    {
        if (!in_array($priority, $this->priority)) {
            return false;
        }

        $callback_function = ($priority == 'normal') ? 'doBackground' : 'do' . ucfirst($priority) . 'Background';
        $this->client->{$callback_function}($function,$param, md5(uniqid(rand(), true)));
        log_message('debug', "Gearman Library: Performed task with function $function with parameter $param");
    }

    /**
     * Perform a job in foreground for a client
     *
     * @access public
     * @param string
     * @param string
     * @param string [high|normal|low]
     * @return string
     */
    public function do_job_foreground($function, $param, $priority = 'normal')
    {
        if (!in_array($priority, $this->priority)) {
            return false;
        }

        $callback_function = 'do' . ucfirst($priority);
        log_message('debug', "Gearman Library: Performed task with function $function with parameter $param");
        return $this->client->{$callback_function}($function, $param, md5(uniqid(rand(), true)));
    }

    /**
     * Runs through all of the servers defined in the configuration and attempts to connect to each
     *
     * @param object
     * @return void
     */
    private function _auto_connect($object)
    {
        $this->ci->load->config('gearman');
        $this->gearman_host = $this->ci->config->item('gearman_server');
        $this->gearman_port = $this->ci->config->item('gearman_port');
        foreach ($this->gearman_host as $key=>$server) {
            if (!$object->addServer($server,$this->gearman_port[$key])) {
                $this->errors[] = "Gearman Library: Could not connect to the server named $key";
                log_message('error', 'Gearman Library: Could not connect to the server named "'.$key.'"');
            } else {
                log_message('debug', 'Gearman Library: Successfully connected to the server named "'.$key.'"');
            }
        }
    }

    /**
     *  Returns worker error
     *
     *  @access public
     *  @return void
     *
     */
    public function error()
    {
        return $this->worker->error();
    }

}
