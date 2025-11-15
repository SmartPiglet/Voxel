<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Feed extends Base_Widget {

	public function get_name() {
		return 'ts-post-feed';
	}

	public function get_title() {
		return __( 'Post feed (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section( 'post_feed_settings', [
			'label' => __( 'Post Feed settings', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$post_types = [];
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$post_types[ $post_type->get_key() ] = $post_type->get_label();
		}

		$this->add_control( 'ts_source', [
			'label' => __( 'Data source', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'search-form',
			'label_block' => true,
			'options' => [
				'search-form' => __( 'Search Form widget', 'voxel-elementor' ),
				'search-filters' => __( 'Filters', 'voxel-elementor' ),
				'manual' => __( 'Manual selection', 'voxel-elementor' ),
				'archive' => __( 'WP default archive', 'voxel-elementor' ),
			],
		] );

		$this->add_control( 'cpt_search_form', [
			'label' => __( 'Link to search form', 'voxel-elementor' ),
			'type' => 'voxel-relation',
			'vx_group' => 'feedToSearch',
			'vx_target' => 'elementor-widget-ts-search-form',
			'vx_side' => 'right',
			'condition' => [ 'ts_source' => 'search-form' ],
		] );

		$this->add_control( 'ts_pagination', [
			'label' => __( 'Pagination', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'load_more',
			'options' => [
				'load_more' => __( 'Load more button', 'voxel-elementor' ),
				'prev_next' => __( 'Prev/Next buttons', 'voxel-elementor' ),
				'none' => __( 'None', 'voxel-elementor' ),
			],
			'condition' => [ 'ts_source' => 'search-form' ],
		] );

		$this->add_control( 'ts_posts_per_page', [
			'label' => __( 'Posts per page', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 10,
			'min' => 0,
			'max' => 100,
			'condition' => [ 'ts_source' => 'search-form' ],
		] );

		$this->add_control( 'ts_show_total_count', [
			'label' => __( 'Display details', 'voxel-elementor' ),
			'description' => 'Display results count and order by (if enabled) on top',
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'condition' => [ 'ts_source' => 'search-form' ],
		] );

		$this->add_control(
			'ts_noresults_text',
			[
				'label' => __( 'No results label', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'There are no results matching your search', 'voxel-elementor' ),
				'placeholder' => __( 'Type your text', 'voxel-elementor' ),
			]
		);

		$post_types = [];
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$post_types[ $post_type->get_key() ] = $post_type->get_label();
		}

		$this->add_control( 'ts_manual_post_type', [
			'label' => __( 'Choose post type', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'options' => $post_types,
			'condition' => [ 'ts_source' => [ 'manual', 'archive' ] ],
		] );

		$repeater = new \Elementor\Repeater;

		$repeater->add_control( 'post_id', [
			'type' => \Elementor\Controls_Manager::NUMBER,
			'label' => __( 'Post ID', 'voxel-elementor' ),
		] );

		$this->add_control( 'ts_manual_posts', [
			'label' => __( 'Choose posts', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::REPEATER,
			'condition' => [ 'ts_source' => 'manual' ],
			'fields' => $repeater->get_controls(),
		] );

		/** Data source: Filters **/

		$this->add_control( 'ts_choose_post_type', [
			'label' => __( 'Choose post type', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'options' => $post_types,
			'condition' => [ 'ts_source' => 'search-filters' ],
		] );

		$this->add_control( 'ts_post_number', [
			'label' => __( 'Number of posts to load', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 10,
			'min' => 0,
			'max' => apply_filters( 'voxel/get_search_results/max_limit', 500 ),
			'condition' => [ 'ts_source' => 'search-filters' ],
		] );

		$this->add_control( 'ts_filters_pagination', [
			'label' => __( 'Pagination', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'none',
			'options' => [
				'load_more' => __( 'Load more button', 'voxel-elementor' ),
				'prev_next' => __( 'Prev/Next buttons', 'voxel-elementor' ),
				'none' => __( 'None', 'voxel-elementor' ),
			],
			'condition' => [ 'ts_source' => 'search-filters' ],
		] );

		$this->add_control( 'ts_post_exclude', [
			'label' => __( 'Exclude posts', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::TEXT,
			'description' => 'Enter the post id or a comma separated list of ids to exclude',
			'label_block' => true,
			'condition' => [ 'ts_source' => 'search-filters' ],
		] );

		$this->add_control( 'ts_priority_filter', [
			'label' => __( 'Filter posts by priority', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'condition' => [ 'ts_source' => 'search-filters' ],
		] );

		$this->add_control( 'ts_priority_min', [
			'label' => __( 'Min. priority', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 0,
			'classes' => 'ts-half-width',
			'label_block' => true,
			'condition' => [
				'ts_source' => 'search-filters',
				'ts_priority_filter' => 'yes',
			],
		] );

		$this->add_control( 'ts_priority_max', [
			'label' => __( 'Max. priority', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 0,
			'classes' => 'ts-half-width',
			'label_block' => true,
			'condition' => [
				'ts_source' => 'search-filters',
				'ts_priority_filter' => 'yes',
			],
		] );

		$this->add_control( 'ts_post_offset', [
			'label' => __( 'Offset', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'min' => 0,
			'default' => 0,
			'label_block' => false,
			'condition' => [ 'ts_source' => 'search-filters' ],
		] );

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$card_templates = $post_type->templates->get_custom_templates()['card'];
			$this->add_control( sprintf( 'ts_card_template__%s', $post_type->get_key() ), [
				'label' => __( 'Preview card template', 'voxel-elementor' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'main',
				'options' => [
					'main' => 'Main template',
				] + array_column( $card_templates, 'label', 'id' ),
				'condition' => [
					'ts_source' => 'search-filters',
					'ts_choose_post_type' => $post_type->get_key(),
				],
			] );

			$this->add_control( sprintf( 'ts_manual_card_template__%s', $post_type->get_key() ), [
				'label' => __( 'Preview card template', 'voxel-elementor' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'main',
				'options' => [
					'main' => 'Main template',
				] + array_column( $card_templates, 'label', 'id' ),
				'condition' => [
					'ts_source' => [ 'manual', 'archive' ],
					'ts_manual_post_type' => $post_type->get_key(),
				],
			] );
		}

		$this->end_controls_section();
		$this->start_controls_section( 'post_feed_layout', [
			'label' => __( 'Layout', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

			$this->add_control(
				'ts_wrap_feed',
				[
					'label' => __( 'Mode', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'ts-feed-grid-default',
					'options' => [
						'ts-feed-grid-default'  => __( 'Grid', 'voxel-elementor' ),
						'ts-feed-nowrap' => __( 'Carousel', 'voxel-elementor' ),
					],
				]
			);

			$this->add_responsive_control(
				'ts_nowrap_item_width',
				[
					'label' => __( 'Item width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'description' => 'Set the width of an individual item in the carousel',
					'size_units' => [ 'px', '%', 'custom' ],
					'range' => [
						'px' => [
							'min' => 50,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} > .post-feed-grid > div' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [ 'ts_wrap_feed' => 'ts-feed-nowrap' ]
				]
			);


			$this->add_control(
				'carousel_autoplay',
				[
					'label' => __( 'Auto slide?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'condition' => [ 'ts_wrap_feed' => 'ts-feed-nowrap' ]
				]
			);

			$this->add_responsive_control(
				'carousel_autoplay_interval',
				[
					'label' => __( 'Auto slide interval (ms)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 3000,
					'condition' => [
						'ts_wrap_feed' => 'ts-feed-nowrap',
						'carousel_autoplay' => 'yes',
					],
				]
			);


			$this->add_responsive_control(
				'ts_feed_column_no',
				[
					'label' => __( 'Number of columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 6,
					'step' => 1,
					'default' => 3,
					'selectors' => [
						'{{WRAPPER}} > .post-feed-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
					],
					'condition' => [ 'ts_wrap_feed' => 'ts-feed-grid-default' ]
				]
			);




			$this->add_responsive_control(
				'ts_feed_col_gap',
				[
					'label' => __( 'Item gap', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'description' => 'Adds gap between the items',
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],

					'default' => [
						'unit' => 'px',
						'size' => 20,
					],
					'selectors' => [
						'{{WRAPPER}} > .post-feed-grid' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);




			$this->add_responsive_control(
				'ts_scroll_padding',
				[
					'label' => __( 'Scroll padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'description' => 'Adds padding to the scrollable area, useful in full width layouts or in responsive mode',
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} > .post-feed-grid' => 'padding: 0 {{SIZE}}{{UNIT}}; scroll-padding: {{SIZE}}{{UNIT}}',
					],
					'condition' => [ 'ts_wrap_feed' => 'ts-feed-nowrap' ]
				]
			);

			$this->add_responsive_control(
				'ts_item_padding',
				[
					'label' => __( 'Item padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'description' => 'Adds padding to an individual item',
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} > .post-feed-grid > .ts-preview' => 'padding: {{SIZE}}{{UNIT}}',
					],
					'condition' => [ 'ts_wrap_feed' => 'ts-feed-nowrap' ]
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
				'ts_lm_icon',
				[
					'label' => __( 'Load more icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_noresults_icon',
				[
					'label' => __( 'No results icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);



			$this->add_control(
				'ts_arrow_right',
				[
					'label' => __( 'Right arrow', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_arrow_left',
				[
					'label' => __( 'Left arrow', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'ts_chevron_right',
				[
					'label' => __( 'Right chevron', 'text-domain' ),
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
				'ts_reset_ico',
				[
					'label' => __( 'Reset icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
				]
			);



		$this->end_controls_section();

		/*
		==========
		Top area
		==========
		*/

		$this->start_controls_section(
			'ts_feed_head',
			[
				'label' => __( 'Counter', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'counter_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .result-count',
				]
			);


			$this->add_responsive_control(
				'ts_counter_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .result-count' => 'color: {{VALUE}}',
					],

				]
			);

			
			$this->add_responsive_control(
				'toparea_padding',
				[
					'label' => __( 'Bottom spacing', 'voxel-elementor' ),
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
						'{{WRAPPER}} .post-feed-header' => 'padding-bottom: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_feed_order',
			[
				'label' => __( 'Order by', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'order_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .vxf-sort',
				]
			);


			$this->add_responsive_control(
				'order_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vxf-sort' => '--ts-shade-3: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'order_col_hover',
				[
					'label' => __( 'Text color (Hover)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vxf-sort' => '--ts-shade-2: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		/*
		==========
		Loading
		==========
		*/

		$this->start_controls_section(
			'ts_feed_loading',
			[
				'label' => __( 'Loading results', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'ts_loading_style',
				[
					'label' => __( 'Loading style', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'vx-opacity',
					'options' => [
						'vx-none'  => __( 'None', 'voxel-elementor' ),
						'vx-opacity' => __( 'Opacity', 'voxel-elementor' ),
						'vx-skeleton' => __( 'Skeleton', 'voxel-elementor' ),
					],
				]
			);

			$this->add_control(
				'vx_opacity_value',
				[
					'label' => __( 'Opacity', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'condition' => [ 'ts_loading_style' => 'vx-opacity' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 1,
							'step' => 0.01,
						],
					],


					'selectors' => [
						'{{WRAPPER}}.vx-loading .vx-opacity' => 'opacity: {{SIZE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'vx_skeleton_bg',
					'label' => esc_html__( 'Background', 'voxel-elementor' ),
					'types' => [ 'classic', 'gradient', 'video' ],
					'condition' => [ 'ts_loading_style' => 'vx-skeleton' ],
					'selector' => '{{WRAPPER}}.vx-loading .vx-skeleton .ts-preview',
				]
			);


		$this->end_controls_section();

		/*
		==========
		No posts
		==========
		*/

		$this->start_controls_section(
			'ts_no_posts',
			[
				'label' => __( 'No results', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'ts_hide_noresults',
				[
					'label' => __( 'Hide screen', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Hide', 'voxel-elementor' ),
					'label_off' => __( 'Show', 'voxel-elementor' ),
					'return_value' => 'none',

					'selectors' => [
						'{{WRAPPER}} .ts-no-posts ' => 'display: {{VALUE}}',
					],

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
						'{{WRAPPER}} .ts-no-posts' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_nopost_ico_col',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-no-posts' => '--ts-icon-color: {{VALUE}}',
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

			$this->add_responsive_control(
				'ts_nopost_link_col',
				[
					'label' => __( 'Link color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-no-posts > p a' => 'color: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		



		$this->start_controls_section(
			'ts_feed_pag',
			[
				'label' => __( 'Load more / Next / Prev', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_fpag_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_fpag_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_pag_top',
						[
							'label' => __( 'Top margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%'],
							'range' => [
								'px' => [
									'min' => 10,
									'max' => 500,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .feed-pagination' => 'padding-top: {{SIZE}}{{UNIT}};',
							],
						]
					);




					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_fpag_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-1',
						]
					);

					$this->add_control(
						'ts_fpag_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px'],
							'selectors' => [
								'{{WRAPPER}}  .ts-btn-1' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fpag_btn_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-1' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fpag_btn_width',
						[
							'label' => __( 'Width', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 500,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => 'width: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_fpag_justify',
						[
							'label' => __( 'Justify', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SELECT,
							'options' => [
								'flex-start'  => __( 'Left', 'voxel-elementor' ),
								'center' => __( 'Center', 'voxel-elementor' ),
								'flex-end' => __( 'Right', 'voxel-elementor' ),
								'space-between' => __( 'Space between', 'voxel-elementor' ),
								'space-around' => __( 'Space around', 'voxel-elementor' ),
							],

							'selectors' => [
								'{{WRAPPER}} .feed-pagination' => 'justify-content: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fpag_btn_spacing',
						[
							'label' => __( 'Spacing between buttons', 'voxel-elementor' ),
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
								'{{WRAPPER}} .feed-pagination' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);



					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_fpag_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-1',
						]
					);

					$this->add_responsive_control(
						'ts_fpag_btn_radius',
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
						'ts_fpag_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_fpag_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => 'background: {{VALUE}}',
							],

						]
					);



					$this->add_responsive_control(
						'ts_fpag_btn_icon_size',
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
								'{{WRAPPER}} .ts-btn-1' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_fpag_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1' => '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_fpag_icon_spacing',
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
								'{{WRAPPER}} .ts-btn-1' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);




				$this->end_controls_tab();

				/* Hover tab */

				$this->start_controls_tab(
					'ts_rpag_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_rpag_btn_c_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_rpag_btn_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_rpag_btn_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_rpag_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-1:hover' => '--ts-icon-color: {{VALUE}}',
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
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn' => '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_fnav_btn_size',
						[
							'label' => __( 'Button size', 'voxel-elementor' ),
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
							],
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
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
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_fnav_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .post-feed-nav .ts-icon-btn:hover' => '--ts-icon-color: {{VALUE}};',
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

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$this->start_controls_section( sprintf( 'ts_sf_filters__%s', $post_type->get_key() ), [
				'label' => sprintf( '%s Filters', $post_type->get_singular_name() ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'ts_source' => 'search-filters',
					'ts_choose_post_type' => $post_type->get_key(),
				],
			] );

			$repeater = new \Elementor\Repeater;

			$filters = [];
			// $defaults = [];
			foreach ( $post_type->get_filters() as $filter ) {
				$filters[ $filter->get_key() ] = $filter->get_label();
				// $defaults[] = [
				// 	'ts_choose_filter' => $filter->get_key(),
				// ];
			}

			$repeater->add_control( 'ts_choose_filter', [
				'label' => __( 'Choose filter', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $filters,
			] );

			foreach ( $post_type->get_filters() as $filter ) {
				foreach ( $filter->get_elementor_controls() as $control_key => $control_args ) {
					if ( isset( $control_args['full_key'] ) ) {
						$control_key = $control_args['full_key'];
					} else {
						$control_key = $filter->get_key().':'.$control_key;
					}

					if ( ! isset( $control_args['condition'] ) ) {
						$control_args['condition'] = [];
					}

					if ( ( $control_args['conditional'] ?? null ) === false ) {
						continue;
					}

					$control_args['condition']['ts_choose_filter'] = $filter->get_key();

					if ( $control_args['responsive'] ?? false ) {
						$repeater->add_responsive_control( $control_key, $control_args );
					} else {
						$repeater->add_control( $control_key, $control_args );
					}
				}
			}

			$this->add_control( sprintf( 'ts_filter_list__%s', $post_type->get_key() ), [
				'label' => __( 'Add filters', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				// 'default' => $defaults,
				'title_field' => '{{ts_choose_filter}}',
				'_disable_loop' => true,
			] );

			$this->end_controls_section();
		}
	}

	protected function render( $instance = [] ) {
		$data_source = $this->get_settings('ts_source');
		if ( $data_source === 'search-filters' ) {
			$post_type = \Voxel\Post_Type::get( $this->get_settings( 'ts_choose_post_type' ) );
			if ( ! $post_type ) {
				return;
			}

			$args = [];
			$args['type'] = $post_type->get_key();

			$filter_list = (array) $this->get_settings_for_display(
				sprintf( 'ts_filter_list__%s', $post_type->get_key() )
			);

			foreach ( $filter_list as $filter_config ) {
				if ( $filter = $post_type->get_filter( $filter_config['ts_choose_filter'] ?? null ) ) {
					$controls = [];
					foreach ( $filter->get_elementor_controls() as $control_key => $control ) {
						$controls[ $control_key ] = $filter_config[ $control['full_key'] ?? sprintf( '%s:%s', $filter->get_key(), $control_key ) ] ?? null;
					}

					$filter->set_elementor_config( $controls );
					$args[ $filter->get_key() ] = $filter->get_default_value_from_elementor( $controls );
				}
			}

			$card_template = $this->get_settings_for_display( sprintf( 'ts_card_template__%s', $post_type->get_key() ) );
			$limit = absint( $this->get_settings_for_display( 'ts_post_number' ) );
			$offset = absint( $this->get_settings_for_display( 'ts_post_offset' ) );
			$template_id = is_numeric( $card_template ) ? (int) $card_template : null;
			$exclude = array_filter( array_map( 'absint', explode( ',', $this->get_settings_for_display('ts_post_exclude') ) ) );

			$priority_min = null;
			$priority_max = null;
			if ( $this->get_settings_for_display('ts_priority_filter') === 'yes' ) {
				$priority_min = $this->get_settings_for_display('ts_priority_min');
				$priority_max = $this->get_settings_for_display('ts_priority_max');
			}

			$results = \Voxel\get_search_results( $args, [
				'limit' => $limit,
				'offset' => $offset,
				'template_id' => $template_id,
				'exclude' => $exclude,
				'priority_min' => $priority_min,
				'priority_max' => $priority_max,
			] );

			$pagination = $this->get_settings_for_display( 'ts_filters_pagination' );
			if ( in_array( $pagination, [ 'prev_next', 'load_more' ], true ) ) {
				$this->add_render_attribute( '_wrapper', 'class', 'ts-post-feed--standalone' );
				$this->add_render_attribute( '_wrapper', 'data-ts-config', wp_json_encode( [
					'filters' => array_filter( array_merge( $args, [
						'pg' => 1,
						'limit' => $limit,
						'__offset' => $offset,
						'__exclude' => join( ',', $exclude ),
						'__template_id' => $template_id,
					] ), function( $value ) {
						return $value !== null;
					} ),
					'pagination' => $pagination,
				] ) );
				wp_enqueue_script( 'vx:post-feed.js' );
			}
		} elseif ( $data_source === 'manual' ) {
			$post_type = \Voxel\Post_Type::get( $this->get_settings( 'ts_manual_post_type' ) );
			if ( ! $post_type ) {
				return;
			}

			$args = [];
			$args['type'] = $post_type->get_key();

			$card_template = $this->get_settings_for_display( sprintf( 'ts_manual_card_template__%s', $post_type->get_key() ) );
			$results = \Voxel\get_search_results( [
				'type' => $post_type->get_key(),
			], [
				'ids' => array_column( (array) $this->get_settings_for_display('ts_manual_posts'), 'post_id' ),
				'template_id' => is_numeric( $card_template ) ? (int) $card_template : null,
			] );

		} elseif ( $data_source === 'archive' ) {
			$post_type = \Voxel\Post_Type::get( $this->get_settings( 'ts_manual_post_type' ) );
			if ( ! $post_type ) {
				return;
			}

			$args = [];
			$args['type'] = $post_type->get_key();

			global $wp_query;

			$post_ids = [];
			if ( ! empty( $wp_query->posts ?? null ) ) {
				$post_ids = array_column( $wp_query->posts, 'ID' );
			}

			$card_template = $this->get_settings_for_display( sprintf( 'ts_manual_card_template__%s', $post_type->get_key() ) );
			$results = \Voxel\get_search_results( [
				'type' => $post_type->get_key(),
			], [
				'ids' => $post_ids,
				'template_id' => is_numeric( $card_template ) ? (int) $card_template : null,
			] );
		} else {
			$search_form = \Voxel\get_related_widget( $this, $this->_get_template_id(), 'feedToSearch', 'right' );
			if ( ! $search_form ) {
				return;
			}

			$widget = new \Voxel\Widgets\Search_Form( $search_form, [] );
			if ( method_exists( $widget, 'add_instance_controls' ) ) {
				$widget->add_instance_controls();
			}

			$post_type = $widget->_get_default_post_type();
			if ( ! $post_type ) {
				return;
			}

			$config = $widget->_get_post_type_config( $post_type );
			$args = [];
			$args['type'] = $post_type->get_key();
			foreach ( $config['filters'] as $filter ) {
				if ( $filter['value'] !== null ) {
					$args[ $filter['key'] ] = $filter['value'];
				}
			}

			if ( $widget->_update_url() ) {
				$args['pg'] = $_GET['pg'] ?? null;
			}

			$posts_per_page = absint( $this->get_settings_for_display( 'ts_posts_per_page' ) );
			$map_widget = \Voxel\get_related_widget( $widget, $widget->_get_template_id(), 'mapToSearch', 'left' );
			$load_markers = !! $map_widget; // only load markers if widget is connected to a Map (VX)
			$load_additional_markers = absint( $widget->get_settings_for_display('ts_map_additional_markers') );
			$results = \Voxel\get_search_results( $args, [
				'limit' => $posts_per_page,
				'template_id' => $config['template_id'],
				'get_total_count' => $this->get_settings_for_display('ts_show_total_count') === 'yes',
				'preload_additional_ids' => ( $load_markers && $load_additional_markers ) ? $load_additional_markers : 1,
				'render_cards_with_markers' => $load_markers,
				'apply_conditional_logic' => true,
			] );

			if ( $load_markers && $load_additional_markers && ! empty( $results['additional_ids'] ) ) {
				$additional_markers = \Voxel\get_search_results( $args, [
					'ids' => $results['additional_ids'],
					'render' => 'markers',
					'pg' => 1,
					'template_id' => null,
					'get_total_count' => false,
					'apply_conditional_logic' => true,
				] );
			}

			if ( empty( $results['ids'] ) ) {
				\Voxel\enqueue_template_css( $results['template_id'] );
			}

			$this->add_render_attribute( '_wrapper', 'data-per-page', $posts_per_page );

			$switchable_desktop = $widget->get_settings( 'mf_switcher_desktop' ) === 'yes';
			$hidden_desktop = $widget->get_settings( 'switcher_desktop_default' ) === 'map';
			$switchable_tablet = $widget->get_settings( 'mf_switcher_tablet' ) === 'yes';
			$hidden_tablet = $widget->get_settings( 'switcher_tablet_default' ) === 'map';
			$switchable_mobile = $widget->get_settings( 'mf_switcher_mobile' ) === 'yes';
			$hidden_mobile = $widget->get_settings( 'switcher_mobile_default' ) === 'map';

			$this->add_render_attribute( '_wrapper', 'class', [
				$switchable_desktop && $hidden_desktop ? 'vx-hidden-desktop' : '',
				$switchable_tablet && $hidden_tablet ? 'vx-hidden-tablet' : '',
				$switchable_mobile && $hidden_mobile ? 'vx-hidden-mobile' : '',
			] );

			if ( method_exists( $widget, 'remove_instance_controls' ) ) {
				$widget->remove_instance_controls();
			}

			$pagination = $this->get_settings_for_display( 'ts_pagination' );
			$this->add_render_attribute( '_wrapper', 'data-paginate', esc_attr( $pagination ) );
		}


		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/post-feed.php' );
	}

	public function get_style_depends() {
		return [ 'vx:post-feed.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
