<?php

namespace Voxel\Post_Types\Field_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Gt extends Base_Condition {
	use Traits\Single_Value_Model;

	public function get_type(): string {
		return 'number:gt';
	}

	public function get_label(): string {
		return _x( 'Greater than', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return (float) $value > (float) $this->props['value'];
	}
}
