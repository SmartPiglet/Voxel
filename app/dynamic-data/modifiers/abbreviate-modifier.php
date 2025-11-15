<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Abbreviate_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Abbreviate number', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'abbreviate';
	}

	public function expects(): array {
		return [ static::TYPE_NUMBER ];
	}

	public function get_description(): string {
		return 'Simplifies large numbers e.g. 1k for 1000, 12.5k for 12500, and so on.';
	}

	public function apply( string $value ) {
		if ( ! is_numeric( $value ) ) {
			return $value;
		}

		$precision = $this->get_arg(0);
		if ( ! is_numeric( $precision ) ) {
			$precision = 1;
		}

		return \Voxel\abbreviate_number( $value, $precision );
	}

}
