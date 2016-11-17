<?php
/**
 * Generates the action-items.json and action-items.xml files
 * based off of the data in action-items.php
 */

require_once 'config/items.php';
require_once 'load.php';

//store generator version
$dgs_items = dgs_prepend_generator_version( $dgs_items );

//sort action items by ID ascending
dgs_sort( $dgs_items->items );

//JSON encode the action items array into the json file
file_put_contents( DGS_BASE_DIR .'/data/items.json', json_encode( $dgs_items ) );

//build XML files
$xml = new SimpleXMLElement( '<items></items>' );
$xml = dgs_to_xml( $dgs_items, $xml );
file_put_contents( 'data/items.xml', dgs_tidy_xml( $xml ) );