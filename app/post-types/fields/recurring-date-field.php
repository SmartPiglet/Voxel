<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Recurring_Date_Field extends Base_Post_Field {

	protected $props = [
		'type' => 'recurring-date',
		'label' => 'Recurring Date',
		'allow_multiple' => true,
		'max_date_count' => 3,
		'allow_recurrence' => true,
		'enable_timepicker' => true,
	];

	protected $_upcoming, $_all, $_previous;

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'description' => $this->get_description_model(),
			'allow_multiple' => [
				'type' => Form_Models\Switcher_Model::class,
				'label' => 'Enable multiple dates',
				'description' => 'Allow users to enter multiple dates',
				'classes' => 'x-col-12',
			],
			'max_date_count' => [
				'v-if' => 'field.allow_multiple',
				'type' => Form_Models\Number_Model::class,
				'label' => 'Maximum number of dates allowed',
				'classes' => 'x-col-12',
			],
			'allow_recurrence' => [
				'type' => Form_Models\Switcher_Model::class,
				'label' => 'Enable recurring dates',
				'description' => 'Allow users to repeat a date at regular intervals (e.g. every 2 weeks, every 6 months, etc.)',
				'classes' => 'x-col-12',
			],
			'enable_timepicker' => [
				'type' => Form_Models\Switcher_Model::class,
				'label' => 'Enable timepicker',
				'description' => 'Set whether users can also select the time of day when adding a date.',
				'classes' => 'x-col-12',
			],
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
		];
	}

	public function sanitize( $value ) {
		$sanitized = [];
		$allowed_units = ['day', 'week', 'month', 'year'];

		foreach ( (array) $value as $date ) {
			$start_date = strtotime( $date['startDate'] ?? '' );
			$end_date = strtotime( $date['endDate'] ?? '' );
			if ( ! $start_date ) {
				continue;
			}

			$is_multiday = !! ( $date['multiday'] ?? false );
			if ( ! ( $is_multiday && $end_date ) ) {
				$end_date = $start_date;
			}

			if ( $end_date < $start_date ) {
				$end_date = $start_date;
			}

			if ( $this->props['enable_timepicker'] ) {
				if ( !! ( $date['allday'] ?? false ) ) {
					$start_time = strtotime( '00:00' );
					$end_time = strtotime( '23:59:59' );
				} else {
					$start_time = strtotime( $date['startTime'] ?? '' ) ?: strtotime( '00:00' );
					$end_time = strtotime( $date['endTime'] ?? '' ) ?: strtotime( '00:00' );
				}

				$start_date += ( absint( date( 'H', $start_time ) ) * 3600 ) + ( absint( date( 'i', $start_time ) ) * 60 );
				$end_date += ( absint( date( 'H', $end_time ) ) * 3600 ) + ( absint( date( 'i', $end_time ) ) * 60 ) + absint( date( 's', $end_time ) );

				// if start time is later than end time within same day events, this just means
				// the event actually ends in the next day, e.g. 8pm to 3am
				if ( $end_date <= $start_date ) {
					$end_date += DAY_IN_SECONDS;
				}
			} else {
				$start_date = strtotime( date( 'Y-m-d 00:00:00', $start_date ) );
				$end_date = strtotime( date( 'Y-m-d 23:59:59', $end_date ) );
			}

			$is_recurring = false;
			if ( $this->props['allow_recurrence'] && ! empty( $date['repeat'] ) ) {
				$is_recurring = true;

				$unit = $date['unit'] ?? null;
				if ( ! in_array( $unit, $allowed_units, true ) ) {
					continue;
				}

				$frequency = absint( $date['frequency'] ?? 0 );
				$until = strtotime( $date['until'] ?? '' );
			}

			if ( $is_recurring ) {
				$sanitized[] = [
					'start' => date( 'Y-m-d H:i:s', $start_date ),
					'end' => date( 'Y-m-d H:i:s', $end_date ),
					'frequency' => $frequency,
					'unit' => $unit,
					'until' => $until ? date( 'Y-m-d', $until ) : null,
					'multiday' => !! ( $date['multiday'] ?? false ),
					'allday' => !! ( $date['allday'] ?? false ),
				];
			} else {
				$sanitized[] = [
					'start' => date( 'Y-m-d H:i:s', $start_date ),
					'end' => date( 'Y-m-d H:i:s', $end_date ),
					'multiday' => !! ( $date['multiday'] ?? false ),
					'allday' => !! ( $date['allday'] ?? false ),
				];
			}
		}

		if ( empty( $sanitized ) ) {
			return null;
		}

		return $sanitized;
	}

	public function validate( $value ): void {
		if ( ! $this->props['allow_multiple'] && count( $value ) > 1 ) {
			throw new \Exception(
				\Voxel\replace_vars( _x( 'Only one entry allowed in @field_name field', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
				] )
			);
		}

		if ( $this->props['allow_multiple'] && count( $value ) > $this->props['max_date_count'] ) {
			throw new \Exception(
				\Voxel\replace_vars( _x( 'Only up to @amount entries allowed in @field_name field', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@amount' => $this->props['max_date_count'],
				] )
			);
		}

		foreach ( $value as $date ) {
			if ( strtotime( $date['end'] ) < strtotime( $date['start'] ) ) {
				throw new \Exception(
					\Voxel\replace_vars( _x( '@field_name: End time cannot be set before start time.', 'field validation', 'voxel' ), [
						'@field_name' => $this->get_label(),
					] )
				);
			}

			if ( ! empty( $date['unit'] ) ) {
				if ( $date['frequency'] < 1 ) {
					throw new \Exception(
						\Voxel\replace_vars( _x( 'Date frequency is required for @field_name', 'field validation', 'voxel' ), [
							'@field_name' => $this->get_label(),
						] )
					);
				}

				if ( $date['until'] === null ) {
					throw new \Exception(
						\Voxel\replace_vars( _x( 'Repeat until date must be set for @field_name', 'field validation', 'voxel' ), [
							'@field_name' => $this->get_label(),
						] )
					);
				}
			}
		}
	}

	public function update( $value ): void {
		global $wpdb;
		if ( $this->is_empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
			$wpdb->delete( $wpdb->prefix.'voxel_recurring_dates', [
				'post_id' => $this->post->get_id(),
				'field_key' => $this->get_key(),
			] );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $value ) ) );

			// delete previous dates
			$wpdb->delete( $wpdb->prefix.'voxel_recurring_dates', [
				'post_id' => $this->post->get_id(),
				'field_key' => $this->get_key(),
			] );

			// prepare and insert new dates
			$rows = [];
			$reference_date = new \DateTime( '2020-01-01 00:00:00', $this->post->get_timezone() );
			$timezone = new \DateTimeZone( $reference_date->format('P') );

			foreach ( $value as $date ) {
				$start = new \DateTime( $date['start'], $timezone );
				$start->setTimezone( new \DateTimeZone('UTC') );

				$end = new \DateTime( $date['end'], $timezone );
				$end->setTimezone( new \DateTimeZone('UTC') );

				if ( isset( $date['frequency'] ) ) {
					if ( $date['unit'] === 'week' ) {
						$date['frequency'] *= 7;
						$date['unit'] = 'day';
					} elseif ( $date['unit'] === 'year' ) {
						$date['frequency'] *= 12;
						$date['unit'] = 'month';
					}
				}

				if ( isset( $date['until'] ) ) {
					$until = new \DateTime( $date['until'], $timezone );
					$until->setTimezone( new \DateTimeZone('UTC') );
				}

				$rows[] = sprintf(
					"(%d,'%s','%s','%s','%s',%s,'%s',%s)",
					absint( $this->post->get_id() ),
					esc_sql( $this->post->post_type->get_key() ),
					esc_sql( $this->get_key() ),
					esc_sql( $start->format( 'Y-m-d H:i:s' ) ),
					esc_sql( $end->format( 'Y-m-d H:i:s' ) ),
					isset( $date['frequency'] ) ? esc_sql( $date['frequency'] ) : 'NULL',
					isset( $date['unit'] ) ? esc_sql( $date['unit'] ) : 'NULL',
					isset( $until ) ? '\''.esc_sql( $until->format( 'Y-m-d H:i:s' ) ).'\'' : 'NULL'
				);
			}

			// update database with new values
			if ( ! empty( $rows ) ) {
				$query = "INSERT INTO {$wpdb->prefix}voxel_recurring_dates
					(`post_id`, `post_type`, `field_key`, `start`, `end`, `frequency`, `unit`, `until`) VALUES ";
				$query .= implode( ',', $rows );
				$wpdb->query( $query );
			}
		}
	}

	public function get_value_from_post() {
		return (array) json_decode( get_post_meta(
			$this->post->get_id(), $this->get_key(), true
		), ARRAY_A );
	}

	protected function editing_value() {
		return array_filter( array_map( function( $date ) {
			if ( ! isset( $date['start'], $date['end'] ) ) {
				return null;
			}

			return [
				'multiday' => !! ( $date['multiday'] ?? false ),
				'startDate' => date( 'Y-m-d', strtotime( $date['start'] ) ),
				'startTime' => date( 'H:i', strtotime( $date['start'] ) ),
				'endDate' => date( 'Y-m-d', strtotime( $date['end'] ) ),
				'endTime' => date( 'H:i', strtotime( $date['end'] ) ),
				'allday' => !! ( $date['allday'] ?? false ),
				'repeat' => ( $date['unit'] ?? null ) !== null,
				'frequency' => $date['frequency'] ?? 1,
				'unit' => $date['unit'] ?? 'week',
				'until' => strtotime( $date['until'] ?? '' ) ? date( 'Y-m-d', strtotime( $date['until'] ?? '' ) ) : null,
			];
		}, (array) $this->get_value() ) );
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}

	protected function frontend_props() {
		wp_enqueue_style( 'pikaday' );

		return [
			'max_date_count' => $this->props['allow_multiple'] && is_numeric( $this->props['max_date_count'] ) ? $this->props['max_date_count'] : 1,
			'allow_recurrence' => $this->props['allow_recurrence'],
			'enable_timepicker' => $this->props['enable_timepicker'],
			'units' => [
				'day' => _x( 'Day(s)', 'recurring date field', 'voxel' ),
				'week' => _x( 'Week(s)', 'recurring date field', 'voxel' ),
				'month' => _x( 'Month(s)', 'recurring date field', 'voxel' ),
				'year' => _x( 'Year(s)', 'recurring date field', 'voxel' ),
			],
		];
	}

	public function get_upcoming() {
		if ( $this->_upcoming === null ) {
			$this->_upcoming = \Voxel\Utils\Recurring_Date\get_upcoming(
				$this->get_value(),
				25,
				null,
				null,
				true
			);
		}

		return $this->_upcoming;
	}

	public function get_all() {
		if ( $this->_all === null ) {
			$this->_all = \Voxel\Utils\Recurring_Date\get_upcoming(
				$this->get_value(),
				50,
				null,
				\Voxel\epoch()
			);
		}

		return $this->_all;
	}

	public function get_previous() {
		if ( $this->_previous === null ) {
			$this->_previous = \Voxel\Utils\Recurring_Date\get_previous(
				$this->get_value(),
				25
			);
		}

		return $this->_previous;
	}

	public function dynamic_data() {
		return Tag::Object( $this->get_label() )->properties( function() {
			return [
				'upcoming' => Tag::Object_List('Upcoming')->items( function() {
					return $this->get_upcoming();
				} )->properties( function( $index, $item ) {
					return [
						'start' => Tag::Date('Start date')->render( function() use ( $item ) {
							return $item['start'] ?? null;
						} ),
						'end' => Tag::Date('End date')->render( function() use ( $item ) {
							return $item['end'] ?? null;
						} ),
						'is_multiday' => Tag::Bool('Is multi-day?')->render( function() use ( $item ) {
							return ( $item['multiday'] ?? false ) ? '1' : '';
						} ),
						'is_happening_now' => Tag::Bool('Is happening now?')->render( function() use ( $item ) {
							$start = $item['start'] ?? null;
							$end = $item['end'] ?? null;
							$now = current_time('timestamp');
							return ( $start && $end && ( strtotime( $start ) <= $now ) && ( strtotime( $end ) >= $now ) ) ? '1' : '';
						} ),
						'is_allday' => Tag::Bool('Is all-day?')->render( function() use ( $item ) {
							$start = $item['start'] ?? null;
							$end = $item['end'] ?? null;
							return (
								( $start && $end )
								&& date( 'H:i:s', strtotime( $start ) ) === '00:00:00'
								&& date( 'H:i:s', strtotime( $end ) ) === '23:59:59'
							) ? '1' : '';
						} ),
					];
				} ),
				'previous' => Tag::Object_List('Previous')->items( function() {
					return $this->get_previous();
				} )->properties( function( $index, $item ) {
					return [
						'start' => Tag::Date('Start date')->render( function() use ( $item ) {
							return $item['start'] ?? null;
						} ),
						'end' => Tag::Date('End date')->render( function() use ( $item ) {
							return $item['end'] ?? null;
						} ),
						'is_multiday' => Tag::Bool('Is multi-day?')->render( function() use ( $item ) {
							return ( $item['multiday'] ?? false ) ? '1' : '';
						} ),
						'is_allday' => Tag::Bool('Is all-day?')->render( function() use ( $item ) {
							$start = $item['start'] ?? null;
							$end = $item['end'] ?? null;
							return (
								( $start && $end )
								&& date( 'H:i:s', strtotime( $start ) ) === '00:00:00'
								&& date( 'H:i:s', strtotime( $end ) ) === '23:59:59'
							) ? '1' : '';
						} ),
					];
				} ),
				'all' => Tag::Object_List('All')->items( function() {
					return $this->get_all();
				} )->properties( function( $index, $item ) {
					return [
						'start' => Tag::Date('Start date')->render( function() use ( $item ) {
							return $item['start'] ?? null;
						} ),
						'end' => Tag::Date('End date')->render( function() use ( $item ) {
							return $item['end'] ?? null;
						} ),
						'is_multiday' => Tag::Bool('Is multi-day?')->render( function() use ( $item ) {
							return ( $item['multiday'] ?? false ) ? '1' : '';
						} ),
						'is_allday' => Tag::Bool('Is all-day?')->render( function() use ( $item ) {
							$start = $item['start'] ?? null;
							$end = $item['end'] ?? null;
							return (
								( $start && $end )
								&& date( 'H:i:s', strtotime( $start ) ) === '00:00:00'
								&& date( 'H:i:s', strtotime( $end ) ) === '23:59:59'
							) ? '1' : '';
						} ),
					];
				} ),
			];
		} );
	}
}
