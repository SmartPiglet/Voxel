<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Work_Hours extends Base_Widget {

	public function get_name() {
		return 'ts-work-hours';
	}

	public function get_title() {
		return __( 'Work hours (VX)', 'voxel-elementor' );
	}


	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_wh_general',
			[
				'label' => __( 'General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$post_type = \Voxel\get_current_post_type();
			if ( $post_type ) {
				$options = [ '' => 'Choose field' ];
				foreach ( $post_type->get_fields() as $field ) {
					if ( $field->get_type() === 'work-hours' ) {
						$options[ $field->get_key() ] = $field->get_label();
					}
				}

				$this->add_control( 'ts_source_field', [
					'label' => __( 'Work hours field', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'work-hours',
					'label_block' => true,
					'options' => $options,
				] );
			}

			$this->add_control(
				'ts_wh_collapse',
				[
					'label' => __( 'Collapse', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'wh-default',
					'options' => [
						'wh-default'  => __( 'Yes', 'voxel-elementor' ),
						'wh-expanded' => __( 'No', 'voxel-elementor' ),
					],
				]
			);



			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'ts_wh_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-work-hours',
				]
			);

			$this->add_responsive_control(
				'ts_wh_radius',
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
						'{{WRAPPER}} .ts-work-hours' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'ts_wh_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-work-hours',
				]
			);



		$this->end_controls_section();

		$this->start_controls_section(
			'ts_wh_top',
			[
				'label' => __( 'Top area', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_wh_top_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-hours-today' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'top_icon_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-open-status i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-open-status svg' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_label_text',
					'label' => __( 'Label typography' ),
					'selector' => '{{WRAPPER}} .ts-hours-today .ts-open-status p',
				]
			);

			$this->add_control(
				'ts_label_color',
				[
					'label' => __( 'Label color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-hours-today .ts-open-status p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_small_text',
					'label' => __( 'Current hours typography' ),
					'selector' => '{{WRAPPER}} .ts-hours-today .ts-current-period',
				]
			);

			$this->add_control(
				'ts_small_color',
				[
					'label' => __( 'Current hours color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-hours-today .ts-current-period' => 'color: {{VALUE}}',
					],
				]
			);



			$this->add_responsive_control(
				'ts_whtop_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}} .ts-hours-today' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_wh_body',
			[
				'label' => __( 'Body', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_wh_body_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-work-hours-list ul' => 'background: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'separate_color',
				[
					'label' => __( 'Separator color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-work-hours-list li' => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_Blabel_text',
					'label' => __( 'Day typography' ),
					'selector' => '{{WRAPPER}} .ts-work-hours-list li p',
				]
			);

			$this->add_control(
				'ts_Blabel_color',
				[
					'label' => __( 'Day color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-work-hours-list li p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_Bsmall_text',
					'label' => __( 'Hours typography' ),
					'selector' => '{{WRAPPER}} .ts-work-hours-list li small',
				]
			);

			$this->add_control(
				'ts_Bsmall_color',
				[
					'label' => __( 'Hours color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-work-hours-list li small' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_whbody_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}} .ts-work-hours-list li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_wh_open',
			[
				'label' => __( 'Open', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_wh_open_icon',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_wh_open_text',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Open now', 'voxel-elementor' ),
					'placeholder' => __( 'Enter label', 'voxel-elementor' ),
				]
			);

			$this->add_control(
				'ts_wh_open_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.open i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-open-status.open svg' => 'fill: {{VALUE}}',
					],
				]
			);


			$this->add_control(
				'ts_wh_open_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.open p' => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		// $this->start_controls_section(
		// 	'ts_wh_opening',
		// 	[
		// 		'label' => __( 'Opening soon', 'voxel-elementor' ),
		// 		'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		// 	]
		// );

		// 	$this->add_control(
		// 		'ts_wh_opening_icon',
		// 		[
		// 			'label' => __( 'Icon', 'text-domain' ),
		// 			'type' => \Elementor\Controls_Manager::ICONS,
		// 		]
		// 	);

		// 	$this->add_control(
		// 		'ts_wh_opening_text',
		// 		[
		// 			'label' => __( 'Label', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::TEXT,
		// 			'default' => __( 'Opening soon', 'voxel-elementor' ),
		// 			'placeholder' => __( 'Enter label', 'voxel-elementor' ),
		// 		]
		// 	);

		// 	$this->add_control(
		// 		'ts_wh_opening_icon_color',
		// 		[
		// 			'label' => __( 'Icon color', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-open-status.opening-soon i' => 'color: {{VALUE}}',
		// 				'{{WRAPPER}} .ts-open-status.opening-soon svg' => 'fill: {{VALUE}}',
		// 			],
		// 		]
		// 	);


		// 	$this->add_control(
		// 		'ts_wh_opening_text_color',
		// 		[
		// 			'label' => __( 'Text color', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-open-status.opening-soon p' => 'color: {{VALUE}}',
		// 			],
		// 		]
		// 	);

		// $this->end_controls_section();

		$this->start_controls_section(
			'ts_wh_closed',
			[
				'label' => __( 'Closed', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_wh_closed_icon',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_wh_closed_text',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Closed', 'voxel-elementor' ),
					'placeholder' => __( 'Enter label', 'voxel-elementor' ),
				]
			);

			$this->add_control(
				'ts_wh_closed_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.closed i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-open-status.closed svg' => 'fill: {{VALUE}}',
					],
				]
			);


			$this->add_control(
				'ts_wh_closed_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.closed p' => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_wh_appointment',
			[
				'label' => __( 'Appointment only', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_wh_appt_icon',
				[
					'label' => __( 'Appointment only', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_wh_appt_text',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Appointment only', 'voxel-elementor' ),
					'placeholder' => __( 'Enter label', 'voxel-elementor' ),
				]
			);

			$this->add_control(
				'ts_wh_appointment_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.appt-only i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-open-status.appt-only svg' => 'fill: {{VALUE}}',
					],
				]
			);


			$this->add_control(
				'ts_wh_appointment_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.appt-only p' => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_wh_na',
			[
				'label' => __( 'Not available', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_wh_na_icon',
				[
					'label' => __( 'Not available', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_wh_na_text',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Not available', 'voxel-elementor' ),
					'placeholder' => __( 'Enter label', 'voxel-elementor' ),
				]
			);

			$this->add_control(
				'ts_wh_na_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.not-available i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-open-status.not-available svg' => 'fill: {{VALUE}}',
					],
				]
			);


			$this->add_control(
				'ts_wh_na_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-open-status.not-available p' => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'ts_ui_icons',
			[
				'label' => __( 'Icons', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'down_icon',
				[
					'label' => __( 'Down arrow icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);



		$this->end_controls_section();
		$this->start_controls_section(
			'acc_buttons',
			[
				'label' => __( 'Accordion button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->start_controls_tabs(
				'acc_buttons_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'acc_buttons_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_responsive_control(
						'acc_btn_size',
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
								'{{WRAPPER}} .ts-icon-btn.ts-smaller' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'acc_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn.ts-smaller i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-icon-btn.ts-smaller svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'acc_btn_icon_size',
						[
							'label' => __( 'Button icon size', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-icon-btn.ts-smaller i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-icon-btn.ts-smaller svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'acc_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn.ts-smaller' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'acc_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-icon-btn.ts-smaller',
						]
					);

					$this->add_responsive_control(
						'ts_acc_btn_radius',
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
								'{{WRAPPER}} .ts-icon-btn.ts-smaller' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);




				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'acc_buttons_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'acc_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn.ts-smaller:hover i' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-icon-btn.ts-smaller:hover svg' => 'fill: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'acc_btn_bg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn.ts-smaller:hover'
								=> 'background-color: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'acc_button_border_c_h',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-icon-btn.ts-smaller:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();



	}

	protected function render( $instance = [] ) {
		$post = \Voxel\get_current_post();
		$field = $post ? $post->get_field( $this->get_settings_for_display( 'ts_source_field' ) ) : null;
		if ( ! ( $post && $field && $field->get_type() === 'work-hours' ) ) {
			return;
		}

		$schedule = $field->get_schedule();
		if ( ! $schedule ) {
			return;
		}

		$is_open_now = $field->is_open_now();
		$weekdays = \Voxel\get_weekdays();
		$keys = array_flip( \Voxel\get_weekday_indexes() );
		array_unshift( $keys, array_pop( $keys ) ); // move sunday to index 0 for compatibility with date format 'w'
		$local_time = $post->get_local_time();
		$today = $keys[ $local_time->format('w') ];

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/work-hours.php' );
	}

	public function get_style_depends() {
		return [ 'vx:work-hours.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
