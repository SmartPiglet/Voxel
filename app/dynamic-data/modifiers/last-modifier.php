<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Last_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Last item', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'last';
	}

	public function expects(): array {
		return [ static::TYPE_ARRAY ];
	}

	public function apply( string $value ) {
		$nearest_loopable = $this->_get_nearest_loopable_ancestor();
		if ( $nearest_loopable === null ) {
			return '';
		}

		$loopable = $nearest_loopable['property'];
		$subpath = $nearest_loopable['subpath'];
		$last_index = count( $loopable->get_items() ) - 1;

		$original_index = $loopable->get_current_index();
		$loopable->set_current_index( $last_index );
		$loop_item = $loopable->get_property( $subpath );
		$loopable->set_current_index( $original_index );

		if ( $loop_item === null ) {
			return '';
		}

		return $loop_item->get_value();
	}

}
