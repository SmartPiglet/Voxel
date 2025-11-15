<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Prepend_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Prepend text', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'prepend';
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Text to prepend', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		return $this->get_arg(0) . $value;
	}

}
