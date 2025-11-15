<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class List_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'List all', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'list';
	}

	public function expects(): array {
		return [ static::TYPE_ARRAY ];
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Item separator', 'modifiers', 'voxel-backend' ),
			'placeholder' => ', ',
		] );

		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Last item separator', 'modifiers', 'voxel-backend' ),
			'placeholder' => ', ',
		] );

		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Item prefix', 'modifiers', 'voxel-backend' ),
		] );

		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Item suffix', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		$nearest_loopable = $this->_get_nearest_loopable_ancestor();
		if ( $nearest_loopable === null ) {
			return $value;
		}

		$loopable = $nearest_loopable['property'];
		$subpath = $nearest_loopable['subpath'];

		$value = [];
		$original_index = $loopable->get_current_index();
		foreach ( $loopable->get_items() as $index => $item ) {
			$loopable->set_current_index( $index );
			$_loop_item = $loopable->get_property( $subpath );
			if ( $_loop_item === null ) {
				continue;
			}

			$_loop_item_value = $_loop_item->get_value();
			if ( $_loop_item_value !== null && $_loop_item_value !== '' ) {
				$value[] = $_loop_item_value;
			}
		}

		$loopable->set_current_index( $original_index );

		if ( empty( $value ) ) {
			return '';
		}

		$prefix = $this->get_arg(2);
		$suffix = $this->get_arg(3);
		if ( ! empty( $prefix ) || ! empty( $suffix ) ) {
			$value = array_map( function( $item ) use ( $prefix, $suffix ) {
				return $prefix.$item.$suffix;
			}, $value );
		}

		if ( count( $value ) === 1 ) {
			return array_shift( $value );
		}

		$last_item = array_pop( $value );
		$separator = $this->get_arg(0);
		if ( $separator === '' ) {
			$separator = ', ';
		}

		$last_separator = $this->get_arg(1);
		if ( $last_separator === '' ) {
			$last_separator = $separator;
		}

		return join( $separator, $value ) . $last_separator . $last_item;
	}

}
