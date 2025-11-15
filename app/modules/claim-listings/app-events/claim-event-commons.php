<?php

namespace Voxel\Modules\Claim_Listings\App_Events;

use \Voxel\Modules\Claim_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Claim_Event_Commons {

	public $order, $order_item, $post, $customer;

	public function prepare( \Voxel\Order_Item $order_item ) {
		if ( $order_item->get_product_field_key() !== 'voxel:claim_request' ) {
			throw new \Exception( 'Missing information.' );
		}

		$order = $order_item->get_order();
		$post_id = $order_item->get_details('voxel:claim_request.post_id');
		$post = \Voxel\Post::get( $post_id );
		if ( ! ( $post && $order ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$customer = $order->get_customer();
		if ( ! $customer ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->order_item = $order_item;
		$this->order = $order;
		$this->post = $post;
		$this->customer = $customer;
	}

	public function get_category() {
		return 'claim_listings';
	}

	public function set_mock_props() {
		$this->customer = \Voxel\User::mock();
		$this->post = \Voxel\Post::mock();
		$this->order = \Voxel\Product_Types\Orders\Order::mock();
	}

	public function dynamic_tags(): array {
		return [
			'customer' => \Voxel\Dynamic_Data\Group::User( $this->customer ),
			'post' => \Voxel\Dynamic_Data\Group::Simple_Post( $this->post ),
			'order' => \Voxel\Dynamic_Data\Group::Order( $this->order ),
		];
	}
}
