<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Const extends Base_Data_Type {

	public function __construct( $value ) {
		$this->value = $value;
	}

	public function set_value( $value ) {
		//
	}

}
