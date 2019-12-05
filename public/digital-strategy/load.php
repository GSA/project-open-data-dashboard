<?php
/**
 * Main bootstrap for the slash digital strategy generator
 * Will auto-propegate local copies of data files if they don't exist
 */

if ( !file_exists( dirname( __FILE__ ) . '/config/config.php' ) )
	die( 'Please copy `config-sample.php` to `config.php` in the project\'s `config` directory' );

//grab config file
require_once( dirname( __FILE__ ) . '/config/config.php' );

//get core functions
require_once DGS_BASE_DIR . '/includes/functions.php';

//bootrstrap form generator
require_once DGS_BASE_DIR . '/includes/forms/load.php';

//generator version, bump with each release
define( 'DGS_VERSION', '1.1' );

//bootstrap DSG Generator Core
foreach ( array( 'items', 'agencies' ) as $plural ) {

	//for readability, we need both singular and plural names
	$singular = dgs_singular( $plural );

	//bootstrap core DGS classes
	require_once DGS_BASE_DIR . "/includes/class.dgs-{$singular}.php";

	//each data type array will be loaded into the global scope
	$global_var = "dgs_{$plural}";

	//Hierarchy of where the generator looks for agencies and action items
	// (1) Global variable established prior to load.php running
	// (2) Persistant cache (APC)
	// (3) Local /data/ directory, for non-expired cached files
	// (4) GSA GitHub Repo
	// (5) /config/ directory
	// Note: change DGS_SCHEMA_BASE to FALSE and DGS_TTL to 0 in config.php to force local refresh

	//look for global var already established, no need to re-parse
	if ( isset( $$global_var ) )
		continue;

	//check APC Cache, if it's installed
	if ( function_exists( 'apc_fetch' ) && $cache = apc_fetch ( $global_var ) ) {
		$$global_var = $cache->$plural;
		continue;
	}

	//look for /data/ files and parse (disk cache)
	if ( $file = dgs_get_disk_cache( $plural ) ) {
		$$global_var = $file->$plural;
		continue;
	}

	//try GitHub (mmm... dogfood)
	if ( DGS_SCHEMA_BASE && $file = dgs_get_live( $plural ) ) {
		$$global_var = $file->$plural;
		continue;
	}

	//fallback to local config and rebuild action items
	//note, we don't set our pseudo-ttl for these files since we don't really trust local config
	//on next run, we'll check if GitHub's reachable and will overwrite what we generated here
	require_once DGS_BASE_DIR . "/includes/build-{$plural}.php";
	$var = $$global_var;
	$$global_var = $var[$plural];

}
