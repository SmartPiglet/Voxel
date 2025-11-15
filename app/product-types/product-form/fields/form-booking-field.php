<?php

namespace Voxel\Product_Types\Product_Form\Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Form_Booking_Field extends Base_Field {

	protected $props = [
		'key' => 'form-booking',
		'label' => 'Booking',
	];

	public function get_conditions(): array {
		return [
			'settings.product_mode' => 'booking',
			'modules.booking.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$value = $this->product_field->get_value();

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			$booking_schema = Schema::Object( [
				'date' => Schema::Date()->format('Y-m-d'),
				'slot' => Schema::Object( [
					'from' => Schema::Date()->format('H:i'),
					'to' => Schema::Date()->format('H:i'),
				] ),
			] );
		} else /* type === days */ {
			if ( $value['booking']['booking_mode'] === 'single_day' ) {
				$booking_schema = Schema::Object( [
					'date' => Schema::Date()->format('Y-m-d'),
				] );
			} else /* booking_mode === date_range */ {
				$booking_schema = Schema::Object( [
					'start_date' => Schema::Date()->format('Y-m-d'),
					'end_date' => Schema::Date()->format('Y-m-d'),
				] );
			}
		}

		$schema->set_prop( 'booking', $booking_schema );
	}

	public function validate( $value ) {
		$config = $this->product_field->get_value();

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			$date = \DateTime::createFromFormat( 'Y-m-d', $value['booking']['date'] ?? '' );
			$from = \DateTime::createFromFormat( 'H:i', $value['booking']['slot']['from'] ?? '' );
			$to = \DateTime::createFromFormat( 'H:i', $value['booking']['slot']['to'] ?? '' );

			if ( $date === false || $from === false || $to === false ) {
				throw new \Exception( _x( 'Please select a date and time', 'product form', 'voxel' ) );
			}

			$this->validate_timeslot( $date, $from, $to );
		} else /* type === days */ {
			if ( $config['booking']['booking_mode'] === 'single_day' ) {
				$date = \DateTime::createFromFormat( 'Y-m-d', $value['booking']['date'] );

				if ( $date === false ) {
					throw new \Exception( _x( 'Please select a date', 'product form', 'voxel' ) );
				}

				$this->validate_single_day( $date );
			} else /* booking_mode === date_range */ {
				$start_date = \DateTime::createFromFormat( 'Y-m-d', $value['booking']['start_date'] ?? '' );
				$end_date = \DateTime::createFromFormat( 'Y-m-d', $value['booking']['end_date'] ?? '' );

				if ( $start_date === false || $end_date === false || $end_date < $start_date ) {
					throw new \Exception( _x( 'Please select a period', 'product form', 'voxel' ) );
				}

				$this->validate_date_range( $start_date, $end_date );
			}
		}
	}

	protected function _get_min_date(): \DateTime {
		$config = $this->product_field->get_value();
		$min_date = ( new \DateTime( 'now', $this->product_field->get_post()->get_timezone() ) );
		$buffer_amount = $config['booking']['availability']['buffer']['amount'];
		$buffer_unit = $config['booking']['availability']['buffer']['unit'];
		$buffer_in_seconds = $buffer_amount * ( $buffer_unit === 'hours' ? HOUR_IN_SECONDS : DAY_IN_SECONDS );

		$min_date->modify( sprintf( '+%d seconds', $buffer_in_seconds ) );

		if ( $buffer_in_seconds >= DAY_IN_SECONDS ) {
			$min_date->setTime(0, 0, 0);
		}

		return $min_date;
	}

	protected function _get_max_date(): \DateTime {
		$config = $this->product_field->get_value();
		$max_date = ( new \DateTime( 'now', $this->product_field->get_post()->get_timezone() ) );

		$max_date->modify( sprintf( '+%d days', $config['booking']['availability']['max_days'] - 1 ) );

		$min_date = $this->_get_min_date();
		if ( $min_date > $max_date ) {
			return $min_date;
		}

		return $max_date;
	}

	public function validate_timeslot( \DateTime $date, \DateTime $from, \DateTime $to ): void {
		$min_date = $this->_get_min_date();
		$max_date = $this->_get_max_date();

		// date+slot must be greater than now+buffer
		if ( strtotime( sprintf( '%s %s', $date->format('Y-m-d'), $from->format( 'H:i:00' ) ) ) < strtotime( $min_date->format( 'Y-m-d H:i:s' ) ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 60 );
		}

		// date must be less than now+max_days
		if ( strtotime( $date->format( 'Y-m-d 00:00:00' ) ) > strtotime( $max_date->format( 'Y-m-d 23:59:59' ) ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 61 );
		}

		$config = $this->product_field->get_value();
		$booking = $this->product_field->get_product_field('booking');

		if ( in_array( $date->format('Y-m-d'), $booking->get_excluded_days(), true ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 62 );
		}

		$slot_key = sprintf( '%s %s-%s', $date->format('Y-m-d'), $from->format('H:i'), $to->format('H:i') );
		$quantity_per_slot = absint( $config['booking']['quantity_per_slot'] ?? 1 );
		if ( ( $booking->get_booked_slot_counts()[ $slot_key ] ?? 0 ) >= $quantity_per_slot ) {
			throw new \Exception( _x( 'This time is fully booked.', 'product form', 'voxel' ), 63 );
		}

		$weekdays_lookup = array_flip( \Voxel\get_weekday_indexes() );
		$day_index = absint( $date->format('N') ) - 1;
		$weekday = $weekdays_lookup[ $day_index ];

		$slot_exists = false;
		foreach ( $config['booking']['timeslots']['groups'] as $slot_group ) {
			if ( in_array( $weekday, $slot_group['days'], true ) ) {
				foreach ( $slot_group['slots'] as $slot ) {
					if ( $slot['from'] === $from->format('H:i') && $slot['to'] === $to->format('H:i') ) {
						$slot_exists = true;
						break(2);
					}
				}
			}
		}

		if ( ! $slot_exists ) {
			throw new \Exception( _x( 'This time is not available.', 'product form', 'voxel' ), 64 );
		}
	}

	public function validate_single_day( \DateTime $date ): void {
		$min_date = $this->_get_min_date();
		$max_date = $this->_get_max_date();

		// date must be greater than now+buffer
		if ( strtotime( $date->format( 'Y-m-d 00:00:00' ) ) < strtotime( $min_date->format( 'Y-m-d 00:00:00' ) ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 70 );
		}

		// date must be less than now+max_days
		if ( strtotime( $date->format( 'Y-m-d 00:00:00' ) ) > strtotime( $max_date->format( 'Y-m-d 23:59:59' ) ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 71 );
		}

		$config = $this->product_field->get_value();
		$booking = $this->product_field->get_product_field('booking');

		if ( in_array( $date->format('Y-m-d'), $booking->get_excluded_days(), true ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 72 );
		}

		$weekdays_lookup = array_flip( \Voxel\get_weekday_indexes() );
		$day_index = absint( $date->format('N') ) - 1;
		$weekday = $weekdays_lookup[ $day_index ];
		if ( in_array( $weekday, $config['booking']['excluded_weekdays'], true ) ) {
			throw new \Exception( _x( 'This date is not available.', 'product form', 'voxel' ), 73 );
		}
	}

	public function validate_date_range( \DateTime $start_date, \DateTime $end_date ): void {
		$min_date = $this->_get_min_date();
		$max_date = $this->_get_max_date();
		$config = $this->product_field->get_value();
		$count_mode = $this->product_type->config('modules.booking.date_ranges.count_mode');

		// start_date must be greater than now+buffer
		if ( strtotime( $start_date->format( 'Y-m-d 00:00:00' ) ) < strtotime( $min_date->format( 'Y-m-d 00:00:00' ) ) ) {
			throw new \Exception( _x( 'This period is not available.', 'product form', 'voxel' ), 80 );
		}

		// end date must be less than now+max_days
		if ( strtotime( $end_date->format( 'Y-m-d 00:00:00' ) ) > strtotime( $max_date->format( 'Y-m-d 23:59:59' ) ) ) {
			throw new \Exception( _x( 'This period is not available.', 'product form', 'voxel' ), 81 );
		}

		// validate range length
		if ( $count_mode === 'nights' ) {
			$range_length = max( 1, abs( floor( ( $end_date->getTimestamp() - $start_date->getTimestamp() ) / DAY_IN_SECONDS ) ) );
		} else {
			$range_length = abs( floor( ( $end_date->getTimestamp() - $start_date->getTimestamp() ) / DAY_IN_SECONDS ) ) + 1;
		}

		$min_range_length = 1;
		$max_range_length = 100;
		if ( $config['booking']['date_range']['set_custom_limits'] ) {
			$min_range_length = $config['booking']['date_range']['min_length'];
			$max_range_length = $config['booking']['date_range']['max_length'];
		}

		if ( $range_length < $min_range_length || $range_length > $max_range_length ) {
			throw new \Exception( _x( 'This period is not available.', 'product form', 'voxel' ), 84 );
		}

		// validate period
		$booking = $this->product_field->get_product_field('booking');
		$excluded_days = $booking->get_excluded_days();
		$weekdays_lookup = array_flip( \Voxel\get_weekday_indexes() );

		$start = clone $start_date;
		$end = clone $end_date;

		while ( $count_mode === 'nights' ? $start < $end : $start <= $end ) {
			if ( in_array( $start->format('Y-m-d'), $excluded_days, true ) ) {
				throw new \Exception( _x( 'This period is not available.', 'product form', 'voxel' ), 82 );
			}

			$day_index = absint( $start->format('N') ) - 1;
			$weekday = $weekdays_lookup[ $day_index ];
			if ( in_array( $weekday, $config['booking']['excluded_weekdays'], true ) ) {
				throw new \Exception( _x( 'This period is not available.', 'product form', 'voxel' ), 83 );
			}

			$start->modify('+1 day');
		}
	}

	public function get_selected_range_length( $value ): int {
		$config = $this->product_field->get_value();

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			return 1;
		}

		if ( $config['booking']['booking_mode'] === 'single_day' ) {
			return 1;
		}

		$count_mode = $this->product_type->config('modules.booking.date_ranges.count_mode');
		$start_stamp = strtotime( $value['booking']['start_date'] );
		$end_stamp = strtotime( $value['booking']['end_date'] );

		if ( $count_mode === 'nights' ) {
			return max( 1, abs( floor( ( $end_stamp - $start_stamp ) / DAY_IN_SECONDS ) ) );
		} else {
			return abs( floor( ( $end_stamp - $start_stamp ) / DAY_IN_SECONDS ) ) + 1;
		}
	}

	public function get_repeat_config( $addon, $value ) {
		if ( ! $addon->repeat_in_booking_range() ) {
			return null;
		}

		$booking = $this->product_field->get_form_field( 'form-booking' );
		if ( ! $booking ) {
			return null;
		}

		$config = $this->product_field->get_value();
		if ( ! ( $this->product_type->config('modules.booking.type') === 'days' && $config['booking']['booking_mode'] === 'date_range' ) ) {
			return null;
		}

		$length = $booking->get_selected_range_length( $value );
		if ( $length < 1 ) {
			return null;
		}

		$count_mode = $this->product_type->config('modules.booking.date_ranges.count_mode');

		return [
			'length' => $length,
			'mode' => $count_mode,
			'start' => $value['booking']['start_date'],
			'end' => $value['booking']['end_date'],
		];
	}

	public function get_base_price_amount( $value ) {
		$base_price = $this->product_field->get_product_field('base-price');
		if ( ! $base_price ) {
			return null;
		}

		$config = $this->product_field->get_value();

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			$amount = 0;
			$date = new \DateTime( $value['booking']['date'], new \DateTimeZone('UTC') );
			$custom_price = $this->product_field->get_custom_price_for_date( $date );
			if ( $custom_price ) {
				$amount += $custom_price['prices']['base_price']['discount_amount'] ?? $custom_price['prices']['base_price']['amount'];
			} else {
				$amount += $config['base_price']['discount_amount'] ?? $config['base_price']['amount'];
			}

			return $amount;
		} else /* type === days */ {
			if ( $config['booking']['booking_mode'] === 'single_day' ) {
				$amount = 0;
				$date = new \DateTime( $value['booking']['date'], new \DateTimeZone('UTC') );
				$custom_price = $this->product_field->get_custom_price_for_date( $date );
				if ( $custom_price ) {
					$amount += $custom_price['prices']['base_price']['discount_amount'] ?? $custom_price['prices']['base_price']['amount'];
				} else {
					$amount += $config['base_price']['discount_amount'] ?? $config['base_price']['amount'];
				}

				return $amount;
			} else /* booking_mode === date_range */ {
				$count_mode = $this->product_type->config('modules.booking.date_ranges.count_mode');
				$range_length = $this->get_selected_range_length( $value );
				$amount = 0;

				$start = new \DateTime( $value['booking']['start_date'], new \DateTimeZone('UTC') );
				$end = new \DateTime( $value['booking']['end_date'], new \DateTimeZone('UTC') );

				while ( $count_mode === 'nights' ? $start < $end : $start <= $end ) {
					$custom_price = $this->product_field->get_custom_price_for_date( $start );
					if ( $custom_price ) {
						$amount += $custom_price['prices']['base_price']['discount_amount'] ?? $custom_price['prices']['base_price']['amount'];
					} else {
						$amount += $config['base_price']['discount_amount'] ?? $config['base_price']['amount'];
					}

					$start->modify('+1 day');
				}

				return $amount;
			}
		}
	}

	public function get_selection_summary( $value ) {
		$config = $this->product_field->get_value();

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			return \Voxel\date_format( strtotime( $value['booking']['date'] ) ) . ' ' . join( ' - ', [
				\Voxel\time_format( strtotime( $value['booking']['slot']['from'] ) ),
				\Voxel\time_format( strtotime( $value['booking']['slot']['to'] ) ),
			] );
		} else /* type === days */ {
			if ( $config['booking']['booking_mode'] === 'single_day' ) {
				return \Voxel\date_format( strtotime( $value['booking']['date'] ) );
			} else /* booking_mode === date_range */ {
				$range_length = $this->get_selected_range_length( $value );
				$count_mode = $this->product_type->config('modules.booking.date_ranges.count_mode');
				$range_label = $count_mode === 'nights'
					? ( $range_length === 1 ? _x( 'One night', 'product form booking', 'voxel' ) : \Voxel\replace_vars( _x( '@count nights', 'product form booking', 'voxel' ), [
						'@count' => $range_length,
					] ) )
					: ( $range_length === 1 ? _x( 'One day', 'product form booking', 'voxel' ) : \Voxel\replace_vars( _x( '@count days', 'product form booking', 'voxel' ), [
						'@count' => $range_length,
					] ) );

				return \Voxel\replace_vars( _x( '@booking_length from @start_date to @end_date', 'booking summary', 'voxel' ), [
					'@booking_length' => $range_label,
					'@start_date' => \Voxel\date_format( strtotime( $value['booking']['start_date'] ) ),
					'@end_date' => \Voxel\date_format( strtotime( $value['booking']['end_date'] ) ),
				] );
			}
		}
	}

	public function frontend_props(): array {
		wp_enqueue_style('pikaday');
		wp_enqueue_script('pikaday');

		$value = $this->product_field->get_value();
		$booking = $this->product_field->get_product_field('booking');

		$now = ( new \DateTime( 'now', $this->product_field->get_post()->get_timezone() ) );
		$today = [
			'date' => $now->format('Y-m-d'),
			'time' => $now->format('H:i:s'),
		];

		$availability = [
			'max_days' => $value['booking']['availability']['max_days'],
			'buffer' => [
				'amount' => $value['booking']['availability']['buffer']['amount'],
				'unit' => $value['booking']['availability']['buffer']['unit'],
			],
		];

		$l10n = [
			'select_nights' => _x( 'Select nights', 'product form booking', 'voxel' ),
			'select_days' => _x( 'Select days', 'product form booking', 'voxel' ),
			'select_end_date' => _x( 'Select end date', 'product form booking', 'voxel' ),
			'one_night' => _x( 'One night', 'product form booking', 'voxel' ),
			'one_day' => _x( 'One day', 'product form booking', 'voxel' ),
			'multiple_nights' => _x( '@count nights', 'product form booking', 'voxel' ),
			'multiple_days' => _x( '@count days', 'product form booking', 'voxel' ),
			'nights_range_error' => _x( 'A minimum of @minlength and maximum of @maxlength nights is required', 'product form booking', 'voxel' ),
			'days_range_error' => _x( 'A minimum of @minlength and maximum of @maxlength days is required', 'product form booking', 'voxel' ),
			'select_start_and_end_date' => _x( 'Select start and end dates', 'product form booking', 'voxel' ),
			'amount_available' => _x( '@count available', 'product form booking', 'voxel' ),
			'booking_price' => _x( 'Price', 'product form booking', 'voxel' ),
		];

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			return [
				'mode' => 'timeslots',
				'today' => $today,
				'availability' => $availability,
				'excluded_days' => $booking->get_excluded_days(),
				'timeslots' => $value['booking']['timeslots'],
				'quantity_per_slot' => $value['booking']['quantity_per_slot'] ?? 1,
				'booked_slot_counts' => $booking->get_booked_slot_counts(),
				'l10n' => $l10n,
			];
		} else /* type === days */ {
			if ( $value['booking']['booking_mode'] === 'single_day' ) {
				return [
					'mode' => 'single_day',
					'today' => $today,
					'availability' => $availability,
					'excluded_days' => $booking->get_excluded_days(),
					'excluded_weekdays' => $value['booking']['excluded_weekdays'],
					'l10n' => $l10n,
				];
			} else /* booking_mode === date_range */ {
				$has_custom_range_limits = $value['booking']['date_range']['set_custom_limits'];
				if ( $has_custom_range_limits ) {
					$min_length = $value['booking']['date_range']['min_length'];
					$max_length = $value['booking']['date_range']['max_length'];
				} else {
					$min_length = 1;
					$max_length = 100;
				}

				return [
					'mode' => 'date_range',
					'today' => $today,
					'count_mode' => $this->product_type->config('modules.booking.date_ranges.count_mode'),
					'availability' => $availability,
					'excluded_days' => $booking->get_excluded_days(),
					'excluded_weekdays' => $value['booking']['excluded_weekdays'],
					'date_range' => [
						'has_custom_limits' => $has_custom_range_limits,
						'min_length' => $min_length,
						'max_length' => $max_length,
					],
					'l10n' => $l10n,
				];
			}
		}
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/product-form/form-booking.php' );

		return $templates;
	}
}
