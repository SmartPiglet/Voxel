<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Switcher_Filter extends Base_Filter {

	protected $props = [
		'type' => 'switcher',
		'label' => 'Switcher',
		'placeholder' => '',
		'source' => 'switcher',
		'compare' => 'checked',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-12' ]),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'source' => function() { ?>
				<div class="ts-form-group x-col-6">
					<label>Data source:</label>
					<select v-model="filter.source">
						<option v-for="field in $root.getFieldsByType('switcher')" :value="field.key">
							{{ field.label }}
						</option>
						<template v-for="field in $root.getFieldsByType('product')">
							<template v-for="group in $root.getProductAddonsByType(field, 'switcher')">
								<optgroup :label="group.label">
									<template v-for="addon in group.addons">
										<option :value="field.key+'->addons->'+group.key+'->'+addon.key">
											{{ addon.label }} (Is enabled)
										</option>
									</template>
								</optgroup>
							</template>
						</template>
						<template v-for="field in $root.getFieldsByType('product')">
							<template v-if="$root.hasProductStockEnabled()">
								<option :value="field.key+'->stock_status'">{{ field.label }}: Is in stock</option>
							</template>
						</template>
					</select>
				</div>
			<?php },
			'compare' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Comparison',
				'classes' => 'x-col-6',
				'choices' => [
					'checked' => 'Is checked',
					'unchecked' => 'Is unchecked',
				],
			],
			'description' => $this->get_description_model(),
			'icon' => $this->get_icon_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$parts = explode( '->', $this->props['source'] );
		$field = $this->post_type->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'switcher' ) {
				$table->add_column( sprintf( '`%s` TINYINT(1) NOT NULL DEFAULT 0', esc_sql( $this->db_key() ) ) );
				$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->db_key() ) ) );
			} elseif ( $field->get_type() === 'product' && ! empty( $field->get_supported_product_types() ) ) {
				if ( ( $parts[1] ?? null ) === 'addons' ) {
					$product_type = \Voxel\Product_Type::get( $parts[2] ?? null );
					if ( $product_type && $product_type->config('modules.addons.enabled') ) {
						$addons = $product_type->config( 'modules.addons.items' );
						foreach ( $addons as $addon ) {
							if ( $addon['type'] === 'switcher' && $addon['key'] === ( $parts[3] ?? null ) ) {
								// addon enabled
								$db_key = sprintf( '%s_addon_%s_enabled', $product_type->get_key(), $addon['key'] );
								$table->add_price_index_column( sprintf( '`%s` TINYINT(1) NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
								$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );

								// addon price
								$db_key = sprintf( '%s_addon_%s_price', $product_type->get_key(), $addon['key'] );
								$table->add_price_index_column( sprintf( '`%s` INT UNSIGNED NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
								$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );
								break;
							}
						}
					}
				} elseif ( ( $parts[1] ?? null ) === 'stock_status' ) {
					$db_key = 'in_stock';
					$table->add_price_index_column( sprintf( '`%s` TINYINT(1) NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
					$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );
				}
			}
		}
	}

	public function index( \Voxel\Post $post ): array {
		$parts = explode( '->', $this->props['source'] );
		$field = $post->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'switcher' ) {
				$value = $field->get_value() ? 1 : 0;
				return [
					$this->db_key() => (int) $value,
				];
			}
		}

		return [];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		$parts = explode( '->', $this->props['source'] );
		$field = $this->post_type->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'switcher' ) {
				$compare = $this->props['compare'] === 'unchecked' ? 0 : 1;
				$query->where( sprintf( '`%s` = %d', esc_sql( $this->db_key() ), $compare ) );
			} elseif ( $field->get_type() === 'product' && ! empty( $field->get_supported_product_types() ) ) {
				if ( ( $parts[1] ?? null ) === 'addons' ) {
					$product_type = \Voxel\Product_Type::get( $parts[2] ?? null );
					if ( $product_type && $product_type->config('modules.addons.enabled') ) {
						$addons = $product_type->config( 'modules.addons.items' );
						foreach ( $addons as $addon ) {
							if ( $addon['type'] === 'switcher' && $addon['key'] === ( $parts[3] ?? null ) ) {
								$db_key = sprintf( '%s_addon_%s_enabled', $product_type->get_key(), $addon['key'] );
								$compare = $this->props['compare'] === 'unchecked' ? 0 : 1;

								$query->join_price_index( $args );
								$query->where( sprintf( 'pricing.`%s` = %d', esc_sql( $db_key ), $compare ) );

								if ( ! isset( $GLOBALS['_addon_filters'] ) ) {
									$GLOBALS['_addon_filters'] = [];
								}

								$GLOBALS['_addon_filters'][ $addon['key'] ] = [
									'enabled' => true,
								];
								break;
							}
						}
					}
				} elseif ( ( $parts[1] ?? null ) === 'stock_status' ) {
					$db_key = 'in_stock';
					$compare = $this->props['compare'] === 'unchecked' ? 0 : 1;

					$query->join_price_index( $args );
					$query->where( sprintf( 'pricing.`%s` = %d', esc_sql( $db_key ), $compare ) );
				}
			}
		}
	}

	public function parse_value( $value ) {
		return absint( $value ) === 1 ? 1 : null;
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'open now filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'checked' => _x( 'Checked', 'switcher filter', 'voxel-backend' ),
					'unchecked' => _x( 'Unchecked', 'switcher filter', 'voxel-backend' ),
				],
			],
			'open_in_popup' => [
				'label' => _x( 'Display as button', 'open now filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'conditional' => false,
			],
		];
	}

	public function get_default_value_from_elementor( $controls ) {
		return ( $controls['value'] ?? null ) === 'checked' ? 1 : null;
	}

	public function frontend_props() {
		return [
			'openInPopup' => ( $this->elementor_config['open_in_popup'] ?? null ) === 'yes',
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
		];
	}
}
