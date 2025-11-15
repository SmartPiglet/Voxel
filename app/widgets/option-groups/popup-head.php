<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Head {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'popup_head_section',
			[
				'label' => __( 'Popup: Head', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$widget->add_control(
				'pg_popup_title',
				[
					'label' => __( 'Popup title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$widget->add_responsive_control(
				'pg_title_icon_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-popup-name' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$widget->add_responsive_control(
				'pg_title_icon_margin',
				[
					'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-popup-head .ts-popup-name' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$widget->add_control(
				'pg_title_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-popup-name' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);



			$widget->add_control(
				'pg_title_color',
				[
					'label' => __( 'Title color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-popup-name > span, .ts-popup-name > span a' => 'color: {{VALUE}}',
					],
				]
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'pg_title_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '.ts-popup-name > span',
				]
			);

			$widget->add_responsive_control(
				'pg_title_avatar_size',
				[
					'label' => __( 'Avatar size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-popup-head .ts-popup-name img' => 'min-width: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$widget->add_responsive_control(
				'pg_title_avatar_radius',
				[
					'label' => __( 'Avatar radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'.ts-popup-head .ts-popup-name img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);






		$widget->end_controls_section();

	}
}
