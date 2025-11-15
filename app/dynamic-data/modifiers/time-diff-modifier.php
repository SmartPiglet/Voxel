<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Time_Diff_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Time diff', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'time_diff';
	}

	public function expects(): array {
		return [ static::TYPE_DATE ];
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Timezone to compare against', 'modifiers', 'voxel-backend' ),
			'description' => _x( 'Enter the timezone identifier e.g. "Europe/London", or an offset e.g. "+02:00". Leave empty to use the timezone set in site options.', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		$timestamp = strtotime( $value );
		if ( ! $timestamp ) {
			return $value;
		}

		try {
			$timezone = new \DateTimeZone( $this->get_arg(0) ?? null );
		} catch ( \Exception $e ) {
			$timezone = wp_timezone();
		}

		return human_time_diff( $timestamp, time() + $timezone->getOffset( \Voxel\utc() ) );
	}

}
