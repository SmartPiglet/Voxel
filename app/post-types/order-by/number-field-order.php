<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Field_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'number-field',
		'source' => '',
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Number field';
	}

	public function get_models(): array {
		return [
			'source' => $this->_get_source_model(),
			'order' => $this->get_order_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		if ( $this->_get_source_filter() ) {
			return;
		}

		$parts = explode( '->', $this->props['source'] );
		$field = $this->post_type->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'number' ) {
				$datatype = $field->_get_column_type();
				$table->add_column( sprintf( '`%s` %s NOT NULL DEFAULT 0', esc_sql( $this->_get_column_key() ), $datatype ) );
				$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->_get_column_key() ) ) );
			}
		}
	}

	public function index( \Voxel\Post $post ): array {
		if ( $this->_get_source_filter() ) {
			return [];
		}

		$parts = explode( '->', $this->props['source'] );
		$field = $post->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'number' ) {
				$value = ! empty( $field->get_value() ) ? $field->_prepare_value( $field->get_value() ) : 0;
				return [
					$this->_get_column_key() => $value,
				];
			}
		}

		return [];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$parts = explode( '->', $this->props['source'] );
		$field = $this->post_type->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'number' ) {
				$query->orderby( sprintf(
					'`%s` %s',
					$this->_get_column_key(),
					$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
				) );
			} elseif ( $field->get_type() === 'product' ) {
				if ( ( $parts[1] ?? null ) === ':minimum_price' ) {
					$query->join_price_index( $args );
					$query->select( $query->get_minimum_price_query( $args ).' AS min_price' );
					$query->orderby( sprintf(
						'min_price %s',
						$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
					) );
				}
			}
		}

	}

	private function _get_source_filter() {
		// check if a filter with this source already exists
		$filter = array_filter( $this->post_type->get_filters(), function( $filter ) {
			return $filter->get_prop('source') === $this->props['source'];
		} );

		return ! empty( $filter ) ? array_pop( $filter ) : null;
	}

	private function _get_column_key() {
		$filter = $this->_get_source_filter();
		if ( $filter ) {
			return $filter->db_key();
		}

		return sprintf( 'numsort_%s', $this->props['source'] );
	}

	public function _get_source_model() {
		return function() { ?>
			<div class="ts-form-group x-col-12">
				<label>Data source:</label>
				<select v-model="clause.source">
					<option v-for="field in $root.getFieldsByType('number')" :value="field.key">
						{{ field.label }}
					</option>
					<template v-for="field in $root.getFieldsByType('product')">
						<optgroup :label="field.label">
							<option :value="field.key+'->:minimum_price'">Minimum price</option>
						</optgroup>
					</template>
				</select>
			</div>
		<?php };
	}
}
