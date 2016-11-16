<?php
/**
 * Recieves the form submission and generates the various response files
 *
 */

require_once DGS_BASE_DIR . '/load.php';

//strip all HTML from input
array_walk_recursive( $_POST, create_function( '&$val, $index', '$val=strip_tags( $val );' ) );

//set report headers
$report = (object) array(
	'agency' => $_POST['agency'],
	'generated' => date( 'Y-m-d H:i:s' ),
	'items' => $dgs_items,
);

//merge POST data into report array
foreach ( $dgs_items as &$item ) {
	foreach ( $item->fields as &$field ) {

		if ( !isset( $_POST[ $field->name ] ) || empty( $_POST[ $field->name ] ) )
			continue;

		//single value, just store as value
		if ( !$item->multiple && !is_array( $_POST[ $field->name] ) ) {

			$field->value = $_POST[ $field->name ];
			continue;

		}

		//multiple possible values
		$field->value = array(); $i = 0;
		while ( isset( $_POST[ $field->name][$i] ) ) {

			//don't store empty values
			if ( empty( $_POST[ $field->name][$i] ) ) {
				$i++;
				continue;
			}

			$field->value[] = $_POST[ $field->name][$i];
			$i++;

		}

	}

}

// Files will be created in a temporary directory and added to zip file.

//create temporary scratch directory in PHP's default temp directory
//this allows the project to run on both unix and WAMP systems (e.g., where /tmp/ doesn't exist)
$dir = sys_get_temp_dir() . '/dgs-' . md5( time() );
mkdir( $dir );

//generate the payload

//json
file_put_contents( $dir . '/digitalstrategy.json', json_encode( $report ) );

//xml
$xml = new SimpleXMLElement( '<report></report>' );
$xml = dgs_to_xml( $report, $xml );
file_put_contents(  $dir . '/digitalstrategy.xml', dgs_tidy_xml( $xml ) );

//html
file_put_contents( $dir . '/digitalstrategy.html', dgs_to_html( $report ) );

// Create zip file.
$filename = "{$report->agency}-report.zip";
dgs_zip( $dir, "{$dir}/$filename" );

//send headers
header( 'Content-Type: application/zip' );
header( "Content-Disposition: attachment; filename=$filename" );
header( 'Content-Length: ' . filesize( "{$dir}/$filename" ) );

//read file
readfile( "{$dir}/$filename" );

//Check for a value in the report directory constant
//if it exists, move the files into the report directory for easy access before clearing the scratch directory
if ( DGS_REPORT_DIR )
	copy( $dir, DGS_REPORT_DIR );

//cleanup
dgs_cleanup( $dir );

exit();
