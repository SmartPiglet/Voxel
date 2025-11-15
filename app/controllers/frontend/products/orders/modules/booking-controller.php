<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.single_order.bookings.cancel_booking', '@cancel_booking' );
		$this->on( 'voxel_ajax_products.single_order.bookings.reschedule_booking', '@reschedule_booking' );
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
		$this->filter( 'voxel/orders/view_order/item/components', '@register_booking_component', 10, 3 );
	}

	protected function cancel_booking() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			if ( ! ( $order_id && $order_item_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			if ( ! ( $current_user->is_customer_of( $order->get_id() ) || $current_user->is_vendor_of( $order->get_id() ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 111 );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! ( $order_item && $order_item->get_type() === 'booking' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 109 );
			}

			$product_type = $order_item->get_product_type();
			if ( ! $product_type ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 110 );
			}

			if ( ! (
				(
					$product_type->config('modules.booking.actions.cancel.customer.enabled')
					&& $current_user->is_customer_of( $order->get_id() )
				) || (
					$product_type->config('modules.booking.actions.cancel.vendor.enabled')
					&& $current_user->is_vendor_of( $order->get_id() )
				)
			) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 112 );
			}

			if ( $order_item->get_details( 'booking_status' ) === 'canceled' ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 113 );
			}

			$post = $order_item->get_post();
			$field = $order_item->get_product_field();
			if ( ! ( $field && $post ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 114 );
			}

			$booking = $field->get_product_field('booking');
			if ( ! $booking ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 115 );
			}

			$order_item->set_details( 'booking_status', 'canceled' );
			$order_item->save();

			$booking->cache_fully_booked_dates();

			$post = \Voxel\Post::force_get( $post->get_id() );
			$post->should_index() ? $post->index() : $post->unindex();

			if ( $current_user->is_vendor_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Canceled_By_Vendor_Event( $product_type ) )->dispatch( $order_item->get_id() );
			} elseif ( $current_user->is_customer_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Canceled_By_Customer_Event( $product_type ) )->dispatch( $order_item->get_id() );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function reschedule_booking() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			if ( ! ( $order_id && $order_item_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			if ( ! ( $current_user->is_customer_of( $order->get_id() ) || $current_user->is_vendor_of( $order->get_id() ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 111 );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! ( $order_item && $order_item->get_type() === 'booking' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 109 );
			}

			$product_type = $order_item->get_product_type();
			if ( ! $product_type ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 110 );
			}

			if ( ! (
				(
					$product_type->config('modules.booking.actions.reschedule.customer.enabled')
					&& $current_user->is_customer_of( $order->get_id() )
				) || (
					$product_type->config('modules.booking.actions.reschedule.vendor.enabled')
					&& $current_user->is_vendor_of( $order->get_id() )
				)
			) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 112 );
			}

			if ( $order_item->get_details( 'booking_status' ) === 'canceled' ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 113 );
			}

			$post = $order_item->get_post();
			$field = $order_item->get_product_field();
			if ( ! ( $field && $post ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 114 );
			}

			$booking = $field->get_product_field('booking');
			$form_booking = $field->get_form_field('form-booking');
			if ( ! ( $booking && $form_booking ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 115 );
			}

			if ( $order_item->get_details( 'booking.type' ) === 'timeslots' ) {
				$date = \DateTime::createFromFormat( 'Y-m-d', $_REQUEST['reschedule_to']['date'] ?? null );
				$from = \DateTime::createFromFormat( 'H:i', $_REQUEST['reschedule_to']['slot']['from'] ?? null );
				$to = \DateTime::createFromFormat( 'H:i', $_REQUEST['reschedule_to']['slot']['to'] ?? null );

				if ( $date === false || $from === false || $to === false ) {
					throw new \Exception( _x( 'Please select a date and time', 'reschedule booking', 'voxel' ), 116 );
				}

				$form_booking->validate_timeslot( $date, $from, $to );

				$order_item->set_details( 'booking.date', $date->format('Y-m-d') );
				$order_item->set_details( 'booking.slot.from', $from->format('H:i') );
				$order_item->set_details( 'booking.slot.to', $to->format('H:i') );
				$order_item->save();
			} else {
				$date = \DateTime::createFromFormat( 'Y-m-d', $_REQUEST['reschedule_to']['date'] ?? null );
				if ( $date === false ) {
					throw new \Exception( _x( 'Please select a date', 'reschedule booking', 'voxel' ), 117 );
				}

				$form_booking->validate_single_day( $date );

				$order_item->set_details( 'booking.date', $date->format('Y-m-d') );
				$order_item->save();
			}

			$booking->cache_fully_booked_dates();

			$post = \Voxel\Post::force_get( $post->get_id() );
			$post->should_index() ? $post->index() : $post->unindex();

			if ( $current_user->is_vendor_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Rescheduled_By_Vendor_Event( $product_type ) )->dispatch( $order_item->get_id() );
			} elseif ( $current_user->is_customer_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Rescheduled_By_Customer_Event( $product_type ) )->dispatch( $order_item->get_id() );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function order_updated( $order ) {
		if (
			$order->get_previous_status() === \Voxel\ORDER_PENDING_PAYMENT
			&& in_array( $order->get_status(), [ 'completed', 'pending_approval', 'sub_active' ], true )
		) {
			foreach ( $order->get_items() as $order_item ) {
				$product_type = $order_item->get_product_type();
				if ( $order_item->get_type() === 'booking' && $product_type !== null ) {
					( new \Voxel\Events\Bookings\Booking_Placed_Event( $product_type ) )->dispatch( $order_item->get_id() );
				}
			}
		}

		if (
			! in_array( $order->get_previous_status(), [ 'completed', 'sub_active' ], true )
			&& in_array( $order->get_status(), [ 'completed', 'sub_active' ], true )
		) {
			foreach ( $order->get_items() as $order_item ) {
				$product_type = $order_item->get_product_type();
				if ( $order_item->get_type() === 'booking' && $product_type !== null ) {
					( new \Voxel\Events\Bookings\Booking_Confirmed_Event( $product_type ) )->dispatch( $order_item->get_id() );
				}
			}
		}
	}

	protected function register_booking_component( $components, $order_item, $order ) {
		if ( $order_item->get_type() !== 'booking' ) {
			return $components;
		}

		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			return $components;
		}

		$details = $order_item->get_booking_details();
		if ( $details === null ) {
			return $components;
		}

		$details['summary'] = $order_item->get_booking_summary();
		$details['booking_status'] = $order_item->get_details( 'booking_status' );
		$details['schedule'] = $order_item->_get_booking_schedule_config();

		$details['actions'] = [
			'add_to_gcal' => [
				'enabled_for_customer' => false,
				'enabled_for_vendor' => false,
			],
			'add_to_ical' => [
				'enabled_for_customer' => false,
				'enabled_for_vendor' => false,
			],
			'cancel' => [
				'enabled_for_customer' => false,
				'enabled_for_vendor' => false,
			],
			'reschedule' => [
				'enabled_for_customer' => false,
				'enabled_for_vendor' => false,
			],
		];

		if ( ( $product_type = $order_item->get_product_type() ) && $details['booking_status'] !== 'canceled' ) {
			$actions = $product_type->config('modules.booking.actions');
			$post = $order_item->get_post();
			$location_field = $post ? $post->get_field('location') : null;
			$location = $location_field && $location_field->get_type() === 'location' ? ( $location_field->get_value()['address'] ?? '' ) : '';
			$timezone = $post ? $post->get_timezone()->getName() : wp_timezone()->getName();

			// add to gcal
			if ( $actions['add_to_gcal']['customer']['enabled'] || $actions['add_to_gcal']['vendor']['enabled'] ) {
				if ( $details['type'] === 'timeslots' ) {
					$link = \Voxel\Utils\Sharer::get_google_calendar_link( [
						'start' => $details['date'] . ' ' . $details['slot']['from'],
						'end' => $details['date'] . ' ' . $details['slot']['to'],
						'title' => $order_item->get_product_label(),
						'description' => $order_item->get_product_description(),
						'location' => $location,
						'timezone' => $timezone,
					] );
				} elseif ( $details['type'] === 'single_day' ) {
					$link = \Voxel\Utils\Sharer::get_google_calendar_link( [
						'start' => $details['date'],
						'end' => $details['date'],
						'title' => $order_item->get_product_label(),
						'description' => $order_item->get_product_description(),
						'location' => $location,
						'timezone' => $timezone,
					] );
				} elseif ( $details['type'] === 'date_range' ) {
					$link = \Voxel\Utils\Sharer::get_google_calendar_link( [
						'start' => $details['start_date'],
						'end' => $details['end_date'],
						'title' => $order_item->get_product_label(),
						'description' => $order_item->get_product_description(),
						'location' => $location,
						'timezone' => $timezone,
					] );
				} else {
					$link = null;
				}

				if ( $link ) {
					$details['actions']['add_to_gcal'] = [
						'enabled_for_customer' => $actions['add_to_gcal']['customer']['enabled'],
						'enabled_for_vendor' => $actions['add_to_gcal']['vendor']['enabled'],
						'link' => $link,
					];
				}
			}

			// add to ical
			if ( $actions['add_to_ical']['customer']['enabled'] || $actions['add_to_ical']['vendor']['enabled'] ) {
				if ( $details['type'] === 'timeslots' ) {
					$ics = \Voxel\Utils\Sharer::get_icalendar_data( [
						'start' => $details['date'] . ' ' . $details['slot']['from'],
						'end' => $details['date'] . ' ' . $details['slot']['to'],
						'title' => $order_item->get_product_label(),
						'description' => $order_item->get_product_description(),
						'location' => $location,
						'url' => $post ? $post->get_link() : '',
					] );
				} elseif ( $details['type'] === 'single_day' ) {
					$ics = \Voxel\Utils\Sharer::get_icalendar_data( [
						'start' => $details['date'],
						'end' => $details['date'],
						'title' => $order_item->get_product_label(),
						'description' => $order_item->get_product_description(),
						'location' => $location,
						'url' => $post ? $post->get_link() : '',
					] );
				} elseif ( $details['type'] === 'date_range' ) {
					$ics = \Voxel\Utils\Sharer::get_icalendar_data( [
						'start' => $details['start_date'],
						'end' => $details['end_date'],
						'title' => $order_item->get_product_label(),
						'description' => $order_item->get_product_description(),
						'location' => $location,
						'url' => $post ? $post->get_link() : '',
					] );
				} else {
					$ics = null;
				}

				if ( $ics ) {
					$link = sprintf( 'data:text/calendar;base64,%s', base64_encode( $ics ) );
					$details['actions']['add_to_ical'] = [
						'enabled_for_customer' => $actions['add_to_ical']['customer']['enabled'],
						'enabled_for_vendor' => $actions['add_to_ical']['vendor']['enabled'],
						'link' => $link,
						'filename' => $order_item->get_product_label() ?: 'event',
					];
				}
			}

			// cancel booking
			if ( $actions['cancel']['customer']['enabled'] || $actions['cancel']['vendor']['enabled'] ) {
				$details['actions']['cancel'] = [
					'enabled_for_customer' => $actions['cancel']['customer']['enabled'],
					'enabled_for_vendor' => $actions['cancel']['vendor']['enabled'],
				];
			}

			// reschedule booking
			if ( $actions['reschedule']['customer']['enabled'] || $actions['reschedule']['vendor']['enabled'] ) {
				$details['actions']['reschedule'] = [
					'enabled_for_customer' => $actions['reschedule']['customer']['enabled'],
					'enabled_for_vendor' => $actions['reschedule']['vendor']['enabled'],
				];
			}
		}

		$details['l10n'] = [
			'appt_canceled' => _x( 'Appointment was canceled', 'order booking details', 'voxel' ),
			'appt_confirmed' => _x( 'Appointment is confirmed', 'order booking details', 'voxel' ),
			'booking_canceled' => _x( 'Booking was canceled', 'order booking details', 'voxel' ),
			'booking_confirmed' => _x( 'Booking is confirmed', 'order booking details', 'voxel' ),
			'add_to_gcal' => _x( 'Google Cal', 'order booking details', 'voxel' ),
			'add_to_ical' => _x( 'iCal', 'order booking details', 'voxel' ),
			'cancel_booking' => _x( 'Cancel booking', 'order booking details', 'voxel' ),
			'cancel_reschedule' => _x( 'Cancel', 'order booking reschedule', 'voxel' ),
			'reschedule' => _x( 'Reschedule', 'order booking reschedule', 'voxel' ),
			'time' => _x( 'Time', 'order booking reschedule', 'voxel' ),
			'pick_time' => _x( 'Pick a time', 'order booking reschedule', 'voxel' ),
		];

		$components[] = [
			'type' => 'order-item-booking-details',
			'src' => \Voxel\get_esm_src('order-item-booking-details.js'),
			'data' => $details,
		];

		return $components;
	}
}
