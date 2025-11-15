<?php

namespace Voxel\Modules\Stripe_Payments;

if ( ! defined('ABSPATH') ) {
	exit;
}

new Controllers\Stripe_Payments_Controller;

// subscription updates (and update previews) through
// the Stripe API don't support dynamic product_data
function get_subscription_update_product( bool $testmode ) {
	$stripe = $testmode ? \Voxel\Modules\Stripe_Payments\Stripe_Client::get_test_client() : \Voxel\Modules\Stripe_Payments\Stripe_Client::get_live_client();

	$products = $stripe->products->search( [
		'query' => 'active:"true" metadata["voxel:category"]:"subscription_update"',
		'limit' => 1,
	] );

	if ( ! empty( $products->data ) ) {
		return $products->data[0];
	} else {
		$product = $stripe->products->create( [
			'name' => 'Subscription update',
			'active' => true,
			'metadata' => [
				'voxel:category' => 'subscription_update',
			],
		] );

		return $product;
	}
}
