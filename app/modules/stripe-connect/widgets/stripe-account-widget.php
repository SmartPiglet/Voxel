<?php

namespace Voxel\Modules\Stripe_Connect\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stripe_Account_Widget extends \Voxel\Widgets\Base_Widget {

	public function get_name() {
		return 'ts-stripe-account';
	}

	public function get_title() {
		return __( 'Stripe Account (VX)', 'voxel-elementor' );
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
				'ts_connect_ico',
				[
					'label' => __( 'Panel icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_setup_ico',
				[
					'label' => __( 'Setup icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_submit_ico',
				[
					'label' => __( 'Submit information icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_update_ico',
				[
					'label' => __( 'Update information icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_stripe_ico',
				[
					'label' => __( 'Portal icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_chevron_left',
				[
					'label' => __( 'Left chevron', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'save_icon',
				[
					'label' => __( 'Save', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);


			$this->add_control(
				'ts_zone_ico',
				[
					'label' => __( 'Zone', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'trash_icon',
				[
					'label' => __( 'Trash', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'down_icon',
				[
					'label' => __( 'Trash', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_search_icon',
				[
					'label' => __( 'Search', 'text-domain' ),
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
				'ts_add_icon',
				[
					'label' => __( 'Plus icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'panel_options',
			[
				'label' => __( 'Panel', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'panel_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel',
				]
			);


			$this->add_responsive_control(
				'panel_radius',
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
						'{{WRAPPER}} .ts-panel' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'panel_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel' => 'background: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'panel_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel',
				]
			);

			$this->add_control(
				'panel_body',
				[
					'label' => __( 'Panel body', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'panel_spacing',
				[
					'label' => __( 'Body spacing', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ac-body' => 'padding: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'panel_gap',
				[
					'label' => __( 'Body content gap', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ac-body' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'body_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel .ac-body p',
				]
			);

			$this->add_responsive_control(
				'body_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-body p' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'panel_head',
				[
					'label' => __( 'Panel head', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'head_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .ac-head' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'head_ico_size',
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
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-panel .ac-head svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'head_ico_margin',
				[
					'label' => __( 'Icon right margin', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-panel .ac-head i' => 'margin-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-panel .ac-head svg' => 'margin-right: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'head_ico_col',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-panel .ac-head svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'head_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel .ac-head b',
				]
			);

			$this->add_responsive_control(
				'head_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head b' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'head_border_col',
				[
					'label' => __( 'Separator color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head' => 'border-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'panel_buttons',
				[
					'label' => __( 'Panel buttons', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);




			$this->add_responsive_control(
				'panel_buttons_gap',
				[
					'label' => __( 'Item gap', 'voxel-elementor' ),
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
						'{{WRAPPER}} .current-plan-btn' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);



		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf1_fields_general',
			[
				'label' => __( 'Form: Fields general', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);






			$this->add_control(
				'ts_sf1_input_lbl',
				[
					'label' => __( 'Field label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_sf1_input_label_text',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-form-group:not(.ui-heading-field) label',
				]
			);


			$this->add_responsive_control(
				'ts_sf1_input_label_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-form-group:not(.ui-heading-field) label' => 'color: {{VALUE}}',
					],

				]
			);




			$this->add_control(
				'ts1_field_req_h',
				[
					'label' => __( 'Field validation', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts1_field_req_t',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} span.is-required',
				]
			);


			$this->add_responsive_control(
				'ts1_field_req_col',
				[
					'label' => __( 'Default Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} span.is-required' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'ts1_field_req_col_err',
				[
					'label' => __( 'Error Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-form .ts-form-group' => '--required-col: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_intxt',
			[
				'label' => __( 'Form: Input & Textarea', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-form input.ts-filter::placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-form textarea.ts-filter::placeholder' => 'color: {{VALUE}}',

							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_intxt_input_input_typo',
							'label' => __( 'Typography' ),
							'selector' =>
								'{{WRAPPER}} .ts-form input.ts-filter::placeholder, .ts-form textarea.ts-filter::placeholder',
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
								'{{WRAPPER}} .ts-form input.ts-filter' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-form textarea.ts-filter' => 'color: {{VALUE}};',
							],

						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_intxt_value_typo',
							'label' => __( 'Typography' ),

							'selector' => '{{WRAPPER}} .ts-form input.ts-filter, {{WRAPPER}} .ts-form textarea.ts-filter',


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
								'{{WRAPPER}} .ts-form textarea.ts-filter' => 'background: {{VALUE}}',
								'{{WRAPPER}} .ts-form input.ts-filter' => 'background: {{VALUE}}',
							],

						]
					);




					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_intxt_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-form textarea.ts-filter, {{WRAPPER}} .ts-form input.ts-filter',


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
								'{{WRAPPER}} .ts-form input.ts-filter' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_intxt_input_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
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
								'{{WRAPPER}}  .ts-form input.ts-filter' => 'height: {{SIZE}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-form textarea.ts-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_intxt_textarea_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 1500,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'{{WRAPPER}}  .ts-form textarea.ts-filter' => 'min-height: {{SIZE}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-form textarea.ts-filter' => 'border-radius: {{SIZE}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-form textarea.ts-filter:hover' => 'background: {{VALUE}}',
								'{{WRAPPER}} .ts-form input.ts-filter:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form textarea.ts-filter:hover' => 'border-color: {{VALUE}}',
								'{{WRAPPER}} .ts-form input.ts-filter:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_placeholder_h',
						[
							'label' => __( 'Placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form input.ts-filter:hover::placeholder' => 'color: {{VALUE}}',
							],

						]

					);

					$this->add_responsive_control(
						'ts_intxt_value_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form input.ts-filter:hover' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-form textarea.ts-filter:hover' => 'color: {{VALUE}};',
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
								'{{WRAPPER}} .ts-form textarea.ts-filter:focus' => 'background: {{VALUE}}',
								'{{WRAPPER}} .ts-form input.ts-filter:focus' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_border_a',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form textarea.ts-filter:focus' => 'border-color: {{VALUE}}',
								'{{WRAPPER}} .ts-form input.ts-filter:focus' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_intxt_placeholder_a',
						[
							'label' => __( 'Placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form input.ts-filter:active::placeholder' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-form textarea.ts-filter:active::placeholder' => 'color: {{VALUE}}',

							],

						]

					);

					$this->add_responsive_control(
						'ts_intxt_value_color_a',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-form input.ts-filter:focus' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-form textarea.ts-filter:focus' => 'color: {{VALUE}};',
							],

						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_input_suffix',
			[
				'label' => __( 'Form: Input suffix', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_suffix_typo',
					'label' => __( 'Button typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .input-container .input-suffix',
				]
			);

			$this->add_responsive_control(
				'ts_suffix_text',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .input-container .input-suffix' => 'color: {{VALUE}}',
					],

				]
			);


			$this->add_responsive_control(
				'ts_suffix_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .input-container .input-suffix' => 'background: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'ts_suffix_radius',
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
						'{{WRAPPER}} .input-container .input-suffix' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'ts_suffix_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .input-container .input-suffix',
				]
			);

			$this->add_responsive_control(
				'ts_suffix_margin',
				[
					'label' => __( 'Side margin', 'voxel-elementor' ),
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
						'{{WRAPPER}} .input-container .input-suffix' => !is_rtl() ? 'right: {{SIZE}}{{UNIT}};' : 'left: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_suffix_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .input-container .input-suffix svg' => 'fill: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_field_switch',
			[
				'label' => __( 'Form: Switcher', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

				$this->add_control(
					'ts_field_switch',
					[
						'label' => __( 'Switch slider', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'ts_field_switch_bg',
					[
						'label' => __( 'Background (Inactive)', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .onoffswitch .onoffswitch-label'
							=> 'background-color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'ts_field_switch_bg_active',
					[
						'label' => __( 'Background (Active)', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .onoffswitch .onoffswitch-checkbox:checked + .onoffswitch-label'
							=> 'background-color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'ts_field_switch_bg_handle',
					[
						'label' => __( 'Handle background', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .onoffswitch .onoffswitch-label:before'
							=> 'background-color: {{VALUE}}',
						],

					]
				);

		$this->end_controls_section();

		$this->start_controls_section(
				'ts_sf_styling_filters',
				[
					'label' => __( 'Form: Popup button', 'voxel-elementor' ),
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
			'ts_form_heading',
			[
				'label' => __( 'Form: Heading', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'ts_form_heading_text',
						'label' => __( 'Typography' ),
						'selector' => '{{WRAPPER}} .create-form-step > .ts-form-group.ui-heading-field label',
					]
				);


				$this->add_responsive_control(
					'ts_form_heading_col',
					[
						'label' => __( 'Color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .create-form-step > .ts-form-group.ui-heading-field label' => 'color: {{VALUE}}',
						],

					]
				);


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_repeater',
			[
				'label' => __( 'Form: Repeater', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$this->add_control(
				'ts_fh_btn_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-field-repeater'
						=> 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'ts_repeater_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-field-repeater',
				]
			);

			$this->add_responsive_control(
				'ts_repeater_radius',
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
						'{{WRAPPER}} .ts-field-repeater' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'ts_repeater_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-field-repeater',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_repeater_head',
			[
				'label' => __( 'Form: Repeater head', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_control(
				'repeater_secondary_heading',
				[
					'label' => __( 'Secondary text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'rhead_stext_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-repeater-head em' => 'color: {{VALUE}}',
					],

				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'rhead_stext_type',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-repeater-head em',
				]
			);



			$this->add_control(
				'repeater_head_other',
				[
					'label' => __( 'Other', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);



			$this->add_responsive_control(
				'rhead_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-repeater-head > i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-repeater-head > svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'rhead_icon_color_s',
				[
					'label' => __( 'Icon color (Success)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-field-repeater.v-checked .ts-repeater-head > i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-field-repeater.v-checked .ts-repeater-head > svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'rhead_icon_color_v',
				[
					'label' => __( 'Icon color (Warning)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-field-repeater.v-error .ts-repeater-head > i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-field-repeater.v-error .ts-repeater-head > svg' => 'fill: {{VALUE}}',
					],

				]
			);



			$this->add_responsive_control(
				'rhead_border_color',
				[
					'label' => __( 'Border color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-repeater-head' => 'border-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'rhead_border_width',
				[
					'label' => __( 'Border width', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-repeater-head' => 'border-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'repeater_icon_button',
			[
				'label' => __( 'Repeater: Icon button', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-field-repeater .ts-smaller i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-field-repeater .ts-smaller svg'
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
								'{{WRAPPER}} .ts-field-repeater .ts-smaller' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'repeat_number_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-field-repeater .ts-smaller',
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
								'{{WRAPPER}} .ts-field-repeater .ts-smaller' => 'border-radius: {{SIZE}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-field-repeater .ts-smaller:hover i'
								=> 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-field-repeater .ts-smaller:hover svg'
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
								'{{WRAPPER}} .ts-field-repeater .ts-smaller:hover'
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
								'{{WRAPPER}} .ts-field-repeater .ts-smaller:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_var_buttons',
			[
				'label' => __( 'Form: Classes', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_var_buttons_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_var_buttons_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_var_btn_typo',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .attribute-select a',
						]
					);



					$this->add_responsive_control(
						'ts_var_btn_radius',
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
								'{{WRAPPER}} .attribute-select a' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);




					$this->add_responsive_control(
						'ts_var_btn_text',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .attribute-select a' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_var_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .attribute-select a' => 'background: {{VALUE}}',
							],

						]
					);




				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_var_buttons_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_var_btn_t_hover',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .attribute-select a:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_var_btn_bg_hover',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .attribute-select a:hover' => 'background-color: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();

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
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_sf_form_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
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
								'{{WRAPPER}} .ts-btn-2' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_form_btn_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);


					$this->add_responsive_control(
						'ts_sf_form_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_sf_form_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'background: {{VALUE}}',
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
								'{{WRAPPER}} .ts-btn-2 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-2 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_color',
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
								'{{WRAPPER}} .ts-btn-2' => 'grid-gap: {{SIZE}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-btn-2:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_bg_hover',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_bo_hover',
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
							'name' => 'ts_sf_form_btn_shadow_h',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2:hover',
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_color_h',
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
			'scnd_btn',
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
							'selector' => '{{WRAPPER}} .ts-btn-1',
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
							'default' => [
								'unit' => 'px',
								'size' => 5,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => 'color: {{VALUE}}',
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
								'{{WRAPPER}} .ts-btn-1' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
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
								'{{WRAPPER}}  .ts-btn-1' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$this->add_responsive_control(
						'scnd_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'scnd_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-1',
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
								'{{WRAPPER}} .ts-btn-1 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-1 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_icon_pad',
						[
							'label' => __( 'Icon right padding', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-1 i' => 'padding-right: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-1 svg' => 'margin-right: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'scnd_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1 i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-1 svg' => 'fill: {{VALUE}}',
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
								'{{WRAPPER}} .ts-btn-1:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'scnd_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-1:hover svg' => 'fill: {{VALUE}}',
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
								'{{WRAPPER}} .ts-btn-4 i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-4 svg'
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
								'{{WRAPPER}} .ts-btn-4 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-4 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'tertiary_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-4'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'tertiary_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-4',
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
								'{{WRAPPER}} .ts-btn-4' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'tertiary_btn_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .ts-btn-4',
						]
					);

					$this->add_control(
						'tertiary_btn_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-4'
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
								'{{WRAPPER}} .ts-btn-4:hover i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-4:hover svg'
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
								'{{WRAPPER}} .ts-btn-4:hover'
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
								'{{WRAPPER}} .ts-btn-4:hover'
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
								'{{WRAPPER}} .ts-btn-4:hover'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

	}

	protected function render( $instance = [] ) {
		if ( ! is_user_logged_in() ) {
			printf( '<p class="ts-restricted">%s</p>', _x( 'You must be logged in to view this content.', 'orders widget', 'voxel-elementor' ) );
			return;
		}

		if ( ! \Voxel\get( 'payments.stripe.stripe_connect.enabled' ) ) {
			printf( '<p class="ts-restricted">%s</p>', _x( 'This feature has not been enabled.', 'orders widget', 'voxel-elementor' ) );
			return;
		}

		$user = \Voxel\get_current_user();

		// update account details after completing onboarding
		if ( isset( $_GET['onboarding_key'] ) ) {
			if ( $_GET['onboarding_key'] === get_user_meta( $user->get_id(), 'voxel:connect_onboarding_key', true ) ) {
				delete_user_meta( $user->get_id(), 'voxel:connect_onboarding_key' );
				try {
					$user->stripe_vendor_updated( $user->get_stripe_vendor() );
					wp_add_inline_script(
						'vx:commons.js',
						'window.addEventListener("DOMContentLoaded", () => Voxel.deleteSearchParam("onboarding_key"))'
					);
				} catch ( \Exception $e ) {}
			}
		}

		$account = $user->get_stripe_vendor_details();

		$onboard_link = add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_connect.account.onboard',
		], home_url('/') );

		$dashboard_link = add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_connect.account.login',
		], home_url('/') );

		$config = [
			'nonce' => wp_create_nonce('vx_vendor_dashboard'),
		];

		if ( \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) === 'vendor' ) {
			$config['shipping_countries'] = \Voxel\Modules\Stripe_Payments\Country_Codes::shipping_supported();
			$config['shipping_classes'] = array_map( function( $shipping_class ) {
				return [
					'key' => $shipping_class->get_key(),
					'label' => $shipping_class->get_label(),
				];
			}, \Voxel\Product_Types\Shipping\Shipping_Class::get_all() );
			$config['zones'] = $user->get_vendor_shipping_zones_config();
			$config['l10n'] = [
				'countries_selected' => _x( '@count countries selected', 'stripe vendor shipping', 'voxel' ),
			];
		}

		// dump($user->get_stripe_vendor());

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'app/modules/stripe-connect/templates/frontend/stripe-account-widget.php' );

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_stripe_connect_dashboard();' );
		}
	}

	public function get_script_depends() {
		return [
			'vx:stripe-connect-dashboard.js',
		];
	}

	public function get_style_depends() {
		return [
			'vx:forms.css',
			'vx:create-post.css',
		];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
