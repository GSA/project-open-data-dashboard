<?php
/**
 * Main config file for digital strategy report generator
 * Copy this file to config.php and edit as needed for your installation
 */

// Set a timezone if needed for local development, otherwise errors will be included in zip file
// date_default_timezone_set('UTC');

//base directory of project, leave at default for parent directory of this file
define( 'DGS_BASE_DIR', dirname( dirname( __FILE__ ) ) );

//directory where reports will reside afer generation, FALSE to delete after sending to user as a zip file
define( 'DGS_REPORT_DIR', FALSE );

//base url for schema, change to FALSE to use local information only (e.g., to generate agency and items data files)
define( 'DGS_SCHEMA_BASE', 'https://raw.github.com/GSA/digital-strategy/1/' );

//TTL of disk / in-memory cache ( default is 1 hour )
define( 'DGS_TTL', 3600 );

//that's it!