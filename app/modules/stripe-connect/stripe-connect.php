<?php

namespace Voxel\Modules\Stripe_Connect;

if ( ! defined('ABSPATH') ) {
	exit;
}

new Controllers\Stripe_Connect_Controller;

function is_marketplace_active(): bool {
	return (
		\Voxel\get('payments.provider') === 'stripe'
		&& \Voxel\get('payments.stripe.stripe_connect.enabled')
	);
}
