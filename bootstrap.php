<?php

require realpath(APPPATH . '../vendor/autoload.php');

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
if (file_exists(realpath(APPPATH . '../.env'))) {
    $dotenv = new \Dotenv\Dotenv(realpath(APPPATH . '../'));
    $dotenv->load();
}
