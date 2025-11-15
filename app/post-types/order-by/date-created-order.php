<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Created_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'date-created',
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Date created';
	}

	public function get_models(): array {
		return [
			'order' => $this->get_order_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$table->add_column( '`date-created` DATETIME' );
		$table->add_key( 'KEY(`date-created`)' );
	}

	public function index( \Voxel\Post $post ): array {
		return [
			'date-created' => sprintf( '\'%s\'', esc_sql( $post->get_date() ) ),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`date-created` %s',
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
	}
}
