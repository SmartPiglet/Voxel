<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gallery extends Base_Widget {

	public function get_name() {
		return 'ts-gallery';
	}

	public function get_title() {
		return __( 'Gallery (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_gallery_content',
			[
				'label' => __( 'Images', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
			$this->add_control(
				'ts_gallery_images',
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

			$this->add_control(
				'ts_gl_column',
				[
					'label' => __( 'Columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_gl_col_gap',
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
						'{{WRAPPER}} .ts-gallery-grid' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_responsive_control(
				'ts_gl_column_no',
				[
					'label' => __( 'Number of columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 6,
					'step' => 1,
					'default' => 3,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
					],
				]
			);



			$this->add_control(
				'ts_remove_empty',
				[
					'label' => __( 'Remove empty items?', 'voxel-elementor' ),

					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'selectors' => [
						'{{WRAPPER}} .ts-empty-item' => 'display: none;',
					],
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
				'ts_gl_row',
				[
					'label' => __( 'Row height', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_gl_row_height',
				[
					'label' => __( 'Set height', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'condition' => [ 'aspect-ratio-row' => '' ],
					'range' => [
						'px' => [
							'min' => 50,
							'max' => 500,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 250,
					],
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid' => 'grid-auto-rows: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control( 'aspect-ratio-row', [
				'label' => __( 'Use aspect ratio instead?', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .ts-gallery-grid' => 'grid-auto-rows: auto;',
				],
			] );

			$this->add_responsive_control( 'vx_paragraph_gap', [
				'label' => __( 'Aspect ratio', 'voxel-backend' ),
				'description' => __( 'Set image aspect ratio e.g 16/9', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'aspect-ratio-row' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .ts-gallery li > *' => 'aspect-ratio: {{VALUE}}; object-fit: cover;',
				],
			] );



			// $this->add_control(
			// 	'ts_gl_mobile_behaviour_head',
			// 	[
			// 		'label' => __( 'Mobile', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::HEADING,
			// 		'separator' => 'before',
			// 	]
			// );

			// $this->add_control(
			// 	'ts_gl_mobile_behaviour',
			// 	[
			// 		'label' => __( 'Mobile/tablet behaviour', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SELECT,
			// 		'default' => 'ts-gallery-grid-default',
			// 		'options' => [
			// 			'ts-gallery-grid-default'  => __( 'Default', 'voxel-elementor' ),
			// 			'ts-gallery-nowrap min-scroll min-scroll-h' => __( 'Nowrap (Horizontal swipe)', 'voxel-elementor' ),
			// 		],
			// 	]
			// );

			// $this->add_control(
			// 	'ts_gl_mobile_behaviour_nowrap',
			// 	[
			// 		'label' => __( 'Mobile Nowrap settings', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::HEADING,
			// 		'separator' => 'before',
			// 		'condition' => [ 'ts_gl_mobile_behaviour' => 'ts-gallery-nowrap min-scroll min-scroll-h' ]
			// 	]
			// );

			// $this->add_responsive_control(
			// 	'ts_nowrap_item_width',
			// 	[
			// 		'label' => __( 'Item width', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SLIDER,
			// 		'size_units' => [ 'px', '%' ],
			// 		'range' => [
			// 			'px' => [
			// 				'min' => 50,
			// 				'max' => 500,
			// 				'step' => 1,
			// 			],
			// 		],
			// 		'selectors' => [
			// 			'{{WRAPPER}} .ts-gallery-nowrap > li' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
			// 		],
			// 		'condition' => [ 'ts_gl_mobile_behaviour' => 'ts-gallery-nowrap min-scroll min-scroll-h' ]
			// 	]
			// );

			// $this->add_responsive_control(
			// 	'ts_gl_mobile_col_nowrap',
			// 	[
			// 		'label' => __( 'Height', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SLIDER,
			// 		'size_units' => [ 'px', '%'],
			// 		'range' => [
			// 			'px' => [
			// 				'min' => 50,
			// 				'max' => 500,
			// 				'step' => 1,
			// 			],
			// 		],
			// 		'selectors' => [
			// 			'{{WRAPPER}} .ts-gallery-nowrap' => 'height: {{SIZE}}{{UNIT}};',
			// 		],
			// 	]
			// );

			// $this->add_responsive_control(
			// 	'ts_scroll_padding',
			// 	[
			// 		'label' => __( 'Scroll padding', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SLIDER,
			// 		'size_units' => [ 'px'],
			// 		'range' => [
			// 			'px' => [
			// 				'min' => 0,
			// 				'max' => 100,
			// 				'step' => 1,
			// 			],
			// 		],

			// 		'default' => [
			// 			'unit' => 'px',
			// 			'size' => 20,
			// 		],
			// 		'selectors' => [
			// 			'{{WRAPPER}} .ts-gallery-nowrap' => 'padding: 0 {{SIZE}}{{UNIT}}; scroll-padding: {{SIZE}}{{UNIT}}',
			// 			'{{WRAPPER}} .ts-gallery-nowrap > li:last-child' => 'margin-right: {{SIZE}}{{UNIT}}',
			// 		],
			// 		'condition' => [ 'ts_gl_mobile_behaviour' => 'ts-gallery-nowrap min-scroll min-scroll-h' ]

			// 	]
			// );

		$this->end_controls_section();
		$this->start_controls_section(
			'ts_gallery_mosaic',
			[
				'label' => __( 'Mosaic', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_mosaic_one',
				[
					'label' => __( 'First item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_one_col',
				[
					'label' => __( 'Column span', 'voxel-elementor' ),
					'description' => __( 'How many columns this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(1)' => 'grid-column-end:  span {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_one_col_start',
				[
					'label' => __( 'Column start', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'description' => __( 'The start position column for this item', 'voxel-elementor' ),
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(1)' => 'grid-column-start: {{VALUE}} !important;',
					],
				]
			);



			$this->add_responsive_control(
				'ts_mosaic_one_row',
				[
					'label' => __( 'Row span', 'voxel-elementor' ),
					'description' => __( 'How many rows this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(1)' => 'grid-row-end: span {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_one_row_start',
				[
					'label' => __( 'Row start', 'voxel-elementor' ),
					'description' => __( 'The start position row for this item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(1)' => 'grid-row-start:  {{VALUE}}!important;',
					],
				]
			);


			$this->add_control(
				'ts_mosaic_two',
				[
					'label' => __( 'Second item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_two_col',
				[
					'label' => __( 'Column span', 'voxel-elementor' ),
					'description' => __( 'How many columns this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(2)' => 'grid-column-end:  span {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_two_col_start',
				[
					'label' => __( 'Column start', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'description' => __( 'The start position column for this item', 'voxel-elementor' ),
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(2)' => 'grid-column-start: {{VALUE}} !important;',
					],
				]
			);



			$this->add_responsive_control(
				'ts_mosaic_two_row',
				[
					'label' => __( 'Row span', 'voxel-elementor' ),
					'description' => __( 'How many rows this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(2)' => 'grid-row-end: span {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_two_row_start',
				[
					'label' => __( 'Row start', 'voxel-elementor' ),
					'description' => __( 'The start position row for this item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(2)' => 'grid-row-start:  {{VALUE}}!important;',
					],
				]
			);

			$this->add_control(
				'ts_mosaic_three',
				[
					'label' => __( 'Third item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_three_col',
				[
					'label' => __( 'Column span', 'voxel-elementor' ),
					'description' => __( 'How many columns this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(3)' => 'grid-column-end:  span {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_three_col_start',
				[
					'label' => __( 'Column start', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'description' => __( 'The start position column for this item', 'voxel-elementor' ),
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(3)' => 'grid-column-start: {{VALUE}} !important;',
					],
				]
			);



			$this->add_responsive_control(
				'ts_mosaic_three_row',
				[
					'label' => __( 'Row span', 'voxel-elementor' ),
					'description' => __( 'How many rows this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(3)' => 'grid-row-end: span {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_three_row_start',
				[
					'label' => __( 'Row start', 'voxel-elementor' ),
					'description' => __( 'The start position row for this item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(3)' => 'grid-row-start:  {{VALUE}}!important;',
					],
				]
			);

			$this->add_control(
				'ts_mosaic_four',
				[
					'label' => __( 'Fourth item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_four_col',
				[
					'label' => __( 'Column span', 'voxel-elementor' ),
					'description' => __( 'How many columns this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(4)' => 'grid-column-end:  span {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_four_col_start',
				[
					'label' => __( 'Column start', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'description' => __( 'The start position column for this item', 'voxel-elementor' ),
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(4)' => 'grid-column-start: {{VALUE}} !important;',
					],
				]
			);



			$this->add_responsive_control(
				'ts_mosaic_four_row',
				[
					'label' => __( 'Row span', 'voxel-elementor' ),
					'description' => __( 'How many rows this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(4)' => 'grid-row-end: span {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_four_row_start',
				[
					'label' => __( 'Row start', 'voxel-elementor' ),
					'description' => __( 'The start position row for this item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(4)' => 'grid-row-start:  {{VALUE}}!important;',
					],
				]
			);

			$this->add_control(
				'ts_mosaic_five',
				[
					'label' => __( 'Fifth item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_five_col',
				[
					'label' => __( 'Column span', 'voxel-elementor' ),
					'description' => __( 'How many columns this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(5)' => 'grid-column-end:  span {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_five_col_start',
				[
					'label' => __( 'Column start', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'description' => __( 'The start position column for this item', 'voxel-elementor' ),
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(5)' => 'grid-column-start: {{VALUE}} !important;',
					],
				]
			);



			$this->add_responsive_control(
				'ts_mosaic_five_row',
				[
					'label' => __( 'Row span', 'voxel-elementor' ),
					'description' => __( 'How many rows this item spans in the grid', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(5)' => 'grid-row-end: span {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_mosaic_five_row_start',
				[
					'label' => __( 'Row start', 'voxel-elementor' ),
					'description' => __( 'The start position row for this item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-gallery-grid > li:nth-child(5)' => 'grid-row-start:  {{VALUE}}!important;',
					],
				]
			);



		$this->end_controls_section();

		$this->start_controls_section(
			'ts_gallery_general',
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

					$this->add_control(
						'ts_gl_general_image',
						[
							'label' => __( 'Image', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',

						]
					);

					// $this->add_responsive_control(
					// 	'ts_gl_general_image_padding',
					// 	[
					// 		'label' => __( 'Padding', 'voxel-elementor' ),
					// 		'type' => \Elementor\Controls_Manager::SLIDER,
					// 		'size_units' => [ 'px'],
					// 		'range' => [
					// 			'px' => [
					// 				'min' => 0,
					// 				'max' => 100,
					// 				'step' => 1,
					// 			],
					// 		],
					// 		'default' => [
					// 			'unit' => 'px',
					// 			'size' => 15,
					// 		],
					// 		'selectors' => [
					// 			'{{WRAPPER}} .ts-gallery li' => 'padding: {{SIZE}}{{UNIT}};',
					// 		],
					// 	]
					// );



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
							'default' => [
								'unit' => 'px',
								'size' => 10,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-gallery li a, {{WRAPPER}} .ts-empty-item > div' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_gl_general_overlay',
						[
							'label' => __( 'Overlay', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',

						]
					);

					$this->add_control(
						'ts_gl_overlay',
						[
							'label' => __( 'Overlay background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-gallery li .ts-image-overlay' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_gl_empty_item',
						[
							'label' => __( 'Empty item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_gl_empty_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-gallery li.ts-empty-item div',
						]
					);

					$this->add_responsive_control(
						'ts_gl_empty_radius',
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
								'{{WRAPPER}} .ts-gallery li.ts-empty-item div' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_gl_general_view',
						[
							'label' => __( 'View all button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',

						]
					);

					$this->add_control(
						'ts_gl_general_view_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} li.ts-gallery-last-item .ts-image-overlay' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_gl_general_view_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} li.ts-gallery-last-item i' => 'color: {{VALUE}}',
								'{{WRAPPER}} li.ts-gallery-last-item .ts-image-overlay svg' => 'fill: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_gl_general_view_icon',
						[
							'label' => __( 'Icon', 'text-domain' ),
							'type' => \Elementor\Controls_Manager::ICONS,
						]
					);

					$this->add_responsive_control(
						'ts_gl_general_view_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 70,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} li.ts-gallery-last-item i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} li.ts-gallery-last-item .ts-image-overlay svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_gl_view_text',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} li.ts-gallery-last-item p' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_gl_view_typo',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} li.ts-gallery-last-item p',
						]
					);


				$this->end_controls_tab();

				$this->start_controls_tab(
					'ts_gl_general_hover',
					[
						'label' => __( 'hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_gl_general_overlay_h',
						[
							'label' => __( 'Overlay', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',

						]
					);

					$this->add_control(
						'ts_gl_overlay_h',
						[
							'label' => __( 'Overlay background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-gallery li a:hover .ts-image-overlay' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_gl_general_view_h',
						[
							'label' => __( 'View all button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',

						]
					);

					$this->add_control(
						'ts_gl_general_view_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-gallery li.ts-gallery-last-item:hover .ts-image-overlay' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_gl_general_view_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} li.ts-gallery-last-item:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} li.ts-gallery-last-item:hover .ts-image-overlay svg' => 'fill: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_gl_view_text_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} li.ts-gallery-last-item:hover p' => 'color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$visible_count = (int) $this->get_settings_for_display( 'ts_visible_count' );
		$display_size = $this->get_settings_for_display( 'ts_display_size' );
		$lightbox_size = $this->get_settings_for_display( 'ts_lightbox_size' );
		$images_ids = $this->get_settings_for_display( 'ts_gallery_images' );

		$images = [];
		foreach ( $images_ids as $image ) {
			if ( ! ( $attachment = get_post( $image['id'] ) ) ) {
				continue;
			}

			$src_display = wp_get_attachment_image_src( $attachment->ID, $display_size );
			if ( ! $src_display ) {
				continue;
			}

			$src_large = wp_get_attachment_image_src( $attachment->ID, $lightbox_size );
			if ( ! $src_large ) {
				$src_large = $src_display;
			}

			$image_data = [
				'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'caption' => wp_get_attachment_caption( $attachment->ID ),
				'src_display' => $src_display[0],
				'src_lightbox' => $src_large[0],
				'description' => $attachment->post_content,
				'title' => $attachment->post_title,
				'id' => $attachment->ID,
				'display_size' => $display_size,
			];

			$images[] = $image_data;
		}

		if ( count( $images ) <= $visible_count ) {
			$visible = $images;
			$hidden = [];
		} else {
			$visible = array_slice( $images, 0, $visible_count - 1 );
			$hidden = array_slice( $images, $visible_count - 1 );
		}

		$is_slideshow = count( $images ) > 1;
		$filler_count = 0;
		if ( $visible_count > count( $images ) ) {
			$filler_count = $visible_count - count( $images );
		}

		$current_post = \Voxel\get_current_post();
		$gallery_id = sprintf( '%s-%s-%s', $this->get_id(), $current_post ? $current_post->get_id() : 0, wp_unique_id() );

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/gallery.php' );
	}

	public function get_style_depends() {
		return [ 'vx:gallery.css', 'e-swiper' ];
	}

	public function get_script_depends(): array {
		return [ 'swiper' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
