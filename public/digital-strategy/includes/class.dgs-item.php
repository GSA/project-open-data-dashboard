<?php

class DGS_Action_Item {

	public $id;     // e.g., 3.1
	public $parent;    //e.g., 3.1 is 3.1.1's parent
	public $text;     // e.g., 'Produce guidance on...'
	public $due;    // e.g., '90 Days', '6 Months', or '12 Months'
	public $due_date;   // e.g., '2012/08/21'
	public $fields = array();  //array of field objects
	public $multiple = false; //whether multiple responses are allowed
	private $start_date = 1337745600; //Unix timestamp representing May 23, 2012, the release of the strategy

	function __construct( $data = null ) {

		//parse any property passed in data array into object
		if ( is_array( $data ) )
			foreach ( get_object_vars( $this ) as $var => $value )
				if ( isset( $data[ $var ] ) )
					$this->$var = $data[ $var ];

				//if only relative date is passed, calculate the absolute date
				if ( $this->due_date == null && $this->due != null )
					$this->due_date = date( 'Y/m/d', strtotime( $this->due, $this->start_date ) );

				//ensure field names are prefixed and unique
				foreach ( $this->fields as &$field )
					$field->name = $this->maybe_prefix_field_name( $field->name );

	}


	/**
	 * Because the same field may be used across multiple action items
	 * and we want to be able to $POST the data, each field name must be unique.
	 * This function prefixes each field name with a slugified version of the action item ID.
	 * e.g., "name" in 1.2.3. becomes "1-3-2-name"
	 */
	private function maybe_prefix_field_name( $name ) {

		$prefix = str_replace( '.', '-', $this->id ) . '-';

		if ( strpos( $name, $prefix ) === 0 )
			return $name;

		return $prefix . $name;

	}


}


class DGS_Field {

	public $type;
	public $name;
	public $label;
	public $options = array();
	public $value;

	function __construct( $data = null ) {

		//parse any property passed in data array into object
		if ( is_array( $data ) )
			foreach ( get_object_vars( $this ) as $var => $value )
				if ( isset( $data[ $var ] ) )
					$this->$var = $data[ $var ];

	}


}


class DGS_Option {

	public $label;
	public $value;

	function __construct( $value, $label ) {
		$this->value = $value;
		$this->label = $label;

	}


}
