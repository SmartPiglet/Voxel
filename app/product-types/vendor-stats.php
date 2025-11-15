<?php

namespace Voxel\Product_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Stats {

	protected $vendor, $vendor_id, $testmode;

	protected $general_stats, $last31_stats;

	public function __construct( \Voxel\User $vendor ) {
		$this->vendor = $vendor;
		$this->vendor_id = absint( $vendor->get_id() );
		$this->testmode = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'true' : 'false';
	}

	protected function _where_vendor_is() {
		if ( $this->vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) {
			return 'orders.vendor_id IS NULL';
		}

		return sprintf( 'orders.vendor_id = %d', absint( $this->vendor->get_id() ) );
	}

	public function get_total_earnings_in_cents() {
		return $this->get_general_stats()['total_earnings_in_cents'] ?? 0;
	}

	public function get_total_fees_in_cents() {
		return $this->get_general_stats()['total_fees_in_cents'] ?? 0;
	}

	public function get_total_customer_count() {
		return $this->get_general_stats()['customer_count'] ?? 0;
	}

	public function get_total_order_count( $order_status ) {
		return $this->get_general_stats()['order_counts'][ $order_status ] ?? 0;
	}

	public function get_this_year_stats() {
		return $this->get_year_stats( date('Y') );
	}

	public function get_this_month_stats() {
		$stats = [
			'orders' => 0,
			'earnings_in_cents' => 0,
			'fees_in_cents' => 0,
		];

		$last31 = $this->get_last31_stats();
		$this_month = date( 'Y-m' );
		foreach ( $last31 as $date => $date_stats ) {
			if ( date( 'Y-m', strtotime( $date ) ) === $this_month ) {
				$stats['orders'] += $date_stats['orders'];
				$stats['earnings_in_cents'] += $date_stats['earnings_in_cents'];
				$stats['fees_in_cents'] += $date_stats['fees_in_cents'];
			}
		}

		return $stats;
	}

	public function get_this_week_stats() {
		$weekdays = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];
		$start_of_week = absint( get_option( 'start_of_week' ) );
		$day_of_week = absint( date('w') );
		if ( $day_of_week === $start_of_week ) {
			return $this->get_today_stats();
		}

		$stats = [
			'orders' => 0,
			'earnings_in_cents' => 0,
			'fees_in_cents' => 0,
		];

		$last31 = $this->get_last31_stats();
		$start_date = date( 'Y-m-d', strtotime( 'previous '.$weekdays[ $start_of_week ] ) );
		for ( $i = 0; $i <= 6; $i++) {
			$date = date( 'Y-m-d', strtotime( '+'.$i.' days', strtotime( $start_date ) ) );
			if ( isset( $last31[ $date ] ) ) {
				$stats['orders'] += $last31[ $date ]['orders'];
				$stats['earnings_in_cents'] += $last31[ $date ]['earnings_in_cents'];
				$stats['fees_in_cents'] += $last31[ $date ]['fees_in_cents'];
			}
		}

