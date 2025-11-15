<?php

namespace Voxel\Dynamic_Data\Data_Types;

use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Object extends Base_Data_Type {

	protected
		$properties_cb,
		$properties,
		$aliases = [],
		$subgroup;

	public function get_type(): string {
		return 'object';
	}

	public function properties( callable $cb ): self {
		$this->properties_cb = $cb;
		return $this;
	}

	public function get_properties(): array {
		if ( $this->properties === null ) {
			$properties = ($this->properties_cb)();

			if ( is_array( $properties ) ) {
				$this->properties = $properties;
			} elseif ( $properties instanceof \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group ) {
				$this->subgroup = $properties;
				$this->properties = $this->subgroup->get_properties();
				$this->aliases = $this->subgroup->get_aliases();
			} else {
				$this->properties = [];
			}

		}

		return $this->properties;
	}

	public function get_aliases(): array {
		return $this->aliases;
	}

	public function get_property( $path ): ?Base_Data_Type {
		$properties = $this->get_properties();
		$aliases = $this->get_aliases();

		if ( is_string( $path ) ) {
			$path = explode( '.', $path );
		}

		$property_key = array_shift( $path );
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
			return $property;
		}
	}

	public function export( ?\Voxel\Dynamic_Data\Exporter $exporter ): array {
		$properties = $this->get_properties();
		$exports = [];

		if ( $this->subgroup ) {
			if ( $exporter ) {
				$exporter->add_subgroup( $this->subgroup );
			}

			return [
				'type' => $this->get_type(),
				'label' => $this->get_label(),
				'description' => $this->get_description(),
				'hidden' => $this->is_hidden(),
				'aliases' => $this->subgroup->get_aliases(),
				'path' => $this->_path,
				'subgroup' => [
					'type' => $this->subgroup->get_type(),
					'key' => $this->subgroup->get_export_key(),
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
			'aliases' => $this->get_aliases(),
			'path' => $this->_path,
		];
	}
}
