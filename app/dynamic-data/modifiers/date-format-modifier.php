<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Format_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Date format', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'date_format';
	}

	public function expects(): array {
		return [ static::TYPE_DATE ];
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Date format', 'modifiers', 'voxel-backend' ),
			'description' => _x( 'Leave empty to use the format set in site options', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		$timestamp = strtotime( $value );
		if ( $timestamp === false ) {
			return $value;
		}

		$format = $this->get_arg(0);
		if ( $format === '' ) {
			$format = get_option( 'date_format' );
		}

		return date_i18n( $format, $timestamp );
	}

}
