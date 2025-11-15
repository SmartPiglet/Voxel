<?php

namespace Voxel\Post_Types\Field_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Switcher_Checked extends Base_Condition {

	public function get_type(): string {
		return 'switcher:checked';
	}

	public function get_label(): string {
		return _x( 'Is checked', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return !! $value;
	}
}
