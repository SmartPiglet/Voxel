<?php

namespace Voxel\Dynamic_Data\Modifiers\Control_Structures;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Is_Between_Control extends Base_Control_Structure {

	public function get_key(): string {
		return 'is_between';
	}

	public function get_label(): string {
		return _x( 'Is between', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Start', 'modifiers', 'voxel-backend' ),
		] );

		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'End', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function passes( bool $last_condition, string $value ): bool {
		$start = $this->get_arg(0);
		$end = $this->get_arg(1);

		if ( ! ( is_numeric( $value ) && is_numeric( $start ) && is_numeric( $end ) ) ) {
			$value = strtotime( $value );
			$start = strtotime( $start );
			$end = strtotime( $end );

			if ( ! ( is_numeric( $value ) && is_numeric( $start ) && is_numeric( $end ) ) ) {
				return false;
			}
		}

		return floatval( $value ) >= floatval( $start ) && floatval( $value ) <= floatval( $end );
	}
}
