<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Priority_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'priority',
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Priority';
	}

	public function get_models(): array {
		return [
			'order' => $this->get_order_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`priority` %s',
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
	}
}
