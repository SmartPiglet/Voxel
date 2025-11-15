<?php

namespace Voxel\Product_Types\Shipping;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Shipping_Zone {

	protected static $instances;

	protected
		$key,
		$label,
		$regions,
		$rates;

	protected $supported_country_codes = [];

	public static function get_all(): array {
		if ( static::$instances === null ) {
			static::$instances = [];
			foreach ( (array) \Voxel\get( 'product_settings.shipping.shipping_zones', [] ) as $data ) {
				try {
					$shipping_zone = new static( (array) $data );
					static::$instances[ $shipping_zone->get_key() ] = $shipping_zone;
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

		$this->regions = [];
		foreach ( (array) ( $data['regions'] ?? [] ) as $region ) {
			if ( ( $region['type'] ?? null ) === 'country' && is_string( $region['country'] ?? null ) && ! empty( $region['country'] ) ) {
				$this->regions[] = [
					'type' => 'country',
					'country' => $region['country'],
				];

				$this->supported_country_codes[ $region['country'] ] = true;
			}
		}

		$this->rates = [];
		foreach ( (array) ( $data['rates'] ?? [] ) as $rate ) {
			try {
				$shipping_rate = Rates\Base_Shipping_Rate::create( $this, (array) $rate );
				$this->rates[ $shipping_rate->get_key() ] = $shipping_rate;
			} catch ( \Exception $e ) {
				//
			}
		}
	}

	public function get_key(): string {
		return $this->key;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_regions(): array {
		return $this->regions;
	}

	public function get_rates(): array {
		return $this->rates;
	}

	public function get_rate( string $rate_key ): ?Rates\Base_Shipping_Rate {
		return $this->rates[ $rate_key ] ?? null;
	}

	public function get_supported_country_codes(): array {
		return $this->supported_country_codes;
	}

	public function supports_country( string $country_code ): bool {
		return isset( $this->supported_country_codes[ $country_code ] );
	}

	public function get_frontend_config(): array {
		return [
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'countries' => (object) $this->get_supported_country_codes(),
			'rates' => array_map( function( $shipping_rate ) {
				return $shipping_rate->get_frontend_config();
			}, $this->get_rates() )
		];
	}
}
