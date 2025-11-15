<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cart_Summary extends Base_Widget {

	public function get_name() {
		return 'ts-cart-summary';
	}

	public function get_title() {
		return __( 'Cart Summary (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_ui_icons',
			[
				'label' => __( 'Icons', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
			$this->add_control(
				'ts_checkout_icon',
				[
					'label' => __( 'Pay now icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_delete_icon',
				[
					'label' => __( 'Delete icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'nostock_ico',
				[
					'label' => __( 'No products icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'continue_ico',
				[
					'label' => __( 'Continue icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);


			$this->add_control(
				'ts_enter',
				[
					'label' => __( 'Login icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'auth_email_ico',
				[
					'label' => __( 'Email icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'auth_user_ico',
				[
					'label' => __( 'User icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_upload_ico',
				[
					'label' => __( 'Upload icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_shipping_ico',
				[
					'label' => __( 'Shipping icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_location',
				[
					'label' => __( 'Location icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'prform_fields_general',
			[
				'label' => __( 'General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_control(
				'ts_field_spacing',
				[
					'label' => __( 'Sections', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'field_spacing_value',
				[
					'label' => __( 'Spacing', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-checkout' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_control(
				'ts_w_title',
				[
					'label' => __( 'Title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'wt_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-cart-head h1',
				]
			);

			$this->add_responsive_control(
				'wt_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cart-head h1' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'ts_tm_nostock',
				[
					'label' => __( 'Empty cart', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$this->add_responsive_control(
				'ts_nopost_content_Gap',
				[
					'label' => __( 'Content gap', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-no-posts' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_nopost_ico_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-no-posts > i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-no-posts > svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_nopost_ico_col',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-no-posts > i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-no-posts > svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_nopost_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-no-posts p',
				]
			);

			$this->add_responsive_control(
				'ts_nopost_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-no-posts > p' => 'color: {{VALUE}}',
					],

				]
			);



		$this->end_controls_section();



		$this->start_controls_section(
			'ts_sf_styling_buttons',
			[
				'label' => __( 'Primary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_sf_buttons_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_sf_buttons_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_submit_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.form-btn',
						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_sf_form_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.form-btn',
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_radius',
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
								'{{WRAPPER}} .ts-btn-2.form-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_form_btn_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.form-btn',
						]
					);


					$this->add_responsive_control(
						'ts_sf_form_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_sf_form_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn' => 'background: {{VALUE}}',
							],

						]
					);



					$this->add_responsive_control(
						'ts_sf_form_btn_icon_size',
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
								'{{WRAPPER}} .ts-btn-2.form-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-2.form-btn svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2.form-btn svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_margin',
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
								'{{WRAPPER}} .ts-btn-2.form-btn' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_sf_buttons_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_sf_form_btn_t_hover',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_bg_hover',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn:hover' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_bo_hover',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_form_btn_shadow_h',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.form-btn:hover',
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.form-btn:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2.form-btn:hover svg' => 'fill: {{VALUE}}',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'auth_scnd_btn',
			[
				'label' => __( 'Secondary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'scnd_btn_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'scnd_btn_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'scnd_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-1.form-btn',
						]
					);


					$this->add_responsive_control(
						'scnd_btn_radius',
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
								'{{WRAPPER}} .ts-btn-1.form-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);




					$this->add_responsive_control(
						'scnd_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'scnd_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-1.form-btn',
						]
					);


					$this->add_responsive_control(
						'scnd_btn_icon_size',
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
								'{{WRAPPER}} .ts-btn-1.form-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-1.form-btn svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_icon_pad',
						[
							'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-1.form-btn' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-1.form-btn svg' => 'fill: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'scnd_btn_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'scnd_btn_c_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1.form-btn:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-1.form-btn:hover svg' => 'fill: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_no_posts',
			[
				'label' => __( 'Loading', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
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
			'ts_addlist',
			[
				'label' => __( 'Radio/Checkboxes', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_addlist_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_addlist_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$this->add_responsive_control(
						'checkbox_border_color',
						[
							'label' => __( 'Border-color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .checkmark' => 'border-color: {{VALUE}}',
							],

						]
					);

				$this->end_controls_tab();



				$this->start_controls_tab(
					'ts_addlist_selected',
					[
						'label' => __( 'Selected', 'voxel-elementor' ),
					]
				);


					$this->add_responsive_control(
						'ts_checkbox_checked',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} input:checked ~ .checkmark' => 'background-color: {{VALUE}};border-color: {{VALUE}}',
							],

						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_checkbox_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} input:checked ~ .checkmark',
						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();



		$this->end_controls_section();



		$this->start_controls_section(
			'cart_styling',
			[
				'label' => __( 'Cart styling', 'voxel-elementor' ),
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
			'ts_cart_stepper',
			[
				'label' => __( 'Icon Button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_cart_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_cart_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'ts_cart_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart-stepper .ts-icon-btn'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .cart-stepper .ts-icon-btn svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);



					$this->add_control(
						'ts_cart_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart-stepper .ts-icon-btn'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_cart_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .cart-stepper .ts-icon-btn',
						]
					);

					$this->add_responsive_control(
						'ts_cart_btn_radius',
						[
							'label' => __( 'Button border radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .cart-stepper .ts-icon-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'cart_input_size',
						[
							'label' => __( 'Value size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 13,
									'max' => 30,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .cart-stepper > span' => 'font-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'cart_input_color',
						[
							'label' => __( 'Value color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart-stepper > span'
								=> 'color: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_cart_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_cart_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart-stepper .ts-icon-btn:hover'
								=> 'color: {{VALUE}};',
								'{{WRAPPER}} .cart-stepper .ts-icon-btn:hover svg'
								=> 'fill: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_cart_btn_bg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart-stepper .ts-icon-btn:hover'
								=> 'background-color: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_cart_border_c_h',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart-stepper .ts-icon-btn:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();



		$this->end_controls_section();

		$this->start_controls_section(
				'ts_sf_styling_filters',
				[
					'label' => __( 'Dropdown button', 'voxel-elementor' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->start_controls_tabs(
					'ts_sf_filters_tabs'
				);

					/* Normal tab */

					$this->start_controls_tab(
						'ts_sf_normal',
						[
							'label' => __( 'Normal', 'voxel-elementor' ),
						]
					);


						$this->add_control(
							'ts_sf_input',
							[
								'label' => __( 'Style', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Typography::get_type(),
							[
								'name' => 'ts_sf_input_input_typo',
								'label' => __( 'Typography' ),
								'selector' => '{{WRAPPER}} .ts-form div.ts-filter',
							]
						);





						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'ts_sf_input_shadow',
								'label' => __( 'Box Shadow', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} div.ts-filter',
							]
						);




						$this->add_responsive_control(
							'ts_sf_input_bg',
							[
								'label' => __( 'Background color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form div.ts-filter' => 'background: {{VALUE}}',
								],

							]
						);


						$this->add_responsive_control(
							'ts_sf_input_value_col',
							[
								'label' => __( 'Text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form div.ts-filter-text' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' => 'ts_sf_input_border',
								'label' => __( 'Border', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} div.ts-filter',
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
									'{{WRAPPER}} .ts-form div.ts-filter' => 'border-radius: {{SIZE}}{{UNIT}};',
								],
							]
						);


						$this->add_responsive_control(
							'ts_sf_input_height',
							[
								'label' => __( 'Height', 'voxel-elementor' ),
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
									'{{WRAPPER}} div.ts-filter' => 'height: {{SIZE}}{{UNIT}};',
								],
							]
						);



						$this->add_control(
							'ts_icon_filters',
							[
								'label' => __( 'Icons', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

						$this->add_responsive_control(
							'ts_sf_input_icon_col',
							[
								'label' => __( 'Icon color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} div.ts-filter i' => 'color: {{VALUE}}',
									'{{WRAPPER}} div.ts-filter svg' => 'fill: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'ts_sf_input_icon_size',
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
								'default' => [
									'unit' => 'px',
									'size' => 24,
								],
								'selectors' => [
									'{{WRAPPER}} div.ts-filter i' => 'font-size: {{SIZE}}{{UNIT}};',
									'{{WRAPPER}} div.ts-filter svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'ts_sf_input_icon_margin',
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
								'default' => [
									'unit' => 'px',
									'size' => 10,
								],
								'selectors' => [
									'{{WRAPPER}} div.ts-filter' => 'grid-gap: {{SIZE}}{{UNIT}};',
								],
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
									'{{WRAPPER}} div.ts-filter .ts-down-icon' => 'display: none !important;',
								],
							]
						);

						$this->add_control(
							'ts_chevron_btn_color',
							[
								'label' => __( 'Chevron color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} div.ts-filter .ts-down-icon' => 'border-color: {{VALUE}}',
								],
							]
						);


					$this->end_controls_tab();


					/* Hover tab */

					$this->start_controls_tab(
						'ts_sf_hover',
						[
							'label' => __( 'Hover', 'voxel-elementor' ),
						]
					);

						$this->add_control(
							'ts_sf_input_h',
							[
								'label' => __( 'Style', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

						$this->add_control(
							'ts_sf_input_bg_h',
							[
								'label' => __( 'Background color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form div.ts-filter:hover' => 'background: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'ts_sf_input_value_col_h',
							[
								'label' => __( 'Text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form div.ts-filter:hover .ts-filter-text' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'ts_sf_input_border_h',
							[
								'label' => __( 'Border color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form .ts-filter:hover' => 'border-color: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'ts_sf_input_icon_col_h',
							[
								'label' => __( 'Icon color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} div.ts-filter:hover i' => 'color: {{VALUE}}',
									'{{WRAPPER}} div.ts-filter:hover svg' => 'fill: {{VALUE}}',
								],

							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'ts_sf_input_shadow_hover',
								'label' => __( 'Box Shadow', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} div.ts-filter:hover',
							]
						);



					$this->end_controls_tab();

					/* Hover tab */

					$this->start_controls_tab(
						'ts_sf_filled',
						[
							'label' => __( 'Filled', 'voxel-elementor' ),
						]
					);

						$this->add_control(
							'ts_sf_input_filled',
							[
								'label' => __( 'Style (Filled)', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Typography::get_type(),
							[
								'name' => 'ts_sf_input_typo_filled',
								'label' => __( 'Typography', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} div.ts-filter.ts-filled',
							]
						);

						$this->add_control(
							'ts_sf_input_background_filled',
							[
								'label' => __( 'Background', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form div.ts-filter.ts-filled' => 'background-color: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'ts_sf_input_value_col_filled',
							[
								'label' => __( 'Text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} div.ts-filter.ts-filled .ts-filter-text' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'ts_sf_input_icon_col_filled',
							[
								'label' => __( 'Icon color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} div.ts-filter.ts-filled i' => 'color: {{VALUE}}',
									'{{WRAPPER}} div.ts-filter.ts-filled svg' => 'fill: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'ts_sf_input_border_filled',
							[
								'label' => __( 'Border color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-form div.ts-filter.ts-filled' => 'border-color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'ts_sf_border_filled_width',
							[
								'label' => __( 'Border width', 'voxel-elementor' ),
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
									'{{WRAPPER}} .ts-form div.ts-filter.ts-filled' => 'border-width: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'ts_sf_input_shadow_active',
								'label' => __( 'Box Shadow', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} div.ts-filter.ts-filled',
							]
						);




					$this->end_controls_tab();

				$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'shipto',
			[
				'label' => __( 'Ship to', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'shipto_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .cn-picker',
				]
			);

			$this->add_responsive_control(
				'shipto_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cn-picker' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'shipto_link_color',
				[
					'label' => __( 'Link color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cn-picker span' => 'color: {{VALUE}}',
						'{{WRAPPER}} .cn-picker svg' => 'fill: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_separator',
			[
				'label' => __( 'Section divider', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'sd_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .or-group .or-text',
				]
			);

			$this->add_responsive_control(
				'sd_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .or-group .or-text' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'sd_div_color',
				[
					'label' => __( 'Divider color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .or-group .or-line' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'sd_div_height',
				[
					'label' => __( 'Divider height', 'voxel-elementor' ),
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
						'{{WRAPPER}} .or-group .or-line' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);


		$this->end_controls_section();



		$this->start_controls_section(
			'prform_calculator',
			[
				'label' => __( 'Subtotal', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
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
			'auth_label_section',
			[
				'label' => __( 'Field label', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);




			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'auth_label_typo',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-form-group label, {{WRAPPER}} .field-info',
				]
			);


			$this->add_responsive_control(
				'auth_label_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-form-group label,{{WRAPPER}} .field-info' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'auth_label_link',
				[
					'label' => __( 'Link color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .field-info a' => 'color: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_intxt',
			[
				'label' => __( 'Input & Textarea', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_intxt_tabs'
			);
				/* Normal tab */

				$this->start_controls_tab(
					'ts_intxt_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_intxt_placeholde_heading',
						[
							'label' => __( 'Placeholder', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_intxt_placeholder',
						[
							'label' => __( 'Placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter::placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group textarea.ts-filter::placeholder' => 'color: {{VALUE}}',

							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_intxt_input_input_typo',
							'label' => __( 'Typography' ),
							'selector' =>
								'{{WRAPPER}} .ts-form-group input.ts-filter::placeholder, .ts-form-group textarea.ts-filter::placeholder',
						]
					);

					$this->add_control(
						'ts_intxt_text',
						[
							'label' => __( 'Value', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);



					$this->add_responsive_control(
						'ts_intxt_value_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-form-group textarea.ts-filter' => 'color: {{VALUE}};',
							],

						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_intxt_value_typo',
							'label' => __( 'Typography' ),

							'selector' => '{{WRAPPER}} .ts-form-group input.ts-filter, {{WRAPPER}} .ts-form-group textarea.ts-filter',


						]
					);


					$this->add_control(
						'ts_intxt_general',
						[
							'label' => __( 'General', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_intxt_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group textarea.ts-filter' => 'background: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group input.ts-filter' => 'background: {{VALUE}}',
							],

						]
					);




					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_intxt_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-form-group textarea.ts-filter, {{WRAPPER}} .ts-form-group input.ts-filter',


						]
					);

					$this->add_control(
						'ts_intxt_input_heading',
						[
							'label' => __( 'Input', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_intxt_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_intxt_input_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
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
								'{{WRAPPER}}  .ts-form-group input.ts-filter' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_intxt_input_radius',
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
								'{{WRAPPER}} .ts-form-group input.ts-filter' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_input2_icon_heading',
						[
							'label' => __( 'Input with icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_input2_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-input-icon input.ts-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);



					$this->add_responsive_control(
						'ts_input2_icon_col',
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
						'ts_intxt_icon_size',
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
								'{{WRAPPER}} .ts-input-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-input-icon svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_intxt_icon_margin',
						[
							'label' => __( 'Icon side padding', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-input-icon i' => !is_rtl() ? 'left: {{SIZE}}{{UNIT}};' : 'right: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-input-icon svg' => !is_rtl() ? 'left: {{SIZE}}{{UNIT}};' : 'right: {{SIZE}}{{UNIT}};',
							],
						]
					);



					$this->add_control(
						'ts_intxt_textarea_heading',
						[
							'label' => __( 'Textarea', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_txt_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-form-group textarea.ts-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);


					$this->add_responsive_control(
						'ts_intxt_textarea_radius',
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
								'{{WRAPPER}} .ts-form-group textarea.ts-filter' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);



				$this->end_controls_tab();

				/* Hover */

				$this->start_controls_tab(
					'ts_intxt_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_intxt_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:hover' => 'background: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group input.ts-filter:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:hover' => 'border-color: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group input.ts-filter:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_placeholder_h',
						[
							'label' => __( 'Placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter:hover::placeholder' => 'color: {{VALUE}}',
							],

						]

					);

					$this->add_responsive_control(
						'ts_intxt_value_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter:hover' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:hover' => 'color: {{VALUE}};',
							],

						]
					);

					$this->add_responsive_control(
						'ts_input2_icon_col_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-input-icon:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-input-icon:hover svg' => 'fill: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();

				/* Filled */

				$this->start_controls_tab(
					'ts_intxt_filled',
					[
						'label' => __( 'Active', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_intxt_bg_a',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:focus' => 'background: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group input.ts-filter:focus' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_border_a',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:focus' => 'border-color: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group input.ts-filter:focus' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_placeholder_a',
						[
							'label' => __( 'Placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter:active::placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:active::placeholder' => 'color: {{VALUE}}',

							],

						]

					);

					$this->add_responsive_control(
						'ts_intxt_value_color_a',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form-group input.ts-filter:focus' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-form-group textarea.ts-filter:focus' => 'color: {{VALUE}};',
							],

						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_cards',
			[
				'label' => __( 'Cards', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_cards_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_cards_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'cards_heading',
						[
							'label' => __( 'Cards', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_cards_gap',
						[
							'label' => __( 'Gap', 'voxel-elementor' ),
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
								'{{WRAPPER}} .addon-cards' => 'gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_cards_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .addon-cards li'
								=> 'background: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_cards_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .addon-cards li',
						]
					);

					$this->add_responsive_control(
						'ts_cards_radius',
						[
							'label' => __( 'Border radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .addon-cards li' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);



					$this->add_control(
						'cards_text',
						[
							'label' => __( 'Text', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_cards_primary',
							'label' => __( 'Primary' ),
							'selector' => '{{WRAPPER}} .adc-title',
						]
					);

					$this->add_responsive_control(
						'ts_cards_pr_c',
						[
							'label' => __( 'Primary Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .adc-title' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_cards_secondary',
							'label' => __( 'Secondary' ),
							'selector' => '{{WRAPPER}} .adc-subtitle',
						]
					);

					$this->add_responsive_control(
						'ts_cards_sc_c',
						[
							'label' => __( 'Secondary Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .adc-subtitle' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_cards_price',
							'label' => __( 'Price' ),
							'selector' => '{{WRAPPER}} .vx-addon-price',
						]
					);

					$this->add_responsive_control(
						'ts_cards_price_c',
						[
							'label' => __( 'Price color Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .vx-addon-price' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'cards_image',
						[
							'label' => __( 'Image', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_cards_img_radius',
						[
							'label' => __( 'Border radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .addon-cards img' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_card_size',
						[
							'label' => __( 'Size', 'voxel-elementor' ),
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
								'{{WRAPPER}} .addon-cards img' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],

						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_cards_selected',
					[
						'label' => __( 'Selected', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_cards_bg_selected',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .addon-cards li.adc-selected'
								=> 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_cards_border_s',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .addon-cards li.adc-selected' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_cards_shadow_s',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} li.adc-selected',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_cards_primary_selected',
							'label' => __( 'Primary text' ),
							'selector' => '{{WRAPPER}} .adc-selected .adc-title',
						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();



		$this->end_controls_section();
		$this->apply_controls( Option_Groups\File_Field::class );
	}

	protected function render( $instance = [] ) {
		require locate_template( 'templates/widgets/cart-summary/cart-item.php' );

		$screen = $_GET['screen'] ?? null;
		if ( $screen === 'promote' && is_user_logged_in() ) {
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( $post && $post->promotions->is_promotable_by_user( \Voxel\get_current_user() ) ) {
				$config = [
					'nonce' => [
						'checkout' => wp_create_nonce('vx_checkout'),
					],
					'post_id' => $post->get_id(),
					'packages' => array_map( function( $package ) {
						return [
							'key' => $package->get_key(),
							'label' => $package->get_label(),
							'description' => $package->get_description(),
							'icon' => $package->get_icon_markup(),
							'color' => $package->get_color(),
							'price_amount' => $package->get_price_amount(),
						];
					}, $post->promotions->get_available_packages() ),
				];

				wp_print_styles( $this->get_style_depends() );
				require locate_template( 'templates/widgets/cart-summary/promote-screen.php' );

				if ( \Voxel\is_edit_mode() ) {
					printf( '<script type="text/javascript">%s</script>', 'window.render_voxel_checkout();' );
				}

				return;
			}
		}

		$auth_link = add_query_arg( 'redirect_to', \Voxel\get_current_url(), get_permalink( \Voxel\get( 'templates.auth' ) ) ?: home_url('/') );

		$config = [
			'nonce' => [
				'cart' => wp_create_nonce('vx_cart'),
				'checkout' => wp_create_nonce('vx_checkout'),
			],
			'guest_customers' => [
				'behavior' => \Voxel\get( 'product_settings.cart_summary.guest_customers.behavior', 'proceed_with_email' ),
				'proceed_with_email' => [
					'require_verification' => \Voxel\get( 'product_settings.cart_summary.guest_customers.proceed_with_email.require_verification', true ),
					'require_tos' => \Voxel\get( 'product_settings.cart_summary.guest_customers.proceed_with_email.require_tos', false ),
				],
			],
			'recaptcha' => [
				'enabled' => \Voxel\get('settings.recaptcha.enabled'),
				'key' => \Voxel\get('settings.recaptcha.key'),
			],
			'auth_link' => $auth_link,
			'multivendor' => [
				'enabled' => !! \Voxel\get( 'payments.stripe.stripe_connect.enabled' ),
				'charge_type' => \Voxel\get('payments.stripe.stripe_connect.charge_type'),
			],
			'shipping' => [
				'zones' => (object) array_map( function( $shipping_zone ) {
					return $shipping_zone->get_frontend_config();
				}, \Voxel\Product_Types\Shipping\Shipping_Zone::get_all() ),
				'countries' => [],
				'default_country' => $this->_determine_customer_shipping_country_code(),
				'responsibility' => \Voxel\get( 'payments.stripe.stripe_connect.enabled' ) ? \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) : 'platform',
			],
			'l10n' => [
				'free' => _x( 'Free', 'cart summary', 'voxel' ),
			],
			'geoip_providers' => [],
		];

		// determine countries with shipping support
		$countries = \Voxel\Modules\Stripe_Payments\Country_Codes::all();
		foreach ( \Voxel\Product_Types\Shipping\Shipping_Zone::get_all() as $shipping_zone ) {
			foreach ( $shipping_zone->get_supported_country_codes() as $country_code => $enabled ) {
				if ( isset( $countries[ $country_code ] ) ) {
					$config['shipping']['countries'][ $country_code ] = $countries[ $country_code ];
				}
			}
		}
		asort( $config['shipping']['countries'] );
		$config['shipping']['countries'] = (object) $config['shipping']['countries'];

		// geoip providers for determining shipping country
		foreach ( (array) \Voxel\get( 'settings.ipgeo.providers' ) as $enabled_geoip_provider ) {
			foreach ( \Voxel\get_ipgeo_providers() as $geoip_provider ) {
				if ( $geoip_provider['key'] === $enabled_geoip_provider['key'] ) {
					$geocode_url = $geoip_provider['geocode_url'];
					if ( ! empty( $enabled_geoip_provider['api_key'] ) && ! empty( $geoip_provider['api_key_param'] ) ) {
						$geocode_url = add_query_arg( $geoip_provider['api_key_param'], $enabled_geoip_provider['api_key'], $geocode_url );
					}

					$config['geoip_providers'][] = [
						'url' => $geocode_url,
						'prop' => $geoip_provider['country_code_key'],
					];

					break;
				}
			}
		}

		if ( ! is_user_logged_in() && $config['guest_customers']['behavior'] === 'proceed_with_email' ) {
			if ( \Voxel\get('settings.recaptcha.enabled') ) {
				wp_enqueue_script( 'google-recaptcha' );
			}
		}

		$icons = [
			'info' => \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\get_svg( 'info.svg' ),
		];

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/cart-summary.php' );

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_voxel_checkout();' );
		}
	}

	protected function _determine_customer_shipping_country_code(): ?string {
		$visitor = \Voxel\Visitor::get();
		if ( $country = $visitor->get_country() ) {
			return $country['alpha-2'];
		}

		return null;
	}

	public function get_script_depends() {
		return [
			'vx:product-summary.js',
		];
	}

	public function get_style_depends() {
		return [
			'vx:forms.css',
			'vx:product-summary.css',
			'vx:create-post.css',
		];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
