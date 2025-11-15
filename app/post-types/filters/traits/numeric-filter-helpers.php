<?php

namespace Voxel\Post_Types\Filters\Traits;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Numeric_Filter_Helpers {

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$datatype = $this->_get_column_type();
		$parts = explode( '->', $this->props['source'] );
		$field = $this->post_type->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'number' ) {
				$table->add_column( sprintf( '`%s` %s NOT NULL DEFAULT 0', esc_sql( $this->db_key() ), $datatype ) );
				$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->db_key() ) ) );
			} elseif ( $field->get_type() === 'product' && ! empty( $field->get_supported_product_types() ) ) {
				if ( ( $parts[1] ?? null ) === 'addons' ) {
					$product_type = \Voxel\Product_Type::get( $parts[2] ?? null );
					if ( $product_type && $product_type->config('modules.addons.enabled') ) {
						$addons = $product_type->config( 'modules.addons.items' );
						foreach ( $addons as $addon ) {
							if ( $addon['type'] === 'numeric' && $addon['key'] === ( $parts[3] ?? null ) ) {
								// addon enabled
								$db_key = sprintf( '%s_addon_%s_enabled', $product_type->get_key(), $addon['key'] );
								$table->add_price_index_column( sprintf( '`%s` TINYINT(1) NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
								$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );

								// addon min
								$db_key = sprintf( '%s_addon_%s_min', $product_type->get_key(), $addon['key'] );
								$table->add_price_index_column( sprintf( '`%s` INT UNSIGNED NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
								$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );

								// addon max
								$db_key = sprintf( '%s_addon_%s_max', $product_type->get_key(), $addon['key'] );
								$table->add_price_index_column( sprintf( '`%s` INT UNSIGNED NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
								$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );

								// addon price
								$db_key = sprintf( '%s_addon_%s_price', $product_type->get_key(), $addon['key'] );
								$table->add_price_index_column( sprintf( '`%s` INT UNSIGNED NOT NULL DEFAULT 0', esc_sql( $db_key ) ) );
								$table->add_price_index_key( sprintf( 'KEY(`%s`)', esc_sql( $db_key ) ) );
								break;
							}
						}
					}
				}
			}
		}
	}

	public function index( \Voxel\Post $post ): array {
		$parts = explode( '->', $this->props['source'] );
		$field = $post->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'number' ) {
				$value = ! empty( $field->get_value() ) ? $this->_prepare_value( $field->get_value() ) : 0;
				return [
					$this->db_key() => $value,
				];
			}
		}

		return [];
	}

	public function _get_max_int_size() {
		$max = max(
			absint( $this->props['range_start'] ),
			absint( $this->props['range_end'] )
		);

		return ceil( $max * $this->_get_value_multiplier() );
	}

	public function _get_value_multiplier() {
		if ( ! is_numeric( $this->props['step_size'] ) ) {
			return 1;
		}

		$step = abs( (float) $this->props['step_size'] );
		$precision = strlen( substr( strrchr( $step, '.' ), 1 ) );

		return pow( 10, $precision );
	}

	public function _get_column_type() {
		$max = $this->_get_max_int_size();

		if ( $max < ((2**7) - 1) ) {
			return 'TINYINT';
		} elseif ( $max < ((2**15) - 1) ) {
			return 'SMALLINT';
		} elseif ( $max < ((2**23) - 1) ) {
			return 'MEDIUMINT';
		} elseif ( $max < ((2**31) - 1) ) {
			return 'INT';
		} else {
			return 'BIGINT';
		}
	}

	public function _prepare_value( $value ) {
		$value = (float) $value;
		return intval( round( $value * $this->_get_value_multiplier(), 0 ) );
	}

	public function _get_source_model() {
		return function() { ?>
			<div class="ts-form-group x-col-6">
				<label>Data source:</label>
				<select v-model="filter.source">
					<option v-for="field in $root.getFieldsByType('number')" :value="field.key">
						{{ field.label }}
					</option>
					<template v-for="field in $root.getFieldsByType('product')">
						<optgroup :label="field.label">
							<option :value="field.key+'->:minimum_price'">Minimum price</option>
						</optgroup>
						<template v-if="filter.type === 'stepper'" v-for="group in $root.getProductAddonsByType(field, 'numeric')">
							<optgroup :label="group.label">
								<template v-for="addon in group.addons">
									<option :value="field.key+'->addons->'+group.key+'->'+addon.key+'->max'">
										{{ addon.label }} (Max.)
									</option>
								</template>
							</optgroup>
						</template>
					</template>
					<optgroup label="Post data">
						<option value=":post->priority">Priority</option>
					</optgroup>
				</select>
			</div>
		<?php };
	}
}
