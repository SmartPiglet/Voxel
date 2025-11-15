<?php

namespace Voxel\Product_Types\Promotions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Promotion_Package {

	protected static $instances;

	protected
		$key,
		$post_types,
		$duration_type,
		$duration_amount,
		$priority,
		$price_amount,
		$label,
		$description,
		$icon,
		$color;

	public static function get( $key ) {
		if ( static::$instances === null ) {
			static::get_all();
		}

		return static::$instances[ $key ] ?? null;
	}

	public static function get_all() {
		if ( static::$instances === null ) {
			static::$instances = [];
			foreach ( (array) \Voxel\get( 'paid_listings.settings.promotions.packages', [] ) as $data ) {
				try {
					$package = new static( (array) $data );
					static::$instances[ $package->get_key() ] = $package;
				} catch ( \Exception $e ) {
					//
				}
			}
		}

		return static::$instances;
	}

	protected function __construct( array $data ) {
		if ( empty( $data['key'] ) || ! is_string( $data['key'] ) ) {
			throw new \Exception( 'Invalid package.' );
		}

		if ( ! is_numeric( $data['priority'] ?? null ) ) {
			throw new \Exception( 'Invalid package.' );
		}

		if ( ! is_numeric( $data['price']['amount'] ?? null ) || $data['price']['amount'] < 0 ) {
			throw new \Exception( 'Invalid package.' );
		}

		if ( ( $data['duration']['amount'] ?? null ) === 'days' ) {
			if ( ! is_numeric( $data['duration']['amount'] ?? null ) || $data['duration']['amount'] < 1 ) {
				throw new \Exception( 'Invalid package.' );
			}
		}

		$this->key = (string) $data['key'];
		$this->post_types = (array) ( $data['post_types'] ?? [] );
		$this->duration_type = (string) ( $data['duration']['type'] ?? [] );
		$this->duration_amount = absint( $data['duration']['amount'] ?? 0 );
		$this->priority = absint( $data['priority'] ?? 0 );
		$this->price_amount = abs( (float) $data['price']['amount'] ?? 0 );
		$this->label = (string) ( $data['ui']['label'] ?? '' );
		$this->description = (string) ( $data['ui']['description'] ?? '' );
		$this->icon = (string) ( $data['ui']['icon'] ?? '' );
		$this->color = (string) ( $data['ui']['color'] ?? '' );
	}

	public function get_key(): string {
		return $this->key;
	}

	public function get_allowed_post_types(): array {
		return $this->post_types;
	}

	public function get_duration_type(): string {
		return $this->duration_type;
	}

	public function get_duration_amount(): int {
		return $this->duration_amount;
	}

	public function get_priority(): int {
		return $this->priority;
	}

	public function get_price_amount(): float {
		return $this->price_amount;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function get_color(): string {
		return $this->color;
	}

	public function get_icon(): string {
		return $this->icon;
	}

	public function get_icon_markup(): string {
		return \Voxel\get_icon_markup( $this->icon );
	}

	public function supports_post_type( \Voxel\Post_Type $post_type ): bool {
		if ( ! in_array( $post_type->get_key(), $this->get_allowed_post_types(), true ) ) {
			return false;
		}

		return true;
	}

	public function supports_post( \Voxel\Post $post ): bool {
		if ( ! in_array( $post->post_type->get_key(), $this->get_allowed_post_types(), true ) ) {
			return false;
		}

		return true;
	}
}
