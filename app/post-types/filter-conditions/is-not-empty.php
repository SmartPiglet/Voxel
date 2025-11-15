<?php

namespace Voxel\Post_Types\Filter_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Is_Not_Empty extends Base_Condition {

	public function get_type(): string {
		return 'common:is_not_empty';
	}

	public function get_label(): string {
		return _x( 'Is not empty', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return $value !== null;
	}
}
