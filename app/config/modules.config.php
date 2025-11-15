<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

return apply_filters( 'voxel/modules', [
	locate_template( 'app/modules/paid-memberships/paid-memberships.php' ),
	locate_template( 'app/modules/claim-listings/claim-listings.php' ),
	locate_template( 'app/modules/stripe-payments/stripe-payments.php' ),
	locate_template( 'app/modules/paddle-payments/paddle-payments.php' ),
	locate_template( 'app/modules/stripe-connect/stripe-connect.php' ),
	locate_template( 'app/modules/elementor/elementor.php' ),
	locate_template( 'app/modules/collections/collections.php' ),
	locate_template( 'app/modules/google-maps/google-maps.php' ),
	locate_template( 'app/modules/mapbox/mapbox.php' ),
	locate_template( 'app/modules/direct-messages/direct-messages.php' ),
	locate_template( 'app/modules/paid-listings/paid-listings.php' ),
] );
