<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Availability_Filter extends Base_Filter {

	protected $props = [
		'type' => 'availability',
		'label' => 'Availability',
		'source' => '',
		'input_mode' => 'date-range',
		'range_matching' => 'lazy',
		'l10n_checkin' => 'Check-in',
		'l10n_checkout' => 'Check-out',
		'l10n_pickdate' => 'Choose date',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'source' => $this->get_source_model( 'product' ),
			'input_mode' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Input mode',
				'classes' => 'x-col-6',
				'choices' => [
					'date-range' => 'Date range',
					'single-date' => 'Single date',
				],
			],
			'range_matching' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'v-if' => 'filter.input_mode === \'date-range\'',
				'label' => 'Date range matching',
				'classes' => 'x-col-6',
				'choices' => [
					'lazy' => 'Lazy: Match posts having at least one available day in given range',
					'greedy' => 'Greedy: Match posts having every day available in given range',
				],
			],
			'l10n_checkin' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Check-in label',
				'classes' => 'x-col-6',
			],
			'l10n_checkout' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Check-out label',
				'classes' => 'x-col-6',
			],
			'l10n_pickdate' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Placeholder',
				'classes' => 'x-col-12',
			],
			'description' => $this->get_description_model(),
			'icon' => $this->get_icon_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$srid = ! \Voxel\is_using_mariadb() ? 'SRID 0' : '';
		$table->add_column( sprintf( '`%s` MULTILINESTRING NOT NULL %s', esc_sql( $this->db_key() ), $srid ) );
		$table->add_key( sprintf( 'SPATIAL KEY(`%s`)', esc_sql( $this->db_key() ) ) );

		// wd => weekdays
		$table->add_column( sprintf( '`%s` MULTILINESTRING NOT NULL %s', esc_sql( $this->db_key().'__wd' ), $srid ) );
		$table->add_key( sprintf( 'SPATIAL KEY(`%s`)', esc_sql( $this->db_key().'__wd' ) ) );

		// make_available_next
		$table->add_column( sprintf( '`%s` SMALLINT UNSIGNED NOT NULL DEFAULT 0', esc_sql( $this->db_key().'__next' ) ) );
		$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->db_key().'__next' ) ) );

		// buffer time
		$table->add_column( sprintf( '`%s` SMALLINT UNSIGNED NOT NULL DEFAULT 0', esc_sql( $this->db_key().'__buffer' ) ) );
		$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->db_key().'__buffer' ) ) );
	}

	public function index( \Voxel\Post $post ): array {
		$weekdays = 'MULTILINESTRING((0 0,6 0))';
		$excluded_days = 'MULTILINESTRING((-0.1 0,-0.1 0))';
		$available_next = 0;
		$buffer = 0;

		$field = $post->get_field( $this->props['source'] );
		if ( $field && $field->get_type() === 'product' && ! empty( $field->get_supported_product_types() ) ) {
			$config = $field->get_value();
			$booking = $field->get_product_field( 'booking' );
			if ( $config['enabled'] && $booking ) {
				$_weekdays = $booking->_get_weekday_linestring();
				if ( $_weekdays !== null ) {
					$weekdays = $_weekdays;
				}

				$_excluded_days = $booking->_get_excluded_days_linestring();
				if ( $_excluded_days !== null ) {
					$excluded_days = $_excluded_days;
				}

				$available_next = absint( $config['booking']['availability']['max_days'] ?? 0 );

				$buffer = absint( $config['booking']['availability']['buffer']['amount'] ?? 0 );
				if ( ( $config['booking']['availability']['buffer']['unit'] ?? 'days' ) === 'hours' && $buffer > 0 ) {
					$buffer = absint( floor( $buffer / 24 ) );
				}
			}
		}

		return [
			$this->db_key() => sprintf( 'ST_GeomFromText( \'%s\', 0 )', $excluded_days ),
			$this->db_key().'__wd' => sprintf( 'ST_GeomFromText( \'%s\', 0 )', $weekdays ),
			$this->db_key().'__next' => $available_next,
			$this->db_key().'__buffer' => $buffer,
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		// preset
		if ( is_string( $value ) ) {
			$preset = \Voxel\get_range_presets( $value );
			if ( ! $preset ) {
				return;
			}

			$value = $preset['callback']( \Voxel\now() );
			if ( ! ( strtotime( $value['start'] ?? '' ) && strtotime( $value['end'] ?? '' ) ) ) {
				return;
			}
		}

		global $wpdb;

		// query against main column
		$start_day = date_diff( \Voxel\epoch(), new \DateTime( $value['start'], new \DateTimeZone('UTC') ) )->days;
		$end_day = date_diff( \Voxel\epoch(), new \DateTime( $value['end'], new \DateTimeZone('UTC') ) )->days;
		$linestring = sprintf( 'LINESTRING(%s 0,%s 0)', $start_day / 1000, $end_day / 1000 );

		$query->where( sprintf( '`%s` > 0', esc_sql( $this->db_key() ).'__next' ) );

		if ( $this->props['input_mode'] === 'date-range' && $this->props['range_matching'] === 'greedy' ) {
			$query->where( sprintf(
				'( ST_Disjoint( ST_GeomFromText( \'%s\', 0 ), `%s` ) )',
				$linestring,
				esc_sql( $this->db_key() )
			) );
		} else {
			$query->where( sprintf(
				'( ST_Disjoint( ST_GeomFromText( \'%s\', 0 ), `%s` ) OR ST_Overlaps( ST_GeomFromText( \'%s\', 0 ), `%s` ) )',
				$linestring,
				esc_sql( $this->db_key() ),
				$linestring,
				esc_sql( $this->db_key() )
			) );
		}

		// query against weekday exceptions
		$indexes = [];
		$date = strtotime( $value['start'] );
		$end = strtotime( $value['end'] );
		do {
			$index = absint( date( 'N', $date ) ) - 1;
			$indexes[] = [ $index, $index ];
			if ( count( $indexes ) >= 7 ) {
				break;
			}

			$date = strtotime( '+1 day', $date );
		} while ( $date <= $end );

		$indexes = \Voxel\merge_ranges( $indexes );

		if ( count( $indexes ) === 1 ) {
			$linestring = sprintf( 'LINESTRING(%s 0,%s 0)', $indexes[0][0], $indexes[0][1] );
		} else {
			$strings = array_map( function( $range ) {
				return sprintf( '(%s 0,%s 0)', $range[0], $range[1] );
			}, $indexes );
			$linestring = sprintf( 'MULTILINESTRING(%s)', join( ',', $strings ) );
		}

		if ( $this->props['input_mode'] === 'date-range' && $this->props['range_matching'] === 'greedy' ) {
			$query->where( sprintf(
				'( ST_Within( ST_GeomFromText( \'%s\', 0 ), `%s` ) )',
				$linestring,
				esc_sql( $this->db_key() ).'__wd'
			) );
		} else {
			$query->where( sprintf(
				'( ST_Intersects( ST_GeomFromText( \'%s\', 0 ), `%s` ) )',
				$linestring,
				esc_sql( $this->db_key() ).'__wd'
			) );
		}

		$query->where( sprintf(
			'DATE_ADD( \'%s\', INTERVAL `%s` DAY ) >= \'%s\'',
			esc_sql( date('Y-m-d') ),
			esc_sql( $this->db_key() ).'__next',
			esc_sql( $value['end'] )
		) );

		$query->where( sprintf(
			' ( `%s` = 0 OR DATE_ADD( \'%s\', INTERVAL `%s` DAY ) <= \'%s\' ) ',
			esc_sql( $this->db_key() ).'__buffer',
			esc_sql( date('Y-m-d') ),
			esc_sql( $this->db_key() ).'__buffer',
			esc_sql( $value['start'] )
		) );

		$GLOBALS['_availability_start_date'] = new \DateTime( date( 'Y-m-d 00:00:00', strtotime( $value['start'] ) ) );
	}

	public function parse_value( $value ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}

		if ( $this->props['input_mode'] === 'date-range' && \Voxel\get_range_presets( $value ) ) {
			return $value;
		}

		$parts = explode( '..', $value );
		$start_stamp = strtotime( $parts[0] );
		$end_stamp = strtotime( $parts[1] ?? '' );
		if ( ! $end_stamp || $this->props['input_mode'] === 'single-date' ) {
			$end_stamp = $start_stamp;
		}

		if ( ! ( $start_stamp && $end_stamp ) ) {
			return null;
		}

		// make sure start stamp is always lower than end stamp
		if ( $start_stamp > $end_stamp ) {
			$tmp = $end_stamp;
			$end_stamp = $start_stamp;
			$start_stamp = $tmp;
		}

		return [
			'start' => date( 'Y-m-d', $start_stamp ),
			'end' => date( 'Y-m-d', $end_stamp ),
		];
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}

	public function frontend_props() {
		if ( ! is_admin() ) {
			wp_enqueue_style( 'pikaday' );
		}

		$value = $this->parse_value( $this->get_value() );
		$start_stamp = strtotime( $value['start'] ?? '' );
		$end_stamp = strtotime( $value['end'] ?? '' );
		return [
			'inputMode' => $this->props['input_mode'],
			'value' => [
				'start' => $start_stamp ? date( 'Y-m-d', $start_stamp ) : null,
				'end' => $end_stamp ? date( 'Y-m-d', $end_stamp ) : null,
			],
			'displayValue' => [
				'start' => $start_stamp ? \Voxel\date_format( $start_stamp ) : null,
				'end' => $end_stamp ? \Voxel\date_format( $end_stamp ) : null,
			],
			'presets' => $this->get_chosen_presets(),
			'l10n' => [
				'checkIn' => $this->props['l10n_checkin'],
				'checkOut' => $this->props['l10n_checkout'],
				'pickDate' => $this->props['l10n_pickdate'],
			],
		];
	}

	public function get_chosen_presets() {
		$presets = [];
		if ( is_array( $this->elementor_config['presets'] ?? null ) ) {
			foreach ( $this->elementor_config['presets'] as $preset ) {
				if ( $preset = \Voxel\get_range_presets( $preset ) ) {
					$presets[] = [
						'key' => $preset['key'],
						'label' => $preset['label'],
					];
				}
			}
		}

		return $presets;
	}

	public function get_elementor_controls(): array {
		if ( $this->props['input_mode'] === 'single-date' ) {
			return [
				'value' => [
					'label' => _x( 'Default value', 'date filter', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::DATE_TIME,
				],
			];
		}

		return [
			'default_value' => [
				'full_key' => $this->get_key().'__default_value',
				'label' => _x( 'Default value', 'date filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'area',
				'options' => [
					'date' => 'Custom date',
					'preset' => 'Preset',
				],
			],
			'start' => [
				'label' => _x( 'Default start date', 'date filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'classes' => 'ts-half-width',
				'condition' => [ $this->get_key().'__default_value' => 'date' ],
			],
			'end' => [
				'label' => _x( 'Default end date', 'date filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'classes' => 'ts-half-width',
				'condition' => [ $this->get_key().'__default_value' => 'date' ],
			],
			'default_preset' => [
				'label' => 'Default preset',
				'type' => \Elementor\Controls_Manager::SELECT,
				'multiple' => true,
				'conditional' => false,
				'condition' => [ $this->get_key().'__default_value' => 'preset' ],
				'options' => array_map( function( $range ) {
					return $range['label'];
				}, \Voxel\get_range_presets() ),
			],
			'presets' => [
				'label' => 'Presets',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'conditional' => false,
				'options' => array_map( function( $range ) {
					return $range['label'];
				}, \Voxel\get_range_presets() ),
			],
		];
	}

	public function get_default_value_from_elementor( $controls ) {
		if ( $this->props['input_mode'] === 'single-date' ) {
			$timestamp = strtotime( $controls['value'] ?? '' );
			return $timestamp ? date( 'Y-m-d', $timestamp ) : null;
		}

		if ( ( $controls['default_value'] ?? 'date' ) === 'preset' ) {
			return $controls['default_preset'] ?? null;
		}

		$start = strtotime( $controls['start'] ?? '' );
		$end = strtotime( $controls['end'] ?? '' );
		return ( $start && $end )
			? sprintf( '%s..%s', date( 'Y-m-d', $start ), date( 'Y-m-d', $end ) )
			: null;
	}
}
