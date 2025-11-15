<?php

namespace Voxel\Modules\Paid_Memberships\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Membership_Default extends Base_Membership {

	protected $details;

	public function get_type(): string {
		return 'default';
	}

	protected function init( array $details ) {
		$this->details = $details;
	}

	public function is_active(): bool {
		return true;
	}

	// initial state, right after a user is registered
	// means the meta field for user plan has not been set yet
	public function is_initial_state(): bool {
		return empty( $this->details );
	}

}
