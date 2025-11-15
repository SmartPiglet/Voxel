<?php

namespace Voxel\Post_Types\Field_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Empty extends Base_Condition {

	public function get_type(): string {
		return 'date:empty';
	}

	public function get_label(): string {
		return _x( 'Is empty', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		return ! strtotime( $value['date'] ?? null );
	}
}
