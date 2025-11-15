<?php

namespace Voxel\Post_Types\Filter_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Contains extends Base_Condition {
	use Traits\Single_Value_Model;

	public function get_type(): string {
		return 'text:contains';
	}

	public function get_label(): string {
		return _x( 'Contains', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		if ( ! is_string( $value ) ) {
			return false;
		}

		return str_contains( strtolower( $value ), strtolower( $this->props['value'] ) );
	}
}
