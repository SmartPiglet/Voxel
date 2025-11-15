<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Slider extends Base_Widget {

	public function get_name() {
		return 'ts-slider';
	}

	public function get_title() {
		return __( 'Slider (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_slider_content',
			[
				'label' => __( 'Images', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
			$this->add_control(
				'ts_slider_images',
				[
					'label' => __( 'Add Images', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::GALLERY,
					'default' => [],
				]
			);

			$this->add_control( 'ts_visible_count', [
				'label' => __( 'Number of images to load', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'min' => 1,
			] );


			$this->add_responsive_control( 'ts_display_size', [
				'label' => __( 'Image size', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'medium',
				'options' => \Voxel\get_image_sizes_with_labels(),
			] );

			$this->add_responsive_control( 'ts_lightbox_size', [
				'label' => __( 'Image size (Lightbox)', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'large',
				'options' => \Voxel\get_image_sizes_with_labels(),
			] );

			$this->add_control( 'ts_link_type', [
				'label' => __( 'Link', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'lightbox',
				'options' => [
					'none' => 'None',
					'custom_link' => 'Custom URL',
					'lightbox' => 'Lightbox',
				]
			] );

			$this->add_control(	'ts_link_src',	[
					'type' => \Elementor\Controls_Manager::URL,
					'placeholder' => __( 'https://your-link.com', 'voxel-elementor' ),
					'default' => [
						'url' => '',
					],
					'condition' => [ 'ts_link_type' => 'custom_link' ],
					'show_external' => true,
				]
			);

			$this->add_control(
				'ts_gl_autofit',
				[
					'label' => __( 'Auto fit?', 'voxel-elementor' ),

					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'condition' => [ 'ts_remove_empty' => 'yes' ],
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid' => 'grid-template-columns: repeat(auto-fit, minmax(0, 1fr));',
					],
				]
			);



			$this->add_control(
				'ts_show_navigation',
				[
					'label' => __( 'Show thumbnails?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'carousel_autoplay',
				[
					'label' => __( 'Auto slide?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
				]
			);

			$this->add_responsive_control(
				'carousel_autoplay_interval',
				[
					'label' => __( 'Auto slide interval (ms)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 3000,
					'condition' => [
						'carousel_autoplay' => 'yes',
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
			'ts_chevron_right',
			[
				'label' => __( 'Right chevron', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
			]
		);

		$this->add_control(
			'ts_chevron_left',
			[
				'label' => __( 'Left chevron', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
			]
		);



		$this->end_controls_section();

		$this->start_controls_section(
			'ts_slider_general',
			[
				'label' => __( 'General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_gl_general_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_gl_general_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control( 'image_slider_ratio', [
						'label' => __( 'Image aspect ratio', 'voxel-backend' ),
						'description' => __( 'Set image aspect ratio e.g 16/9', 'voxel-backend' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'selectors' => [
							'{{WRAPPER}} .ts-slider img, {{WRAPPER}} .ts-single-slide img' => 'aspect-ratio: {{VALUE}};',
						],
					] );


					$this->add_responsive_control(
						'ts_gl_general_image_radius',
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
								'{{WRAPPER}} .ts-slider, {{WRAPPER}} .ts-single-slide ' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_gl_general_image_opacity',
						[
							'label' => __( 'Opacity', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 1,
									'step' => 0.05,
								],
							],
							'selectors' => [
								'{{WRAPPER}}' => 'opacity: {{SIZE}};',
							],
						]
					);




				$this->end_controls_tab();

				$this->start_controls_tab(
					'ts_gl_general_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_gl_general_image_opacity_hover',
						[
							'label' => __( 'Opacity', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 1,
									'step' => 0.05,
								],
							],
							'selectors' => [
								'{{WRAPPER}}:hover' => 'opacity: {{SIZE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_thumbnails_general',
			[
				'label' => __( 'Thumbnails', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_thumbnails_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_thumbnails_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_responsive_control(
						'ts_thumbnail_size',
						[
							'label' => __( 'Size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 20,
									'max' => 200,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-slide-nav a ' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_thumbnails_radius',
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
								'{{WRAPPER}} .ts-slide-nav a ' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_thumbnail_opacity',
						[
							'label' => __( 'Opacity', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 1,
									'step' => 0.05,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-slide-nav a' => 'opacity: {{SIZE}};',
							],
						]
					);




				$this->end_controls_tab();

				$this->start_controls_tab(
					'ts_thumbnail_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_thumbnail_opacity_h',
						[
							'label' => __( 'Opacity', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 1,
									'step' => 0.05,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-slide-nav a:hover' => 'opacity: {{SIZE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
		$this->start_controls_section(
			'ts_form_nav',
			[
				'label' => __( 'Carousel navigation', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_fnav_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_fnav_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$this->add_responsive_control(
						'ts_fnav_btn_horizontal',
						[
							'label' => __( 'Horizontal position', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => -100,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav li:last-child' => 'margin-right: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .post-feed-nav li:first-child' => 'margin-left: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fnav_btn_vertical',
						[
							'label' => __( 'Vertical position', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => -500,
									'max' => 500,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav li' => 'margin-top: {{SIZE}}{{UNIT}};',
							],
						]
					);







					$this->add_control(
						'ts_fnav_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_fnav_btn_size',
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
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fnav_btn_icon_size',
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
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_fnav_btn_nbg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_fnav_blur',
						[
							'label' => __( 'Backdrop blur', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 10,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn' => 'backdrop-filter: blur({{SIZE}}{{UNIT}});',

							],
						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_fnav_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .post-feed-nav .ts-icon-btn',
						]
					);

					$this->add_responsive_control(
						'ts_fnav_btn_radius',
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
								'{{WRAPPER}} .post-feed-nav  .ts-icon-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);





				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_fnav_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_fnav_btn_size_h',
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
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fnav_btn_icon_size_h',
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
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_fnav_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover i' => 'color: {{VALUE}};',
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover svg' => 'fill: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_fnav_btn_nbg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover'
								=> 'background-color: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_fnav_border_c_h',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$visible_count = $this->get_settings_for_display( 'ts_visible_count' );
		$display_size = $this->get_settings_for_display( 'ts_display_size' );
		$lightbox_size = $this->get_settings_for_display( 'ts_lightbox_size' );
		$image_ids = $this->get_settings_for_display( 'ts_slider_images' );
		$slider_id = 'slider-'.wp_unique_id();

		$image_ids = array_unique( $image_ids, SORT_REGULAR );
		if ( empty( $visible_count ) || count( $image_ids ) <= (int) $visible_count ) {
			$visible_ids = $image_ids;
		} else {
			$visible_ids = array_slice( $image_ids, 0, $visible_count );
		}

		$images = [];
		$processed = [];
		foreach ( $visible_ids as $image ) {
			if ( ! ( $attachment = get_post( $image['id'] ) ) ) {
				continue;
			}

			// prevent duplicates
			if ( isset( $processed[ $attachment->ID ] ) ) {
				continue;
			}
			$processed[ $attachment->ID ] = true;

			$src_display = wp_get_attachment_image_src( $attachment->ID, $display_size );
			if ( ! $src_display ) {
				continue;
			}

			$src_large = wp_get_attachment_image_src( $attachment->ID, $lightbox_size );
			if ( ! $src_large ) {
				$src_large = $src_display;
			}

			$image_data = [
				'caption' => wp_get_attachment_caption( $attachment->ID ),
				'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'description' => $attachment->post_content,
				'src_lightbox' => $src_large[0],
				'title' => $attachment->post_title,
				'id' => $attachment->ID,
				'display_size' => $display_size,
			];

			$images[] = $image_data;
		}

		$is_slideshow = count( $images ) > 1;
		$link_type = $this->get_settings_for_display( 'ts_link_type' );

		$current_post = \Voxel\get_current_post();
		$gallery_id = sprintf( '%s-%s-%s', $this->get_id(), $current_post ? $current_post->get_id() : 0, wp_unique_id() );

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/slider.php' );
	}

	public function get_style_depends() {
		return [ 'vx:post-feed.css', 'e-swiper' ];
	}

	public function get_script_depends() {
		return [ 'swiper' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
