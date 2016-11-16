<?php

class DGS_Agency {
	public $name;
	public $id;
	public $url;

	function __construct( $name, $id, $url ) {

		foreach ( get_object_vars( $this ) as $var => $value )
			$this->$var = $$var;

	}


}