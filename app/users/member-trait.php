<?php

namespace Voxel\Users;

use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Member_Trait {

	protected $_membership;
	public function get_membership( $refresh_cache = false ): Membership {
		if ( $refresh_cache ) {
			$this->_membership = null;
		}

		if ( ! is_null( $this->_membership ) ) {
			return $this->_membership;
		}

		$this->_membership = Membership::get( $this->get_id() );

		return $this->_membership;
	}

	public function is_eligible_for_free_trial(): bool {
		$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
		if ( ! metadata_exists( 'user', $this->get_id(), \Voxel\get_site_specific_user_meta_key( $meta_key ) ) ) {
			return true;
		}

		$details = (array) json_decode( \Voxel\get_site_specific_user_meta( $this->get_id(), $meta_key, true ), true );
		if ( ( $details['plan'] ?? null ) === 'default' && ( $details['trial_allowed'] ?? null ) ) {
			return true;
		}

		return false;
	}

}
