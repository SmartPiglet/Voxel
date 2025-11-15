<?php

namespace Voxel\Product_Types\Shipping\Vendor_Rates;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Vendor_Base_Shipping_Rate {

	public $shipping_zone;

	protected
		$key,
		$label,
		$has_delivery_estimate,
		$min_delivery_unit,
		$min_delivery_time,
		$max_delivery_unit,
		$max_delivery_time;

	abstract public function get_type(): string;

	abstract protected function init( array $data ): void;

	abstract public function get_frontend_config(): array;

	public function get_key(): string {
		return $this->key;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function __construct( \Voxel\Product_Types\Shipping\Vendor_Shipping_Zone $shipping_zone, array $data ) {
		$this->shipping_zone = $shipping_zone;
		$this->key = $data['key'];
		$this->label = $data['label'];

		$this->init( $data );
	}

	public static function create( \Voxel\Product_Types\Shipping\Vendor_Shipping_Zone $shipping_zone, array $data ) {
		if ( $data['type'] === 'free_shipping' ) {
			return new Vendor_Free_Shipping_Rate( $shipping_zone, $data );
		} elseif ( $data['type'] === 'fixed_rate' ) {
			return new Vendor_Fixed_Shipping_Rate( $shipping_zone, $data );
		} else {
			return null;
		}
	}

	public function has_delivery_estimate(): bool {
		return !! $this->has_delivery_estimate;
	}

	public function get_minimum_delivery_time(): int {
		return $this->min_delivery_time;
	}

	public function get_minimum_delivery_unit(): string {
		return $this->min_delivery_unit;
	}

	public function get_maximum_delivery_time(): int {
		return $this->max_delivery_time;
	}

	public function get_maximum_delivery_unit(): string {
		return $this->max_delivery_unit;
	}

	public function get_delivery_estimate_message(): ?string {
		if ( ! $this->has_delivery_estimate() ) {
			return null;
		}

		$units = [
			'hour' => [
				'singular' => _x( 'hour', 'delivery estimate', 'voxel' ),
				'plural' => _x( 'hours', 'delivery estimate', 'voxel' ),
			],
			'day' => [
				'singular' => _x( 'day', 'delivery estimate', 'voxel' ),
				'plural' => _x( 'days', 'delivery estimate', 'voxel' ),
			],
			'business_day' => [
				'singular' => _x( 'business day', 'delivery estimate', 'voxel' ),
				'plural' => _x( 'business days', 'delivery estimate', 'voxel' ),
			],
			'week' => [
				'singular' => _x( 'week', 'delivery estimate', 'voxel' ),
				'plural' => _x( 'weeks', 'delivery estimate', 'voxel' ),
			],
			'month' => [
				'singular' => _x( 'month', 'delivery estimate', 'voxel' ),
				'plural' => _x( 'months', 'delivery estimate', 'voxel' ),
			],
		];

		if ( $this->min_delivery_unit === $this->max_delivery_unit ) {
			if ( $this->min_delivery_time === $this->max_delivery_time ) {
				return sprintf(
					_x( 'Arrives in %d %s', 'delivery estimate (exact)', 'voxel' ),
					$this->min_delivery_time, $units[ $this->min_delivery_unit ][ $this->min_delivery_time === 1 ? 'singular' : 'plural' ]
				);
			} else {
				return sprintf(
					_x( 'Arrives in %d to %d %s', 'delivery estimate (same unit)', 'voxel' ),
					$this->min_delivery_time,
					$this->max_delivery_time,
					$units[ $this->min_delivery_unit ]['plural']
				);
			}
		} else {
			return sprintf(
				_x( 'Arrives in %d %s to %d %s', 'delivery estimate (different units)', 'voxel' ),
				$this->min_delivery_time,
				$this->min_delivery_time, $units[ $this->min_delivery_unit ][ $this->min_delivery_time === 1 ? 'singular' : 'plural' ],
				$this->max_delivery_time,
				$this->max_delivery_time, $units[ $this->max_delivery_unit ][ $this->max_delivery_time === 1 ? 'singular' : 'plural' ]
			);
		}
	}
}
