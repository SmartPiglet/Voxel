<?php

namespace Voxel\Post_Types\Filter_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Taxonomy_Not_Contains extends Base_Condition {
	use Traits\Single_Value_Model;

	public function get_type(): string {
		return 'taxonomy:not_contains';
	}

	public function get_label(): string {
		return _x( 'Does not contain term', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return ! ( is_array( $value ) && in_array( $this->props['value'], $value ) );
	}
}
