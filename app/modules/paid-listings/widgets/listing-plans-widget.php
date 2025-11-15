<?php

namespace Voxel\Modules\Paid_Listings\Widgets;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Listing_Plans_Widget extends \Voxel\Widgets\Base_Widget {

	public function get_name() {
		return 'ts-listing-plans';
	}

	public function get_title() {
		return __( 'Listing plans (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		$plans = Module\Listing_Plan::all();
		$options = [];

		foreach ( $plans as $plan ) {
			$options[ $plan->get_key() ] = $plan->get_label();
		}

	

	$this->start_controls_section( 'ts_prices_section', [
		'label' => __( 'Price groups', 'voxel-elementor' ),
		'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
	] );

	$repeater = new \Elementor\Repeater;
	$repeater->add_control( 'group_label', [
		'label' => __( 'Group label', 'voxel-elementor' ),
		'type' => \Elementor\Controls_Manager::TEXT,
		'default' => '',
	] );

		$repeater->add_control( 'prices', [
			'label' => __( 'Choose prices', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT2,
			'multiple' => true,
			'options' => $options,
			'label_block' => true,
		] );

			$this->add_control( 'ts_price_groups', [
				'label' => __( 'Items', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'_disable_loop' => true,
				'title_field' => '{{{ group_label }}}',
			] );

			$this->end_controls_section();



			$this->start_controls_section(
				'plans_general',
				[
					'label' => __( 'General', 'voxel-elementor' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'plans_columns',
					[
						'label' => __( 'Number of columns', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'min' => 1,
						'max' => 5,
						'step' => 1,
						'selectors' => [
							'{{WRAPPER}} .ts-plans-list' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
						],
					]
				);

				$this->add_responsive_control(
					'pplans_gap',
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
							'{{WRAPPER}} .ts-plans-list' => 'grid-gap: {{SIZE}}{{UNIT}};',
						],

					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'pplans_border',
						'label' => __( 'Border', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-container',
					]
				);


				$this->add_responsive_control(
					'pplans_radius',
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
							'{{WRAPPER}} .ts-plan-container' => 'border-radius: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'pplans_bg',
					[
						'label' => __( 'Background', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-plan-container' => 'background: {{VALUE}}',
						],

					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'pplans_shadow',
						'label' => __( 'Box Shadow', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-container',
					]
				);

				

				$this->add_control(
					'plan_body',
					[
						'label' => __( 'Plan body', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				

				$this->add_responsive_control(
					'pplans_spacing',
					[
						'label' => __( 'Body padding', 'voxel-elementor' ),
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
							'{{WRAPPER}} .ts-plan-body' => 'padding: {{SIZE}}{{UNIT}};',
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
						],
						'selectors' => [
							'{{WRAPPER}} .ts-plan-body' => 'grid-gap: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'plan_image',
					[
						'label' => __( 'Plan image', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_responsive_control(
					'plan_img_pad',
					[
						'label' => __( 'Image padding', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em' ],
						'selectors' => [
							'{{WRAPPER}} .ts-plan-image img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'plan_img_max',
					[
						'label' => __( 'height', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 500,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ts-plan-image img' => 'height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'panel_pricing',
					[
						'label' => __( 'Plan pricing', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'pricing_align',
					[
						'label' => __( 'Align', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'flex-start',
						'options' => [
							'flex-start'  => __( 'Left', 'voxel-elementor' ),
							'center' => __( 'Center', 'voxel-elementor' ),
							'flex-end' => __( 'Right', 'voxel-elementor' ),
						],

						'selectors' => [
							'{{WRAPPER}} .ts-plan-pricing' => 'justify-content: {{VALUE}}',
						],
					]
				);
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'price_typo',
						'label' => __( 'Price typography', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-price',
					]
				);

				$this->add_responsive_control(
					'price_col',
					[
						'label' => __( 'Price text color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-plan-price' => 'color: {{VALUE}}',
						],

					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'period_typo',
						'label' => __( 'Period typography', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-price-period',
					]
				);

				$this->add_responsive_control(
					'period_col',
					[
						'label' => __( 'Period text color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-price-period' => 'color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'plan_name_section',
					[
						'label' => __( 'Plan name', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'content_align',
					[
						'label' => __( 'Align content', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'flex-start',
						'options' => [
							'flex-start'  => __( 'Left', 'voxel-elementor' ),
							'center' => __( 'Center', 'voxel-elementor' ),
							'flex-end' => __( 'Right', 'voxel-elementor' ),
						],

						'selectors' => [
							'{{WRAPPER}} .ts-plan-details' => 'justify-content: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'name_typo',
						'label' => __( 'Name typography', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-name',
					]
				);

				$this->add_responsive_control(
					'name_col',
					[
						'label' => __( 'Name text color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-plan-name' => 'color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'plan_desc_section',
					[
						'label' => __( 'Plan description', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'desc_align',
					[
						'label' => __( 'Text align', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'left',
						'options' => [
							'left'  => __( 'Left', 'voxel-elementor' ),
							'center' => __( 'Center', 'voxel-elementor' ),
							'right' => __( 'Right', 'voxel-elementor' ),
						],

						'selectors' => [
							'{{WRAPPER}} .ts-plan-desc p' => 'text-align: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'desc_typo',
						'label' => __( 'Typography', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-desc p',
					]
				);

				$this->add_responsive_control(
					'desc_col',
					[
						'label' => __( 'Color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-plan-desc p' => 'color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'plan_list_section',
					[
						'label' => __( 'Plan features', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'list_align',
					[
						'label' => __( 'Align content', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'flex-start',
						'options' => [
							'flex-start'  => __( 'Left', 'voxel-elementor' ),
							'center' => __( 'Center', 'voxel-elementor' ),
							'flex-end' => __( 'Right', 'voxel-elementor' ),
						],

						'selectors' => [
							'{{WRAPPER}} .ts-plan-features ul' => 'align-items: {{VALUE}}',
						],
					]
				);



				$this->add_responsive_control(
					'list_gap',
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
							'{{WRAPPER}} .ts-plan-features ul' => 'grid-gap: {{SIZE}}{{UNIT}};',
						],

					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'list_typo',
						'label' => __( 'Typography', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-features ul li span',
					]
				);

				$this->add_responsive_control(
					'list_col',
					[
						'label' => __( 'Color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-plan-features ul li span' => 'color: {{VALUE}}',
						],

					]
				);

				$this->add_responsive_control(
					'list_ico_col',
					[
						'label' => __( 'Icon color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ts-plan-features ul li i' => 'color: {{VALUE}}',
							'{{WRAPPER}} .ts-plan-features ul li svg' => 'fill: {{VALUE}}',
						],

					]
				);

				$this->add_responsive_control(
					'list_ico_size',
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
							'{{WRAPPER}} .ts-plan-features ul li i' => 'font-size: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ts-plan-features ul li svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'list_ico_right_pad',
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
							'{{WRAPPER}} .ts-plan-features ul li i' => 'padding-right: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ts-plan-features ul li svg' => 'margin-right: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'featured_plan',
					[
						'label' => __( 'Featured plan', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'featured_border',
						'label' => __( 'Border', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-container.plan-featured',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'featured_shadow',
						'label' => __( 'Box Shadow', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .ts-plan-container.plan-featured',
					]
				);

				$this->add_control(
					'featured_badge_color',
					[
						'label' => __( 'Badge color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} span.ts-plan-featured-text' => 'background-color: {{VALUE}}',
						],

					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'badge_text',
						'label' => __( 'Tab typography' ),
						'selector' => '{{WRAPPER}} span.ts-plan-featured-text',
					]
				);




			$this->end_controls_section();

			$this->start_controls_section(
				'pltabs_section',
				[
					'label' => __( 'Tabs', 'voxel-elementor' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->start_controls_tabs(
					'pltabs_tabs'
				);

					/* Normal tab */

					$this->start_controls_tab(
						'pltabs_normal',
						[
							'label' => __( 'Normal', 'voxel-elementor' ),
						]
					);


						$this->add_control(
							'pltabs_tabs_heading',
							[
								'label' => __( 'Tabs', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

						$this->add_control(
							'pltabs_disable',
							[
								'label' => __( 'Disable tabs', 'voxel-elementor' ),
								'description' => __( 'Disable label on tablet', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::SWITCHER,

								'return_value' => 'none',
								'selectors' => [
									'{{WRAPPER}} .ts-plan-tabs' => 'display: none;',
								],
							]
						);

						$this->add_control(
							'pltabs_justify',
							[
								'label' => __( 'Justify', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::SELECT,
								'default' => 'flex-start',
								'options' => [
									'flex-start'  => __( 'Left', 'voxel-elementor' ),
									'center' => __( 'Center', 'voxel-elementor' ),
									'flex-end' => __( 'Right', 'voxel-elementor' ),
									'space-between' => __( 'Space between', 'voxel-elementor' ),
									'space-around' => __( 'Space around', 'voxel-elementor' ),
								],

								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs' => 'justify-content: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'pltabs_padding',
							[
								'label' => __( 'Padding', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::DIMENSIONS,
								'size_units' => [ 'px', '%', 'em' ],
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
								],
							]
						);

						$this->add_control(
							'pltabs_margin',
							[
								'label' => __( 'Margin', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::DIMENSIONS,
								'size_units' => [ 'px', '%', 'em' ],
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Typography::get_type(),
							[
								'name' => 'pltabs_text',
								'label' => __( 'Tab typography' ),
								'selector' => '{{WRAPPER}} .ts-generic-tabs li a',
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Typography::get_type(),
							[
								'name' => 'pltabs_active',
								'label' => __( 'Active tab typography' ),
								'selector' => '{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a',
							]
						);


						$this->add_control(
							'pltabs_text_color',
							[
								'label' => __( 'Text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li a' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_active_text_color',
							[
								'label' => __( 'Active text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_bg_color',
							[
								'label' => __( 'Background', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li a' => 'background-color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_bg_active_color',
							[
								'label' => __( 'Active background', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a' => 'background-color: {{VALUE}}',
								],

							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' => 'pltabs_border',
								'label' => __( 'Border', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} .ts-generic-tabs li a',
							]
						);

						$this->add_control(
							'pltabs_border_active',
							[
								'label' => __( 'Active border color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a' => 'border-color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_radius',
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
									'{{WRAPPER}} .ts-generic-tabs li a' => 'border-radius: {{SIZE}}{{UNIT}};',
								],
							]
						);


					$this->end_controls_tab();

					/* Hover tab */

					$this->start_controls_tab(
						'pltabs_hover',
						[
							'label' => __( 'Hover', 'voxel-elementor' ),
						]
					);

						$this->add_control(
							'pltabs_tabs_h',
							[
								'label' => __( 'Timeline tabs', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

						$this->add_control(
							'pltabs_text_color_h',
							[
								'label' => __( 'Text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li a:hover' => 'color: {{VALUE}}',
								],

							]
						);



						$this->add_control(
							'pltabs_active_text_color_h',
							[
								'label' => __( 'Active text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a:hover' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_border_color_h',
							[
								'label' => __( 'Border color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li a:hover' => 'border-color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_border_h_active',
							[
								'label' => __( 'Active border color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a:hover' => 'border-color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_bg_color_h',
							[
								'label' => __( 'Background', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li a:hover' => 'background-color: {{VALUE}}',
								],

							]
						);

						$this->add_control(
							'pltabs_active_color_h',
							[
								'label' => __( 'Active background', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-generic-tabs li.ts-tab-active a:hover' => 'background-color: {{VALUE}}',
								],

							]
						);


					$this->end_controls_tab();

				$this->end_controls_tabs();

			$this->end_controls_section();

			$this->start_controls_section(
				'primary_btn',
				[
					'label' => __( 'Primary button', 'voxel-elementor' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->start_controls_tabs(
					'primary_btn_tabs'
				);

					/* Normal tab */

					$this->start_controls_tab(
						'primary_btn_normal',
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
							'primary_btn_padding',
							[
								'label' => __( 'Padding', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::DIMENSIONS,
								'size_units' => [ 'px', '%', 'em' ],
								'selectors' => [
									'{{WRAPPER}} .ts-btn-2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'primary_btn_height',
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
									'{{WRAPPER}}  .ts-btn-2' => 'height: {{SIZE}}{{UNIT}};',
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

						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' => 'primary_btn_border',
								'label' => __( 'Border', 'voxel-elementor' ),
								'selector' => '{{WRAPPER}} .ts-btn-2',
							]
						);


						$this->add_responsive_control(
							'primary_btn_icon_size',
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
									'{{WRAPPER}} .ts-btn-2 i' => 'font-size: {{SIZE}}{{UNIT}};',
									'{{WRAPPER}} .ts-btn-2 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'primary_btn_icon_pad',
							[
								'label' => __( 'Text/Icon spacing', 'voxel-elementor' ),
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
									'{{WRAPPER}} .ts-btn-2' => 'grid-gap: {{SIZE}}{{UNIT}};padding-right: 0px;',
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
					$this->end_controls_tab();
					/* Hover tab */

					$this->start_controls_tab(
						'primary_btn_hover',
						[
							'label' => __( 'Hover', 'voxel-elementor' ),
						]
					);

						$this->add_responsive_control(
							'primary_btn_c_h',
							[
								'label' => __( 'Text color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-btn-2:hover' => 'color: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'primary_btn_bg_h',
							[
								'label' => __( 'Background color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-btn-2:hover' => 'background: {{VALUE}}',
								],

							]
						);

						$this->add_responsive_control(
							'primary_btn_border_h',
							[
								'label' => __( 'Border color', 'voxel-elementor' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ts-btn-2:hover' => 'border-color: {{VALUE}}',
								],

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
								'label' => __( 'Text/Icon spacing', 'voxel-elementor' ),
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
									'{{WRAPPER}} .ts-btn-1' => 'grid-gap: {{SIZE}}{{UNIT}};',
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
				'ts_dialog',
				[
					'label' => __( 'Dialog', 'voxel-elementor' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
	
				$this->add_control(
					'ts_dialog_color',
					[
						'label' => __( 'Text color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .vx-dialog-content' => 'color: {{VALUE}}',
						],
					]
				);
	
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'ts_dialog_typo',
						'label' => __( 'Typography', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .vx-dialog-content',
					]
				);
				$this->add_control(
					'ts_dialog_bg',
					[
						'label' => __( 'Background color', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .vx-dialog-content' => 'background-color: {{VALUE}}',
						],
					]
				);
	
				$this->add_responsive_control(
					'ts_dialog_radius',
					[
						'label' => __( 'Radius', 'voxel-elementor' ),
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
							'{{WRAPPER}} .vx-dialog-content' => 'border-radius: {{SIZE}}{{UNIT}};',
						],
					]
				);
	
				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'ts_dialog_shadow',
						'label' => __( 'Box Shadow', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .vx-dialog-content',
					]
				);
	
				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'ts_dialog_shadow',
						'label' => __( 'Box Shadow', 'voxel-elementor' ),
						'selector' => '{{WRAPPER}} .vx-dialog-content',
					]
				);
	
	
	
			$this->end_controls_section();

			$this->start_controls_section(
				'ts_ui_icons',
				[
					'label' => __( 'Icons', 'voxel-elementor' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);



				$this->add_control(
					'ts_arrow_right',
					[
						'label' => __( 'Right arrow', 'text-domain' ),
						'type' => \Elementor\Controls_Manager::ICONS,
					]
				);


			$this->end_controls_section();
		foreach ( $plans as $plan ) {
			$key = sprintf( 'ts_plan__%s', $plan->get_key() );

			$this->start_controls_section( $key.':section', [
				'label' => sprintf( __( 'Plan: %s', 'voxel-elementor' ), $plan->get_label() ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			] );

			$this->add_control( $key.'__image', [
				'label' => __( 'Choose image', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
			] );

			$repeater = new \Elementor\Repeater;
			$repeater->add_control( 'text', [
				'label' => __( 'Text', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			] );

			$repeater->add_control(
				'feature_ico',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);
			$this->add_control( $key.'__features', [
				'label' => __( 'Features', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'_disable_loop' => true,
				'title_field' => '{{{ text }}}',
				'prevent_empty' => false,
			] );

			$this->add_control( $key.'__featured', [
				'label' => __( 'Mark as featured', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'voxel-elementor' ),
				'label_off' => __( 'No', 'voxel-elementor' ),
				'return_value' => 'yes',
				
			] );

			$this->add_control( $key.'__featured_text', [
				'label' => __( 'Featured text', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Featured',
				'placeholder' => __( 'e.g. Featured', 'voxel-elementor' ),
				'condition' => [ $key.'__featured' => 'yes' ],
			] );
			

			$this->end_controls_section();
		}

		$this->start_controls_section( 'ts_general_section', [
			'label' => __( 'Fallback redirect', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );
	
		$this->add_control( 'ts_fallback_redirect', [
			'label' => __( 'Fallback redirect URL', 'voxel-elementor' ),
			'description' => __( 'Where to redirect users after buying a plan (when widget is not used in the create post flow)', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::URL,
			'placeholder' => home_url('/'),
			'show_external' => false,
		] );
	
		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$process = \Voxel\from_list( $_GET['process'] ?? null, [ 'new', 'relist', 'claim', 'switch' ], null );
		if ( $process === 'new' ) {
			$post_type = \Voxel\Post_Type::get( $_GET['item_type'] ?? null );
			$post = null;

			if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
				return null;
			}
		} elseif ( $process === 'relist' ) {
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( ! (
				$post
				&& $post->post_type
				&& $post->is_editable_by_current_user()
				&& $post->get_status() === 'expired'
			) ) {
				return null;
			}

			$post_type = $post->post_type;
		} elseif ( $process === 'switch' ) {
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( ! (
				$post
				&& $post->post_type
				&& $post->is_editable_by_current_user()
				&& $post->get_status() === 'publish'
				&& Module\has_plans_for_post_type( $post->post_type )
			) ) {
				return null;
			}

			$post_type = $post->post_type;
		} elseif ( $process === 'claim' ) {
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( ! ( $post && \Voxel\Modules\Claim_Listings\is_claimable( $post ) ) ) {
				return null;
			}

			$post_type = $post->post_type;
		} else {
			$post = null;
			$post_type = null;
		}

	$groups = $this->get_settings_for_display( 'ts_price_groups' );
	$plans = [];
	
	// Get fallback redirect URL from widget settings
	$fallback_redirect_settings = $this->get_settings_for_display( 'ts_fallback_redirect' );
	$fallback_redirect_url = ! empty( $fallback_redirect_settings['url'] ) ? $fallback_redirect_settings['url'] : null;
	
	// Use redirect_to from GET param, or fallback to widget setting
	$redirect_to = $_GET['redirect_to'] ?? $fallback_redirect_url;
	
	foreach ( $groups as $group ) {
		if ( ! is_array( $group['prices'] ) || empty( $group['prices'] ) ) {
			continue;
		}

		foreach ( $group['prices'] as $plan_key ) {
			try {
				$link = add_query_arg( [
					'action' => 'paid_listings.choose_plan',
					'plan' => $plan_key,
					'redirect_to' => $redirect_to,
					'process' => $process,
					'item_type' => $post_type?->get_key(),
					'post_id' => $post?->get_id(),
					'_wpnonce' => wp_create_nonce( 'vx_choose_plan' ),
				], home_url('/?vx=1') );

					$plan = Module\Listing_Plan::get( $plan_key );
					if ( $plan === null ) {
						continue;
					}

					$billing = [
						'is_free' => floatval( $plan->get_billing_amount() ) === 0.0,
						'amount' => \Voxel\currency_format(
							$plan->get_billing_amount(),
							strtoupper( \Voxel\get_primary_currency() ),
							false
						),
						'discount_amount' => ( $plan->get_billing_discount_amount() !== null )
							? \Voxel\currency_format(
								$plan->get_billing_discount_amount(),
								strtoupper( \Voxel\get_primary_currency() ),
								false
							) : null,
						'period' => ( $plan->get_billing_mode() === 'subscription' )
							? \Voxel\interval_format(
								$plan->get_billing_interval(),
								$plan->get_billing_frequency()
							)
							: null,
					];

					$plans[] = [
						'key' => $plan->get_key(),
						'group' => $group['_id'],
						'label' => $plan->get_label(),
						'description' => $plan->get_description(),
						'image' => $this->_get_plan_image( $plan->get_key() ),
						'features' => $this->_get_plan_features( $plan->get_key() ),
						'featured' => ( $this->get_settings_for_display( sprintf( 'ts_plan__%s__featured', $plan->get_key() ) ) === 'yes' ),
						'featured_text' => $this->get_settings_for_display( sprintf( 'ts_plan__%s__featured_text', $plan->get_key() ) ),
						'link' => $link,
						'billing' => $billing,
						'supported_post_types' => $plan->get_supported_post_types(),
					];
				} catch ( \Exception $e ) {
					if ( \Voxel\is_dev_mode() ) {
						// dump($plan_key.': '.$e->getMessage());
					}
				}
			}
		}

		if ( is_user_logged_in() ) {
			$current_user = \Voxel\get_current_user();
			$packages_by_plan = [];

			if ( $post_type !== null ) {
				$packages = Module\get_available_packages( $current_user, $post_type );

				foreach ( $packages as $package ) {
					$plan = $package->get_plan();
					if ( ! $plan ) {
						continue;
					}

					if ( ! isset( $packages_by_plan[ $plan->get_key() ] ) ) {
						$packages_by_plan[ $plan->get_key() ] = [
							'total' => 0,
							'used' => 0,
							'package_id' => null,
						];
					}

					foreach ( $package->get_limits() as $limit ) {
						if (
							in_array( $post_type->get_key(), $limit['post_types'], true )
							&& $limit['total'] > $limit['usage']['count']
						) {
							$packages_by_plan[ $plan->get_key() ]['total'] += $limit['total'];
							$packages_by_plan[ $plan->get_key() ]['used'] += $limit['usage']['count'];

							if ( $packages_by_plan[ $plan->get_key() ]['package_id'] === null ) {
								$packages_by_plan[ $plan->get_key() ]['package_id'] = $package->get_id();
							}
						}
					}
				}

				// filter out plans that don't support the requested post type
				$plans = array_values( array_filter( $plans, function( $plan ) use ( $post_type ) {
					return in_array( $post_type->get_key(), $plan['supported_post_types'], true );
				} ) );
			}
		}

		if ( empty( $plans ) ) {
			return null;
		}

		$default_group = $groups[0]['_id'];

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'app/modules/paid-listings/templates/frontend/listing-plans-widget.php' );
	}

	public function get_style_depends() {
		return [ 'vx:pricing-plan.css' ];
	}

	public function get_script_depends() {
		return [
			'vx:listing-plans-widget.js',
		];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}

	private function _get_plan_image( $plan_key ) {
		if ( $this->get_settings_for_display( sprintf( 'ts_plan__%s__image', $plan_key ) ) ) {
			return \Elementor\Group_Control_Image_Size::get_attachment_image_html(
				$this->get_settings_for_display(),
				'thumbnail',
				sprintf( 'ts_plan__%s__image', $plan_key )
			);
		}

		return '';
	}

	private function _get_plan_features( $plan_key ) {
		return $this->get_settings_for_display( sprintf( 'ts_plan__%s__features', $plan_key ) );
	}

}
