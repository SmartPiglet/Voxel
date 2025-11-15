<?php

namespace Voxel\Dynamic_Data\Data_Groups\User;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Vendor_Data {

	protected function get_vendor_data(): Base_Data_Type {
		return Tag::Object('Vendor stats')->properties( function() {
			return [
				'earnings' => Tag::Number('Total earnings')->render( function() {
					$amount = $this->user->get_vendor_stats()->get_total_earnings_in_cents();
					if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
						$amount = round( $amount / 100, 2 );
					}

					return $amount;
				} ),
				'fees' => Tag::Number('Total platform fees')->render( function() {
					$amount = $this->user->get_vendor_stats()->get_total_fees_in_cents();
					if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
						$amount = round( $amount / 100, 2 );
					}

					return $amount;
				} ),
				'customers' => Tag::Number('Customer count')->render( function() {
					return $this->user->get_vendor_stats()->get_total_customer_count();
				} ),
				'orders' => Tag::Object('Order count')->properties( function() {
					return [
						'completed' => Tag::Number('Completed')->render( function() {
							return $this->user->get_vendor_stats()->get_total_order_count( \Voxel\ORDER_COMPLETED );
						} ),
						'pending_approval' => Tag::Number('Pending approval')->render( function() {
							return $this->user->get_vendor_stats()->get_total_order_count( \Voxel\ORDER_PENDING_APPROVAL );
						} ),
						'canceled' => Tag::Number('Canceled')->render( function() {
							return $this->user->get_vendor_stats()->get_total_order_count( \Voxel\ORDER_CANCELED );
						} ),
						'refunded' => Tag::Number('Refunded')->render( function() {
							return $this->user->get_vendor_stats()->get_total_order_count( \Voxel\ORDER_REFUNDED );
						} ),
					];
				} ),
				'this-year' => Tag::Object('This year')->properties( function() {
					return [
						'earnings' => Tag::Number('Earnings')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_this_year_stats()['earnings_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
						'orders' => Tag::Number('Completed orders')->render( function() {
							return $this->user->get_vendor_stats()->get_this_year_stats()['orders'];
						} ),
						'fees' => Tag::Number('Platform fees')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_this_year_stats()['fees_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
					];
				} ),
				'this-month' => Tag::Object('This month')->properties( function() {
					return [
						'earnings' => Tag::Number('Earnings')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_this_month_stats()['earnings_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
						'orders' => Tag::Number('Completed orders')->render( function() {
							return $this->user->get_vendor_stats()->get_this_month_stats()['orders'];
						} ),
						'fees' => Tag::Number('Platform fees')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_this_month_stats()['fees_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
					];
				} ),
				'this-week' => Tag::Object('This week')->properties( function() {
					return [
						'earnings' => Tag::Number('Earnings')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_this_week_stats()['earnings_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
						'orders' => Tag::Number('Completed orders')->render( function() {
							return $this->user->get_vendor_stats()->get_this_week_stats()['orders'];
						} ),
						'fees' => Tag::Number('Platform fees')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_this_week_stats()['fees_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
					];
				} ),
				'today' => Tag::Object('Today')->properties( function() {
					return [
						'earnings' => Tag::Number('Earnings')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_today_stats()['earnings_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
						'orders' => Tag::Number('Completed orders')->render( function() {
							return $this->user->get_vendor_stats()->get_today_stats()['orders'];
						} ),
						'fees' => Tag::Number('Platform fees')->render( function() {
							$amount = $this->user->get_vendor_stats()->get_today_stats()['fees_in_cents'];
							if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( \Voxel\get_primary_currency() ) ) {
								$amount = round( $amount / 100, 2 );
							}

							return $amount;
						} ),
					];
				} ),
			];
		} );
	}

}
