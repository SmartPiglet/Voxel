<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_General {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'popup_general_section',
			[
				'label' => __( 'Popup: General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$widget->add_control(
				'pg_general',
				[
					'label' => __( 'General', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$widget->add_control(
				'pg_background',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup, .ts-sticky-top' => 'background-color: {{VALUE}}',
					],
				]
			);



			$widget->add_responsive_control(
				'pg_top_margin',
				[
					'label' => __( 'Top/Bottom margin', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'description' => __( 'Does not affect mobile', 'voxel-elementor' ),
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-field-popup-container' => 'margin: {{SIZE}}{{UNIT}} 0;',
					],
				]
			);


			$widget->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'pg_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '.ts-field-popup',
				]
			);


			$widget->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'pg_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '.ts-field-popup',
				]
			);

			$widget->add_responsive_control(
				'pg_radius',
				[
					'label' => __( 'Border radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-field-popup' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$widget->add_control(
				'pg_scroll-color',
				[
					'label' => __( 'Scroll background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .min-scroll' => '--ts-scroll-color: {{VALUE}}',
					],
				]
			);

			$widget->add_control(
				'disable_reveal_fx',
				[
					'label' => __( 'Disable reveal animation', 'voxel-elementor' ),

					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'unset',
					'selectors' => [
						'.ts-field-popup' => 'animation: unset!important; opacity: 1 !important;',
					],
				]
			);


			$widget->add_control(
				'pg_title_separator',
				[
					'label' => __( 'Separator color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-popup-head, .ts-field-popup .ts-popup-controller, .ts-field-popup .uib, .ts-cart-controller' => 'border-color: {{VALUE}}',
					],

				]
			);




		$widget->end_controls_section();

	}
}
