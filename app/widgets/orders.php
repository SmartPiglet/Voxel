<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Orders extends Base_Widget {

	public function get_name() {
		return 'ts-orders';
	}

	public function get_title() {
		return __( 'Orders (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_order_general',
			[
				'label' => __( 'Orders head', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
			$this->add_control(
				'ts_head_hide',
				[
					'label' => __( 'Hide', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Hide', 'voxel-elementor' ),
					'label_off' => __( 'Show', 'voxel-elementor' ),
					'return_value' => 'none',

					'selectors' => [
						'{{WRAPPER}} .widget-head' => 'display: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'orders_title',
				[
					'label' => esc_html__( 'Title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Orders', 'voxel-elementor' ),
					'placeholder' => esc_html__( 'Type text', 'voxel-elementor' ),
				]
			);

			$this->add_control(
				'orders_subtitle',
				[
					'label' => esc_html__( 'Subtitle', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'View all orders related to your account', 'voxel-elementor' ),
					'placeholder' => esc_html__( 'Type text', 'voxel-elementor' ),
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
				'ts_search_icon',
				[
					'label' => __( 'Search icon', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_reset_search',
				[
					'label' => __( 'Reset icon', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_back',
				[
					'label' => __( 'Chevron left', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_forward',
				[
					'label' => __( 'Chevron right', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_down',
				[
					'label' => __( 'Chevron down', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_noresults_icon',
				[
					'label' => __( 'No results', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_inbox',
				[
					'label' => __( 'Inbox', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_checkmark',
				[
					'label' => __( 'Checkmark', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_menu',
				[
					'label' => __( 'Menu', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_info',
				[
					'label' => __( 'Info', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_files',
				[
					'label' => __( 'Files', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_trashs',
				[
					'label' => __( 'Trash can', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_calendar',
				[
					'label' => __( 'Calendar', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);



		$this->end_controls_section();

		$this->start_controls_section(
			'orders_head',
			[
				'label' => __( 'General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'main_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .widget-head h1',
				]
			);

			$this->add_responsive_control(
				'main_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .widget-head h1' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'sub_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .widget-head p',
				]
			);

			$this->add_responsive_control(
				'sub_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .widget-head p' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'whead_spacing',
				[
					'label' => __( 'Spacing', 'voxel-elementor' ),
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
						'{{WRAPPER}} .vx-orders-widget' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'primary_styling_buttons',
			[
				'label' => __( 'Primary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'primary_buttons_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'primary_buttons_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'primary_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'primary_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);

					$this->add_responsive_control(
						'primary_btn_radius',
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
								'{{WRAPPER}} .ts-btn-2' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'primary_btn_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);


					$this->add_responsive_control(
						'primary_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'primary_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'background: {{VALUE}}',
							],

						]
					);



					$this->add_responsive_control(
						'primary_btn_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-2 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-2 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'primary_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2 i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2 svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'primary_btn_icon_margin',
						[
							'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-2' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'primary_buttons_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'primary_btn_t_hover',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'primary_btn_bg_hover',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'primary_btn_bo_hover',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'primary_btn_shadow_h',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2:hover',
						]
					);

					$this->add_responsive_control(
						'primary_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2:hover svg' => 'fill: {{VALUE}}',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_scndry_btn',
			[
				'label' => __( 'Secondary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'scndry_btn_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'scndry_btn_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'scndry_btn_icon_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1 i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-1 svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scndry_btn_icon_size',
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
								'{{WRAPPER}} .ts-btn-1 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-1 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scndry_btn_icon_margin',
						[
							'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-1' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'scndry_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'scndry_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-1',
						]
					);

					$this->add_responsive_control(
						'scndry_btn_radius',
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
								'{{WRAPPER}} .ts-btn-1' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'scndry_btn_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .ts-btn-1',
						]
					);

					$this->add_control(
						'scndry_btn_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'scndry_btn_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'scndry_btn_icon_color_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-1:hover svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'scndry_btn_bg_h',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'scndry_btn_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover'
								=> 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'scndry_btn_text_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_card',
			[
				'label' => __( 'Cards', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'card_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'card_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'card_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .vx-order-card' => 'background-color: {{VALUE}}',
							],

						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'card_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .vx-order-card',
						]
					);
					$this->add_responsive_control(
						'card_radius',
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
								'{{WRAPPER}} .vx-order-card' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_card_avatar',
						[
							'label' => __( 'Avatar', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_card_avatar_size',
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
								'{{WRAPPER}}  .vx-order-card .vx-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_card_avatar_radius',
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
								'{{WRAPPER}} .vx-order-card .vx-avatar' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_card_id',
						[
							'label' => __( 'Order ID', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_card_id_type',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .order-badge',
						]
					);

					$this->add_control(
						'ts_card_id_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .order-badge' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_card_id_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .order-badge' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_card_id_radius',
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
								'{{WRAPPER}} .order-badge' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_card_title',
						[
							'label' => __( 'Order title', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_card_title_type',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .vx-order-card b',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_card_title_type_pending',
							'label' => __( 'Typography (Pending)', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .vx-order-card.vx-status-pending_approval b',
						]
					);

					$this->add_control(
						'ts_card_title_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .vx-order-card b' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_card_details',
						[
							'label' => __( 'Order details', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_card_details_type',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .vx-order-meta span',
						]
					);


					$this->add_control(
						'ts_card_details_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .vx-order-meta span' => 'color: {{VALUE}}',
							],

						]
					);

				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'card_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'card_bg_hover',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .vx-order-card:hover' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'cards_border_hover',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .vx-order-card:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_statuses',
			[
				'label' => __( 'Order statuses', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'status_style',
				[
					'label' => __( 'General', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'status_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}} .order-status' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'status_radius',
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
						'{{WRAPPER}} .order-status' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'status_typography',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .order-status',
				]
			);


			$this->add_control(
				'status_colors',
				[
					'label' => __( 'Colors', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$this->add_control(
				'order_orange',
				[
					'label' => __( 'Orange', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-orange'
						=> '--s-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'order_green',
				[
					'label' => __( 'Green', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-green'
						=> '--s-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'order_neutral',
				[
					'label' => __( 'Neutral', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-neutral'
						=> '--s-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'order_red',
				[
					'label' => __( 'Red', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-red'
						=> '--s-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'order_grey',
				[
					'label' => __( 'Grey', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-grey'
						=> '--s-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'order_blue',
				[
					'label' => __( 'Blue', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-blue'
						=> '--s-color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_common_styles',
			[
				'label' => __( 'Filters: Common styles', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'common_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'common_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);
					$this->add_control(
						'ts_gn_filters',
						[
							'label' => __( 'Filters', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_sf_input_height',
						[
							'label' => __( 'Filter height', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-filter' => 'height: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .inline-input' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);





					$this->add_responsive_control(
						'ts_sf_input_radius',
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
								'{{WRAPPER}} .ts-form .ts-filter' => 'border-radius: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .inline-input' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_input_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-filter, {{WRAPPER}} .inline-input',

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_sf_input_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-filter, {{WRAPPER}} .inline-input',

						]
					);

					$this->add_responsive_control(
						'ts_sf_input_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form .ts-filter' => 'background: {{VALUE}}',
								'{{WRAPPER}} .inline-input' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_input_value_col',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form .ts-filter-text' => 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-input' => 'color: {{VALUE}}',
							],

						]
					);




					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_sf_input_input_typo',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .ts-form .ts-filter, {{WRAPPER}} .inline-input',
						]
					);

					$this->add_control(
						'ts_chevron',
						[
							'label' => __( 'Chevron', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_hide_chevron',
						[

							'label' => __( 'Hide chevron', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'label_on' => __( 'Hide', 'voxel-elementor' ),
							'label_off' => __( 'Show', 'voxel-elementor' ),
							'return_value' => 'yes',

							'selectors' => [
								'{{WRAPPER}} .ts-filter .ts-down-icon' => 'display: none !important;',
							],
						]
					);

					$this->add_control(
						'ts_chevron_btn_color',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-filter .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);
				$this->end_controls_tab();

				/* Hover tab */

				$this->start_controls_tab(
					'common_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);
					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_input_shadow_hover',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-filter:hover, {{WRAPPER}} .inline-input:hover',
						]
					);

					$this->add_control(
						'ts_sf_input_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-filter:hover,{{WRAPPER}} .inline-input:hover' => 'border-color: {{VALUE}}',
							],


						]
					);

					$this->add_control(
						'ts_sf_input_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form .ts-filter:hover' => 'background: {{VALUE}}',
								'{{WRAPPER}} .inline-input:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_input_value_col_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-filter:hover .ts-filter-text' => 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-input:hover' => 'color: {{VALUE}}',
							],

						]
					);



					$this->add_control(
						'ts_chevron_btn_color_h',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-filter:hover .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();



		$this->start_controls_section(
			'ts_sf_styling_filters',
			[
				'label' => __( 'Filter: Dropdown', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_sf_filters_tabs'
			);

				/* Normal tab */


				$this->start_controls_tab(
					'ts_sf_filled',
					[
						'label' => __( 'Filled', 'voxel-elementor' ),
					]
				);



					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_sf_input_typo_filled',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-filter.ts-filled',

						]
					);

					$this->add_control(
						'ts_sf_input_background_filled',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form .ts-filter.ts-filled' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_input_value_col_filled',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-filter.ts-filled .ts-filter-text' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_control(
						'ts_sf_input_border_filled',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form .ts-filter.ts-filled' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_sf_border_filled_width',
						[
							'label' => __( 'Border width', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-form .ts-filter.ts-filled' => 'border-width: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_input_shadow_active',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-filter.ts-filled',
						]
					);

					$this->add_control(
						'ts_chevron_filled',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-filter.ts-filled .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);



				$this->end_controls_tab();







			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'inline_input',
			[
				'label' => __( 'Filter: Input', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'inline_input_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'inline_sfi_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$this->add_control(
						'inline_input_placeholder_color',
						[
							'label' => __( 'Input placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-input::-webkit-input-placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-input:-moz-placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-input::-moz-placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-input:-ms-input-placeholder' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_sf_input_icon_size',
						[
							'label' => __( 'Component icon', 'voxel-elementor' ),
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
							'default' => [
								'unit' => 'px',
								'size' => 24,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-inline-filter .ts-input-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-inline-filter .ts-input-icon svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_sf_input_icon_col',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-input-icon i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-input-icon svg' => 'fill: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'inline_input_icon_size_m',
						[
							'label' => __( 'Icon side margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 40,
									'step' => 1,
								],
							],
							'default' => [
								'unit' => 'px',
								'size' => 15,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-inline-filter .ts-input-icon > i, {{WRAPPER}} .ts-inline-filter .ts-input-icon > span, {{WRAPPER}} .ts-inline-filter .ts-input-icon > svg' => !is_rtl() ? 'left: {{SIZE}}{{UNIT}};' : 'right: {{SIZE}}{{UNIT}};',

							],
						]
					);

				$this->end_controls_tab();
				$this->start_controls_tab(
					'inline_sfi_active',
					[
						'label' => __( 'Focus', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'inline_input_bg_a',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-input:focus' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'inline_input_a_border',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-input:focus' => 'border-color: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();




			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_single_event',
			[
				'label' => __( 'Single: Order event', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'ts_single_avatar',
				[
					'label' => __( 'Avatar', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_single_avatar_size',
				[
					'label' => __( 'Size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 150,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}}  .order-event .vx-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_single_avatar_radius',
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
						'{{WRAPPER}} .order-event .vx-avatar' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'ts_single_heading',
				[
					'label' => __( 'Order title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'sheading_text',
						'label' => __( 'Typography' ),
						'selector' => '{{WRAPPER}} .order-event h3',
					]
				);

				$this->add_control(
					'sheading_text_color',
					[
						'label' => __( 'Text color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .order-event h3'
							=> 'color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'ts_single_subheading',
					[
						'label' => __( 'Event title', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'sub_heading_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .order-event > b',
						]
					);

					$this->add_control(
						'sub_heading_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .order-event > b'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'details_subheading',
						[
							'label' => __( 'Event details', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'details_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .order-event > span',
						]
					);

					$this->add_control(
						'details_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .order-event > span'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'event_separator',
						[
							'label' => __( 'Divider', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'separator_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .order-event:after'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

		$this->end_controls_section();

		$this->start_controls_section(
			'single_box',
			[
				'label' => __( 'Single: Event Box', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'box_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}} .order-event-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'box_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .order-event-box',
				]
			);

			$this->add_responsive_control(
				'box_radius',
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
						'{{WRAPPER}} .order-event-box' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'box_bg_color',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .order-event-box' => 'background-color: {{VALUE}}',
					],
				]
			);





		$this->end_controls_section();

		$this->start_controls_section(
			'ts_single_items',
			[
				'label' => __( 'Single: Order items', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
			$this->add_responsive_control(
				'cart_spacing',
				[
					'label' => __( 'Item spacing', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-cart-list' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'cart_item_spacing',
				[
					'label' => __( 'Item content spacing', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-cart-list li' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_cart_img_size',
				[
					'label' => __( 'Picture size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 16,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-cart-list .cart-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_cart_img_radius',
				[
					'label' => __( 'Picture radius', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-cart-list .cart-image img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_title_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-cart-list .cart-item-details a',
				]
			);

			$this->add_control(
				'ts_title_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cart-list .cart-item-details a' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_subtitle_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-cart-list .cart-item-details span',
				]
			);

			$this->add_control(
				'ts_subtitle_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cart-list .cart-item-details span' => 'color: {{VALUE}}',
					],
				]
			);
		$this->end_controls_section();

		$this->start_controls_section(
			'prform_calculator',
			[
				'label' => __( 'Single: Table', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'calc_list_gap',
				[
					'label' => __( 'List spacing', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-cost-calculator' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'calc_text',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-cost-calculator li p',
				]
			);

			$this->add_control(
				'calc_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cost-calculator li p'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'calc_text_total',
					'label' => __( 'Typography (Total)' ),
					'selector' => '{{WRAPPER}} .ts-cost-calculator li.ts-total p',
				]
			);

			$this->add_control(
				'calc_text_color_total',
				[
					'label' => __( 'Text color (Total)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cost-calculator li.ts-total p'
						=> 'color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'single_accordion',
			[
				'label' => __( 'Single: Accordion title', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'accordion_text',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .order-accordion summary',
				]
			);

			$this->add_control(
				'accordion_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .order-accordion summary'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'accordion_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .order-accordion summary svg'
						=> 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'box_divider_color',
				[
					'label' => __( 'Divider color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .order-accordion' => 'border-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'single_notes',
			[
				'label' => __( 'Single: Notes', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'notes_text',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .details-body p',
				]
			);

			$this->add_control(
				'accordion_notes_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .details-body p'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'accordion_notes_l_color',
				[
					'label' => __( 'Link color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .details-body p a'
						=> 'color: {{VALUE}}',
					],

				]
			);



		$this->end_controls_section();


	}

	protected function render( $instance = [] ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		global $wpdb;

		$testmode = \Voxel\is_test_mode() ? 'true' : 'false';

		if ( current_user_can( 'administrator' ) ) {
			$available_statuses = $wpdb->get_col( <<<SQL
				SELECT `status` FROM {$wpdb->prefix}vx_orders
				WHERE testmode IS {$testmode}
				GROUP BY `status`
			SQL );

			$available_shipping_statuses = $wpdb->get_col( <<<SQL
				SELECT `shipping_status` FROM {$wpdb->prefix}vx_orders
				WHERE shipping_status IS NOT NULL
					AND testmode IS {$testmode}
				GROUP BY `shipping_status`
			SQL );

			$available_product_types = $wpdb->get_col( <<<SQL
				SELECT items.product_type FROM {$wpdb->prefix}vx_orders AS orders
				LEFT JOIN {$wpdb->prefix}vx_order_items AS items ON orders.id = items.order_id
				WHERE items.product_type IS NOT NULL
					AND orders.testmode IS {$testmode}
				GROUP BY items.product_type
			SQL );
		} else {
			$available_statuses = $wpdb->get_col( $wpdb->prepare( <<<SQL
				SELECT `status` FROM {$wpdb->prefix}vx_orders
				WHERE ( customer_id = %d OR vendor_id = %d )
					AND testmode IS {$testmode}
				GROUP BY `status`
			SQL, get_current_user_id(), get_current_user_id() ) );

			$available_shipping_statuses = $wpdb->get_col( $wpdb->prepare( <<<SQL
				SELECT `shipping_status` FROM {$wpdb->prefix}vx_orders
				WHERE ( customer_id = %d OR vendor_id = %d )
					AND shipping_status IS NOT NULL
					AND testmode IS {$testmode}
				GROUP BY `shipping_status`
			SQL, get_current_user_id(), get_current_user_id() ) );

			$available_product_types = $wpdb->get_col( $wpdb->prepare( <<<SQL
				SELECT items.product_type FROM {$wpdb->prefix}vx_orders AS orders
				LEFT JOIN {$wpdb->prefix}vx_order_items AS items ON orders.id = items.order_id
				WHERE ( orders.customer_id = %d OR orders.vendor_id = %d )
					AND items.product_type IS NOT NULL
					AND orders.testmode IS {$testmode}
				GROUP BY items.product_type
			SQL, get_current_user_id(), get_current_user_id() ) );
		}

		$config = [
			'nonce' => wp_create_nonce( 'vx_orders' ),
			'statuses' => \Voxel\Order::get_status_config(),
			'shipping_statuses' => \Voxel\Order::get_shipping_status_config(),
			'product_types' => [],
			'statuses_ui' => [
				'completed' => [
					'class' => 'vx-green',
					'icon' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkmark') ) ?: \Voxel\get_svg( 'checkmark-circle.svg' ),
				],
				'pending_payment' => [
					'class' => 'vx-orange',
				],
				'pending_approval' => [
					'class' => 'vx-orange',
				],
				'canceled' => [
					'class' => 'vx-red',
				],
				'refunded' => [
					'class' => 'vx-red',
				],
				'sub_active' => [
					'class' => 'vx-green',
				],
				'sub_trialing' => [
					'class' => 'vx-neutral',
				],
				'sub_incomplete' => [
					'class' => 'vx-orange',
				],
				'sub_incomplete_expired' => [
					'class' => 'vx-red',
				],
				'sub_past_due' => [
					'class' => 'vx-orange',
				],
				'sub_canceled' => [
					'class' => 'vx-red',
				],
				'sub_unpaid' => [
					'class' => 'vx-orange',
				],
				'sub_paused' => [
					'class' => 'vx-orange',
				],
			],
			'available_statuses' => $available_statuses,
			'available_shipping_statuses' => $available_shipping_statuses,
			'messages' => [
				'url' => get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/'),
				'enquiry_text' => [
					'vendor' => _x( 'Hello, we\'re enquiring about your order #@order_id', 'vendor enquiry text', 'voxel' ),
					'customer' => _x( 'Hello, I\'m enquiring about my order #@order_id', 'customer enquiry text', 'voxel' ),
				],
			],
			'data_inputs' => [
				'content_length' => apply_filters( 'voxel/single_order/data_inputs/max_content_length', 128 ),
			],
		];

		foreach ( $available_product_types as $product_type_key ) {
			if ( $product_type = \Voxel\Product_Type::get( $product_type_key ) ) {
				$config['product_types'][ $product_type->get_key() ] = [
					'key' => $product_type->get_key(),
					'label' => $product_type->get_label(),
				];
			}
		}

		$icons = [
			'info' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_info') ) ?: \Voxel\get_svg( 'info.svg' ),
			'files' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_files') ) ?: \Voxel\get_svg( 'cloud-download.svg' ),
			'trash' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_trash') ) ?: \Voxel\get_svg( 'trash-can.svg' ),
			'calendar' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar') ) ?: \Voxel\get_svg( 'calendar.svg' ),
			'box' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_box') ) ?: \Voxel\get_svg( 'box.svg' ),
			'checkmark' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkmark') ) ?: \Voxel\get_svg( 'checkmark-circle.svg' ),
			'down' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_down') ) ?: \Voxel\get_svg( 'chevron-down.svg' ),
		];

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/orders.php' );

		if ( \Voxel\is_edit_mode() ) {
           printf( '<script type="text/javascript">%s</script>', 'window.render_orders();' );
        }
	}

	public function get_script_depends() {
		return [ 'vx:orders.js', 'pikaday' ];
	}

	public function get_style_depends() {
		return [ 'vx:forms.css', 'vx:orders.css', 'vx:product-summary.css', 'pikaday' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
