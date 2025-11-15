<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Field_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'date-field',
		'source' => '',
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Date field';
	}

	public function get_models(): array {
		return [
			'source' => $this->get_source_model( 'date' ),
			'order' => $this->get_order_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		if ( $this->_get_source_filter() ) {
			return;
		}

		$field = $this->post_type->get_field( $this->props['source'] );
		if ( $field && $field->get_type() === 'date' ) {
			$datatype = $field && $field->get_prop( 'enable_timepicker' ) ? 'DATETIME' : 'DATE';
			$table->add_column( sprintf( '`%s` %s', esc_sql( $this->_get_column_key() ), $datatype ) );
			$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->_get_column_key() ) ) );
		}
	}

	public function index( \Voxel\Post $post ): array {
		if ( $this->_get_source_filter() ) {
			return [];
		}

		$field = $post->get_field( $this->props['source'] );
		$value = $field ? $field->get_value() : null;
		if ( $value !== null ) {
			$timestamp = strtotime( $value );
			$format = $field->get_prop( 'enable_timepicker' ) ? 'Y-m-d H:i:s' : 'Y-m-d';
			if ( $timestamp ) {
				$value = date( $format, $timestamp );
			}
		}

		return [
			$this->_get_column_key() => $value ? sprintf( '\'%s\'', esc_sql( $value ) ) : 'NULL',
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`%s` %s',
			$this->_get_column_key(),
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
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

		return sprintf( 'datesort_%s', $this->props['source'] );
	}
}
