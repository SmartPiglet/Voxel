<?php

namespace Voxel\Post_Types\Fields\Product_Field\Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Get_Prices_For_Index {

	public function get_prices_for_index(): array {
		if ( ! ( $product_type = $this->get_product_type() ) ) {
			return [];
		}

		$prices = [];
		$config = $this->get_value();

		$get_days_ls = function( $conditions ) {
			$ranges = [];
			$now = date_diff( \Voxel\epoch(), \Voxel\utc() )->days;
			foreach ( $conditions as $condition ) {
				if ( $condition['type'] === 'date_range' ) {
					$range_from = date_diff( \Voxel\epoch(), new \DateTime( $condition['range']['from'], new \DateTimeZone('UTC') ) )->days;
					$range_to = date_diff( \Voxel\epoch(), new \DateTime( $condition['range']['to'], new \DateTimeZone('UTC') ) )->days;
					if ( $range_to < $now ) {
						continue;
					}

					$ranges[] = [ $range_from, $range_to ];
				} elseif ( $condition['type'] === 'date' ) {
					$range_date = date_diff( \Voxel\epoch(), new \DateTime( $condition['date'], new \DateTimeZone('UTC') ) )->days;
					if ( $range_date < $now ) {
						continue;
					}

					$ranges[] = [ $range_date, $range_date ];
				}
			}

			$ranges = \Voxel\merge_ranges( $ranges );
			if ( empty( $ranges ) ) {
				return 'MULTILINESTRING((-0.1 0,-0.1 0))';
			}

			$strings = array_map( function( $range ) {
				return sprintf( '(%s 0,%s 0)', $range[0], $range[1] );
			}, $ranges );

			return sprintf( 'MULTILINESTRING(%s)', join( ',', $strings ) );
		};

		$get_weekdays_ls = function( $conditions ) {
			$ranges = [];
			$indexes = \Voxel\get_weekday_indexes();
			foreach ( $conditions as $condition ) {
				if ( $condition['type'] === 'day_of_week' ) {
					foreach ( $condition['days'] as $day ) {
						$day_index = $indexes[ $day ] ?? null;
						if ( $day_index !== null ) {
							$ranges[ $day_index ] = [ $day_index, $day_index ];
						}
					}
				}
			}

			$ranges = \Voxel\merge_ranges( $ranges );
			if ( empty( $ranges ) ) {
				return 'MULTILINESTRING((-0.1 0,-0.1 0))';
			}

			$strings = array_map( function( $range ) {
				return sprintf( '(%s 0,%s 0)', $range[0], $range[1] );
			}, $ranges );

			return sprintf( 'MULTILINESTRING(%s)', join( ',', $strings ) );
		};

		if ( $custom_prices = $this->get_product_field('custom-prices') ) {
			foreach ( $custom_prices->get_custom_prices() as $custom_price ) {
				$price_data = [
					'post_id' => $this->post->get_id(),
					'product_type' => $this->product_type->get_key(),
					'is_custom' => 1,
					'days' => $get_days_ls( $custom_price['conditions'] ),
					'weekdays' => $get_weekdays_ls( $custom_price['conditions'] ),
					'minimum_price' => absint( $this->get_minimum_price( [
						'with_discounts' => true,
						'custom_price' => $custom_price['prices'],
						'range_length' => 1,
					] ) ?? 0 ) * 100,
					'base_price' => 0,
				];

				if ( isset( $custom_price['prices']['base_price'] ) ) {
					$price_data['base_price'] = absint( $custom_price['prices']['base_price']['discount_amount'] ?? $custom_price['prices']['base_price']['amount'] ?? 0 ) * 100;
				}

				$prices[] = $price_data;
			}
		}

		$prices[] = [
			'post_id' => $this->post->get_id(),
			'product_type' => $this->product_type->get_key(),
			'minimum_price' => absint( $this->get_minimum_price( [
				'with_discounts' => true,
				'custom_price' => null,
				'range_length' => 1,
			] ) ?? 0 ) * 100,
			'base_price' => absint( $config['base_price']['discount_amount'] ?? $config['base_price']['amount'] ?? 0 ) * 100,
			'is_custom' => 0,
		];

		foreach ( $prices as $i => $price ) {
			foreach ( $this->post->post_type->get_filters() as $filter ) {
				if ( $filter->get_type() === 'switcher' && str_starts_with( $filter->get_prop('source'), 'product->' ) ) {
					$parts = explode( '->', $filter->get_prop('source') );
					if ( ( $parts[1] ?? null ) === 'addons' ) {
						$product_type = \Voxel\Product_Type::get( $parts[2] ?? null );
						if ( $product_type && $product_type->config('modules.addons.enabled') ) {
							$addons = $product_type->config( 'modules.addons.items' );
							foreach ( $addons as $addon ) {
								if ( $addon['type'] === 'switcher' && $addon['key'] === ( $parts[3] ?? null ) ) {
									$db_key__enabled = sprintf( '%s_addon_%s_enabled', $product_type->get_key(), $addon['key'] );
									$db_key__price = sprintf( '%s_addon_%s_price', $product_type->get_key(), $addon['key'] );
									$db_value__enabled = 0;
									$db_value__price = 0;

									if ( ( $addons = $this->get_product_field('addons') ) && $product_type->get_key() === $this->product_type->get_key() ) {
										$addon = $addons->get_addon( $parts[3] ?? null );
										$db_value__enabled = $addon && $addon->is_active() ? 1 : 0;
										if ( $addon && $addon->is_active() ) {
											$addon_value = $addon->get_value();
											$db_value__price = absint( $addon_value['price'] ?? 0 ) * 100;
										}
									}

									$prices[ $i ][ $db_key__enabled ] = absint( $db_value__enabled );
									$prices[ $i ][ $db_key__price ] = absint( $db_value__price );
									break;
								}
							}
						}
					} elseif ( ( $parts[1] ?? null ) === 'stock_status' ) {
							$db_key = 'in_stock';
							$db_value = $this->is_in_stock() ? 1 : 0;

							$prices[ $i ][ $db_key ] = absint( $db_value );
							break;
					}
				}

				if ( in_array( $filter->get_type(), [ 'stepper', 'range' ], true ) && str_starts_with( $filter->get_prop('source'), 'product->' ) ) {
					$parts = explode( '->', $filter->get_prop('source') );
					if ( ( $parts[1] ?? null ) === 'addons' ) {
						$product_type = \Voxel\Product_Type::get( $parts[2] ?? null );
						if ( $product_type && $product_type->config('modules.addons.enabled') ) {
							$addons = $product_type->config( 'modules.addons.items' );
							foreach ( $addons as $addon ) {
								if ( $addon['type'] === 'numeric' && $addon['key'] === ( $parts[3] ?? null ) ) {
									$db_key__enabled = sprintf( '%s_addon_%s_enabled', $product_type->get_key(), $addon['key'] );
									$db_key__min = sprintf( '%s_addon_%s_min', $product_type->get_key(), $addon['key'] );
									$db_key__max = sprintf( '%s_addon_%s_max', $product_type->get_key(), $addon['key'] );
									$db_key__price = sprintf( '%s_addon_%s_price', $product_type->get_key(), $addon['key'] );
									$db_value__enabled = 0;
									$db_value__min = 0;
									$db_value__max = 0;
									$db_value__price = 0;

									if ( ( $addons = $this->get_product_field('addons') ) && $product_type->get_key() === $this->product_type->get_key() ) {
										$addon = $addons->get_addon( $parts[3] ?? null );
										if ( $addon ) {
											$db_value__enabled = $addon->is_active() ? 1 : 0;
											if ( $addon && $addon->is_active() ) {
												$addon_value = $addon->get_value();
												$db_value__min = absint( $addon_value['min'] ?? 0 );
												$db_value__max = absint( $addon_value['max'] ?? 0 );
												$db_value__price = absint( $addon_value['price'] ?? 0 ) * 100;
											}
										}
									}

									$prices[ $i ][ $db_key__enabled ] = absint( $db_value__enabled );
									$prices[ $i ][ $db_key__min ] = absint( $db_value__min );
									$prices[ $i ][ $db_key__max ] = absint( $db_value__max );
									$prices[ $i ][ $db_key__price ] = absint( $db_value__price );
									break;
								}
							}
						}
					}
				}
			}
		}

		return $prices;
	}

}
