<?php

namespace Voxel\Dynamic_Data\Data_Groups\User;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Membership_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'user/membership';
	}

	public $membership;
	public function __construct( \Voxel\Modules\Paid_Memberships\Membership\Base_Membership $membership ) {
		$this->membership = $membership;
	}

	protected function properties(): array {
		return [
			'plan' => Tag::Object('Plan')->properties( function() {
				return [
					'key' => Tag::String('Key')->render( function() {
						return $this->membership->get_selected_plan()->get_key();
					} ),
					'label' => Tag::String('Label')->render( function() {
						return $this->membership->get_selected_plan()->get_label();
					} ),
					'description' => Tag::String('Description')->render( function() {
						return $this->membership->get_selected_plan()->get_description();
					} ),
				];
			} ),
			'pricing' => Tag::Object('Pricing')->properties( function() {
				return [
					'formatted' => Tag::Number('Formatted')->render( function() {
						if ( $this->membership->get_type() === 'order' ) {
							$amount = \Voxel\currency_format(
								$this->membership->get_amount(),
								$this->membership->get_currency(),
								false
							);

							$period = \Voxel\interval_format(
								$this->membership->get_interval(),
								$this->membership->get_frequency()
							);

							return sprintf( '%s %s', $amount, $period );
						} else {
							return '';
						}
					} ),
					'amount' => Tag::Number('Amount')->render( function() {
						if ( $this->membership->get_type() === 'order' ) {
							return $this->membership->get_amount();
						} else {
							return 0;
						}
					} ),
					'currency' => Tag::String('Currency')->render( function() {
						if ( $this->membership->get_type() === 'order' ) {
							return $this->membership->get_currency();
						} else {
							return \Voxel\get_primary_currency();
						}
					} ),
					'period' => Tag::String('Period')->render( function() {
						if ( $this->membership->get_type() === 'order' && $this->membership->get_interval() && $this->membership->get_frequency() ) {
							return \Voxel\interval_format( $this->membership->get_interval(), $this->membership->get_frequency() );
						} else {
							return '';
						}
					} ),
					'status' => Tag::String('Status')->render( function() {
						if ( $this->membership->get_type() === 'order' && ( $order = $this->membership->get_order() ) ) {
							return $order->get_status();
						} else {
							return '';
						}
					} ),
					'start_date' => Tag::Date('Start date')->render( function() {
						if ( $this->membership->get_type() === 'order' && ( $order = $this->membership->get_order() ) && ( $created_at = $order->get_created_at() ) ) {
							return date( 'Y-m-d H:i:s', $created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
						} else {
							return '';
						}
					} ),
				];
			} ),
		];
	}

	public static function mock(): self {
		return new static( \Voxel\User::dummy()->get_membership() );
	}
}
