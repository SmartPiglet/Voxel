<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Custom_Prices_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'custom-prices',
		'label' => 'Custom prices',
		'description' => '',
		'placeholder' => '',
	];

	public function get_models(): array {
		return [
			'label' => Form_Models::Text( [
				'label' => 'Label',
				'classes' => 'x-col-12',
			] ),
			'description' => Form_Models::Textarea( [
				'label' => 'Description',
				'classes' => 'x-col-12',
			] ),
		];
	}

	public function get_conditions(): array {
		return [
			'settings.product_mode' => [
				'compare' => 'in_array',
				'value' => [ 'regular', 'booking' ],
			],
			'modules.custom_prices.enabled' => true,
		];
	}

	public function get_prices_schema(): Data_Object {
		$prices = Schema::Object( [] );
		if ( $this->product_type->config('modules.base_price.enabled') ) {
			$prices->set_prop( 'base_price', Schema::Object( [
				'amount' => Schema::Float()->min(0),
			] ) );

			if ( $this->product_type->config( 'modules.base_price.discount_price.enabled' ) ) {
				$prices->get_prop('base_price')->set_prop( 'discount_amount', Schema::Float()->min(0) );
			}
		}

		if ( $this->product_type->config('modules.addons.enabled') ) {
			$prices->set_prop( 'addons', Schema::Object( [] ) );
			foreach ( $this->get_addons() as $addon ) {
				$prices->get_prop('addons')->set_prop( $addon->get_key(), $addon->get_custom_price_schema() );
			}
		}

		return $prices;
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'custom_prices', Schema::Object( [
			'enabled' => Schema::Bool()->default(false),
			'list' => Schema::Object_List( [
				'enabled' => Schema::Bool()->default(true),
				'label' => Schema::String(),
				'conditions' => Schema::Object_List( [
					'type' => Schema::Enum( [ 'day_of_week', 'date', 'date_range' ] )->default('day_of_week'),
					'days' => Schema::List()->validator( '\Voxel\is_valid_weekday' )->default([]),
					'date' => Schema::Date()->format('Y-m-d'),
					'range' => Schema::Object( [
						'from' => Schema::Date()->format('Y-m-d'),
						'to' => Schema::Date()->format('Y-m-d'),
					] ),
				] )->default([]),
				'prices' => $this->get_prices_schema(),
			] )->default([]),
		] ) );
	}

	public function max_custom_prices(): int {
		return apply_filters( 'voxel/product-types/custom-prices/limit', 5 );
	}

	public function max_custom_price_conditions(): int {
		return apply_filters( 'voxel/product-types/custom-prices/conditions/limit', 10 );
	}

	public function validate( $value ): void {
		if ( count( $value['custom_prices']['list'] ) > $this->max_custom_prices() ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: You cannot add more than @max custom prices', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
					'@max' => $this->max_custom_prices(),
				]
			) );
		}

		if ( $value['custom_prices']['enabled'] ) {
			foreach ( $value['custom_prices']['list'] as $item ) {
				if ( count( $item['conditions'] ) > $this->max_custom_price_conditions() ) {
					throw new \Exception( \Voxel\replace_vars(
						_x( '@field_name: You cannot add more than @max conditions for custom prices', 'field validation', 'voxel' ), [
							'@field_name' => $this->product_field->get_label(),
							'@max' => $this->max_custom_price_conditions(),
						]
					) );
				}

				if ( $item['enabled'] ) {
					if ( $this->product_type->config('modules.base_price.enabled') ) {
						/*if ( $item['prices']['base_price']['amount'] === null ) {
							throw new \Exception( \Voxel\replace_vars(
								_x( '@field_name custom prices: Base price is required', 'field validation', 'voxel' ), [
									'@field_name' => $this->product_field->get_label(),
								]
							) );
						}*/

						if ( $this->product_type->config( 'modules.base_price.discount_price.enabled' ) ) {
							if ( $item['prices']['base_price']['amount'] !== null && $item['prices']['base_price']['discount_amount'] !== null && $item['prices']['base_price']['discount_amount'] > $item['prices']['base_price']['amount'] ) {
								throw new \Exception( \Voxel\replace_vars(
									_x( '@field_name custom prices: Discount price cannot be larger than regular price', 'field validation', 'voxel' ), [
										'@field_name' => $this->product_field->get_label(),
									]
								) );
							}
						}
					}

					/*if ( $this->product_type->config('modules.addons.enabled') ) {
						foreach ( $this->get_addons() as $addon ) {
							if ( ! ( $addon->is_required() || $value['addons'][ $addon->get_key() ]['enabled'] ) ) {
								continue;
							}

							if ( in_array( $addon->get_type(), [ 'switcher', 'numeric' ], true ) ) {
								if ( ! isset( $item['prices']['addons'][ $addon->get_key() ]['price'] ) ) {
									throw new \Exception( \Voxel\replace_vars(
										_x( '@field_name custom prices: Price is required for @addon_name', 'field validation', 'voxel' ), [
											'@field_name' => $this->product_field->get_label(),
											'@addon_name' => $addon->get_label(),
										]
									) );
								}
							} elseif ( in_array( $addon->get_type(), [ 'select', 'multiselect', 'custom-select', 'custom-multiselect' ], true ) ) {
								$has_valid_price = false;
								foreach ( (array) ( $item['prices']['addons'][ $addon->get_key() ] ) as $choice ) {
									if ( isset( $choice['price'] ) ) {
										$has_valid_price = true;
									}
								}

								if ( ! $has_valid_price ) {
									throw new \Exception( \Voxel\replace_vars(
										_x( '@field_name custom prices: Price is required for @addon_name', 'field validation', 'voxel' ), [
											'@field_name' => $this->product_field->get_label(),
											'@addon_name' => $addon->get_label(),
										]
									) );
								}
							}
						}
					}*/
				}
			}
		}
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}

	public function frontend_props(): array {
		wp_enqueue_style('pikaday');

		return [
			'weekdays' => \Voxel\get_weekdays(),
			'base_price' => [
				'enabled' => !! $this->product_type->config('modules.base_price.enabled'),
				'discount_price' => [
					'enabled' => !! $this->product_type->config( 'modules.base_price.discount_price.enabled' ),
				],
			],
			'addons' => [
				'enabled' => !! $this->product_type->config('modules.addons.enabled'),
			],
			'prices_schema' => $this->get_prices_schema()->export(),
			'limits' => [
				'custom_prices' => $this->max_custom_prices(),
				'custom_price_conditions' => $this->max_custom_price_conditions(),
			],
		];
	}

	protected $addons_cache;
	public function get_addons(): array {
		if ( $this->addons_cache === null ) {
			if ( $this->product_type->config('modules.addons.enabled') ) {
				$items = $this->product_type->config('modules.addons.items');
				$classes = \Voxel\config('product_types.product_addons');
				$this->addons_cache = [];

				foreach ( $items as $props ) {
					$addon = new $classes[ $props['type'] ]( $props );
					$addon->set_product_type( $this->product_type );
					$addon->set_product_field( $this->product_field );

					$this->addons_cache[ $addon->get_key() ] = $addon;
				}
			} else {
				$this->addons_cache = null;
			}
		}

		return $this->addons_cache;
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/custom-prices/single-price.php' );

		return $templates;
	}

	public function get_custom_prices( ?array $args = null ): array {
		$config = $this->product_field->get_value();
		if ( ! $config['custom_prices']['enabled'] ) {
			return [];
		}

		$custom_prices = [];
		foreach ( $config['custom_prices']['list'] as $item ) {
			if ( ! $item['enabled'] ) {
				continue;
			}

			$conditions = [];
			foreach ( $item['conditions'] as $condition ) {
				if ( $condition['type'] === 'day_of_week' ) {
					if ( ! empty( $condition['days'] ) ) {
						$conditions[] = [
							'type' => 'day_of_week',
							'days' => $condition['days'],
						];
					}
				} elseif ( $condition['type'] === 'date' ) {
					if ( $condition['date'] !== null ) {
						$conditions[] = [
							'type' => 'date',
							'date' => $condition['date'],
						];
					}
				} elseif ( $condition['type'] === 'date_range' ) {
					if ( $condition['range']['from'] !== null && $condition['range']['to'] !== null ) {
						$conditions[] = [
							'type' => 'date_range',
							'range' => [
								'from' => $condition['range']['from'],
								'to' => $condition['range']['to'],
							],
						];
					}
				}
			}

			$prices = [];
			if ( $this->product_type->config('modules.base_price.enabled') ) {
				if ( isset( $item['prices']['base_price']['amount'] ) ) {
					$prices['base_price'] = [
						'amount' => $item['prices']['base_price']['amount'],
						'discount_amount' => $item['prices']['base_price']['discount_amount'] ?? null,
					];
				} else {
					$prices['base_price'] = [
						'amount' => $config['base_price']['amount'],
						'discount_amount' => $config['base_price']['discount_amount'] ?? null,
					];
				}
			}

			if ( $this->product_type->config('modules.addons.enabled') ) {
				$prices['addons'] = [];

				foreach ( $this->get_addons() as $addon ) {
					if ( ! $addon->is_active() ) {
						continue;
					}

					if ( in_array( $addon->get_type(), [ 'switcher', 'numeric' ], true ) ) {
						if ( ! isset( $item['prices']['addons'][ $addon->get_key() ]['price'] ) ) {
							$item['prices']['addons'][ $addon->get_key() ]['price'] = $config['addons'][ $addon->get_key() ]['price'];
						}

						$prices['addons'][ $addon->get_key() ] = $item['prices']['addons'][ $addon->get_key() ];
					} elseif ( in_array( $addon->get_type(), [ 'select', 'multiselect', 'custom-select', 'custom-multiselect' ], true ) ) {
						$active_choices = $addon->get_active_choices();
						foreach ( (array) ( $item['prices']['addons'][ $addon->get_key() ] ) as $choice_key => $choice ) {
							if ( ! isset( $choice['price'] ) ) {
								if ( isset( $config['addons'][ $addon->get_key() ]['choices'][ $choice_key ]['price'] ) ) {
									$item['prices']['addons'][ $addon->get_key() ][ $choice_key ]['price'] = $config['addons'][ $addon->get_key() ]['choices'][ $choice_key ]['price'];
								} else {
									// unset( $item['prices']['addons'][ $addon->get_key() ][ $choice_key ]['price'] );
								}
							}
						}

						$prices['addons'][ $addon->get_key() ] = $item['prices']['addons'][ $addon->get_key() ];
					}
				}
			}

			if ( ! empty( $conditions ) ) {
				$data = [
					'label' => $item['label'],
					'conditions' => $conditions,
					'prices' => $prices,
				];

				if ( ( $args['with_minimum_price'] ?? null ) !== false ) {
					$data['minimum_price'] = $this->product_field->get_minimum_price( [
						'custom_price' => $prices,
						'range_length' => 1,
					] );
				}

				$custom_prices[] = $data;
			}
		}

		return $custom_prices;
	}

}
