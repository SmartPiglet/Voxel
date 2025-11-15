<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Timeline_Kit extends Base_Widget {

	public function get_name() {
		return 'ts-timeline-kit';
	}

	public function get_title() {
		return __( 'Timeline Style Kit (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_vxfeed_general',
			[
				'label' => __( 'General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'vxf-text-1',
				[
					'label' => __( 'Primary text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--main-text: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-text-2',
				[
					'label' => __( 'Secondary text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--faded-text: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-text-3',
				[
					'label' => __( 'Link color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--main-link: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--main-bg: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-border',
				[
					'label' => __( 'Border Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--main-border: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-detail',
				[
					'label' => __( 'Detail color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--detail-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'vxf-shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '.vxf-post, .vxf-create-post ',

				]
			);

			$this->add_responsive_control(
				'xl-radius',
				[
					'label' => __( 'XL radius', 'voxel-elementor' ),
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
						'.vxfeed' => '--xl-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'lg-radius',
				[
					'label' => __( 'LG radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 30,
							'step' => 1,
						],
					],
					'selectors' => [
						'.vxfeed' => '--lg-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'md-radius',
				[
					'label' => __( 'MD radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 15,
							'step' => 1,
						],
					],
					'selectors' => [
						'.vxfeed' => '--md-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_vxfeed_actions',
			[
				'label' => __( 'Icons', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'main-icon-size',
				[
					'label' => __( 'Post Actions', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 15,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'.vxfeed' => '--main-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'reply-icon-size',
				[
					'label' => __( 'Reply actions', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 15,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'.vxfeed' => '--reply-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'vxf-action-1',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxfeed' => '--main-icon-color: {{VALUE}}',
					],
				]
			);

			

			$this->add_control(
				'vxf-action-2',
				[
					'label' => __( 'Liked Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxf-liked' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-action-3',
				[
					'label' => __( 'Reposted Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxf-reposted' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-action-4',
				[
					'label' => __( 'Verified Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.vxf-verified' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'vxf-action-5',
				[
					'label' => __( 'Star Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.rs-stars li .ts-star-icon' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_kit_reviews',
			[
				'label' => __( 'Post reviews', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'rev-min-width',
			[
				'label' => __( 'Review categories (Min width)', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'.rev-cats' => '--max-r-width: {{SIZE}}%;',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'ts_sf_popup_controls',
			[
				'label' => __( 'Buttons', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_popup_control_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_sfc_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_popup_btn_general',
						[
							'label' => __( 'General', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_popup_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '.vxfeed .ts-btn',
						]
					);



					$this->add_responsive_control(
						'ts_popup_btn_radius',
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
								'.vxfeed .ts-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$this->add_control(
						'ts_popup_clear',
						[
							'label' => __( 'Primary button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);


					$this->add_control(
						'ts_popup_button_1',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-1' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_button_1_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-1' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_popup_button_1_icon',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-1' => '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_popup_button_1_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '.vxfeed .ts-btn-1',
						]
					);



					$this->add_control(
						'ts_popup_submit',
						[
							'label' => __( 'Accent button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_popup_button_2',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-2' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_button_2_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-2' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_popup_button_2_icon',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-2' => '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_popup_button_2_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '.vxfeed .ts-btn-2',
						]
					);

					$this->add_control(
						'ts_popup_tertiary',
						[
							'label' => __( 'Tertiary button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_popuptertiary_2',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-4' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_tertiary_2_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-4' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_popup_button_3_icon',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-4' => '--ts-icon-color: {{VALUE}}',
							],

						]
					);







				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_sfc_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'ts_popup_clear_h',
						[
							'label' => __( 'Primary button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);


					$this->add_control(
						'ts_popup_button_1_h',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-1:hover' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_button_1_c_h',
						[
							'label' => __( 'Button color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-1:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_button_1_b_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-1:hover' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_submit_H',
						[
							'label' => __( 'Accent button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_popup_button_2_h',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-2:hover' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_button_2_c_h',
						[
							'label' => __( 'Button color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-2:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_button_2_b_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-2:hover' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_tertiary_H',
						[
							'label' => __( 'Tertiary button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_popup_tertiary_2_h',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-4:hover' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_popup_tertiary_2_c_h',
						[
							'label' => __( 'Button color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.vxfeed .ts-btn-4:hover' => 'color: {{VALUE}}',
							],
						]
					);




				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

	}

	protected function render( $instance = [] ) {
		require locate_template( 'templates/widgets/timeline-kit.php' );
	}

	public function get_style_depends() {
		return [ 'vx:forms.css', 'vx:social-feed.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
