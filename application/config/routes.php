<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'docs/routes';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['healthcheck'] = 'healthcheck/index';

$route['export'] = "docs/routes/export";
$route['merge'] = "docs/merge";
$route['upgrade-schema'] = "campaign/upgrade_schema";

$route['digitalstrategy'] = "campaign/digitalstrategy";

$route['validate'] = "campaign/validate";
$route['changeset'] = "campaign/changeset";

$route['datagov/status-update'] = "campaign/status_update";
$route['datagov/status-review-update'] = "campaign/status_review_update";


$route['datagov/(:any)']                = "campaign/$1";
$route['datagov/(:any)/']               = "campaign/$1";
$route['datagov/(:any)/(:any)']         = "campaign/$1/$2";
$route['datagov/(:any)/(:any)/(:any)']  = "campaign/$1/$2/$3";

$route['offices/all']       = "offices/routes/all";
$route['offices/detail']    = "offices/routes/detail";
$route['offices/qa']        = "offices/routes/qa";

// Specific date reports (of the form Y-m-d) can have up to four parameters
$route['offices/(\d{4}-\d{2}-\d{2})/?(:any)?/?(:any)?/?(:any)?/?(:any)?'] =
    "offices/routes/$1/$2/$3/$4";

// "Special" doc pages
$route['docs/(intro|export|user)']  = "docs/routes/$1";

// Search for a file named `$1.md` to transform)
$route['docs/(:any)']               = "docs/routes/$1";

//$route['login'] = "auth/session/github";
$route['logout'] = "user/logout";
$route['account'] = "docs/routes/user";

$old_route = $route;

foreach($old_route as $key => $value) {
    $route['dashboard/'.$key] = $value;
}
$route['dashboard/(:any)'] = '$1';
$route['dashboard'] = $route['default_controller'];

