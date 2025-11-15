<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Time_Field_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'time-field',
		'source' => '',
		'order' => 'ASC',
	];

	public function get_label(): string {
		return 'Time field';
	}

	public function get_models(): array {
		return [
			'source' => $this->get_source_model( 'time' ),
			'order' => $this->get_order_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$field = $this->post_type->get_field( $this->props['source'] );
		if ( $field && $field->get_type() === 'time' ) {
			$table->add_column( sprintf( '`%s` %s', esc_sql( $this->_get_column_key() ), 'TIME' ) );
			$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->_get_column_key() ) ) );
		}
	}

	public function index( \Voxel\Post $post ): array {
		$value = null;
		$field = $post->get_field( $this->props['source'] );
		if ( $field && $field->get_type() === 'time' ) {
			if ( $timestamp = strtotime( $field->get_value() ) ) {
				$value = date( 'H:i:s', $timestamp );
			}
		}

		return [
			$this->_get_column_key() => $value ? sprintf( '\'%s\'', esc_sql( $value ) ) : 'NULL',
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`%s` IS NULL, `%s` %s',
			$this->_get_column_key(),
			$this->_get_column_key(),
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
	}


	private function _get_column_key() {
		return sprintf( 'timesort_%s', $this->props['source'] );
	}
}
