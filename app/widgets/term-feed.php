<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Term_Feed extends Base_Widget {

	public function get_name() {
		return 'ts-term-feed';
	}

	public function get_title() {
		return __( 'Term feed (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section( 'term_feed_settings', [
			'label' => __( 'Term feed settings', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'ts_source', [
			'label' => __( 'Data source', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'filters',
			'label_block' => true,
			'options' => [
				'filters' => __( 'Filters', 'voxel-elementor' ),
				'manual' => __( 'Manual selection', 'voxel-elementor' ),
			],
		] );

		$repeater = new \Elementor\Repeater;

		$repeater->add_control( 'term_id', [
			'type' => \Elementor\Controls_Manager::NUMBER,
			'label' => __( 'Term ID', 'voxel-elementor' ),
		] );

		$this->add_control( 'ts_manual_terms', [
			'label' => __( 'Choose terms', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::REPEATER,
			'condition' => [ 'ts_source' => 'manual' ],
			'fields' => $repeater->get_controls(),
		] );


		$taxonomies = [];
		foreach ( \Voxel\Taxonomy::get_voxel_taxonomies() as $taxonomy ) {
			$taxonomies[ $taxonomy->get_key() ] = sprintf( '%s (%s)', $taxonomy->get_label(), $taxonomy->get_key() );
		}

		$this->add_control( 'ts_choose_taxonomy', [
			'label' => __( 'Choose taxonomy', 'voxel-elementor' ),
			'label_block' => true,
			'type' => \Elementor\Controls_Manager::SELECT,
			'options' => $taxonomies,
			'condition' => [ 'ts_source' => 'filters' ],
		] );

		$this->add_control( 'ts_parent_term_id', [
			'label' => __( 'Direct children of (Term ID)', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'condition' => [ 'ts_source' => 'filters' ],
		] );

		$this->add_control( 'ts_order', [
			'label' => __( 'Order', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'default',
			'label_block' => true,
			'options' => [
				'default' => __( 'Default', 'voxel-elementor' ),
				'name' => __( 'Alphabetical', 'voxel-elementor' ),
			],
			'condition' => [ 'ts_source' => 'filters' ],
		] );

		$this->add_control( 'ts_per_page', [
			'label' => __( 'Number of terms to load', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 10,
			'min' => 0,
			'max' => apply_filters( 'voxel/get_term_results/max_limit', 500 ),
			'condition' => [ 'ts_source' => 'filters' ],
		] );

		$this->add_control( 'ts_hide_empty', [
			'label' => __( 'Hide empty terms?', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
		] );

		$post_types = [];
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$post_types[ $post_type->get_key() ] = $post_type->get_label();
		}

		$this->add_control( 'ts_hide_empty_pt', [
			'label' => __( 'Hide terms without a post in:', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => ':all',
			'options' => [
				':all' => 'Any post type',
			] + $post_types,
			'condition' => [ 'ts_hide_empty' => 'yes' ],
		] );



		$this->add_control( 'ts_card_template', [
			'label' => __( 'Preview card template', 'voxel-elementor' ),
			'label_block' => true,
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'main',
			'options' => array_column( \Voxel\get_custom_templates()['term_card'], 'label', 'id' ),
		] );

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
						'{{WRAPPER}} > .post-feed-grid > div:last-of-type' => 'margin-right: {{SIZE}}{{UNIT}}',
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

			$this->add_control(
				'mod_accent',
				[
					'label' => __( 'Replace accent color?', 'voxel-elementor' ),
					'description' => 'Replaces the color of any element utilizing accent color to the term color',
					'type' => \Elementor\Controls_Manager::SWITCHER,
				]
			);


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
	}

	protected function render( $instance = [] ) {
		global $wpdb;

		$source = $this->get_settings( 'ts_source' );
		$template_id = $this->get_settings( 'ts_card_template' );

		if ( $this->get_settings('ts_source') === 'manual' ) {
			$term_ids = array_column( (array) $this->get_settings( 'ts_manual_terms' ), 'term_id' );
			if ( empty( $term_ids ) ) {
				return;
			}

			_prime_term_caches( $term_ids );
			$terms = array_filter( array_map( '\Voxel\Term::get', $term_ids ) );

			if ( $this->get_settings( 'ts_hide_empty' ) === 'yes' ) {
				$terms = array_filter( $terms, function( $term ) {
					$counts = $term->post_counts->get_counts();
					if ( empty( $counts ) ) {
						return null;
					}

					if ( ! empty( $this->get_settings( 'ts_hide_empty_pt' ) ) && post_type_exists( $this->get_settings( 'ts_hide_empty_pt' ) ) ) {
						$post_type_key = $this->get_settings( 'ts_hide_empty_pt' );

						if ( ! is_numeric( $counts[ $post_type_key ] ?? null ) ) {
							return null;
						}

						if ( ! ( $counts[ $post_type_key ] > 0 ) ) {
							return null;
						}
					}

					return $term;
				} );
			}

			if ( empty( $terms ) ) {
				return;
			}
		} else {
			$taxonomy_key = $this->get_settings( 'ts_choose_taxonomy' );
			if ( empty( $taxonomy_key ) ) {
				return;
			}

			$parent_id = $this->get_settings( 'ts_parent_term_id' );
			$order = $this->get_settings( 'ts_order' );
			$per_page = $this->get_settings( 'ts_per_page' );

			$joins = [];
			$wheres = [];

			if ( is_numeric( $parent_id ) ) {
				$wheres[] = sprintf( 'tt.parent = %d', absint( $parent_id ) );
			}

			if ( $this->get_settings( 'ts_hide_empty' ) === 'yes' ) {
				$joins[] = <<<SQL
					INNER JOIN {$wpdb->termmeta} AS post_counts ON (
						post_counts.term_id = t.term_id
						AND post_counts.meta_key = 'voxel:post_counts'
					)
				SQL;

				if ( ! empty( $this->get_settings( 'ts_hide_empty_pt' ) ) && post_type_exists( $this->get_settings( 'ts_hide_empty_pt' ) ) ) {
					$post_type_key = esc_sql( $this->get_settings( 'ts_hide_empty_pt' ) );
					$wheres[] = "(
						JSON_VALID( post_counts.meta_value )
						AND JSON_EXTRACT( post_counts.meta_value, '$.\"{$post_type_key}\"' ) > 0
					)";
				}
			}

			$query_taxonomy = esc_sql( $taxonomy_key );
			$query_order_by = $order === 'name' ? 't.name ASC' : 't.voxel_order ASC, t.name ASC';
			$query_limit = is_numeric( $per_page ) ? absint( $per_page ) : 10;
			$_join_clauses = join( ' ', $joins );
			$_where_clauses = ! empty( $wheres ) ? ' AND '.join( ' AND ', $wheres ) : '';
			$sql = <<<SQL
				SELECT t.term_id FROM {$wpdb->terms} AS t
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				{$_join_clauses}
				WHERE tt.taxonomy = '{$query_taxonomy}'
				{$_where_clauses}
				ORDER BY {$query_order_by}
				LIMIT {$query_limit}
			SQL;
			// dump_sql($sql);
			$term_ids = $wpdb->get_col( $sql );
			if ( empty( $term_ids ) ) {
				return;
			}

			_prime_term_caches( $term_ids );
			$terms = array_map( '\Voxel\Term::get', $term_ids );
			if ( empty( $terms ) ) {
				return;
			}
		}

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/term-feed.php' );
	}

	public function get_style_depends() {
		return [ 'vx:post-feed.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
