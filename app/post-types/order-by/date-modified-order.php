<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Modified_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'date-modified',
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Date modified';
	}

	public function get_models(): array {
		return [
			'order' => $this->get_order_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$table->add_column( '`date-modified` DATETIME' );
		$table->add_key( 'KEY(`date-modified`)' );
	}

	public function index( \Voxel\Post $post ): array {
		return [
			'date-modified' => sprintf( '\'%s\'', esc_sql( $post->get_modified_date() ) ),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`date-modified` %s',
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
	}
}
