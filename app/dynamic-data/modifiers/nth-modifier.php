<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Nth_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Nth item', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'nth';
	}

	public function expects(): array {
		return [ static::TYPE_ARRAY ];
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Index', 'modifiers', 'voxel-backend' ),
			'description' => _x( 'Retrieve an item by index (first is 0). Negative indexes are allowed (e.g., -1 for last item).', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		$nearest_loopable = $this->_get_nearest_loopable_ancestor();
		if ( $nearest_loopable === null ) {
			return '';
		}

		$loopable = $nearest_loopable['property'];
		$subpath = $nearest_loopable['subpath'];
		$item_count = count( $loopable->get_items() );

		$requested_index = $this->get_arg(0);
		if ( ! is_numeric( $requested_index ) ) {
			return '';
		}

		if ( $requested_index < 0 ) {
			$requested_index = $item_count - abs( $requested_index );
		}

		$original_index = $loopable->get_current_index();
		$loopable->set_current_index( $requested_index );
		$loop_item = $loopable->get_property( $subpath );
		$loopable->set_current_index( $original_index );

		if ( $loop_item === null ) {
			return '';
		}

		return $loop_item->get_value();
	}

}
