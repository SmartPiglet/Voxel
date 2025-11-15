<?php

namespace Voxel\Dynamic_Data\Data_Groups;

use \Voxel\Dynamic_Data\Modifiers;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Data_Group {

	abstract public function get_type(): string;

	/**
	 * List of group properties.
	 *
	 * @since 1.5
	 */
	abstract protected function properties(): array;

	/**
	 * Get the list of top-level properties and cache for subsequent calls.
	 *
	 * @since 1.5
	 */
	protected $properties_cache;
	final public function get_properties(): array {
		if ( $this->properties_cache === null ) {
			$this->properties_cache = apply_filters(
				sprintf( 'voxel/dynamic-data/groups/%s/properties', $this->get_type() ),
				$this->properties(),
				$this
			);
		}

		return $this->properties_cache;
	}

	protected function aliases(): array {
		return [];
	}

	protected $property_aliases_cache;
	final public function get_aliases(): array {
		if ( $this->property_aliases_cache === null ) {
			$this->property_aliases_cache = $this->aliases();
		}

		return $this->property_aliases_cache;
	}

	/**
	 * Get the property object in the given path.
	 *
	 * @since 1.5
	 */
	final public function get_property( $path ): ?Base_Data_Type {
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

	/**
	 * Get the value of the property in the given path.
	 *
	 * @since 1.5
	 */
	final public function get_value( $path ) {
		$property = $this->get_property( $path );
		if ( $property === null ) {
			return null;
		}

		return $property->get_value();
	}

	final public function get_modifier( string $modifier_key ) {
		$methods = $this->get_methods();
		if ( isset( $methods[ $modifier_key ] ) ) {
			return new $methods[ $modifier_key ];
		}

		$modifiers = static::get_common_modifiers();
		if ( isset( $modifiers[ $modifier_key ] ) ) {
			return new $modifiers[ $modifier_key ];
		}

		return null;
	}

	protected static $common_modifiers;
	public static function get_common_modifiers(): array {
		if ( static::$common_modifiers === null ) {
			static::$common_modifiers = \Voxel\Dynamic_Data\Config::get_modifiers();
		}

		return static::$common_modifiers;
	}

	protected static $visibility_rules;
	public static function get_visibility_rules(): array {
		if ( static::$visibility_rules === null ) {
			static::$visibility_rules = \Voxel\Dynamic_Data\Config::get_visibility_rules();
		}

		return static::$visibility_rules;
	}

	protected static $all_groups;
	public static function get_all(): array {
		if ( static::$all_groups === null ) {
			static::$all_groups = \Voxel\Dynamic_Data\Config::get_data_groups();
		}

		return static::$all_groups;
	}

	public function get_export_key(): string {
		return $this->get_type();
	}

	protected $methods_cache;
	public function get_methods(): array {
		if ( $this->methods_cache === null ) {
			$this->methods_cache = apply_filters(
				sprintf( 'voxel/dynamic-data/groups/%s/methods', $this->get_type() ),
				$this->methods(),
				$this
			);
		}

		return $this->methods_cache;
	}

	protected function methods(): array {
		return [];
	}

	public static function mock(): self {
		return new static;
	}
}
