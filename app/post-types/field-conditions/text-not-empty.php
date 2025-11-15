<?php

namespace Voxel\Post_Types\Field_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Not_Empty extends Base_Condition {

	public function get_type(): string {
		return 'text:not_empty';
	}

	public function get_label(): string {
		return _x( 'Is not empty', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return ! empty( trim( $value ?? '' ) );
	}
}
