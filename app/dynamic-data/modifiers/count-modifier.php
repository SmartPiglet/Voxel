<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Count_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Count all', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'count';
	}

	public function expects(): array {
		return [ static::TYPE_ARRAY ];
	}

	public function apply( string $value ) {
		$property = $this->tag->get_property();
		if ( ! $property ) {
			return 0;
		}

		if ( $property->get_type() === 'object-list' ) {
			return count( $property->get_items() );
		}

		$loopable = $this->_get_nearest_loopable_ancestor();
		if ( $loopable === null ) {
			return 0;
		}

		return count( $loopable['property']->get_items() );
	}

}
