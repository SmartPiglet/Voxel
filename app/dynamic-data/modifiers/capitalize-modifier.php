<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Capitalize_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Capitalize', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'capitalize';
	}

	public function apply( string $value ) {
		return ucwords( $value );
	}

}
