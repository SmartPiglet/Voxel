<?php

namespace Voxel\Dynamic_Data\Data_Groups\User;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Membership_Data {

	protected function get_membership_plan_data(): Base_Data_Type {
		return Tag::Object('Membership plan')->properties( function() {
			return [
				'key' => Tag::String('Key')->render( function() {
					$membership = $this->user->get_membership();
					return $membership->get_active_plan()->get_key();
				} ),
				'label' => Tag::String('Label')->render( function() {
					$membership = $this->user->get_membership();
					return $membership->get_active_plan()->get_label();
				} ),
				'description' => Tag::String('Description')->render( function() {
					$membership = $this->user->get_membership();
					return $membership->get_active_plan()->get_description();
				} ),
				'pricing' => Tag::Object('Pricing')->properties( function() {
					return [
						'amount' => Tag::Number('Amount')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'order' ) {
								return $membership->get_amount();
							} else {
								return 0;
							}
						} ),
						'period' => Tag::String('Period')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'order' && $membership->get_interval() && $membership->get_frequency() ) {
								return \Voxel\interval_format( $membership->get_interval(), $membership->get_frequency() );
							} else {
								return '';
							}
						} ),
						'currency' => Tag::String('Currency')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'order' ) {
								return $membership->get_currency();
							} else {
								return \Voxel\get_primary_currency();
							}
						} ),
						'status' => Tag::String('Status')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'order' && ( $order = $membership->get_order() ) ) {
								return $order->get_status();
							} else {
								return '';
							}
						} ),
						'start_date' => Tag::Date('Start date')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'order' && ( $order = $membership->get_order() ) && ( $created_at = $order->get_created_at() ) ) {
								return date( 'Y-m-d H:i:s', $created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
							} else {
								return '';
							}
						} ),
					];
				} ),
			];
		} );
	}

}
