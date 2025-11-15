<?php

namespace Voxel\Dynamic_Data\Modifiers\Control_Structures;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Is_Equal_To_Control extends Base_Control_Structure {

	public function get_key(): string {
		return 'is_equal_to';
	}

	public function get_label(): string {
		return _x( 'Is equal to', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Value', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function passes( bool $last_condition, string $value ): bool {
		return $value === $this->get_arg(0);
	}
}
