<?php
/**
 * Generates the agencies.json and agencies.xml files
 * based off of the data in action-items.php
 */

require_once 'config/agencies.php';
require_once 'load.php';

//store generator version
$dgs_agencies = dgs_prepend_generator_version( $dgs_agencies );

//sort agencies by name alpha ascending
dgs_sort( $dgs_agencies->agencies, 'name' );

//output JSON
file_put_contents( DGS_BASE_DIR . '/data/agencies.json', json_encode( $dgs_agencies ) );

//now onto XML
$xml = new SimpleXMLElement( '<agencies></agencies>' );
$xml = dgs_to_xml( $dgs_agencies, $xml );
file_put_contents( 'data/agencies.xml', dgs_tidy_xml( $xml ) );