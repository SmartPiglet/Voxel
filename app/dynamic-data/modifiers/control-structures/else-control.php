<?php

namespace Voxel\Dynamic_Data\Modifiers\Control_Structures;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Else_Control extends Base_Control_Structure {

	public function get_key(): string {
		return 'else';
	}

	public function get_label(): string {
		return _x( 'Else', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Content', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function passes( bool $last_condition, string $value ): bool {
		return ! $last_condition;
	}

	public function apply( string $value ) {
		return $this->get_arg(0);
	}
}
