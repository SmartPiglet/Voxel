<?php

namespace Voxel\Modules\Paid_Memberships;

use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

new Controllers\Paid_Memberships_Controller;

function update_user_plan( int $user_id, array $details, ?bool $is_test_mode = null ) {
	$user = \Voxel\User::get( $user_id );

	if ( $is_test_mode === null ) {
		$is_test_mode = \Voxel\is_test_mode();
	}

	$meta_key = $is_test_mode ? 'voxel:test_plan' : 'voxel:plan';

	$previous_plan = Membership::from( (array) json_decode(
		\Voxel\get_site_specific_user_meta( $user->get_id(), $meta_key, true ),
		true
	) );

	$new_plan = Membership::from( $details );

	\Voxel\update_site_specific_user_meta(
		$user->get_id(),
		$meta_key,
		wp_slash( wp_json_encode( $details ) )
	);

	do_action(
		'voxel/paid_memberships/updated_user_plan',
		$new_plan,
		$previous_plan,
		$user
	);
}
