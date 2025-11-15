<?php

namespace Voxel\Product_Types\Shipping;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Shipping_Class {

	protected static $instances;

	protected
		$key,
		$label,
		$description;

	public static function get_all(): array {
		if ( static::$instances === null ) {
			static::$instances = [];
			foreach ( (array) \Voxel\get( 'product_settings.shipping.shipping_classes', [] ) as $data ) {
				try {
					$shipping_class = new static( (array) $data );
					static::$instances[ $shipping_class->get_key() ] = $shipping_class;
				} catch ( \Exception $e ) {
					//
				}
			}
		}

		return static::$instances;
	}

	public static function get( $key ) {
		if ( static::$instances === null ) {
			static::get_all();
		}

		return static::$instances[ $key ] ?? null;
	}


	protected function __construct( array $data ) {
		if ( empty( $data['key'] ) || ! is_string( $data['key'] ) ) {
			throw new \Exception( 'Invalid data.' );
		}

		if ( empty( $data['label'] ) || ! is_string( $data['label'] ) ) {
			throw new \Exception( 'Invalid data.' );
		}

		$this->key = (string) $data['key'];
		$this->label = (string) ( $data['label'] ?? '' );
		$this->description = (string) ( $data['description'] ?? '' );
	}

	public function get_key(): string {
		return $this->key;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_description(): string {
		return $this->description;
	}
}
