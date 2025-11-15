<?php

namespace Voxel\Modules\Paid_Memberships\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Membership_Legacy_Subscription extends Base_Membership {

	protected
		$subscription_id,
		$price_id,
		$status,
		$trial_end,
		$current_period_end,
		$cancel_at_period_end,
		$amount,
		$currency,
		$interval,
		$interval_count,
		$created,
		$metadata;

	protected function init( array $details ) {
		$this->subscription_id = $details['subscription_id'] ?? null;
		$this->price_id = $details['price_id'] ?? null;
		$this->status = $details['status'] ?? null;
		$this->trial_end = $details['trial_end'] ?? null;
		$this->current_period_end = $details['current_period_end'] ?? null;
		$this->cancel_at_period_end = $details['cancel_at_period_end'] ?? null;
		$this->amount = $details['amount'] ?? null;
		$this->currency = $details['currency'] ?? null;
		$this->interval = $details['interval'] ?? null;
		$this->interval_count = $details['interval_count'] ?? null;
		$this->created = $details['created'] ?? null;
		$this->metadata = $details['metadata'] ?? null;
	}

	public function get_type(): string {
		return 'legacy_subscription';
	}

	public function is_active(): bool {
		return in_array( $this->status, [ 'trialing', 'active' ], true );
	}

	public function get_subscription_id() {
		return $this->subscription_id;
	}

	public function get_price_id() {
		if ( ! empty( $this->metadata['voxel:original_price_id'] ) ) {
			return $this->metadata['voxel:original_price_id'];
		}

		return $this->price_id;
	}

	public function get_active_price_id() {
		return $this->price_id;
	}

	public function get_additional_limits() {
		if ( ! $this->is_active() ) {
			return [];
		}

		$limits = json_decode( $this->metadata['voxel:limits'] ?? '', true );
		if ( ! is_array( $limits ) ) {
			return [];
		}

		return array_filter( array_map( 'absint', $limits ) );
	}

	public function get_status() {
		return $this->status;
	}

	public function get_trial_end() {
		return $this->trial_end;
	}

	public function get_amount() {
		return $this->amount;
	}

	public function get_currency() {
		return $this->currency;
	}

	public function get_interval() {
		return $this->interval;
	}

	public function get_interval_count() {
		return $this->interval_count;
	}

	public function get_current_period_end() {
		return $this->current_period_end;
	}

	public function will_cancel_at_period_end() {
		return $this->cancel_at_period_end;
	}

	public function is_switchable() {
		return ! in_array( $this->get_status(), [ 'canceled', 'incomplete_expired' ], true );
	}

	public function get_created_at() {
		return $this->created;
	}

	public function get_metadata() {
		return $this->metadata;
	}

}
