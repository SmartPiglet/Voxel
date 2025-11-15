<?php

namespace Voxel\Dynamic_Data\Data_Types;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Object_List extends Base_Data_Type {

	protected
		$properties_cb,
		$properties = [],
		$items_cb,
		$items,
		$aliases = [],
		$current_index = 0;

	public function get_type(): string {
		return 'object-list';
	}

	public function properties( callable $cb ): self {
		$this->properties_cb = $cb;
		return $this;
	}

	public function items( callable $cb ): self {
		$this->items_cb = $cb;
		return $this;
	}

	public function get_properties( int $index ): array {
		$items = $this->get_items();
		if ( ! isset( $items[ $index ] ) ) {
			return [];
		}

		if ( ! isset( $this->properties[ $index ] ) ) {
			$properties = ($this->properties_cb)( $index, $items[ $index ] );

			if ( is_array( $properties ) ) {
				$this->properties[ $index ] = $properties;
			} elseif ( $properties instanceof \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group ) {
				$this->properties[ $index ] = $properties->get_properties();
				$this->aliases = $properties->get_aliases();
			} else {
				$this->properties[ $index ] = [];
			}

		}

		return $this->properties[ $index ];
	}

	public function get_items(): array {
		if ( $this->items === null ) {
			$items = ($this->items_cb)();
			if ( ! is_array( $items ) ) {
				$items = [];
			}

			$this->items = array_values( $items );
		}

		return $this->items;
	}

	public function get_aliases(): array {
		return $this->aliases;
	}

	public function set_current_index( int $index ): void {
		$this->current_index = $index;
	}

	public function get_current_index(): int {
		return $this->current_index;
	}

	public function get_property( $path ): ?Base_Data_Type {
		$properties = $this->get_properties( $this->current_index );
		$aliases = $this->get_aliases();

		if ( is_string( $path ) ) {
			$path = explode( '.', $path );
		}

		$list_all = false;
		$property_key = array_shift( $path );
		if ( str_ends_with( $property_key, '[]' ) ) {
			$list_all = true;
			$property_key = mb_substr( $property_key, 0, -2 );
		}

		if ( isset( $aliases[ $property_key ] ) ) {
			$property_key = $aliases[ $property_key ];
		}

		if ( ! isset( $properties[ $property_key ] ) ) {
			return null;
		}

		$property = $properties[ $property_key ];
		$property->set_parent( $this );
		if ( $property->get_type() === 'object' ) {
			if ( empty( $path ) ) {
				return $property;
			}

			return $property->get_property( $path );
		} elseif ( $property->get_type() === 'object-list' ) {
			if ( empty( $path ) ) {
				return $property;
			}

			return $property->get_property( $path );
		} elseif ( $path ) {
			return null;
		} else {
			// backward compatibility (<1.5): support use of property bracket
			// notation (with and without an accompanying .list() modifier)
			if ( $list_all ) {
				$tmp = Tag::String('Items')->render( function() use ( $property_key ) {
					$all = [];
					foreach ( $this->get_items() as $i => $item ) {
						$props = $this->get_properties( $i );
						if ( isset( $props[ $property_key ] ) ) {
							$value = (string) $props[ $property_key ]->get_value();
							if ( $value !== '' ) {
								$all[ $i ] = $props[ $property_key ]->get_value();
							}
						}
					}

					return join( ', ', $all );
				} );

				$tmp->set_parent( $this );

				return $tmp;
			} else {
				return $property;
			}
		}
	}

	public function export( ?\Voxel\Dynamic_Data\Exporter $exporter ): array {
		$subgroup = null;
		$properties = [];
		$aliases = [];

		$_properties = ($this->properties_cb)( 0, null );
		if ( is_array( $_properties ) ) {
			$properties = $_properties;
		} elseif ( $_properties instanceof \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group ) {
			$subgroup = $_properties;
			$properties = $subgroup->get_properties();
			$aliases = $subgroup->get_aliases();
		}

		$exports = [];

		if ( $subgroup ) {
			if ( $exporter ) {
				$exporter->add_subgroup( $subgroup );
			}

			return [
				'type' => $this->get_type(),
				'label' => $this->get_label(),
				'description' => $this->get_description(),
				'hidden' => $this->is_hidden(),
				'aliases' => $aliases,
				'path' => $this->_path,
				'subgroup' => [
					'type' => $subgroup->get_type(),
					'key' => $subgroup->get_export_key(),
				],
			];
		}

		foreach ( $properties as $property_key => $property ) {
			$property->_path = $this->_path;
			$property->_path[] = $property_key;

			$exports[ $property_key ] = $property->export( $exporter );
		}

		return [
			'type' => $this->get_type(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'hidden' => $this->is_hidden(),
			'exports' => $exports,
			'aliases' => $aliases,
			'path' => $this->_path,
		];
	}
}
