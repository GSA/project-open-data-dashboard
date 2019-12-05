<?php
/**
 * Recusively travserses through an array to propegate SimpleXML objects
 * @param array $array the array to parse
 * @param object $xml the Simple XML object (must be at least a single empty node)
 * @return object the Simple XML object (with array objects added)
 */
function dgs_to_xml( $array, $xml ) {

	//array of keys that will be treated as attributes, not children
	$attributes = array( 'id' );

	//recursively loop through each item
	foreach ( $array as $key => $value ) {

		//if this is a numbered array,
		//grab the parent node to determine the node name
		if ( is_numeric( $key ) )
			$key = dgs_singular( $xml->getName() );

		//if this is an attribute, treat as an attribute
		if ( in_array( $key, $attributes ) ) {
			$xml->addAttribute( $key, htmlentities($value, ENT_XML1) );

			//if this value is an object or array, add a child node and treat recursively
		} else if ( is_object( $value ) || is_array( $value ) ) {
				$child = $xml->addChild(  $key );
				$child = dgs_to_xml( $value, $child );

				//simple key/value child pair
			} else {
			$xml->addChild( $key, htmlentities($value, ENT_XML1) );
		}

	}

	return $xml;

}


/**
 * Given a plural node name, converts to singular for human-readability in XML
 * @param string $plural the plural string
 * @return string the singular string if possible, otherwise the plural given
 */
function dgs_singular( $plural ) {

	//translation array of plural => singular
	$trans = array(
		'agencies' => 'agency',
		'options'  => 'option',
		'fields'   => 'field',
		'items'    => 'item' );

	//no translation, safe fallback, don't err out
	if ( !array_key_exists( $plural, $trans ) )
		return $plural;

	//return singular version of node name
	return $trans[ $plural ];

}


/**
 *SimpleXML removes all line breaks, but we want pretty XML
 *Load the simpleXML object into DomDocument to format
 */
function dgs_tidy_xml( $xml ) {

	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML( $xml->asXML() );
	return $dom->saveXML();

}


/**
 * Zips all files in a given folder
 * @param string the path to the folder to zip
 * @param string the path to the destination zip
 */
function dgs_zip( $dir, $destination ) {

	//create zip
	$zip = new ZipArchive();
	$zip->open( $destination, ZIPARCHIVE::CREATE );

	//loop through all files in directory
	foreach ( glob( $dir . '/*' ) as $path ) {

		//make path within zip relative to zip base, not server root
		$local_path = str_replace( "$dir/", '', $path );

		//add file
		$zip->addFile( realpath( $path ), $local_path );

	}

	$zip->close();
}


/**
 * Deletes temporary folder (rmdir requires empty directories)
 * @param string $dir the directory to delete
 */
function dgs_cleanup( $dir ) {

	foreach ( glob( $dir . '/*' ) as $file )
		unlink( $file );

	rmdir( $dir );

}


/**
 * Given an Agency ID, return its human-readable name
 * @param string $abbr the agency ID, e.g., FBI
 * @return string the human-readable name
 */
function dgs_agency_name( $abbr ) {
	global $dgs_agencies;

	foreach ( $dgs_agencies as $agency )
		if ( $agency->id == $abbr )
			return $agency->name;

		return false;

}


/**
 * Simple template function to include the HTML header
 */
function dgs_header() {
	include DGS_BASE_DIR . '/includes/header.php';
}


/**
 * Template function to include the footer
 */
function dgs_footer() {
	include DGS_BASE_DIR . '/includes/footer.php';
}


/**
 * Convert a generated report into HTML
 * @param object $report the report object
 */
function dgs_to_html( $report ) {
	ob_start();
	dgs_header(); ?>

<!-- ************ BEGIN BARE-BONES HTML; COPY STARTING HERE IF ADDING TO A CMS ************ -->

<h1>Digital Government Strategy Report for the <?php echo dgs_agency_name( $report->agency ); ?></h1>

<?php foreach ( $report->items as $item ) {

		//if this is a sub-item, output as an h3, otherwise, output as an h2
		$tag = ( $item->parent == null ) ? 'h2' : 'h3';

		echo "<{$tag}>{$item->id}. ".htmlentities($item->text, ENT_XHTML)."</{$tag}>\n";

		//simple single field, just output
		if ( !$item->multiple ) {

			foreach ( $item->fields as $field )
				echo "\t<strong>{$field->label}</strong>: ".htmlentities($field->value, ENT_XHTML)."<br />\n";

			continue;

		}

		//we've got a multi-response field
		//right now they're grouped by field, we want the answers to align
		$values = array();
		foreach ( $item->fields as $field )
			foreach ( $field->value as $ID => $value )
				$values[$ID][$field->label] = $value;

		foreach ( $values as $value ) {

			foreach ( $value as $k => $v )
				echo "\t<strong>{$k}</strong>: ".htmlentities($v, ENT_XHTML)."<br />\n";

			echo "<hr >";
		}

	} //close item loop ?>

<p><em>Last updated <?php echo date( 'F n, o', strtotime( $report->generated ) ); ?> at <?php echo date( 'g:i a', strtotime( $report->generated ) ); ?></em></p>

<!-- ************ END BARE-BONES HTML; STOP COPYING HERE IF ADDING TO A CMS ************ -->

<?php dgs_footer();
	return ob_get_clean();
}


