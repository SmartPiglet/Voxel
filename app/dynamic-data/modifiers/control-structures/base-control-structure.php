<?php

namespace Voxel\Dynamic_Data\Modifiers\Control_Structures;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Control_Structure extends \Voxel\Dynamic_Data\Modifiers\Base_Modifier {

	public function get_type(): string {
		return 'control-structure';
	}

	public function apply( string $value ) {
		return $value;
	}

	public function passes( bool $last_condition, string $value ): bool {
		return true;
	}
}
