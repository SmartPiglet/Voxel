<?php

namespace Voxel\Post_Types\Fields\Product_Field\Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Get_Minimum_Price {

	public function get_minimum_price( array $args = null ) {
		$WITH_DISCOUNTS = $args['with_discounts'] ?? true;
		$CUSTOM_PRICE = $args['custom_price'] ?? null;
		$RANGE_LENGTH = $args['range_length'] ?? 1;

		$product_type = $this->get_product_type();
		if ( ! $product_type ) {
			return null;
		}

		$mode = $product_type->get_product_mode();
		$value = $this->get_value();

		if ( $mode === 'regular' ) {
			$minimum_price = 0;

			if ( $base_price = $this->get_product_field('base-price') ) {
				if ( $WITH_DISCOUNTS ) {
					if ( $CUSTOM_PRICE ) {
						$minimum_price += $CUSTOM_PRICE['base_price']['discount_amount'] ?? $CUSTOM_PRICE['base_price']['amount'];
					} else {
						$minimum_price += $value['base_price']['discount_amount'] ?? $value['base_price']['amount'];
					}
				} else {
					if ( $CUSTOM_PRICE ) {
						$minimum_price += $CUSTOM_PRICE['base_price']['amount'];
					} else {
						$minimum_price += $value['base_price']['amount'];
					}
				}
			}

			if ( $addons = $this->get_product_field('addons') ) {
				foreach ( $addons->get_addons() as $addon ) {
					if ( ! $addon->is_active() ) {
						continue;
					}

					$addon_value = $value['addons'][ $addon->get_key() ];

					if ( $addon->get_type() === 'numeric' ) {
						if ( isset( $args['addons'][ $addon->get_key() ]['min'] ) ) {
							$minimum_quantity = $args['addons'][ $addon->get_key() ]['min'];
							if ( $minimum_quantity < $addon_value['min'] ) {
								$minimum_quantity = $addon_value['min'];
							}
						} elseif ( $addon->is_required() ) {
							$minimum_quantity = $addon_value['min'];
						} else {
							continue;
						}

						if ( $CUSTOM_PRICE ) {
							$addon_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ]['price'];
						} else {
							$addon_price = $addon_value['price'];
						}

						$minimum_price += ( $addon_price * $minimum_quantity );
					} elseif ( $addon->get_type() === 'switcher' ) {
						if ( ! ( isset( $args['addons'][ $addon->get_key() ]['enabled'] ) || $addon->is_required() ) ) {
							continue;
						}

						if ( $CUSTOM_PRICE ) {
							$addon_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ]['price'];
						} else {
							$addon_price = $addon_value['price'];
						}

						$minimum_price += $addon_price;
					} elseif ( $addon->get_type() === 'select' || $addon->get_type() === 'multiselect' ) {
						if ( ! $addon->is_required() ) {
							continue;
						}

						$lowest_price = null;
						foreach ( (array) $addon_value['choices'] as $choice_key => $choice ) {
							if ( $CUSTOM_PRICE ) {
								$choice_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ][ $choice_key ]['price'];
							} else {
								$choice_price = $choice['price'];
							}

							if ( $choice['enabled'] ) {
								if ( $lowest_price === null || $lowest_price > $choice_price ) {
									$lowest_price = $choice_price;
								}
							}
						}

						if ( $lowest_price !== null ) {
							$minimum_price += $lowest_price;
						}
					} elseif ( $addon->get_type() === 'custom-select' || $addon->get_type() === 'custom-multiselect' ) {
						if ( ! $addon->is_required() ) {
							continue;
						}

						$lowest_price = null;
						foreach ( (array) $addon_value['choices'] as $choice_key => $choice ) {
							if ( $CUSTOM_PRICE ) {
								$choice_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ][ $choice_key ]['price'];
							} else {
								$choice_price = $choice['price'];
							}

							if ( $choice_price === null ) {
								continue;
							}

							if ( $choice['quantity']['enabled'] ) {
								$choice_price *= $choice['quantity']['min'];
							}

							if ( $lowest_price === null || $lowest_price > $choice_price ) {
								$lowest_price = $choice_price;
							}
						}

						if ( $lowest_price !== null ) {
							$minimum_price += $lowest_price;
						}
					}
				}
			}

			return $minimum_price;
		} elseif ( $mode === 'variable' ) {
			$minimum_price = 0;

			$lowest_price = null;
			foreach ( $value['variations']['variations'] as $variation ) {
				if ( ! $variation['enabled'] ) {
					continue;
				}

				if ( $product_type->config('modules.stock.enabled') ) {
					if ( $variation['config']['stock']['enabled'] && $variation['config']['stock']['quantity'] < 1 ) {
						continue;
					}
				}

				if ( $WITH_DISCOUNTS ) {
					$variation_price = $variation['config']['base_price']['discount_amount'] ?? $variation['config']['base_price']['amount'];
				} else {
					$variation_price = $variation['config']['base_price']['amount'];
				}

				if ( $lowest_price === null || $lowest_price > $variation_price ) {
					$lowest_price = $variation_price;
				}
			}

			if ( $lowest_price !== null ) {
				$minimum_price += $lowest_price;
			}

			return $minimum_price;
		} elseif ( $mode === 'booking' ) {
			$minimum_price = 0;

			$minimum_range = 1;
			$booking_type = $product_type->config( 'modules.booking.type' );
			if ( $booking_type === 'days' && $value['booking']['booking_mode'] === 'date_range' && $value['booking']['date_range']['set_custom_limits'] ) {
				// $minimum_range = $value['booking']['date_range']['min_length'];
			}

			if ( $RANGE_LENGTH !== null ) {
				$minimum_range = $RANGE_LENGTH;
			}

			if ( $base_price = $this->get_product_field('base-price') ) {
				if ( $WITH_DISCOUNTS ) {
					if ( $CUSTOM_PRICE ) {
						$minimum_price += ( $CUSTOM_PRICE['base_price']['discount_amount'] ?? $CUSTOM_PRICE['base_price']['amount'] ) * $minimum_range;
					} else {
						$minimum_price += ( $value['base_price']['discount_amount'] ?? $value['base_price']['amount'] ) * $minimum_range;
					}
				} else {
					if ( $CUSTOM_PRICE ) {
						$minimum_price += $CUSTOM_PRICE['base_price']['amount'] * $minimum_range;
					} else {
						$minimum_price += $value['base_price']['amount'] * $minimum_range;
					}
				}
			}

			if ( $addons = $this->get_product_field('addons') ) {
				foreach ( $addons->get_addons() as $addon ) {
					if ( ! $addon->is_active() ) {
						continue;
					}

					$addon_value = $value['addons'][ $addon->get_key() ];

					if ( $addon->get_type() === 'numeric' ) {
						if ( isset( $args['addons'][ $addon->get_key() ]['min'] ) ) {
							$minimum_quantity = $args['addons'][ $addon->get_key() ]['min'];
							if ( $minimum_quantity < $addon_value['min'] ) {
								$minimum_quantity = $addon_value['min'];
							}
						} elseif ( $addon->is_required() ) {
							$minimum_quantity = $addon_value['min'];
						} else {
							continue;
						}

						if ( $CUSTOM_PRICE ) {
							$addon_price = ( $CUSTOM_PRICE['addons'][ $addon->get_key() ]['price'] * $minimum_quantity );
						} else {
							$addon_price = ( $addon_value['price'] * $minimum_quantity );
						}

						if ( $addon->repeat_in_booking_range() ) {
							$addon_price *= $minimum_range;
						}
// dd($addon_price, $CUSTOM_PRICE);
						$minimum_price += $addon_price;
					} elseif ( $addon->get_type() === 'switcher' ) {
						if ( ! ( isset( $args['addons'][ $addon->get_key() ]['enabled'] ) || $addon->is_required() ) ) {
							continue;
						}

						if ( $CUSTOM_PRICE ) {
							$addon_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ]['price'];
						} else {
							$addon_price = $addon_value['price'];
						}

						if ( $addon->repeat_in_booking_range() ) {
							$addon_price *= $minimum_range;
						}

						$minimum_price += $addon_price;
					} elseif ( $addon->get_type() === 'select' || $addon->get_type() === 'multiselect' ) {
						if ( ! $addon->is_required() ) {
							continue;
						}

						$lowest_price = null;
						foreach ( (array) $addon_value['choices'] as $choice_key => $choice ) {
							if ( $choice['enabled'] ) {
								if ( $CUSTOM_PRICE ) {
									$choice_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ][ $choice_key ]['price'];
								} else {
									$choice_price = $choice['price'];
								}

								if ( $addon->repeat_in_booking_range() ) {
									$choice_price *= $minimum_range;
								}

								if ( $lowest_price === null || $lowest_price > $choice_price ) {
									$lowest_price = $choice_price;
								}
							}
						}

						if ( $lowest_price !== null ) {
							$minimum_price += $lowest_price;
						}
					} elseif ( $addon->get_type() === 'custom-select' || $addon->get_type() === 'custom-multiselect' ) {
						if ( ! $addon->is_required() ) {
							continue;
						}

						$lowest_price = null;
						foreach ( (array) $addon_value['choices'] as $choice_key => $choice ) {
							if ( $CUSTOM_PRICE ) {
								$choice_price = $CUSTOM_PRICE['addons'][ $addon->get_key() ][ $choice_key ]['price'];
							} else {
								$choice_price = $choice['price'];
							}

							if ( $choice_price === null ) {
								continue;
							}

							if ( $choice['quantity']['enabled'] ) {
								$choice_price *= $choice['quantity']['min'];
							}

							if ( $addon->repeat_in_booking_range() ) {
								$choice_price *= $minimum_range;
							}

							if ( $lowest_price === null || $lowest_price > $choice_price ) {
								$lowest_price = $choice_price;
							}
						}

						if ( $lowest_price !== null ) {
							$minimum_price += $lowest_price;
						}
					}
				}
			}

			return $minimum_price;
		} else {
			return null;
		}
	}

	public function get_minimum_price_for_date( \DateTimeInterface $date, array $args = null ) {
		return $this->get_minimum_price( [
			'with_discounts' => $args['with_discounts'] ?? true,
			'range_length' => $args['range_length'] ?? 1,
			'custom_price' => $this->get_custom_price_for_date( $date )['prices'] ?? null,
			'addons' => $args['addons'] ?? null,
		] );
	}

	public function get_custom_price_for_date( \DateTimeInterface $date ): ?array {
		if ( ! ( $product_type = $this->get_product_type() ) ) {
			return null;
		}

		if ( $custom_prices = $this->get_product_field('custom-prices') ) {
			$weekday_indexes = array_flip( \Voxel\get_weekday_indexes() );
			$weekday_index =  $weekday_indexes[ absint( $date->format('N') - 1 ) ] ?? null;
			foreach ( $custom_prices->get_custom_prices( [ 'with_minimum_price' => false ] ) as $custom_price ) {
				foreach ( $custom_price['conditions'] as $condition ) {
					if ( $condition['type'] === 'date_range' ) {
						$from =  new \DateTime( $condition['range']['from'], new \DateTimeZone('UTC') );
						$to =  new \DateTime( $condition['range']['to'], new \DateTimeZone('UTC') );

						if ( $date >= $from && $date <= $to ) {
							return $custom_price;
						}
					} elseif ( $condition['type'] === 'date' ) {
						if ( $date->format( 'Y-m-d' ) === $condition['date'] ) {
							return $custom_price;
						}
					} elseif ( $condition['type'] === 'day_of_week' ) {
						if ( $weekday_index !== null && in_array( $weekday_index, $condition['days'], true ) ) {
							return $custom_price;
						}
					}
				}
			}
		}

		return null;
	}
}
