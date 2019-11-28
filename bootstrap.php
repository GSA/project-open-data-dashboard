<?php

require 'vendor/autoload.php';

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
if (file_exists(FCPATH . '/.env')) {
    $dotenv = new \Dotenv\Dotenv(FCPATH);
    $dotenv->load();
}
