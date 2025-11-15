<?php

namespace Voxel\Modules\Paid_Memberships\Membership;

use \Voxel\Modules\Paid_Memberships as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Membership {

	protected $selected_plan;

	protected $_raw_details = [];

	abstract public function get_type(): string;

	public function __construct( array $details ) {
		$this->_raw_details = $details;

		$this->selected_plan = Module\Plan::get( $details['plan'] ?? null ) ?? Module\Plan::get('default');
		$this->init( $details );
	}

	protected function init( array $details ) {
		//
	}

	public function is_active(): bool {
		return true;
	}

	public function get_selected_plan(): Module\Plan {
		return $this->selected_plan;
	}

	public function get_active_plan(): Module\Plan {
		return $this->is_active() ? $this->selected_plan : Module\Plan::get('default');
	}

	public static function get( int $user_id ) {
		$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
		$details = (array) json_decode( \Voxel\get_site_specific_user_meta( $user_id, $meta_key, true ), true );

		return static::from( $details );
	}

	public static function from( array $details ) {
		$type = $details['type'] ?? 'default';

		if ( $type === 'order' ) {
			return new Membership_Order( $details );
		} else {
			return new Membership_Default( $details );
		}
	}

	public static function get_legacy( int $user_id ) {
		$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
		$details = (array) json_decode( get_user_meta( $user_id, $meta_key, true ), true );

		$type = $details['type'] ?? 'default';

		if ( $type === 'subscription' ) {
			return new Membership_Legacy_Subscription( $details );
		} elseif ( $type === 'payment' ) {
			return new Membership_Legacy_Payment( $details );
		} else {
			return null;
		}
	}

	public function to_array(): array {
		return $this->_raw_details;
	}
}
