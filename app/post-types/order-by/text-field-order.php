<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Field_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'text-field',
		'source' => '',
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Text field';
	}

	public function get_models(): array {
		return [
			'source' => $this->get_source_model( [
				'title',
				'description',
				'text',
				'texteditor',
				'profile-name',
			] ),
			'order' => $this->get_order_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$table->add_column( sprintf( '`%s` VARCHAR(16) NOT NULL DEFAULT \'\'', esc_sql( $this->_get_column_key() ) ) );
		$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->_get_column_key() ) ) );
	}

	public function index( \Voxel\Post $post ): array {
		$field = $post->get_field( $this->props['source'] );
		if ( ! $field ) {
			$value = '';
		} else {
			$value = $field->get_value();
			$value = is_string( $value ) ? mb_substr( $value, 0, 16 ) : '';
		}

		return [
			$this->_get_column_key() => sprintf( '\'%s\'', esc_sql( $value ) ),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`%s` %s',
			$this->_get_column_key(),
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
	}

	private function _get_column_key() {
		return sprintf( 'txtsort_%s', $this->props['source'] );
	}
}
