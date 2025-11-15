<?php

namespace Voxel\Dynamic_Data\Modifiers\Control_Structures;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Contains_Control extends Base_Control_Structure {

	public function get_key(): string {
		return 'contains';
	}

	public function get_label(): string {
		return _x( 'Contains', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Value', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function passes( bool $last_condition, string $value ): bool {
		return mb_stripos( $value, $this->get_arg(0) ) !== false;
	}
}
