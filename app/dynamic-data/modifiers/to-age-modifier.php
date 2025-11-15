<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class To_Age_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Get age', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'to_age';
	}

	public function expects(): array {
		return [ static::TYPE_DATE ];
	}

	public function apply( string $value ) {
		$timestamp = strtotime( $value );
		if ( ! $timestamp ) {
			return '';
		}

		$now = time();
		if ( $now < $timestamp ) {
			return '';
		}

		return floor( ( $now - $timestamp ) / ( 365.25 * DAY_IN_SECONDS ) );
	}

}
