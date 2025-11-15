<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Map extends Base_Widget {

	public function get_name() {
		return 'ts-map';
	}

	public function get_title() {
		return __( 'Map (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 'post_feed_settings', [
			'label' => __( 'Map settings', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'ts_source', [
			'label' => __( 'Markers', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'search-form',
			'label_block' => true,
			'options' => [
				'search-form' => __( 'Get markers from Search Form widget', 'voxel-elementor' ),
				'current-post' => __( 'Show marker of current post', 'voxel-elementor' ),
			],
		] );

		$this->add_control( 'cpt_search_form', [
			'label' => __( 'Link to search form', 'voxel-elementor' ),
			'type' => 'voxel-relation',
			'vx_group' => 'mapToSearch',
			'vx_target' => 'elementor-widget-ts-search-form',
			'vx_side' => 'right',
			'condition' => [ 'ts_source' => 'search-form' ],
			'reload' => 'editor',
		] );

		$this->add_control( 'ts_drag_search', [
			'label' => __( 'Show "Search this area" button', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
			'label_on' => __( 'Yes', 'voxel-backend' ),
			'label_off' => __( 'No', 'voxel-backend' ),
			'return_value' => 'yes',
			'condition' => [ 'ts_source' => 'search-form' ],
		] );

		$this->add_control( 'ts_drag_search_mode', [
			'label' => __( 'Search mode', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'manual',
			'options' => [
				'automatic' => 'Automatic: Search is performed automatically as the user drags the map',
				'manual' => 'Manual: Search is performed when the button is clicked',
			],
			'condition' => [ 'ts_source' => 'search-form', 'ts_drag_search' => 'yes' ],
		] );

		$this->add_control( 'ts_drag_search_default', [
			'label' => __( 'Map drag default state', 'voxel-elementor' ),
			'description' => __( 'If enabled, dragging the map will trigger a search for posts within the visible map bounds.', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'unchecked',
			'options' => [
				'checked' => 'Checked',
				'unchecked' => 'Unchecked',
			],
			'condition' => [ 'ts_source' => 'search-form', 'ts_drag_search' => 'yes', 'ts_drag_search_mode' => 'automatic' ],
		] );

		$this->add_responsive_control(
			'ts_map_height',
			[
				'label' => __( 'Height', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh'],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1200,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 400,
				],
				'selectors' => [
					'{{WRAPPER}} .ts-map' => 'height: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .ts-map' => 'height: {{VALUE}};',
				],
				'condition' => [ 'enable_calc_height' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'pg_radius',
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
					'{{WRAPPER}} .ts-map' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section( 'ts_map_defaults', [
			'label' => __( 'Default map location', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'ts_default_lat', [
			'label'   => _x( 'Default latitude', 'Explore map', 'voxel-backend' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 51.492,
			'min'     => -90,
			'max'     => 90,
			'classes' => 'ts-half-width',
			'label_block' => true,
		] );

		$this->add_control( 'ts_default_lng', [
			'label'   => _x( 'Default longitude', 'Explore map', 'voxel-backend' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => -0.130,
			'min'     => -180,
			'max'     => 180,
			'classes' => 'ts-half-width',
			'label_block' => true,
		] );

		$this->add_control( 'ts_default_zoom', [
			'label'   => _x( 'Default zoom level', 'Explore map', 'voxel-backend' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 11,
			'min'     => 0,
			'max'     => 30,
		] );

		$this->add_control( 'ts_min_zoom', [
			'label'   => _x( 'Minimum zoom level', 'Explore map', 'voxel-backend' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 2,
			'min'     => 0,
			'max'     => 30,
			'classes' => 'ts-half-width',
			'label_block' => true,
		] );

		$this->add_control( 'ts_max_zoom', [
			'label'   => _x( 'Maximum zoom level', 'Explore map', 'voxel-backend' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 18,
			'min'     => 0,
			'max'     => 30,
			'classes' => 'ts-half-width',
			'label_block' => true,
		] );

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_clusters',
			[
				'label' => __( 'Clusters', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'cluster_size',
				[
					'label' => __( 'Size', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-marker-cluster' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'cluster_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-marker-cluster' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'cluster_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-marker-cluster',
				]
			);

			$this->add_responsive_control(
				'cluster_radius',
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
						'{{WRAPPER}} .ts-marker-cluster' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'm_cluster_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-marker-cluster',
				]
			);

			$this->add_responsive_control(
				'cluster_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-marker-cluster' => 'color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_marker_ico',
			[
				'label' => __( 'Icon marker', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'mico_general',
				[
					'label' => __( 'General', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'mico_size',
				[
					'label' => __( 'Marker size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 30,
							'max' => 60,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .marker-type-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'mico_icon_size',
				[
					'label' => __( 'Marker icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 30,
							'max' => 60,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .marker-type-icon' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'mico_radius',
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
						'{{WRAPPER}} .marker-type-icon' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'tmico_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .marker-type-icon',
				]
			);



			$this->add_control(
				'mico_static',
				[
					'label' => __( 'Static marker', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'mico_static_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mi-static' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mico_static_bg_active',
				[
					'label' => __( 'Background color (Active)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .marker-active .mi-static' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mico_static_icon',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mi-static' => '--ts-icon-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mico_static_icon_a',
				[
					'label' => __( 'Icon color (Active)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .marker-active .mi-static' => '--ts-icon-color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();
		$this->start_controls_section(
			'ts_marker_text',
			[
				'label' => __( 'Text marker', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$this->add_responsive_control(
				'mtext_static_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .marker-type-text' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mtext_static_bg_active',
				[
					'label' => __( 'Background color (Active)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .marker-active .marker-type-text' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mtext_static_text',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .marker-type-text' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mtext_static_text_a',
				[
					'label' => __( 'Text color (Active)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .marker-active .marker-type-text' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'mtext_radius',
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
						'{{WRAPPER}} .marker-type-text' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'marker_text_type',
					'label' => __( 'Title typography' ),
					'selector' => '{{WRAPPER}} .marker-type-text',
				]
			);

			$this->add_responsive_control(
				'marker_text_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .marker-type-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'marker_text_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .marker-type-text',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_marker_img',
			[
				'label' => __( 'Image marker', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_responsive_control(
				'mimg_size',
				[
					'label' => __( 'Marker size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 30,
							'max' => 60,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .marker-type-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);



			$this->add_responsive_control(
				'mimg_radius',
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
						'{{WRAPPER}} .marker-type-image' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'mimg_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .marker-type-image',
				]
			);


		$this->end_controls_section();


		$this->start_controls_section(
			'ts_map_preview',
			[
				'label' => __( 'Map popup', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_responsive_control(
				'm_mp_width',
				[
					'label' => __( 'Card width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 700,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-preview-popup' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'ts_tm_loading',
				[
					'label' => __( 'Loader', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-loading-popup .ts-loader' => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'tm_color2',
				[
					'label' => __( 'Color 2', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-loading-popup .ts-loader' => 'border-bottom-color: {{VALUE}}',
					],
				]
			);





		$this->end_controls_section();

		$this->start_controls_section(
			'ts_map_btn',
			[
				'label' => __( 'Search button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'm_mapbtn_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-map-btn',
				]
			);
			$this->add_responsive_control(
				'map_btn_c',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-map-btn' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'map_btn_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-map-btn' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'map_btn_ic',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-map-btn svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'map_btn_ic_a',
				[
					'label' => __( 'Icon color (Active)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-map-btn.active svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'map_btn_radius',
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
						'{{WRAPPER}} .ts-map-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control( 'ts_checkmark_icon', [
				'label' => __( 'Checkmark icon', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
			] );



		$this->end_controls_section();


		$this->start_controls_section(
			'ts_form_nav',
			[
				'label' => __( 'Next/Prev buttons', 'voxel-elementor' ),
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




					$this->add_control(
						'ts_fnav_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn svg' => 'fill: {{VALUE}}',
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
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_fnav_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_fnav_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-map-nav .ts-icon-btn',
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
								'{{WRAPPER}} .ts-map-nav  .ts-icon-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_fnav_btn_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-map-nav  .ts-icon-btn',
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
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
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

					$this->add_control(
						'ts_fnav_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn:hover i' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn:hover svg' => 'fill: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_fnav_btn_bg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn:hover'
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
								'{{WRAPPER}} .ts-map-nav .ts-icon-btn:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();


		// $this->start_controls_section(
		// 	'ts_move_search',
		// 	[
		// 		'label' => __( 'Map: Search this area', 'voxel-elementor' ),
		// 		'tab' => \Elementor\Controls_Manager::TAB_STYLE,
		// 	]
		// );


		// 	$this->add_responsive_control(
		// 		'm_move_spacing',
		// 		[
		// 			'label' => __( 'Margin', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::SLIDER,
		// 			'size_units' => [ 'px'],
		// 			'range' => [
		// 				'px' => [
		// 					'min' => 0,
		// 					'max' => 100,
		// 					'step' => 1,
		// 				],
		// 			],
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag' => 'padding: {{SIZE}}{{UNIT}};',
		// 			],
		// 		]
		// 	);

		// 	$this->add_control(
		// 		'ts_move_justify',
		// 		[
		// 			'label' => __( 'Justify', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::SELECT,
		// 			'options' => [
		// 				'flex-start'  => __( 'Left', 'voxel-elementor' ),
		// 				'center' => __( 'Center', 'voxel-elementor' ),
		// 				'flex-end' => __( 'Right', 'voxel-elementor' ),
		// 			],

		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag' => 'justify-content: {{VALUE}}',
		// 			],
		// 		]
		// 	);

		// 	$this->add_control(
		// 		'ts_drag_btn',
		// 		[
		// 			'label' => __( 'Button', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::HEADING,
		// 			'separator' => 'before',
		// 		]
		// 	);

		// 	$this->add_control(
		// 		'm_movebtn_padding',
		// 		[
		// 			'label' => __( 'Padding', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::DIMENSIONS,
		// 			'size_units' => [ 'px'],
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		// 			],
		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_bg',
		// 		[
		// 			'label' => __( 'Background color', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn' => 'background-color: {{VALUE}}',
		// 			],

		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_bg_hover',
		// 		[
		// 			'label' => __( 'Background color (Hover)', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn:hover' => 'background-color: {{VALUE}}',
		// 			],

		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_bg_active',
		// 		[
		// 			'label' => __( 'Background color (Active)', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn.active' => 'background-color: {{VALUE}}',
		// 			],

		// 		]
		// 	);

		// 	$this->add_group_control(
		// 		\Elementor\Group_Control_Border::get_type(),
		// 		[
		// 			'name' => 'm_movebtn_border',
		// 			'label' => __( 'Border', 'voxel-elementor' ),
		// 			'selector' => '{{WRAPPER}} .ts-map-drag .ts-btn',
		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_btn_radius',
		// 		[
		// 			'label' => __( 'Border radius', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::SLIDER,
		// 			'size_units' => [ 'px', '%' ],
		// 			'range' => [
		// 				'px' => [
		// 					'min' => 0,
		// 					'max' => 100,
		// 					'step' => 1,
		// 				],
		// 				'%' => [
		// 					'min' => 0,
		// 					'max' => 100,
		// 				],
		// 			],
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
		// 			],
		// 		]
		// 	);

		// 	$this->add_group_control(
		// 		\Elementor\Group_Control_Box_Shadow::get_type(),
		// 		[
		// 			'name' => 'm_movebtn_shadow',
		// 			'label' => __( 'Box Shadow', 'voxel-elementor' ),
		// 			'selector' => '{{WRAPPER}} .ts-map-drag .ts-btn',
		// 		]
		// 	);

		// 	$this->add_group_control(
		// 		\Elementor\Group_Control_Typography::get_type(),
		// 		[
		// 			'name' => 'm_move_text',
		// 			'label' => __( 'Typography', 'voxel-elementor' ),
		// 			'selector' => '{{WRAPPER}} .ts-map-drag .ts-btn',
		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_btn_color',
		// 		[
		// 			'label' => __( 'Color', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn, {{WRAPPER}} .ts-map-drag .ts-btn i' => 'color: {{VALUE}}',
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn svg' => 'fill: {{VALUE}}',
		// 			],

		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_btn_color_h',
		// 		[
		// 			'label' => __( 'Color (Hover)', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn:hover, {{WRAPPER}} .ts-map-drag .ts-btn:hover i' => 'color: {{VALUE}}',
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn:hover svg' => 'fill: {{VALUE}}',
		// 			],

		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_btn_color_a',
		// 		[
		// 			'label' => __( 'Color (Active)', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::COLOR,
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn.active, {{WRAPPER}} .ts-map-drag .ts-btn.active i' => 'color: {{VALUE}}',
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn.active svg' => 'fill: {{VALUE}}',
		// 			],

		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_ico_size',
		// 		[
		// 			'label' => __( 'Icon size', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::SLIDER,
		// 			'size_units' => [ 'px'],
		// 			'range' => [
		// 				'px' => [
		// 					'min' => 0,
		// 					'max' => 100,
		// 					'step' => 1,
		// 				],
		// 			],
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
		// 			],
		// 		]
		// 	);

		// 	$this->add_responsive_control(
		// 		'm_move_text_margin',
		// 		[
		// 			'label' => __( 'Icon/Text margin', 'voxel-elementor' ),
		// 			'type' => \Elementor\Controls_Manager::SLIDER,
		// 			'size_units' => [ 'px', '%' ],
		// 			'range' => [
		// 				'px' => [
		// 					'min' => 0,
		// 					'max' => 100,
		// 					'step' => 1,
		// 				],
		// 				'%' => [
		// 					'min' => 0,
		// 					'max' => 100,
		// 				],
		// 			],
		// 			'selectors' => [
		// 				'{{WRAPPER}} .ts-map-drag .ts-btn' => 'grid-gap: {{SIZE}}{{UNIT}};',
		// 			],
		// 		]
		// 	);


		// $this->end_controls_section();




	}

	protected function render( $instance = [] ) {
		$source = $this->get_settings_for_display( 'ts_source' );

		if ( $source === 'current-post' ) {
			$post = \Voxel\get_current_post();
			if ( ! $post ) {
				return;
			}

			$location = $post->get_field('location');
			if ( ! $location ) {
				return;
			}

			$address = $location->get_value();
			if ( ! ( $address['latitude'] && $address['longitude'] ) ) {
				return;
			}
		} else {
			$search_form = \Voxel\get_related_widget( $this, $this->_get_template_id(), 'mapToSearch', 'right' );
			if ( ! $search_form ) {
				return;
			}

			$widget = new \Voxel\Widgets\Search_Form( $search_form, [] );

			$switchable_desktop = $widget->get_settings( 'mf_switcher_desktop' ) === 'yes';
			$hidden_desktop = $widget->get_settings( 'switcher_desktop_default' ) === 'feed';
			$switchable_tablet = $widget->get_settings( 'mf_switcher_tablet' ) === 'yes';
			$hidden_tablet = $widget->get_settings( 'switcher_tablet_default' ) === 'feed';
			$switchable_mobile = $widget->get_settings( 'mf_switcher_mobile' ) === 'yes';
			$hidden_mobile = $widget->get_settings( 'switcher_mobile_default' ) === 'feed';

			$this->add_render_attribute( '_wrapper', 'class', [
				$switchable_desktop && $hidden_desktop ? 'vx-hidden-desktop' : '',
				$switchable_tablet && $hidden_tablet ? 'vx-hidden-tablet' : '',
				$switchable_mobile && $hidden_mobile ? 'vx-hidden-mobile' : '',
			] );
		}

		$default_zoom = $this->get_settings_for_display( 'ts_default_zoom' );
		if ( is_numeric( $default_zoom ) ) {
			$default_zoom = (float) $default_zoom;
		} else {
			$default_zoom = null;
		}

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/map.php' );

	}

	public function get_style_depends() {
		$styles = [ 'vx:map.css' ];
		if ( \Voxel\get( 'settings.maps.provider' ) === 'mapbox' && ! \Voxel\is_preview_mode() ) {
			$styles[] = 'vx:mapbox.css';
			$styles[] = 'mapbox-gl';
		}

		return $styles;
	}

	public function get_script_depends() {
		if ( \Voxel\get( 'settings.maps.provider' ) === 'mapbox' && ! \Voxel\is_preview_mode() ) {
			return [
				'vx:mapbox.js',
				'mapbox-gl',
			];
		} else {
			return [
				'vx:google-maps.js',
				'google-maps',
			];
		}
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
