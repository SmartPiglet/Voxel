<?php

namespace Voxel\Utils\Object_Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Field {
	use Base_Model_Helpers;
	use Base_Validation_Helpers;

	/**
	 * Slugified string used to identify a field. Alias of `$this->props['key']`
	 *
	 * @since 1.0
	 */
	protected $key;

	/**
	 * List of field properties/configuration. Values below are available for
	 * all field types, but there can be additional props for specific field types.
	 *
	 * @since 1.0
	 */
	protected $props = [];

	public function __construct( $props = [] ) {
		$this->props = array_merge( $this->base_props(), $this->props );

		$this->before_props_assigned();

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}

		$this->key = $this->props['key'];
	}

	public static function preset( $props = [] ) {
		return ( new static( $props ) )->get_props();
	}

	abstract protected function base_props(): array;
	public function get_models(): array {
		return [];
	}

	public function sanitize( $value ) {}
	public function validate( $value ): void {}
	public function update( $value ): void {}
	public function get_value() {}

	public function check_validity( $value ) {
		$this->validate_is_empty( $value );
		if ( ! $this->is_empty( $value ) ) {
			$this->validate( $value );
		}
	}

	/**
	 * Similar to `check_validity()` with the exception that required fields
	 * can be skipped. Useful for saving post data as draft.
	 *
	 * @since 1.2.6
	 */
	public function check_validity_bypass_required( $value ) {
		if ( ! $this->is_empty( $value ) ) {
			$this->validate( $value );
		}
	}

	public function is_empty( $value ) {
		return empty( $value ) && ! in_array( $value, [ 0, '0', 0.0 ], true );
	}

	public function before_props_assigned(): void {
		//
	}

	public function set_value( $value ) {
		$this->update( $value );
	}

	public function set_prop( $key, $value ) {
		if ( array_key_exists( $key, $this->props ) ) {
			$this->props[ $key ] = $value;
		}
	}

	/* Getters */
	public function get_prop( $prop ) {
		if ( ! isset( $this->props[ $prop ] ) ) {
			return null;
		}

		return $this->props[ $prop ];
	}

	public function get_model_value( $model_key ) {
		return $this->get_prop( $model_key );
	}

	public function get_props() {
		return $this->props;
	}

	public function get_type() {
		return $this->props['type'];
	}

	public function get_key() {
		return $this->key;
	}

	public function get_label() {
		return $this->props['label'];
	}

	public function get_description() {
		return $this->props['description'];
	}

	public function is_required() {
		return (bool) $this->props['required'];
	}

	public function get_required_scripts(): array {
		return [];
	}
}
