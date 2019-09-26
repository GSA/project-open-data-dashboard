<?php defined('BASEPATH') OR exit('No direct script access allowed');


// In environments configured with Ansible, this file will either be
// overwritten, or superseded by `config/(development|production/...php`
// This file is in force on docker-compose and cloud.gov platforms
/*
|--------------------------------------------------------------------------
| Upload Arguments
|--------------------------------------------------------------------------
*/

// This following line doesn't work and I (peterb) don't know why
//   $project_shared_path = $config['project_shared_path'];
// so using this:
$project_shared_path = getenv("PROJECT_SHARED_PATH") ?: "/var/www/app";

$config['upload_path'] = $project_shared_path . '/uploads/'; // absolute path
$config['allowed_types'] = '*'; //'gif|jpg|png|csv|txt|JPG|GIF|PNG|CSV|TXT';
$config['max_size'] = '500000';
