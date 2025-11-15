<?php

namespace Voxel\Dynamic_Data\Data_Groups\Orders;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Promotion_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'orders/promotion';
	}

	public $order_item;
	public function __construct( \Voxel\Product_Types\Order_Items\Order_Item $order_item ) {
		$this->order_item = $order_item;
	}

	protected function properties(): array {
		return [
			'start_date' => Tag::Date('Start date')->render( function() {
				return $this->order_item->get_details( 'promotion.start_date' );
			} ),
			'end_date' => Tag::Date('End date')->render( function() {
				return $this->order_item->get_details( 'promotion.end_date' );
			} ),
			'canceled_at' => Tag::Date('Cancellation date')->render( function() {
				return $this->order_item->get_details( 'promotion.canceled_at' );
			} ),
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Product_Types\Order_Items\Order_Item_Regular::mock() );
	}
}
