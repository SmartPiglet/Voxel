<?php

namespace Voxel\Post_Types\Filters\Traits;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Date_Filter_Helpers {

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
			'start' => date( 'Y-m-d 00:00:00', $start_stamp ),
			'end' => date( 'Y-m-d 23:59:59', $end_stamp ),
		];
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
				'from' => $this->props['l10n_from'],
				'to' => $this->props['l10n_to'],
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
			]
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
