<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Fallback_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Fallback', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'fallback';
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Fallback text', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		if ( $value !== '' ) {
			return $value;
		}

		return $this->get_arg(0);
	}
}