		return $stats;
	}

	public function get_today_stats() {
		$default_stats = [
			'orders' => 0,
			'earnings_in_cents' => 0,
			'fees_in_cents' => 0,
		];

		return $this->get_last31_stats()[ date( 'Y-m-d' ) ] ?? $default_stats;
	}


	/**
	 * Stat helpers
	 */

	public function get_year_stats( $year ) {
		$stats = [
			'orders' => 0,
			'earnings_in_cents' => 0,
			'fees_in_cents' => 0,
		];

		$months = $this->get_general_stats()['categorized'][ $year ] ?? [];
		foreach ( $months as $month ) {
			$stats['orders'] += $month['orders'];
			$stats['earnings_in_cents'] += $month['earnings_in_cents'];
			$stats['fees_in_cents'] += $month['fees_in_cents'];
		}

		return $stats;
	}


	/**
	 * Charts
	 */

	public function get_year_chart( $year ) {
		$months = $this->get_general_stats()['categorized'][ $year ] ?? [];
		$currency = \Voxel\get_primary_currency();

		$min = 0;
		$max = max( ! empty( $months ) ? max( array_column( $months, 'earnings_in_cents' ) ) : 0, 1 );
		$steps = $this->get_steps_from_max_earnings( $max );

		$items = [];

		for ( $i = 1; $i <= 12; $i++ ) {
			$label = date_i18n( 'M', strtotime( sprintf( '2022-%s-01', zeroise( $i, 2 ) ) ) );

			if ( ! isset( $months[ $i ] ) ) {
				$items[] = [
					'label' => $label,
					'percent' => 0,
					'earnings' => 0,
					'orders' => 0,
				];
				continue;
			}

			$items[] = [
				'label' => $label,
				'percent' => round( ( $months[ $i ]['earnings_in_cents'] / $max ) * 100, 3 ),
				'earnings' => \Voxel\currency_format( $months[ $i ]['earnings_in_cents'], $currency ),
				'orders' => number_format_i18n( $months[ $i ]['orders'] ),
			];
		}


		$meta = [
			'label' => $year,
			'state' => [
				'date' => date( 'Y-01-01', strtotime( $year ) ),
				'has_next' => strtotime( '+1 year', strtotime( date( 'Y-01-01', strtotime( $year ) ) ) ) < time(),
				'has_prev' => ! $this->is_before_first_order( date( 'Y-12-31', strtotime( '-1 year', strtotime( date( 'Y-01-01', strtotime( $year ) ) ) ) ) ),
				'has_activity' => ! empty( $months ) && max( array_column( $months, 'earnings_in_cents' ) ) > 0,
			],
		];

		return compact( 'steps', 'items', 'meta' );
	}

	public function get_all_time_chart() {
		$years = $this->get_general_stats()['categorized'] ?? [];
		$current_year = (int) date('Y');
		$start_year = ! empty( $years) ? min( array_keys( $years ) ) : $current_year;

		$stats = [];
		$currency = \Voxel\get_primary_currency();

		for ( $i = $start_year; $i <= $current_year; $i++ ) {
			$stats[ $i ] = $this->get_year_stats( $i );
		}

		$min = 0;
		$max = max( ! empty( $stats ) ? max( array_column( $stats, 'earnings_in_cents' ) ) : 0, 1 );
		$steps = $this->get_steps_from_max_earnings( $max );

		$items = [];

		for ( $i = $start_year; $i <= $current_year; $i++ ) {
			$label = date_i18n( 'Y', strtotime( sprintf( '%s-01-01', $i ) ) );

			if ( ! isset( $stats[ $i ] ) ) {
				$items[] = [
					'label' => $label,
					'percent' => 0,
					'earnings' => 0,
					'orders' => 0,
				];
				continue;
			}

			$items[] = [
				'label' => $label,
				'percent' => round( ( $stats[ $i ]['earnings_in_cents'] / $max ) * 100, 3 ),
				'earnings' => \Voxel\currency_format( $stats[ $i ]['earnings_in_cents'], $currency ),
				'orders' => number_format_i18n( $stats[ $i ]['orders'] ),
			];
		}

		$meta = [
			'label' => _x( 'All time stats', 'sales chart', 'voxel' ),
			'state' => [
				'date' => null,
				'has_next' => false,
				'has_prev' => false,
				'has_activity' => ! empty( $stats ) && max( array_column( $stats, 'earnings_in_cents' ) ) > 0,
			],
		];

		return compact( 'steps', 'items', 'meta' );
	}

	public function get_month_chart( $month ) {
		$timestamp = strtotime( $month );
		$days = $this->calculate_in_date_range( date( 'Y-m-01', $timestamp ), date( 'Y-m-t', $timestamp ) );

		$min = 0;
		$max = max( ! empty( $days ) ? max( array_column( $days, 'earnings_in_cents' ) ) : 0, 1 );
		$steps = $this->get_steps_from_max_earnings( $max );
		$currency = \Voxel\get_primary_currency();
		$days_in_month = (int) date( 't', $timestamp );

		$items = [];

		for ( $i = 1; $i <= $days_in_month; $i++ ) {
			$key = date( sprintf( 'Y-m-%s', zeroise( $i, 2 ) ), $timestamp );
			$label = zeroise( $i, 2 );

			if ( ! isset( $days[ $key ] ) ) {
				$items[] = [
					'label' => $label,
					'percent' => 0,
					'earnings' => 0,
					'orders' => 0,
				];
				continue;
			}

			$items[] = [
				'label' => $label,
				'percent' => round( ( $days[ $key ]['earnings_in_cents'] / $max ) * 100, 3 ),
				'earnings' => \Voxel\currency_format( $days[ $key ]['earnings_in_cents'], $currency ),
				'orders' => number_format_i18n( $days[ $key ]['orders'] ),
			];
		}

		$meta = [
			'label' => date_i18n( 'F', $timestamp ),
			'state' => [
				'date' => date( 'Y-m-01', $timestamp ),
				'has_next' => strtotime( '+1 month', strtotime( date( 'Y-m-01', $timestamp ) ) ) < time(),
				'has_prev' => ! $this->is_before_first_order( date( 'Y-m-t', strtotime( '-1 month', strtotime( date( 'Y-m-01', $timestamp ) ) ) ) ),
				'has_activity' => ! empty( $days ) && max( array_column( $days, 'earnings_in_cents' ) ) > 0,
			],
		];

		return compact( 'steps', 'items', 'meta' );
	}

	public function get_week_chart( $date ) {
		$timestamp = strtotime( $date );
		$weekdays = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];
		$start_of_week = absint( get_option( 'start_of_week' ) );
		$day_of_week = absint( date( 'w', $timestamp ) );
		if ( $day_of_week === $start_of_week ) {
			$start_day = date( 'Y-m-d', $timestamp );
		} else {
			$start_day = date( 'Y-m-d', strtotime( 'previous '.$weekdays[ $start_of_week ], $timestamp ) );
		}

		$end_day = date( 'Y-m-d', strtotime( '+6 days', strtotime( $start_day ) ) );

		$days = $this->calculate_in_date_range( $start_day, $end_day );

		$min = 0;
		$max = max( ! empty( $days ) ? max( array_column( $days, 'earnings_in_cents' ) ) : 0, 1 );
		$steps = $this->get_steps_from_max_earnings( $max );
		$currency = \Voxel\get_primary_currency();

		$items = [];

		for ( $i = 0; $i <= 6; $i++ ) {
			$date = date( 'Y-m-d', strtotime( '+'.$i.' days', strtotime( $start_day ) ) );
			$label = date_i18n( 'D', strtotime( $date ) );

			if ( ! isset( $days[ $date ] ) ) {
				$items[] = [
					'label' => $label,
					'percent' => 0,
					'earnings' => 0,
					'orders' => 0,
				];
				continue;
			}

			$items[] = [
				'label' => $label,
				'percent' => round( ( $days[ $date ]['earnings_in_cents'] / $max ) * 100, 3 ),
				'earnings' => \Voxel\currency_format( $days[ $date ]['earnings_in_cents'], $currency ),
				'orders' => number_format_i18n( $days[ $date ]['orders'] ),
			];
		}

		$meta = [
			'label' => sprintf(
				'%s - %s',
				\Voxel\date_format( strtotime( $start_day ) ),
				\Voxel\date_format( strtotime( $end_day ) )
			),
			'state' => [
				'date' => $start_day,
				'has_next' => strtotime( '+7 days', strtotime( $start_day ) ) < time(),
				'has_prev' => ! $this->is_before_first_order( date( 'Y-m-d', strtotime( '-7 days', strtotime( $end_day ) ) ) ),
				'has_activity' => ! empty( $days ) && max( array_column( $days, 'earnings_in_cents' ) ) > 0,
			],
		];

		return compact( 'steps', 'items', 'meta' );
	}

	protected function get_steps_from_max_earnings( $max ) {
		$currency = \Voxel\get_primary_currency();
		$steps = [ $max, $max * 0.8, $max * 0.6, $max * 0.4, $max * 0.2, 0 ];
		$steps = array_map( function( $step ) use ( $currency ) {
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$step /= 100;
			}

			$step = round( $step, 0 );
			return \Voxel\currency_format( $step, $currency, false );
		}, $steps );

		return array_unique( $steps );
	}

	protected function get_first_order_date() {
		return $this->get_general_stats()['first_order_date'] ?? null;
	}

	public function is_before_first_order( $date ) {
		$first_order_date = strtotime( date( 'Y-m-d', strtotime( $this->get_first_order_date() ?? '' ) ) );
		return ! $first_order_date || $first_order_date > strtotime( $date );
	}

	/**
	 * Expire stats when an orders transitions from/to completed.
	 */

	public function expire_general_stats() {
		delete_user_meta( $this->vendor->get_id(), 'voxel:vendor_stats' );
	}

	public function expire_last31_stats() {
		delete_user_meta( $this->vendor->get_id(), 'voxel:vendor_last31' );
	}

	/**
	 * Retrieve stats from database
	 */

	public function get_general_stats() {
		if ( ! is_null( $this->general_stats ) ) {
			return $this->general_stats;
		}

		$stats = (array) json_decode( get_user_meta( $this->vendor->get_id(), 'voxel:vendor_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total_earnings_in_cents'] ) ) {
			$overview = $this->calculate_overview();
			$customer_count = $this->calculate_customer_count();
			$order_counts = $this->calculate_order_counts_by_status();
			$first_order_date = $this->calculate_first_order_date();

			$stats = [
				'customer_count' => $customer_count,
				'total_earnings_in_cents' => $overview['total_earnings_in_cents'],
				'total_fees_in_cents' => $overview['total_fees_in_cents'],
				'categorized' => $overview['categorized'],
				'order_counts' => $order_counts,
				'first_order_date' => $first_order_date,
			];

			update_user_meta( $this->vendor->get_id(), 'voxel:vendor_stats', wp_slash( wp_json_encode( $stats ) ) );
		}

		$this->general_stats = $stats;
		return $this->general_stats;
	}

	public function get_last31_stats() {
		if ( ! is_null( $this->last31_stats ) ) {
			return $this->last31_stats;
		}

		$stats = (array) json_decode( get_user_meta( $this->vendor->get_id(), 'voxel:vendor_last31', true ), ARRAY_A );
		if ( empty( $stats ) ) {
			$stats = $this->calculate_last31();
			update_user_meta( $this->vendor->get_id(), 'voxel:vendor_last31', wp_slash( wp_json_encode( $stats ) ) );
		}

		$this->last31_stats = $stats;
		return $this->last31_stats;
	}


	/**
	 * Calculate stats
	 */

	protected function calculate_customer_count() {
		global $wpdb;

		$sql = <<<SQL
			SELECT COUNT(DISTINCT orders.customer_id)
			FROM {$wpdb->prefix}vx_orders AS orders
			WHERE
				{$this->_where_vendor_is()}
				AND orders.status = 'completed'
				AND orders.testmode IS {$this->testmode}
		SQL;

		return absint( $wpdb->get_var( $sql ) );
	}

	protected function calculate_order_counts_by_status() {
		global $wpdb;

		$sql = <<<SQL
			SELECT `status`, COUNT(`status`) AS `count`
			FROM {$wpdb->prefix}vx_orders AS orders
			WHERE
				{$this->_where_vendor_is()}
				AND orders.testmode IS {$this->testmode}
			GROUP BY `status`
		SQL;

		$counts = $wpdb->get_results( $sql );

		$statuses = [];
		foreach ( $counts as $item ) {
			$statuses[ $item->status ] = absint( $item->count );
		}

		return $statuses;
	}

	protected function calculate_overview() {
		global $wpdb;

		$sql = <<<SQL
			SELECT
				DATE_FORMAT( orders.created_at, '%Y-%m' ) AS `period`,
				COUNT(*) AS `orders`,
				SUM( CASE
					WHEN orders.payment_method = 'stripe_transfer'
						THEN JSON_UNQUOTE(JSON_EXTRACT(orders.details, '$.transfer.amount'))
					ELSE JSON_UNQUOTE(JSON_EXTRACT(orders.details, '$.payment_intent.amount'))
					END
				) AS `earnings`,
				SUM( JSON_UNQUOTE( JSON_EXTRACT(
					orders.details, "$.payment_intent.application_fee_amount"
				) ) ) AS `fees`
			FROM {$wpdb->prefix}vx_orders AS orders
			WHERE
				{$this->_where_vendor_is()}
				AND orders.status = 'completed'
				AND orders.testmode IS {$this->testmode}
			GROUP BY `period`
		SQL;

		$results = $wpdb->get_results( $sql );

		$overview = [];
		$total_earnings = 0;
		$total_fees = 0;

		foreach ( $results as $period ) {
			list( $year, $month ) = explode( '-', $period->period );
			$year = (int) $year;
			$month = (int) $month;

			$orders = (int) $period->orders;
			$earnings = (int) $period->earnings;
			$fees = (int) $period->fees;

			$total_earnings += $earnings;
			$total_fees += $fees;

			if ( ! isset( $overview[ $year ] ) ) {
				$overview[ $year ] = [];
			}

			$overview[ $year ][ $month ] = [
				'orders' => $orders,
				'earnings_in_cents' => $earnings,
				'fees_in_cents' => $fees,
			];
		}

		return [
			'total_earnings_in_cents' => $total_earnings,
			'total_fees_in_cents' => $total_fees,
			'categorized' => $overview,
		];
	}

	protected function calculate_first_order_date() {
		global $wpdb;

		$result = $wpdb->get_var( <<<SQL
			SELECT orders.created_at
			FROM {$wpdb->prefix}vx_orders AS orders
			WHERE
				{$this->_where_vendor_is()}
				AND orders.status = 'completed'
				AND orders.testmode IS {$this->testmode}
			ORDER BY orders.created_at ASC
			LIMIT 1
		SQL );

		$timestamp = strtotime( $result ?? '' );
		return $timestamp ? date( 'Y-m-d H:i:s', $timestamp ) : null;
	}

	protected function calculate_last31() {
		global $wpdb;

		$results = $wpdb->get_results( <<<SQL
			SELECT
				DATE( orders.created_at ) AS `period`,
				COUNT(*) AS `orders`,
				SUM( JSON_UNQUOTE( JSON_EXTRACT(
					orders.details, "$.payment_intent.amount"
				) ) ) AS `earnings`,
				SUM( JSON_UNQUOTE( JSON_EXTRACT(
					orders.details, "$.payment_intent.application_fee_amount"
				) ) ) AS `fees`
			FROM {$wpdb->prefix}vx_orders AS orders
			WHERE
				{$this->_where_vendor_is()}
				AND orders.status = 'completed'
				AND orders.testmode IS {$this->testmode}
			GROUP BY `period`
			ORDER BY `period` DESC
			LIMIT 31
		SQL );

		$last_31 = [];
		foreach ( $results as $period ) {
			$last_31[ $period->period ] = [
				'orders' => (int) $period->orders,
				'earnings_in_cents' => (int) $period->earnings,
				'fees_in_cents' => (int) $period->fees,
			];
		}

		return $last_31;
	}

	protected function calculate_in_date_range( $start_date, $end_date ) {
		global $wpdb;

		$start_stamp = strtotime( $start_date ?? '' );
		$end_stamp = strtotime( $end_date ?? '' );
		if ( ! ( $start_stamp && $end_stamp && $end_stamp >= $start_stamp ) ) {
			return [];
		}

		$last31 = $this->get_last31_stats();
		$earliest_cached = strtotime( array_key_last( $last31 ) ?? '' );
		if ( $earliest_cached && $earliest_cached <= $start_stamp ) {
			return array_reverse( array_filter( $last31, function( $date ) use ( $start_stamp, $end_stamp ) {
				$timestamp = strtotime( $date );
				return $timestamp >= $start_stamp && $timestamp <= $end_stamp;
			}, ARRAY_FILTER_USE_KEY ) );
		}

		$start_range = esc_sql( date( 'Y-m-d 00:00:00', $start_stamp ) );
		$end_range = esc_sql( date( 'Y-m-d 23:59:59', $end_stamp ) );

		$results = $wpdb->get_results( <<<SQL
			SELECT
				DATE( orders.created_at ) AS `period`,
				COUNT(*) AS `orders`,
				SUM( JSON_UNQUOTE( JSON_EXTRACT(
					orders.details, "$.payment_intent.amount"
				) ) ) AS `earnings`,
				SUM( JSON_UNQUOTE( JSON_EXTRACT(
					orders.details, "$.payment_intent.application_fee_amount"
				) ) ) AS `fees`
			FROM {$wpdb->prefix}vx_orders AS orders
			WHERE
				{$this->_where_vendor_is()}
				AND orders.status = 'completed'
				AND orders.testmode IS {$this->testmode}
				AND (
					orders.created_at >= '{$start_range}'
					AND orders.created_at <= '{$end_range}'
				)
			GROUP BY `period`
			ORDER BY `period` ASC
		SQL );

		$items = [];
		foreach ( $results as $period ) {
			$items[ $period->period ] = [
				'orders' => (int) $period->orders,
				'earnings_in_cents' => (int) $period->earnings,
				'fees_in_cents' => (int) $period->fees,
			];
		}

		return $items;
	}
}
