<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Truncate_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Truncate text', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'truncate';
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Max length', 'modifiers', 'voxel-backend' ),
			'placeholder' => 130,
		] );
	}

	public function apply( string $value ) {
		$max_length = $this->get_arg(0);
		return \Voxel\truncate_text( $value, absint( is_numeric( $max_length ) ? $max_length : 130 ) );
	}

}
