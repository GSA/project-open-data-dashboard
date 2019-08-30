<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Upload Arguments
|--------------------------------------------------------------------------
*/

$project_shared_path = $config['project_shared_path'];
$config['upload_path'] = $project_shared_path . '/uploads/'; // absolute path
$config['allowed_types'] = '*'; //'gif|jpg|png|csv|txt|JPG|GIF|PNG|CSV|TXT';
$config['max_size'] = '500000';
