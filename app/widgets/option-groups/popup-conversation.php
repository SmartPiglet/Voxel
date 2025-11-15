<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Conversation {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'pg_conversation',
			[
				'label' => __( 'Popup: Conversation', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


					$widget->add_control(
						'ts_convo_heading',
						[
							'label' => __( 'Conversation', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_ua_dropdown_convo_r1_bg',
						[
							'label' => __( 'Message background (User 1)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-conversation-body .ts-message-list p' => 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_ua_dropdown_convo_r1',
						[
							'label' => __( 'Message color (User 1)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-conversation-body .ts-message-list p' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_ua_dropdown_convo_r2_bg',
						[
							'label' => __( 'Message background (User 2)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-conversation-body .ts-message-list li.ts-responder-2 p' => 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_ua_dropdown_convo_r2',
						[
							'label' => __( 'Message color (User 2)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-conversation-body .ts-message-list li.ts-responder-2 p' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_ua_dropdown_convo_message_typo',
							'label' => __( 'Message typography', 'voxel-elementor' ),
							'selector' => '.ts-conversation-body .ts-message-list p',
						]
					);

					$widget->add_control(
						'ts_message_date_color',
						[
							'label' => __( 'Date/time color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-message-list li span' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_message_date_typo',
							'label' => __( 'Date/time typography', 'voxel-elementor' ),
							'selector' => '.ts-message-list li span',
						]
					);

					$widget->add_responsive_control(
						'message_border_rad',
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
								' .ts-conversation-body .ts-message-list p' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_control(
						'ts_convo_compose',
						[
							'label' => __( 'Send message', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'pg_send_separator',
						[
							'label' => __( 'Separator color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-conversation-body' => 'border-color: {{VALUE}}',
							],

						]
					);


					$widget->add_control(
						'ts_send_icon',
						[
							'label' => __( 'Send icon', 'text-domain' ),
							'type' => \Elementor\Controls_Manager::ICONS,
							'default' => [
								'value' => 'lar la-paper-plane',
								'library' => 'la-regular',
							],
						]
					);

					$widget->add_control(
						'ts_attach_icon',
						[
							'label' => __( 'Attach icon', 'text-domain' ),
							'type' => \Elementor\Controls_Manager::ICONS,
							'default' => [
								'value' => 'las la-cloud-upload-alt',
								'library' => 'la-regular',
							],
						]
					);

					$widget->add_responsive_control(
						'ts_send_btn_size',
						[
							'label' => __( 'Button size', 'voxel-elementor' ),
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
								'.ts-compose-buttons .ts-icon-btn' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);



					$widget->add_responsive_control(
						'ts_send_btn_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
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
								'.ts-compose-buttons .ts-icon-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
							],
						]
					);


		$widget->end_controls_section();

	}

}
