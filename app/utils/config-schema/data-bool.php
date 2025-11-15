<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Bool extends Base_Data_Type {

	public function set_value( $value ) {
		if ( $value === null ) {
			return;
		}

		$this->value = !! $value;
	}

}
