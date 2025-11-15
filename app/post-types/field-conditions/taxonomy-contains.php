<?php

namespace Voxel\Post_Types\Field_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Taxonomy_Contains extends Base_Condition {
	use Traits\Single_Value_Model;

	public function get_type(): string {
		return 'taxonomy:contains';
	}

	public function get_label(): string {
		return _x( 'Contains term', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return is_array( $value ) && in_array( $this->props['value'], $value );
	}
}