/**
 * Handles file import via JSON upload
 * @reurn array array of all values to propegate form
 */
function dgs_values() {

	$values = array();

	//santity checks
	if ( empty( $_FILES ) || empty( $_FILES['import'] ) || ( $_FILES['import']['error'] && !$_FILES['import']['autoimport'] ) )
		return array();

	//attemp to JSON decode upload
	$import = json_decode( file_get_contents( $_FILES['import']['tmp_name'] ) );

	//not valid JSON, proceed no further
	if ( !$import )
		return array();

	//grab agency from outside item array
	$values[ 'agency' ] = $import->agency;

	//loop through each field and create key/value pairs to pass to form generator
	// but lets sanitize before we do for good measure
	foreach ( $import->items as $item ) {

		//simple field
		if ( !$item->multiple ) {

			foreach ( $item->fields as $field )
				$values[ $field->name ] = strip_tags( $field->value );

		} else {

			//multi field
			foreach ( $item->fields as $field )
				foreach ( $field->value as $key => $value )
					$values[ $field->name ][$key] = strip_tags( $value );

		}

	}

	return $values;

}


/**
 * Atempts to retrieve schema files from disk cache
 * @param string $file the file to retrieve (either items or agencies)
 * @return object the json_decoded file being requested
 */
function dgs_get_disk_cache( $file ) {

	//file just isn't there
	if ( !file_exists( DGS_BASE_DIR . "/data/{$file}.json" ) )
		return false;

	//file was created locally off of config, say cache in invalid
	// and hope we can grab fresh from GitHub
	if ( !file_exists( DGS_BASE_DIR . "/data/{$file}.ttl" ) )
		return false;

	//file's expired
	$ttl = file_get_contents( DGS_BASE_DIR . "/data/{$file}.ttl" );
	if ( ( time() - $ttl ) > DGS_TTL )
		return false;

	$file = file_get_contents( DGS_BASE_DIR . "/data/{$file}.json" );

	//bad JSON
	if ( !$data = json_decode( $file ) )
		return false;

	return $data;

}


/**
 * Make a call to GSA's GitHub repo to retrieve a new version of the schema
 * @param string $file the file to retrieve (either items or agencies)
 * @return object the json_decoded file being requested
 */
function dgs_get_live( $file ) {

	//try to get file
	$raw = file_get_contents( DGS_SCHEMA_BASE . "{$file}.json" );

	//http request failed
	if ( !$raw )
		return false;

	//bad JSON
	if ( !$data = json_decode( $raw ) )
		return false;

	//cache to disk
	file_put_contents( DGS_BASE_DIR . "/data/{$file}.json", $raw ); //store actual file
	file_put_contents( DGS_BASE_DIR . "/data/{$file}.ttl", time() ); //store a simple timestamp so we can ttl

	//try APC
	if ( function_exists( 'apc_store' ) )
		apc_store( 'dgs_' . $file, $data, DGS_TTL );

	return $data;

}

/**
 * Determines the maximum number of values for a given item
 * @param object $item the item object
 * @param array the import array
 * @return int the maximum number of responses to any field within the item
 */
function dgs_max_values( $item, $import ) {

	$max = 1;

	if ( !$item->multiple )
		return $max;

	foreach ( $item->fields as $field ) {

		//didn't import this field
		if ( !isset( $import[ $field->name ] ) )
			continue;

		$field = $import[ $field->name ];

		if ( sizeof( $field ) > $max )
			$max = sizeof( $field );
	}

	return $max;

}

/**
 * Helper function to sort items and agencies before generating
 * @param array $items array of agency objects
 * @param string $field field to sort by (optional, default ID)
 * @param int $dir sort direction, either SORT_ASC or SORT_DESC (optional, default ASC)
 */
function dgs_sort( &$items, $field = 'id' , $dir = SORT_ASC ) {

	foreach ($items as $obj)
    	$order[ $obj->$field ] = $obj;

    array_multisort( $order, $dir, $items );

}

function dgs_prepend_generator_version( $array ) {

	return array_merge( array( 'generator_version' => DGS_VERSION ), $array );

}