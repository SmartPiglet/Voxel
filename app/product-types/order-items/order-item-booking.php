<?php

namespace Voxel\Product_Types\Order_Items;

use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Item_Booking extends Order_Item {
	use Traits\Addons_Trait;

	public function get_type(): string {
		return 'booking';
	}

	public function get_product_description() {
		$description = [];

		$booking_summary = $this->get_booking_summary();
		if ( ! empty( $booking_summary ) ) {
			$description[] = $booking_summary;
		}

		if ( $this->has_summary_item( 'addons' ) ) {
			$addon_summary = $this->get_addon_summary();
			if ( ! empty( $addon_summary ) ) {
				$description[] = $addon_summary;
			}
		}

		return join( ', ', $description );
	}

	public function get_booking_summary() {
		$details = $this->get_booking_details();
		if ( $details === null ) {
			return '';
		}

		if ( $details['type'] === 'date_range' ) {
			$start_stamp = strtotime( $details['start_date'] );
			$end_stamp = strtotime( $details['end_date'] );

			if ( $details['count_mode'] === 'nights' ) {
				$range_length = max( 1, abs( floor( ( $end_stamp - $start_stamp ) / DAY_IN_SECONDS ) ) );
			} else {
				$range_length = abs( floor( ( $end_stamp - $start_stamp ) / DAY_IN_SECONDS ) ) + 1;
			}

			$range_label = $details['count_mode'] === 'nights'
				? ( $range_length === 1 ? _x( 'One night', 'booking summary', 'voxel' ) : \Voxel\replace_vars( _x( '@count nights', 'booking summary', 'voxel' ), [
					'@count' => $range_length,
				] ) )
				: ( $range_length === 1 ? _x( 'One day', 'booking summary', 'voxel' ) : \Voxel\replace_vars( _x( '@count days', 'booking summary', 'voxel' ), [
					'@count' => $range_length,
				] ) );

			return \Voxel\replace_vars( _x( '@booking_length from @start_date to @end_date', 'booking summary', 'voxel' ), [
				'@booking_length' => $range_label,
				'@start_date' => \Voxel\date_format( strtotime( $details['start_date'] ) ),
				'@end_date' => \Voxel\date_format( strtotime( $details['end_date'] ) ),
			] );
		} elseif ( $details['type'] === 'single_day' ) {
			return \Voxel\date_format( strtotime( $details['date'] ) );
		} elseif ( $details['type'] === 'timeslots' ) {
			return \Voxel\date_format( strtotime( $details['date'] ) ) . ' ' . join( ' - ', [
				\Voxel\time_format( strtotime( $details['slot']['from'] ) ),
				\Voxel\time_format( strtotime( $details['slot']['to'] ) ),
			] );
		} else {
			return '';
		}
	}

	protected $_get_booking_details_cache;
	public function get_booking_details(): ?array {
		if ( $this->_get_booking_details_cache === null ) {
			$config = $this->get_details( 'booking' );
			$booking_type = $this->get_details( 'booking.type' );

			if ( $booking_type === 'date_range' ) {
				$schema = Schema::Object( [
					'type' => Schema::String()->default( 'date_range' ),
					'count_mode' => Schema::enum( [ 'days', 'nights' ] ),
					'start_date' => Schema::Date()->format('Y-m-d'),
					'end_date' => Schema::Date()->format('Y-m-d'),
				] );

				$schema->set_value( $config );
				$details = $schema->export();

				if ( is_null( $details['count_mode'] ) || is_null( $details['start_date'] ) || is_null( $details['end_date'] ) ) {
					return null;
				}

				$this->_get_booking_details_cache = $details;
			} elseif ( $booking_type === 'single_day' ) {
				$schema = Schema::Object( [
					'type' => Schema::String()->default( 'single_day' ),
					'date' => Schema::Date()->format('Y-m-d'),
				] );

				$schema->set_value( $config );
				$details = $schema->export();

				if ( is_null( $details['date'] ) ) {
					return null;
				}

				$this->_get_booking_details_cache = $details;
			} elseif ( $booking_type === 'timeslots' ) {
				$schema = Schema::Object( [
					'type' => Schema::String()->default( 'timeslots' ),
					'date' => Schema::Date()->format('Y-m-d'),
					'slot' => Schema::Object( [
						'from' => Schema::Date()->format('H:i'),
						'to' => Schema::Date()->format('H:i'),
					] ),
				] );

				$schema->set_value( $config );
				$details = $schema->export();

				if ( is_null( $details['date'] ) || is_null( $details['slot']['from'] ) || is_null( $details['slot']['to'] ) ) {
					return null;
				}

				$this->_get_booking_details_cache = $details;
			} else {
				return null;
			}
		}

		return $this->_get_booking_details_cache;
	}

	public function _get_booking_schedule_config(): ?array {
		$product_field = $this->get_product_field();
		if ( ! $product_field ) {
			return null;
		}

		$booking = $product_field->get_product_field('booking');
		if ( ! $booking ) {
			return null;
		}

		$config = $product_field->get_value();
		$product_type = $product_field->get_product_type();
		$booking_details = $this->get_booking_details();
		$now = ( new \DateTime( 'now', $product_field->get_post()->get_timezone() ) );
		$today = [
			'date' => $now->format('Y-m-d'),
			'time' => $now->format('H:i:s'),
		];

		$availability = [
			'max_days' => $config['booking']['availability']['max_days'],
			'buffer' => [
				'amount' => $config['booking']['availability']['buffer']['amount'],
				'unit' => $config['booking']['availability']['buffer']['unit'],
			],
		];

		if ( $product_type->config('modules.booking.type') === 'timeslots' && $booking_details['type'] === 'timeslots' ) {
			return [
				'today' => $today,
				'availability' => $availability,
				'excluded_days' => $booking->get_excluded_days(),
				'timeslots' => $config['booking']['timeslots'],
				'quantity_per_slot' => $config['booking']['quantity_per_slot'] ?? 1,
				'booked_slot_counts' => $booking->get_booked_slot_counts(),
			];
		} elseif ( $product_type->config('modules.booking.type') === 'days' && $booking_details['type'] === 'single_day' ) {
			return [
				'today' => $today,
				'availability' => $availability,
				'excluded_days' => $booking->get_excluded_days(),
				'excluded_weekdays' => $config['booking']['excluded_weekdays'],
			];
		} else {
			return null;
		}
	}

	public function reduce_stock() {
		if ( $this->get_details( 'stock.handled' ) ) {
			return;
		}

		$this->set_details( 'stock.handled', true );
		$this->save();

		$post = $this->get_post();
		$field = $this->get_product_field();
		if ( ! ( $post && $field ) ) {
			return;
		}

		$booking = $field->get_product_field('booking');
		if ( ! $booking ) {
			return;
		}

		$booking->cache_fully_booked_dates();

		$post = \Voxel\Post::force_get( $post->get_id() );
		$post->should_index() ? $post->index() : $post->unindex();
	}
}
