<?php

namespace Voxel\Modules\Direct_Messages\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Messages_Widget extends \Voxel\Widgets\Base_Widget {

	public function get_name() {
		return 'ts-messages';
	}

	public function get_title() {
		return __( 'Messages (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'ts_inbox_general',
			[
				'label' => __( 'General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$this->add_responsive_control(
				'ts_map_height',
				[
					'label' => __( 'Height', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'vh'],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 1200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-inbox' => 'height: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_responsive_control(
				'enable_calc_height',
				[
					'label' => __( 'Calculate height?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'return_value' => 'yes',
					'default' => 'no'
				]
			);

			$this->add_responsive_control(
				'map_calc_height',
				[
					'label' => esc_html__( 'Calculation', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'calc()', 'voxel-elementor' ),
					'description' => __( 'Use CSS calc() to calculate height e.g calc(100vh - 215px)', 'voxel-elementor' ),
					'selectors' => [
						'{{WRAPPER}} .ts-inbox' => 'height: {{VALUE}};',
					],
					'condition' => [ 'enable_calc_height' => 'yes' ],
				]
			);

			$this->add_responsive_control(
				'ts_inbox_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox, {{WRAPPER}} .ts-inbox input, {{WRAPPER}} .ts-inbox-top, {{WRAPPER}} .ts-inbox-bottom' => 'background: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'ts_cal_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-inbox',
				]
			);


			$this->add_responsive_control(
				'ts_cal_radius',
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
						'{{WRAPPER}} .ts-inbox' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'ts_cal_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-inbox',
				]
			);

			$this->add_responsive_control(
				'ts_message_area_width',
				[
					'label' => __( 'Sidebar width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 1200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .inbox-left' => 'width: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-message-body' => '    width: calc(100% - {{SIZE}}{{UNIT}})',
					],

				]
			);

			$this->add_responsive_control(
				'ts_content_sep',
				[
					'label' => __( 'Separator color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top, {{WRAPPER}} .ts-inbox-top, {{WRAPPER}} .ts-inbox-bottom,{{WRAPPER}} .inbox-left' => 'border-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'ts_cal_scroll_color',
				[
					'label' => __( 'Scrollbar color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox .min-scroll' => '--ts-scroll-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_ms_single',
			[
				'label' => __( 'Inbox: Message', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ms_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ms_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'ms_single_general',
						[
							'label' => __( 'General', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ms_single_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px' ],
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);



					$this->add_control(
						'ms_single_content',
						[
							'label' => __( 'Content', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ms_single_cont_gap',
						[
							'label' => __( 'Content gap', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'.ts-field-popup px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a .message-details' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ms_single_title_color',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a .message-details b' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ms_single_title_typo',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-convo-list li a .message-details b',
						]
					);

					$this->add_control(
						'ms_single_subtitle_color',
						[
							'label' => __( 'Subtitle color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a .message-details span' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ms_single_subtitle_typo',
							'label' => __( 'Subtitle typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-convo-list li a .message-details span',
						]
					);

					$this->add_control(
						'ms_single_avatar',
						[
							'label' => __( 'Avatar / Logo', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ms_single_avatar_size',
						[
							'label' => __( 'Size', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-convo-list li a .convo-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'pg_title_avatar_radius',
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
							],
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a .convo-avatar img' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ms_single_avatar_gap',
						[
							'label' => __( 'Gap against content', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'.ts-field-popup px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a .convo-avatars' => 'margin-right: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ms_single_post',
						[
							'label' => __( 'Secondary logo', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ms_sl_size',
						[
							'label' => __( 'Size', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-convo-list li a .post-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'pg_sl_radius',
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
							],
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a .post-avatar img' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_sl_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-convo-list li a .post-avatar img',
						]
					);
				$this->end_controls_tab();

				/* Normal tab */

				$this->start_controls_tab(
					'ms_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_ms_hover_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a:hover'
								=> 'background: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ms_single_title_color_hover',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a:hover .message-details b' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_control(
						'ms_single_subtitle_color_hover',
						[
							'label' => __( 'Subtitle color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li a:hover .message-details span' => 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();



				$this->start_controls_tab(
					'ms_active',
					[
						'label' => __( 'Active', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_ms_active_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li.ts-active-chat a'
								=> 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ms_active_border',
						[
							'label' => __( 'Border width', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-convo-list li a' => 'border-width: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ms_border_active',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li.ts-active-chat a' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ms_single_title_color_active',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li.ts-active-chat a .message-details b' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_control(
						'ms_single_subtitle_color_active',
						[
							'label' => __( 'Subtitle color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-convo-list li.ts-active-chat a .message-details span' => 'color: {{VALUE}}',
							],

						]
					);




				$this->end_controls_tab();



				$this->start_controls_tab(
					'ms_unread',
					[
						'label' => __( 'Unread', 'voxel-elementor' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ms_single_title_typo_unread',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-convo-list li.ts-unread-message a .message-details b',
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'ms_new',
					[
						'label' => __( 'New', 'voxel-elementor' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ms_new_ms_border',
							'label' => __( 'Avatar border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-convo-list li.ts-new-message a .convo-avatar img',
						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_ms_search',
			[
				'label' => __( 'Inbox: Search', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'inline_input_font',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-inbox-top input',
				]
			);

			$this->add_control(
				'inline_input_value_col',
				[
					'label' => __( 'Value color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top input' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'inline_input_placeholder_color',
				[
					'label' => __( 'Input placeholder color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top input::-webkit-input-placeholder' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-inbox-top input:-moz-placeholder' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-inbox-top input::-moz-placeholder' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-inbox-top input:-ms-input-placeholder' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'inline_input_popup_icon',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top .ts-input-icon i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-inbox-top .ts-input-icon svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'inline_input_icon_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top .ts-input-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-inbox-top .ts-input-icon svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);




		$this->end_controls_section();

		$this->start_controls_section(
			'convo_top',
			[
				'label' => __( 'Conversation: Top', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);




			$this->add_responsive_control(
				'ctop_avatar_radius',
				[
					'label' => __( 'Avatar radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top .ts-convo-name img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ctop_avatar_gap',
				[
					'label' => __( 'Avatar / Text gap', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'.ts-field-popup px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top .ts-convo-name' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ctop_font',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-inbox-top .ts-convo-name span',
				]
			);

			$this->add_control(
				'ctop_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-inbox-top .ts-convo-name span'
						=> 'color: {{VALUE}}',
					],

				]
			);
		$this->end_controls_section();

		$this->start_controls_section(
			'start_conversation',
			[
				'label' => __( 'Conversation: Intro', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'start_cont_gap',
				[
					'label' => __( 'Content gap', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'.ts-field-popup px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .start-convo' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'start_avatar_size',
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
						'{{WRAPPER}} .start-convo img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'start_avatar_radius',
				[
					'label' => __( 'Avatar border radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .start-convo img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'start_primary_font',
					'label' => __( 'Name typography' ),
					'selector' => '{{WRAPPER}} .start-convo h4',
				]
			);

			$this->add_control(
				'start_primary_color',
				[
					'label' => __( 'Name color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .start-convo h4'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'start_scnd_font',
					'label' => __( 'Subtitle typography' ),
					'selector' => '{{WRAPPER}} .start-convo span',
				]
			);

			$this->add_control(
				'start_scnd_color',
				[
					'label' => __( 'Subtitle color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .start-convo span'
						=> 'color: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'convo_body',
			[
				'label' => __( 'Conversation: Body', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'convo_body_gap',
				[
					'label' => __( 'Message gap', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'.ts-field-popup px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'smesssage_general',
				[
					'label' => __( 'Single message', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'smessage_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-single-message > p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'smessage_typo',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-single-message > p',
				]
			);

			$this->add_control(
				'stmessage_radius',
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
					],

					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-single-message > p' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'smesssage_r1',
				[
					'label' => __( 'Responder 1', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
				    'name' => 'r1_bg',
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-responder-1 > p',

				]
			);

			$this->add_control(
				'r1_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-responder-1 > p' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-responder-1 > p a' => 'color: {{VALUE}}; border-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'smesssage_r2',
				[
					'label' => __( 'Responder 2', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
				    'name' => 'r2_bg',
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-responder-2 > p',

				]
			);

			$this->add_control(
				'r2_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-responder-2 > p' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-responder-2 > p a' => 'color: {{VALUE}}; border-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'smesssage_error',
				[
					'label' => __( 'Error', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
				    'name' => 'error_bg',
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-error-message > p',

				]
			);

			$this->add_control(
				'error_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-error-message > p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'smesssage_date',
				[
					'label' => __( 'Message info', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'smessage_date_typo',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-conversation-body .ts-message-list li',
				]
			);

			$this->add_control(
				'smessage_date_color',
				[
					'label' => __( 'Default Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list li' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'smessage_delete_color',
				[
					'label' => __( 'Delete color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list li.deletems' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'smesssage_seen',
				[
					'label' => __( 'Seen', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'smessage_seen_typo',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-message-seen .seen-badge',
				]
			);

			$this->add_control(
				'smessage_seen_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list .ts-message-seen .seen-badge' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'smesssage_image',
				[
					'label' => __( 'Images', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'smessage_image_radius',
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
					],

					'selectors' => [
						'{{WRAPPER}} .ts-conversation-body .ts-message-list li .ts-image-attachment img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);



		$this->end_controls_section();

		$this->start_controls_section(
			'ms_compose',
			[
				'label' => __( 'Conversation: Compose', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'comp_avatar_radius',
				[
					'label' => __( 'Avatar radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-convo-form .active-avatar img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'comp_placeholder',
				[
					'label' => __( 'Placeholder', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'comp_placeholder_font',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-convo-form .compose-message .compose-placeholder',
				]
			);

			$this->add_control(
				'comp_placeholder_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-convo-form .compose-message .compose-placeholder' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'comp_value',
				[
					'label' => __( 'Value', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'comp_value_font',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-convo-form .compose-message textarea',
				]
			);

			$this->add_control(
				'comp_vakue_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-convo-form .compose-message textarea' => 'color: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'repeater_icon_button',
			[
				'label' => __( 'Icon button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'repeater_icon_button_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'repeater_icon_button_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$this->add_control(
						'repeater_ib_styling',
						[
							'label' => __( 'Button styling', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);



					$this->add_control(
						'repeat_number_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-icon-btn svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);



					$this->add_control(
						'repeat_number_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'repeat_number_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-icon-btn',
						]
					);

					$this->add_responsive_control(
						'repeat_number_btn_radius',
						[
							'label' => __( 'Button border radius', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,

								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'repeat_icon_button_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'repeat_popup_number_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn:hover i'
								=> 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-icon-btn:hover svg'
								=> 'fill: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'repeat_number_btn_bg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn:hover'
								=> 'background-color: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'repeat_button_border_c_h',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_tertiary_btn',
			[
				'label' => __( 'Tertiary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'tertiary_btn_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'tertiary_btn_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'tertiary_btn_icon_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4 i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .n-load-more .ts-btn-4 svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'tertiary_btn_icon_size',
						[
							'label' => __( 'Button icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .n-load-more .ts-btn-4 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'tertiary_btn_bg',
					[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'tertiary_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .n-load-more .ts-btn-4',
						]
					);

					$this->add_responsive_control(
						'tertiary_btn_radius',
						[
							'label' => __( 'Button border radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .n-load-more .ts-btn-4' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'tertiary_btn_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .n-load-more .ts-btn-4',
						]
					);

					$this->add_control(
						'tertiary_btn_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'tertiary_btn_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'tertiary_btn_icon_color_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4:hover i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .n-load-more .ts-btn-4:hover svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'tertiary_btn_bg_h',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4:hover'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'tertiary_btn_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4:hover'
								=> 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'tertiary_btn_text_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .n-load-more .ts-btn-4:hover'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'nochat',
			[
				'label' => __( 'No messages / No chat selected', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'ts_empty_icon_size',
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
					'default' => [
						'unit' => 'px',
						'size' => 35,
					],
					'selectors' => [
						'{{WRAPPER}} .ts-empty-user-tab i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-empty-user-tab svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$this->add_control(
				'ts_empty_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-empty-user-tab i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-empty-user-tab svg' => 'fill: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'ts_empty_title_color',
				[
					'label' => __( 'Title color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-empty-user-tab p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_empty_title_text',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-empty-user-tab p',
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'loading',
			[
				'label' => __( 'Loading', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'ts_tm_loading',
				[
					'label' => __( 'Loading', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'tm_color1',
				[
					'label' => __( 'Color 1', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-loader' => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'tm_color2',
				[
					'label' => __( 'Color 2', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-loader' => 'border-bottom-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_order_filter_icons',
			[
				'label' => __( 'Icons', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ms_search',
				[
					'label' => __( 'Search', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_chat',
				[
					'label' => __( 'Chat', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_load',
				[
					'label' => __( 'Load more', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_back',
				[
					'label' => __( 'Back', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_more',
				[
					'label' => __( 'More', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_user',
				[
					'label' => __( 'User', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_clear',
				[
					'label' => __( 'Clear', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_ban',
				[
					'label' => __( 'Ban', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_trash',
				[
					'label' => __( 'Trash', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_upload',
				[
					'label' => __( 'Upload', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_gallery',
				[
					'label' => __( 'Media library', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_emoji',
				[
					'label' => __( 'Emoji', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ms_send',
				[
					'label' => __( 'Send', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

		$this->end_controls_section();

	}

	protected function render( $instance = [] ) {
		$config = [
			'user' => [
				'id' => get_current_user_id(),
			],
			'polling' => [
				'enabled' => \Voxel\get( 'settings.messages.enable_real_time', true ),
				'url' => trailingslashit( get_template_directory_uri() ) . 'app/modules/direct-messages/check-activity.php',
				'frequency' => 1000, // ms
			],
			'seen_badge' => [
				'enabled' => \Voxel\get( 'settings.messages.enable_seen', true ),
			],
			'emojis' => [
				'url' => trailingslashit( get_template_directory_uri() ) . 'assets/vendor/emoji-list/emoji-list.json',
			],
			'nonce' => wp_create_nonce( 'vx_chat' ),
			'files' => [
				'enabled' => \Voxel\get( 'settings.messages.files.enabled', true ),
				'allowed_file_types' => \Voxel\get( 'settings.messages.files.allowed_file_types' ),
				'max_size' => \Voxel\get( 'settings.messages.files.max_size' ),
				'max_count' => \Voxel\get( 'settings.messages.files.max_count' ),
			],
			'l10n' => [
				'emoji_groups' => [
					'Smileys & Emotion' => _x( 'Smileys & Emotion', 'emoji popup', 'voxel' ),
					'People & Body' => _x( 'People & Body', 'emoji popup', 'voxel' ),
					'Animals & Nature' => _x( 'Animals & Nature', 'emoji popup', 'voxel' ),
					'Food & Drink' => _x( 'Food & Drink', 'emoji popup', 'voxel' ),
					'Travel & Places' => _x( 'Travel & Places', 'emoji popup', 'voxel' ),
					'Activities' => _x( 'Activities', 'emoji popup', 'voxel' ),
					'Objects' => _x( 'Objects', 'emoji popup', 'voxel' ),
				],
			],

			/**
			 * WebKit bug: https://bugs.webkit.org/show_bug.cgi?id=274702
			 *
			 * After clearing the composer value, if a new compositional string that can
			 * combine with the previous string is entered, the last character that was
			 * cleared reappears in the input field.
			 *
			 * Workaround solution: Blur and re-focus composer. On iOS, this will
			 * temporarily hide the keyboard.
			 *
			 * ```
			 * add_filter( 'voxel/direct-messages/blur-on-send', function() {
			 * 	$visitor = \Voxel\Visitor::get();
			 * 	return $visitor->get_os() === 'ios';
			 * } );
			 * ```
			 */
			'blur_on_send' => apply_filters( 'voxel/direct-messages/blur-on-send', false ),
		];

		wp_print_styles( $this->get_style_depends() );

		require locate_template( 'app/modules/direct-messages/templates/frontend/messages-widget.php' );
		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_messages();' );
		}
	}

	public function get_style_depends() {
		return [ 'vx:forms.css', 'vx:messages.css', 'e-swiper' ];

	}

	public function get_script_depends() {
		return [ 'vx:messages.js', 'swiper' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
