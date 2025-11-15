<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Notifications {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'pg_notifications',
			[
				'label' => __( 'Popup: Notifications', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$widget->start_controls_tabs(
				'pg_notifications_tabs'
			);

				/* Normal tab */

				$widget->start_controls_tab(
					'pg_notifications_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$widget->add_control(
						'pg_notf',
						[
							'label' => __( 'Single notification', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);



					$widget->add_control(
						'pg_notf_title_color',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a .notification-details b' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'pg_notf_title_typo',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => '.ts-notification-list li a .notification-details b',
						]
					);

					$widget->add_control(
						'pg_notf_subtitle',
						[
							'label' => __( 'Subtitle color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a .notification-details span' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'pg_notf_subtitle_typo',
							'label' => __( 'Subitle typography', 'voxel-elementor' ),
							'selector' => '.ts-notification-list li a .notification-details span',
						]
					);



					$widget->add_control(
						'pg_notf_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a .notification-image i' => 'color: {{VALUE}}',
								'.ts-notification-list li a .notification-image svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_notf_icon_bg',
						[
							'label' => __( 'Icon background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a .notification-image' => 'background-color: {{VALUE}}',
							],

						]
					);



					$widget->add_control(
						'ts_not_ico_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 20,
									'max' => 50,
									'step' => 1,
								],
							],
							'selectors' => [
								'.ts-notification-list li a .notification-image i' => 'font-size: {{SIZE}}{{UNIT}};',
								'.ts-notification-list li a .notification-image svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_control(
						'ts_not_con_size',
						[
							'label' => __( 'Icon container size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 20,
									'max' => 50,
									'step' => 1,
								],
							],
							'selectors' => [
								'.ts-notification-list li a .notification-image' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}}; min-width:{{SIZE}}{{UNIT}};',
							],
						]
					);



					$widget->add_responsive_control(
						'pg_not_avatar_radius',
						[
							'label' => __( 'Border radius', 'voxel-elementor' ),
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
								'.ts-notification-list li a .notification-image' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$widget->add_control(
						'pg_notf_unread',
						[
							'label' => __( 'Unvisited notification', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'pg_notf_title_typo_new',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => 'li.ts-unread-notification a .notification-details b',
						]
					);


					$widget->add_control(
						'pg_unread_title',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'li.ts-unread-notification a .notification-details b' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_new_unread',
						[
							'label' => __( 'Unseen notification', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);


					$widget->add_control(
						'pg_new_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li.ts-new-notification a .notification-image i' => 'color: {{VALUE}}',
								'.ts-notification-list li.ts-new-notification a .notification-image svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_new_icon_bg',
						[
							'label' => __( 'Icon background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li.ts-new-notification a .notification-image' => 'background: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'pg_new_border',
							'label' => __( 'Icon/Picture border', 'voxel-elementor' ),
							'selector' => '.ts-notification-list li.ts-new-notification a .notification-image',
						]
					);






				$widget->end_controls_tab();

				/* Hover tab */

				$widget->start_controls_tab(
					'pg_notifications_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);



					$widget->add_control(
						'pg_notf_h',
						[
							'label' => __( 'Notifications/Messages item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'pg_notf_bg_h',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li:hover' => 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_notf_title_color_hover',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a:hover .notification-details b' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_notf_subtitle_hover',
						[
							'label' => __( 'Subtitle color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a:hover .notification-details span' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_notf_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a:hover .notification-image i' => 'color: {{VALUE}}',
								'.ts-notification-list li a:hover .notification-image svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_notf_icon_bg_h',
						[
							'label' => __( 'Icon background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a:hover .notification-image' => 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'pg_notf_icon_border_h',
						[
							'label' => __( 'Icon/Avatar border', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-notification-list li a:hover .notification-image' => 'border-color: {{VALUE}}',
							],

						]
					);


				$widget->end_controls_tab();



			$widget->end_controls_tabs();

		$widget->end_controls_section();

	}

}
