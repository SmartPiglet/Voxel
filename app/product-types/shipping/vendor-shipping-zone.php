<?php

namespace Voxel\Product_Types\Shipping;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Shipping_Zone {

	protected static $instances;

	protected
		$key,
		$label,
		$countries,
		$rates;

	protected $supported_country_codes = [];

	public function __construct( array $data ) {
		$this->key = $data['key'];
		$this->label = $data['label'];
		$this->countries = $data['countries'];
		foreach ( $data['countries'] as $country_code ) {
			$this->supported_country_codes[ $country_code ] = true;
		}

		$this->rates = [];
		foreach ( $data['rates'] as $rate ) {
			try {
				$shipping_rate = Vendor_Rates\Vendor_Base_Shipping_Rate::create( $this, $rate );
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

	public function get_countries(): array {
		return $this->countries;
	}

	public function get_rates(): array {
		return $this->rates;
	}

	public function get_rate( string $rate_key ): ?Vendor_Rates\Vendor_Base_Shipping_Rate {
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
