<?php

namespace Voxel\Dynamic_Data\Data_Groups\Orders;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'orders/booking';
	}

	public $order_item;
	public function __construct( \Voxel\Product_Types\Order_Items\Order_Item $order_item ) {
		$this->order_item = $order_item;
	}

	protected function properties(): array {
		return [
			'order_summary' => Tag::String('Order summary')->render( function() {
				return $this->order_item->get_product_description();
			} ),
			'booking_schedule' => Tag::String('Booking schedule')->render( function() {
				return $this->order_item->get_booking_summary();
			} ),
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Product_Types\Order_Items\Order_Item_Booking::mock() );
	}
}
