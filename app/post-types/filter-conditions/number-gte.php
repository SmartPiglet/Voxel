<?php

namespace Voxel\Post_Types\Filter_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Gte extends Base_Condition {
	use Traits\Single_Value_Model;

	public function get_type(): string {
		return 'number:gte';
	}

	public function get_label(): string {
		return _x( 'Greater than or equal to', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return (float) $value >= (float) $this->props['value'];
	}
}
