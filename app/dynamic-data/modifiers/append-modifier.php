<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Append_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Append text', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'append';
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Text to append', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		return $value . $this->get_arg(0);
	}

}
