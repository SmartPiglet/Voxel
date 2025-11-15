<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'booking',
		'label' => 'Booking calendar',
	];

	public function get_models(): array {
		return [
			'label' => Form_Models::Text( [
				'label' => 'Label',
				'classes' => 'x-col-12',
			] ),
			'description' => Form_Models::Textarea( [
				'label' => 'Description',
				'classes' => 'x-col-12',
			] ),
		];
	}

	public function get_conditions(): array {
		return [
			'settings.product_mode' => 'booking',
			'modules.booking.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$field_schema = Schema::Object( [
			'availability' => Schema::Object( [
				'max_days' => Schema::Int()->min(0)->default(30),
				'buffer' => Schema::Object( [
					'amount' => Schema::Int()->min(0)->default(1),
					'unit' => Schema::Enum( [ 'days', 'hours' ] )->default('days'),
				] ),
			] ),
			'excluded_days_enabled' => Schema::Bool()->default(false),
			'excluded_days' => Schema::List()
				->validator( function( $item ) {
					$timestamp = strtotime( $item );
					return $timestamp && ( $timestamp > ( time() - DAY_IN_SECONDS ) );
				} )
				->transformer( function( $item ) {
					return date( 'Y-m-d', strtotime( $item ) );
				} )
				->default([])
		] );

		if ( $this->is_quantity_enabled() ) {
			$field_schema->set_prop( 'quantity_per_slot', Schema::Int()->min(0)->default(1) );
		}

		if ( $this->get_booking_type() === 'days' ) {
			$field_schema->set_prop( 'excluded_weekdays', Schema::List()->validator( '\Voxel\is_valid_weekday' )->default([]) );

			$field_schema->set_prop( 'booking_mode', Schema::Enum( [
				'single_day',
				'date_range',
			] )->default( 'date_range' ) );

			$range_length_limit = apply_filters( 'voxel/product-types/booking/range-length-limit', 100 );

			$field_schema->set_prop( 'date_range', Schema::Object( [
				'set_custom_limits' => Schema::Bool()->default(false),
				'min_length' => Schema::Int()->min(1)->max( $range_length_limit )->default(1),
				'max_length' => Schema::Int()->min(1)->max( $range_length_limit )->default(7),
			] ) );
		} elseif ( $this->get_booking_type() === 'timeslots' ) {
			$field_schema->set_prop( 'timeslots', Schema::Object( [
				'groups' => Schema::Object_List( [
					'days' => Schema::List()->validator( '\Voxel\is_valid_weekday' )->default([]),
					'slots' => Schema::List()
						->validator( function( $item ) {
							return is_array( $item ) && strtotime( $item['from'] ?? '' ) && strtotime( $item['to'] ?? '' );
						} )
						->transformer( function( $item ) {
							return [
								'from' => date( 'H:i', strtotime( $item['from'] ) ),
								'to' => date( 'H:i', strtotime( $item['to'] ) ),
							];
						} )
						->default([]),
				] )->default([]),
			] ) );
		}

		$schema->set_prop( 'booking', $field_schema );
	}

	public function get_booking_type() {
		return $this->product_type->config( 'modules.booking.type' );
	}

	public function is_quantity_enabled(): bool {
		return !! $this->product_type->config( 'modules.booking.quantity_per_slot.enabled' );
	}

	public function validate( $value ): void {
		if ( $this->get_booking_type() === 'timeslots' ) {
			foreach ( $value['booking']['timeslots']['groups'] as $group ) {
				if ( count( $group['slots'] ) > 50 ) {
					throw new \Exception( \Voxel\replace_vars(
						_x( '@field_name: You cannot add more than 50 timeslots per day', 'field validation', 'voxel' ), [
							'@field_name' => $this->product_field->get_label(),
						]
					) );
				}
			}
		}

		if ( $this->get_booking_type() === 'days' ) {
			if ( $value['booking']['booking_mode'] === 'date_range' ) {
				if ( $value['booking']['date_range']['set_custom_limits'] ) {
					if ( $value['booking']['date_range']['max_length'] < $value['booking']['date_range']['min_length'] ) {
						throw new \Exception( \Voxel\replace_vars(
							_x( '@field_name: Minimum range length cannot be larger than maximum length', 'field validation', 'voxel' ), [
								'@field_name' => $this->product_field->get_label(),
							]
						) );
					}
				}
			}
		}
	}

	public function update( $value ) {
		if ( $this->get_booking_type() === 'timeslots' ) {
			foreach ( $value['booking']['timeslots']['groups'] as $i => $group ) {
				$slots = array_unique( $group['slots'], SORT_REGULAR );

				usort( $slots, function( $a, $b ) {
					$a_from = strtotime( $a['from'] );
					$b_from = strtotime( $b['from'] );
					$a_to = strtotime( $a['to'] );
					$b_to = strtotime( $b['to'] );

					if ( $a_from === $b_from ) {
					    return $a_to - $b_to;
					}

					return $a_from - $b_from;
				} );

				$value['booking']['timeslots']['groups'][ $i ]['slots'] = $slots;
			}
		}

		$this->cache_fully_booked_dates( $value );

		return $value;
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}

	public function frontend_props(): array {
		wp_enqueue_style( 'pikaday' );

		return [
			'booking_type' => $this->get_booking_type(),
			'quantity_per_slot' => [
				'enabled' => $this->is_quantity_enabled(),
			],
			'weekdays' => \Voxel\get_weekdays(),
			'weekdays_short' => \Voxel\get_weekdays_short(),
		];
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/booking/weekday-exclusions.php' );
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/booking/timeslots.php' );

		return $templates;
	}

	public function cache_fully_booked_dates( $config = null ): void {
		$post = $this->product_field->get_post();

		$fully_booked_dates = $this->get_fully_booked_dates( $config );
		if ( empty( $fully_booked_dates ) ) {
			delete_post_meta( $post->get_id(), $this->product_field->get_key().'__fully_booked' );
		} else {
			update_post_meta( $post->get_id(), $this->product_field->get_key().'__fully_booked', wp_slash( wp_json_encode( $fully_booked_dates ) ) );
		}

		if ( $this->get_booking_type() === 'timeslots' ) {
			$this->_mode_timeslots_cache_booked_slot_counts( $config );
		}
	}

	public function get_fully_booked_dates( $config = null ): array {
		$post = $this->product_field->get_post();
		$config = $config ?? $this->product_field->get_value();

		if ( $this->get_booking_type() === 'timeslots' ) {
			$dates = $this->_mode_timeslots_get_fully_booked_dates( $config );
		} else /* type === days */ {
			if ( $config['booking']['booking_mode'] === 'single_day' ) {
				$dates = $this->_mode_single_day_get_fully_booked_dates( $config );
			} else /* booking_mode === date_range */ {
				$dates = $this->_mode_date_range_get_fully_booked_dates( $config );
			}
		}

		// merge manually excluded days
		$dates = array_merge( $dates, $config['booking']['excluded_days'] ?? [] );

		// convert to ranges
		$ranges = \Voxel\merge_ranges( array_map( function( $day ) {
			$days = date_diff(
				\Voxel\epoch(),
				new \DateTime( $day, new \DateTimeZone('UTC') )
			)->days;

			return [ $days, $days ];
		}, $dates ) );

		// prepare value for storage
		$booked_ranges = array_map( function( $range ) {
			$start = strtotime( sprintf( '+%d days', $range[0] ), \Voxel\epoch()->getTimestamp() );
			$end = strtotime( sprintf( '+%d days', $range[1] ), \Voxel\epoch()->getTimestamp() );

			if ( $start === $end ) {
				return date( 'Y-m-d', $start );
			}

			return sprintf( '%s..%s', date( 'Y-m-d', $start ), date( 'Y-m-d', $end ) );
		}, $ranges );

		return $booked_ranges;
	}

	protected function _mode_date_range_get_fully_booked_dates( $config = null ): array {
		global $wpdb;

		$post = $this->product_field->get_post();
		$config = $config ?? $this->product_field->get_value();
		$quantity_per_slot = absint( $config['booking']['quantity_per_slot'] ?? 1 );

		$sql = $wpdb->prepare( <<<SQL
			SELECT
				COUNT(*) AS total_count,
				JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.start_date" ) ) AS start_date,
				JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.end_date" ) ) AS end_date,
				JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.count_mode" ) ) AS count_mode
			FROM {$wpdb->prefix}vx_order_items AS order_items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( order_items.order_id = orders.id )
			WHERE
				order_items.post_id = %d
				AND orders.status IN ('completed', 'sub_active')
			    AND order_items.product_type = %s
			    AND order_items.field_key = %s
				AND JSON_EXTRACT( order_items.details, "$.booking.type" ) = 'date_range'
			    AND JSON_EXTRACT( order_items.details, "$.booking.end_date" ) >= %s
				AND ( JSON_EXTRACT( order_items.details, "$.booking_status" ) IS NULL OR JSON_EXTRACT( order_items.details, "$.booking_status" ) != 'canceled' )
			GROUP BY start_date, end_date, count_mode
		SQL, $post->get_id(), $this->product_type->get_key(), $this->product_field->get_key(), date( 'Y-m-d', time() ) );

		$results = $wpdb->get_results( $sql );

		$fully_booked_days = [];
		$dates = [];
		foreach ( $results as $range ) {
			$total = absint( $range->total_count );
			$start_date = strtotime( $range->start_date );
			$end_date = strtotime( $range->end_date );
			$count_mode = $range->count_mode === 'nights' ? 'nights' : 'days';
			$now = time();
			if ( ! ( $start_date && $end_date && $start_date <= $end_date ) ) {
				continue;
			}

			$date = $start_date;
			do {
				if ( $date >= $now ) {
					$key = date( 'Y-m-d', $date );
					if ( ! isset( $dates[ $key ] ) ) {
						$dates[ $key ] = 0;
					}

					$dates[ $key ] += $total;

					if ( $dates[ $key ] >= $quantity_per_slot ) {
						$fully_booked_days[ $key ] = true;
					}
				}

				$date = strtotime( '+1 day', $date );
			} while ( $count_mode === 'nights' ? $date < $end_date : $date <= $end_date );
		}

		return array_keys( $fully_booked_days );
	}

	protected function _mode_single_day_get_fully_booked_dates( $config = null ): array {
		global $wpdb;

		$post = $this->product_field->get_post();
		$config = $config ?? $this->product_field->get_value();
		$quantity_per_slot = absint( $config['booking']['quantity_per_slot'] ?? 1 );

		$sql = $wpdb->prepare( <<<SQL
			SELECT
				COUNT(*) AS total_count,
				JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.date" ) ) AS booking_date
			FROM {$wpdb->prefix}vx_order_items AS order_items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( order_items.order_id = orders.id )
			WHERE
				order_items.post_id = %d
				AND orders.status IN ('completed', 'sub_active')
			    AND order_items.product_type = %s
			    AND order_items.field_key = %s
				AND JSON_EXTRACT( order_items.details, "$.booking.type" ) = 'single_day'
			    AND JSON_EXTRACT( order_items.details, "$.booking.date" ) >= %s
				AND ( JSON_EXTRACT( order_items.details, "$.booking_status" ) IS NULL OR JSON_EXTRACT( order_items.details, "$.booking_status" ) != 'canceled' )
			GROUP BY booking_date
			HAVING total_count >= %d
		SQL, $post->get_id(), $this->product_type->get_key(), $this->product_field->get_key(), date( 'Y-m-d', time() ), $quantity_per_slot );

		$results = $wpdb->get_results( $sql );

		$fully_booked_days = [];

		foreach ( $results as $result ) {
			$booking_date = strtotime( $result->booking_date );
			if ( $booking_date ) {
				$fully_booked_days[ date( 'Y-m-d', $booking_date ) ] = true;
			}
		}

		return array_keys( $fully_booked_days );
	}

	protected function _mode_timeslots_get_fully_booked_dates( $config = null ): array {
		global $wpdb;

		$post = $this->product_field->get_post();
		$config = $config ?? $this->product_field->get_value();
		$quantity_per_slot = absint( $config['booking']['quantity_per_slot'] ?? 1 );

		$sql = $wpdb->prepare( <<<SQL
			SELECT
				COUNT(*) AS total_count,
				JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.date" ) ) AS booking_date,
				CONCAT_WS(
					'-',
					JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.slot.from") ),
					JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.slot.to" ) )
				) AS timeslot
			FROM {$wpdb->prefix}vx_order_items AS order_items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( order_items.order_id = orders.id )
			WHERE
				order_items.post_id = %d
				AND orders.status IN ('completed', 'sub_active')
			    AND order_items.product_type = %s
			    AND order_items.field_key = %s
				AND JSON_EXTRACT( order_items.details, "$.booking.type" ) = 'timeslots'
			    AND JSON_EXTRACT( order_items.details, "$.booking.date" ) >= %s
				AND ( JSON_EXTRACT( order_items.details, "$.booking_status" ) IS NULL OR JSON_EXTRACT( order_items.details, "$.booking_status" ) != 'canceled' )
			GROUP BY booking_date, timeslot
			HAVING total_count >= %d
		SQL, $post->get_id(), $this->product_type->get_key(), $this->product_field->get_key(), date( 'Y-m-d', time() ), $quantity_per_slot );

		$results = $wpdb->get_results( $sql );

		$timeslots = $config['booking']['timeslots'] ?? [];
		$weekdays_lookup = array_flip( \Voxel\get_weekday_indexes() );
		$slots_by_day = [];

		foreach ( $timeslots['groups'] as $group ) {
			$prepared_slots = [];
			foreach ( $group['slots'] as $slot ) {
				$prepared_slots[ sprintf( '%s-%s', $slot['from'], $slot['to'] ) ] = false;
			}

			foreach ( $group['days'] as $day ) {
				if ( ! isset( $slots_by_day[ $day ] ) ) {
					$slots_by_day[ $day ] = $prepared_slots;
				}
			}
		}

		$dates = [];
		foreach ( $results as $result ) {
			$day_index = absint( date( 'N', strtotime( $result->booking_date ) ) ) - 1;
			if ( ! isset( $slots_by_day[ $weekdays_lookup[ $day_index ] ] ) ) {
				continue;
			}

			if ( ! isset( $dates[ $result->booking_date ] ) ) {
				$dates[ $result->booking_date ] = $slots_by_day[ $weekdays_lookup[ $day_index ] ];
			}

			if ( isset( $dates[ $result->booking_date ][ $result->timeslot ] ) ) {
				$dates[ $result->booking_date ][ $result->timeslot ] = true;
			}
		}

		// get all dates with all their slots fully booked
		return array_keys( array_filter( $dates, function( $slots ) {
			return ! in_array( false, $slots, true );
		} ) );
	}

	protected function _mode_timeslots_cache_booked_slot_counts( $config = null ): void {
		global $wpdb;

		$post = $this->product_field->get_post();
		$config = $config ?? $this->product_field->get_value();
		$quantity_per_slot = absint( $config['booking']['quantity_per_slot'] ?? 1 );

		// get all fully booked slots
		$sql = $wpdb->prepare( <<<SQL
			SELECT
				COUNT(*) AS total_count,
				JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.date" ) ) AS booking_date,
				CONCAT_WS(
					'-',
					JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.slot.from") ),
					JSON_UNQUOTE( JSON_EXTRACT( order_items.details, "$.booking.slot.to" ) )
				) AS timeslot
			FROM {$wpdb->prefix}vx_order_items AS order_items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( order_items.order_id = orders.id )
			WHERE
				order_items.post_id = %d
				AND orders.status IN ('completed', 'sub_active')
			    AND order_items.product_type = %s
			    AND order_items.field_key = %s
				AND JSON_EXTRACT( order_items.details, "$.booking.type" ) = 'timeslots'
			    AND JSON_EXTRACT( order_items.details, "$.booking.date" ) >= %s
				AND ( JSON_EXTRACT( order_items.details, "$.booking_status" ) IS NULL OR JSON_EXTRACT( order_items.details, "$.booking_status" ) != 'canceled' )
			GROUP BY booking_date, timeslot
			HAVING total_count >= 1
		SQL, $post->get_id(), $this->product_type->get_key(), $this->product_field->get_key(), date( 'Y-m-d', time() ) );

		$results = $wpdb->get_results( $sql );

		$slots = [];
		foreach ( $results as $result ) {
			if ( ! empty( $result->timeslot ) ) {
				$slots[ sprintf( '%s %s', $result->booking_date, $result->timeslot ) ] = absint( $result->total_count );
			}
		}

		if ( empty( $slots ) ) {
			delete_post_meta( $post->get_id(), $this->product_field->get_key().'__booked_slot_counts' );
		} else {
			update_post_meta( $post->get_id(), $this->product_field->get_key().'__booked_slot_counts', wp_slash( wp_json_encode( $slots ) ) );
		}
	}

	public function get_booked_slot_counts(): array {
		$post = $this->product_field->get_post();
		$dates = (array) json_decode( get_post_meta(
			$post->get_id(), $this->product_field->get_key().'__booked_slot_counts', true
		), true );

		return $dates;
	}

	public function get_excluded_days(): array {
		$post = $this->product_field->get_post();
		$config = $this->product_field->get_value();
		$excluded_days = $config['booking']['excluded_days_enabled'] ? $config['booking']['excluded_days'] : [];

		$fully_booked = (array) json_decode( get_post_meta(
			$post->get_id(), $this->product_field->get_key().'__fully_booked', true
		), true );

		if ( ! empty( $fully_booked ) ) {
			foreach ( $fully_booked as $booked_range ) {
				$parts = explode( '..', $booked_range );
				if ( ! strtotime( $parts[0] ?? '' ) ) {
					return null;
				}

				$start_day = new \DateTime( $parts[0], new \DateTimeZone('UTC') );
				$end_day = $start_day;
				if ( strtotime( $parts[1] ?? '' ) ) {
					$end_day = new \DateTime( $parts[1], new \DateTimeZone('UTC') );
				}

				while ( $start_day < $end_day ) {
					$excluded_days[] = $start_day->format('Y-m-d');
					$start_day->modify('+1 day');
				}

				$excluded_days[] = $end_day->format('Y-m-d');
			}
		}

		return array_values( array_unique( $excluded_days ) );
	}

	public function _get_weekday_linestring(): ?string {
		$config = $this->product_field->get_value();
		$indexes = \Voxel\get_weekday_indexes();

		if ( $this->get_booking_type() === 'days' ) {
			$ranges = [];
			for ( $i=0; $i <= 6; $i++ ) {
				$ranges[ $i ] = [ $i, $i ];
			}

			$excluded_weekdays = $config['booking']['excluded_weekdays'] ?? [];
			foreach ( $excluded_weekdays as $day ) {
				if ( isset( $indexes[ $day ] ) && isset( $ranges[ $indexes[ $day ] ] ) ) {
					unset( $ranges[ $indexes[ $day ] ] );
				}
			}
		} elseif ( $this->get_booking_type() === 'timeslots' ) {
			$ranges = [];

			foreach ( $config['booking']['timeslots']['groups'] as $group ) {
				if ( ! empty( $group['slots'] ) ) {
					foreach ( $group['days'] as $day ) {
						if ( isset( $indexes[ $day ] ) ) {
							$day_index = $indexes[ $day ];
							$ranges[ $day_index ] = [ $day_index, $day_index ];
						}
					}
				}
			}
		}

		$ranges = \Voxel\merge_ranges( $ranges );
		if ( empty( $ranges ) ) {
			return null;
		}

		$strings = array_map( function( $range ) {
			return sprintf( '(%s 0,%s 0)', $range[0], $range[1] );
		}, $ranges );

		return sprintf( 'MULTILINESTRING(%s)', join( ',', $strings ) );
	}

	public function _get_excluded_days_linestring(): ?string {
		$post = $this->product_field->get_post();

		$ranges = (array) json_decode( get_post_meta(
			$post->get_id(), $this->product_field->get_key().'__fully_booked', true
		), ARRAY_A );

		$strings = array_filter( array_map( function( $range ) {
			$parts = explode( '..', $range );
			if ( ! strtotime( $parts[0] ?? '' ) ) {
				return null;
			}

			$start_day = date_diff(
				\Voxel\epoch(),
				new \DateTime( $parts[0], new \DateTimeZone('UTC') )
			)->days;

			$end_day = $start_day;
			if ( strtotime( $parts[1] ?? '' ) ) {
				$end_day = date_diff(
					\Voxel\epoch(),
					new \DateTime( $parts[1], new \DateTimeZone('UTC') )
				)->days;
			}

			return sprintf( '(%s 0,%s 0)', $start_day / 1000, $end_day / 1000 );
		}, $ranges ) );

		if ( empty( $strings ) ) {
			return null;
		}

		return sprintf( 'MULTILINESTRING(%s)', join( ',', $strings ) );
	}
}
