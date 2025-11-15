<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Replace_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Replace text', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'replace';
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Search', 'modifiers', 'voxel-backend' ),
		] );

		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Replace with', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		return str_ireplace( $this->get_arg(0), $this->get_arg(1), $value );
	}

}
