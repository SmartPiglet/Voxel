<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Calendar {

	public static function controls( $widget ) {
		
		$widget->start_controls_section(
			'ts_sf_popup_calendar',
			[
				'label' => __( 'Popup: Calendar', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
			$widget->add_control(
				'calendar_months',
				[
					'label' => __( 'Months', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'months_typo',
					'label' => __( 'Typography' ),
					'selector' => '.ts-field-popup .pika-label',
				]
			);

			$widget->add_control(
				'months_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .pika-label'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$widget->add_control(
				'days_of_week',
				[
					'label' => __( 'Days of the week', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_calendary_days_typo',
					'label' => __( 'Typography' ),
					'selector' => '.ts-field-popup .pika-table abbr[title]',
				]
			);

			$widget->add_control(
				'ts_calendary_days_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .pika-table abbr[title]'
						=> 'color: {{VALUE}}',
					],

				]
			);



			$widget->add_control(
				'available_date',
				[
					'label' => __( 'Dates (available)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_calendary_number_typo',
					'label' => __( 'Typography' ),
					'selector' => '.ts-field-popup td:not(.is-disabled) .pika-button',
				]
			);

			$widget->add_control(
				'ts_calendary_number_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup td:not(.is-disabled) .pika-button'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$widget->add_control(
				'ts_calendary_number_color_h',
				[
					'label' => __( 'Color (Hover)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup td:not(.is-disabled) .pika-button:hover'
						=> 'color: {{VALUE}}',
					],

				]
			);


			$widget->add_control(
				'ts_calendary_number_bg_h',
				[
					'label' => __( 'Background (Hover)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup td:not(.is-disabled) .pika-button:hover'
						=> 'background-color: {{VALUE}}',
					],

				]
			);

			$widget->add_control(
				'ts_calendary_number_bor_h',
				[
					'label' => __( 'Border color (Hover)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup td:not(.is-disabled) .pika-button:hover'
						=> 'border-color: {{VALUE}}',
					],

				]
			);



			$widget->add_control(
				'days_range_h',
				[
					'label' => __( 'Dates (Range)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$widget->add_control(
				'range_nm_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .is-inrange:not(.is-disabled) .pika-button'
						=> 'color: {{VALUE}} !important;',
					],

				]
			);

			$widget->add_control(
				'range_nm_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup  .is-inrange:not(.is-disabled) .pika-button'
						=> 'background-color: {{VALUE}} !important;',
					],

				]
			);

			$widget->add_control(
				'days_sides_h',
				[
					'label' => __( 'Dates (Range start and end)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$widget->add_control(
				'sides_nm_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .ts-booking-date .is-startrange .pika-button, .ts-field-popup .ts-booking-date .is-endrange .pika-button'
						=> 'color: {{VALUE}} !important;',
					],

				]
			);

			$widget->add_control(
				'sides_nm_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .ts-booking-date .is-startrange .pika-button, .ts-field-popup .ts-booking-date .is-endrange .pika-button'
						=> 'background-color: {{VALUE}} !important;',
					],

				]
			);

			$widget->add_control(
				'selected_date',
				[
					'label' => __( 'Dates (Selected - Single date)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$widget->add_control(
				'slected_nm_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .pika-single:not(.pika-range) .is-selected .pika-button'
						=> 'color: {{VALUE}} !important;',
					],

				]
			);

			$widget->add_control(
				'selected_nm_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .pika-single:not(.pika-range) .is-selected .pika-button'
						=> 'background-color: {{VALUE}} !important;',
					],

				]
			);

			$widget->add_control(
				'unavailable_date',
				[
					'label' => __( 'Dates (disabled)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'unvailable_date_t',
					'label' => __( 'Typography' ),
					'selector' => ' .ts-field-popup td.is-disabled .pika-button',
				]
			);

			$widget->add_control(
				'unvailable_date_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup td.is-disabled .pika-button'
						=> 'color: {{VALUE}}',
					],

				]
			);


			$widget->add_control(
				'days_settings',
				[
					'label' => __( 'Other settings', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);




		$widget->end_controls_section();
	}
}
