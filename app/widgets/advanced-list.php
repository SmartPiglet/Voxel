<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Advanced_List extends Base_Widget {

	public function get_name() {
		return 'ts-advanced-list';
	}

	public function get_title() {
		return __( 'Actions (VX)', 'voxel-elementor' );
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_action_content',
			[
				'label' => __( 'Content', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);


			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
				'ts_action_content_default',
				[
					'label' => __( 'Action content (Default)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',

				]
			);

			$actions = apply_filters( 'voxel/advanced-list/actions', [
				'none'  => __( 'None', 'voxel-elementor' ),
				'action_link' => __( 'Link', 'voxel-elementor' ),
				'add_to_cart' => __( 'Add to cart', 'voxel-elementor' ),
				'promote_post' => __( 'Promote post', 'voxel-elementor' ),
				'action_follow_post' => \Voxel\get( 'settings.timeline.enabled', true ) ? __( 'Follow post', 'voxel-elementor' ) : null,
				'action_follow' => \Voxel\get( 'settings.timeline.enabled', true ) ? __( 'Follow post author', 'voxel-elementor' ) : null,
				'edit_post' => __( 'Edit post', 'voxel-elementor' ),
				'delete_post' => __( 'Delete post', 'voxel-elementor' ),
				'unpublish_post' => __( 'Unpublish post', 'voxel-elementor' ),
				'publish_post' => __( 'Publish post', 'voxel-elementor' ),
				'show_post_on_map' => __( 'Show on map', 'voxel-elementor' ),
				'share_post' => __( 'Share post', 'voxel-elementor' ),
				'view_post_stats' => __( 'View post stats', 'voxel-elementor' ),
				'go_back' => __( 'Go back', 'voxel-elementor' ),
				'select_addition' => __( 'Select add-on', 'voxel-elementor' ),
				'back_to_top' => __( 'Back to top', 'voxel-elementor' ),
				'scroll_to_section' => __( 'Scroll to section', 'voxel-elementor' ),
				'action_gcal' => __( 'Add to Google Calendar', 'voxel-elementor' ),
				'action_ical' => __( 'Add to iCalendar', 'voxel-elementor' ),
			] );

			$repeater->add_control(
				'ts_action_type',
				[
					'label' => __( 'Choose action', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'none',
					'options' => array_filter( $actions ),
				]
			);

			$repeater->add_control( 'ts_addition_id', [
				'label' => __( 'Addition ID', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'ts_action_type' => 'select_addition' ],
			] );

			$repeater->add_control(
				'ts_action_link',
				[
					'label' => __( 'Link', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::URL,
					'placeholder' => __( 'https://your-link.com', 'voxel-elementor' ),
					'condition' => [ 'ts_action_type' => 'action_link' ],
					'show_external' => true,
					'default' => [
						'url' => '',
						'is_external' => true,
						'nofollow' => true,
					],
				]
			);

			$repeater->add_control( 'ts_scroll_to', [
				'label' => __( 'Section ID', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'ts_action_type' => 'scroll_to_section' ],
			] );

			$repeater->add_control( 'ts_action_cal_start_date', [
				'label' => __( 'Event start Date', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => [
					'dateFormat' => 'Y-m-d H:i:s',
				],
				'default' => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
				'condition' => [ 'ts_action_type' => [ 'action_gcal', 'action_ical' ] ],
			] );

			$repeater->add_control( 'ts_action_cal_end_date', [
				'label' => __( 'Event end Date', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => [
					'dateFormat' => 'Y-m-d H:i:s',
				],
				'condition' => [ 'ts_action_type' => [ 'action_gcal', 'action_ical' ] ],
			] );

			$repeater->add_control( 'ts_action_cal_title', [
				'label' => __( 'Event title', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'ts_action_type' => [ 'action_gcal', 'action_ical' ] ],
			] );

			$repeater->add_control( 'ts_action_cal_desc', [
				'label' => __( 'Event description', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'ts_action_type' => [ 'action_gcal', 'action_ical' ] ],
			] );

			$repeater->add_control( 'ts_action_cal_location', [
				'label' => __( 'Event location', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'ts_action_type' => [ 'action_gcal', 'action_ical' ] ],
			] );

			$repeater->add_control( 'ts_action_cal_url', [
				'label' => __( 'Event URL', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'ts_action_type' => [ 'action_ical' ] ],
			] );

			$repeater->add_control(
				'ts_acw_initial_text',
				[
					'label' => __( 'Text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Action', 'voxel-elementor' ),
					'placeholder' => __( 'Action title', 'voxel-elementor' ),
				]
			);

			$repeater->add_control(
				'ts_enable_tooltip',
				[
					'label' => __( 'Enable tooltip', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'On', 'voxel-elementor' ),
					'label_off' => __( 'Off', 'voxel-elementor' ),
					'return_value' => 'yes',
				]
			);

			$repeater->add_control(
				'ts_tooltip_text',
				[
					'label' => __( 'Tooltip text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Action', 'voxel-elementor' ),
					'placeholder' => __( 'Action title', 'voxel-elementor' ),
					'condition' => [ 'ts_enable_tooltip' => 'yes' ],
				]
			);

			$repeater->add_control(
				'ts_acw_initial_icon',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			/* Add to cart: Select options */

			$repeater->add_control(
				'ts_cart_opts_text',
				[
					'label' => __( 'Select Options: Text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Select options', 'voxel-elementor' ),
					'placeholder' => __( 'Select options', 'voxel-elementor' ),
					'condition' => [ 'ts_action_type' => [ 'add_to_cart' ] ],
				]
			);

			$repeater->add_control(
				'ts_cart_opts_enable_tooltip',
				[
					'label' => __( 'Select Options: Enable tooltip', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'On', 'voxel-elementor' ),
					'label_off' => __( 'Off', 'voxel-elementor' ),
					'return_value' => 'yes',
					'condition' => [ 'ts_action_type' => [ 'add_to_cart' ] ],
				]
			);

			$repeater->add_control(
				'ts_cart_opts_tooltip_text',
				[
					'label' => __( 'Select Options: Tooltip text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Action', 'voxel-elementor' ),
					'placeholder' => __( 'Action title', 'voxel-elementor' ),
					'condition' => [ 'ts_cart_opts_enable_tooltip' => 'yes', 'ts_action_type' => [ 'add_to_cart' ] ],
				]
			);

			$repeater->add_control(
				'ts_cart_opts_icon',
				[
					'label' => __( 'Select Options: Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [ 'ts_action_type' => [ 'add_to_cart' ] ],
				]
			);

			/* End: Add to cart: Select options */

			$repeater->add_control(
				'ts_acw_reveal_heading',
				[
					'label' => __( 'Active state', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [ 'ts_action_type' => [ 'action_follow', 'action_follow_post', 'action_save', 'select_addition', 'promote_post' ] ],
				]
			);

			$repeater->add_control(
				'ts_acw_reveal_text',
				[
					'label' => __( 'Text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Action', 'voxel-elementor' ),
					'placeholder' => __( 'Action title', 'voxel-elementor' ),
					'condition' => [ 'ts_action_type' => [ 'action_follow', 'action_follow_post', 'action_save', 'select_addition', 'promote_post' ] ],
				]
			);

			$repeater->add_control(
				'ts_acw_enable_tooltip',
				[
					'label' => __( 'Enable tooltip', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'On', 'voxel-elementor' ),
					'label_off' => __( 'Off', 'voxel-elementor' ),
					'return_value' => 'yes',
					'condition' => [ 'ts_action_type' => [ 'select_addition', 'promote_post', 'action_save', 'action_follow', 'action_follow_post' ] ],
				]
			);

			$repeater->add_control(
				'ts_acw_tooltip_text',
				[
					'label' => __( 'Tooltip text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Action', 'voxel-elementor' ),
					'placeholder' => __( 'Action title', 'voxel-elementor' ),
					'condition' => [ 'ts_acw_enable_tooltip' => 'yes', 'ts_action_type' => [ 'select_addition', 'promote_post', 'action_save', 'action_follow', 'action_follow_post' ] ],
				]
			);

			$repeater->add_control(
				'ts_acw_reveal_icon',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [ 'ts_action_type' => [ 'action_follow', 'action_follow_post', 'action_save', 'select_addition', 'promote_post' ] ],
				]
			);


			$repeater->add_control(
				'ts_acw_custom_style',
				[
					'label' => __( 'Custom style', 'voxel-elementor' ),
					'description' => __( 'Use custom styling for this specific item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'On', 'voxel-elementor' ),
					'label_off' => __( 'Off', 'voxel-elementor' ),
					'default' => '',
				]
			);


			$repeater->add_control(
				'ts_acw_icon_color_custom',
				[
					'label' => __( 'Icon Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} .ts-action-icon' => '--ts-icon-color: {{VALUE}}',
					],
					'condition' => [ 'ts_acw_custom_style' => 'yes' ],
				]
			);


			$repeater->add_control(
				'ts_acw_icon_color_a_custom',
				[
					'label' => __( 'Icon Color (Active)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} {{CURRENT_ITEM}} .active.ts-action-con .ts-action-icon' => '--ts-icon-color: {{VALUE}}',
					],
					'condition' => [ 'ts_acw_custom_style' => 'yes' ],
				]
			);


			$this->add_control(
				'ts_actions',
				[
					'label' => __( 'Items', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $repeater->get_controls(),
					'title_field' => '{{{ ts_action_type }}}',
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
				'ts_close_ico',
				[
					'label' => __( 'Close icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);


			$this->add_control(
				'ts_message_ico',
				[
					'label' => __( 'Direct message icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_link_ico',
				[
					'label' => __( 'Copy link icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_share_ico',
				[
					'label' => __( 'Share via icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);





		$this->end_controls_section();

		$this->start_controls_section(
			'ts_advanced_list_general',
			[
				'label' => __( 'List', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control( 'csgrid_action_on', [
				'label' => __( 'Enable CSS grid', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .ts-advanced-list' => 'display: grid;',

				],
			] );

			$this->add_responsive_control(
				'ts_cgrid_columns',
				[
					'label' => __( 'Number of columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 24,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-advanced-list' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',

					],
					'condition' => [ 'csgrid_action_on' => 'yes' ],
				]
			);







			$this->add_control(
				'ts_al_columns_no',
				[
					'label' => __( 'Item width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'elementor-col-auto',
					'options' => [
						'elementor-col-auto'  => __( 'Auto', 'voxel-elementor' ),
						'elementor-col-cstm'  => __( 'Custom item width', 'voxel-elementor' ),
					],
					  'condition' => [ 'csgrid_action_on' => '' ],

				]
			);

			$this->add_responsive_control(
				'ts_al_columns_cstm',
				[
					'label' => __( 'Width (px)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 50,
							'max' => 200,
							'step' => 1,
						],
					],
					'condition' => [ 'ts_al_columns_no' => 'elementor-col-cstm' ],
					'selectors' => [
						'{{WRAPPER}} .ts-advanced-list .ts-action' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_al_justify',
				[
					'label' => __( 'Justify', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'left'  => __( 'Left', 'voxel-elementor' ),
						'center' => __( 'Center', 'voxel-elementor' ),
						'right' => __( 'Right', 'voxel-elementor' ),
						'space-between' => __( 'Space between', 'voxel-elementor' ),
						'space-around' => __( 'Space around', 'voxel-elementor' ),
					],
					'selectors' => [
						'{{WRAPPER}} .ts-advanced-list' => 'justify-content: {{VALUE}}',
					],
					  'condition' => [ 'csgrid_action_on' => '' ],
				]
			);
			$this->add_responsive_control(
				'ts_cgrid_gap',
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
						'{{WRAPPER}} .ts-advanced-list' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);



		$this->end_controls_section();

		$this->start_controls_section(
			'ts_advanced_list_item',
			[
				'label' => __( 'List item', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'al_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'al_normal_tab',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'al_item_general',
						[
							'label' => __( 'General', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_al_align',
						[
							'label' => __( 'Justify content', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::CHOOSE,

							'options' => [
								'flex-start'  => [ 'Left', 'voxel-elementor', 'icon' => 'eicon-text-align-left', ],
								'center' => [ 'Center', 'voxel-elementor', 'icon' => 'eicon-text-align-center', ],
								'flex-end' => [ 'Right', 'voxel-elementor',  'icon' => 'eicon-text-align-right',],
							],

							'selectors' => [
								'{{WRAPPER}} .ts-action  .ts-action-con' => 'justify-content: {{VALUE}};',
							],
						]
					);




					$this->add_responsive_control(
						'ts_action_padding',
						[
							'label' => __( 'Item padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-action  .ts-action-con' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);




					$this->add_responsive_control(
						'ts_acw_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 200,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-action  .ts-action-con' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$this->add_control(
						'al_item_border',
						[
							'label' => __( 'Border', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_acw_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-action-con',
						]
					);

					$this->add_control(
						'ts_acw_border_radius',
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
								'{{WRAPPER}} .ts-action-con' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_acw_border_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}}  .ts-action-con',
						]
					);

					$this->add_control(
						'al_item_typo',
						[
							'label' => __( 'Typography', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_acw_typography',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}}  .ts-action-con',
						]
					);

					$this->add_control(
						'ts_item_colors',
						[
							'label' => __( 'Item colors', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_initial_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con' => 'color: {{VALUE}}',
							],
						]
					);


					$this->add_control(
						'ts_acw_initial_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .ts-action-con' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_container',
						[
							'label' => __( 'Icon container', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_icon_con_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-icon' => 'background: {{VALUE}}',
							],
						]
					);





					$this->add_responsive_control(
						'ts_acw_icon_con_size',
						[
							'label' => __( 'Size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 30,
									'max' => 70,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-action-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_acw_icon_con_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-action-icon',
						]
					);

					$this->add_control(
						'ts_acw_icon_con_radius',
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
								'size' => 26,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-action-icon' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_acw_con_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}}  .ts-action-icon',
						]
					);

					$this->add_responsive_control(
						'ts_acw_icon_margin',
						[
							'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 20,
									'step' => 1,
								],
							],
							'default' => [
								'unit' => 'px',
								'size' => 0,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-action-con, {{WRAPPER}} .ts-action span' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_heading',
						[
							'label' => __( 'Icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_icon_orient',
						[
							'label' => __( 'Icon on top?', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'label_on' => __( 'Yes', 'voxel-elementor' ),
							'label_off' => __( 'No', 'voxel-elementor' ),
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .ts-action-con, {{WRAPPER}} .ts-action-con span' => 'flex-direction: column;',
							],
						]
					);


					$this->add_responsive_control(
						'ts_acw_icon_size',
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
							'default' => [
								'unit' => 'px',
								'size' => 26,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-action-icon' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_color',
						[
							'label' => __( 'Icon Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-icon' => '--ts-icon-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_action_margin',
						[
							'label' => __( 'Margin (Deprecated)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-action' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);






				$this->end_controls_tab();

				/* Hover tab */

				$this->start_controls_tab(
					'al_hover_tab',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_acw_border_shadow_h',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-action-con:hover',
						]
					);

					$this->add_control(
						'ts_item_colors_h',
						[
							'label' => __( 'Item colors', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_border_h',
						[
							'label' => __( 'Border color (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con:hover' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_initial_color_h',
						[
							'label' => __( 'Text color (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_initial_bg_h',
						[
							'label' => __( 'Background color (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con:hover' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_con_hover',
						[
							'label' => __( 'Icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_icon_con_bg_h',
						[
							'label' => __( 'Background (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con:hover .ts-action-icon' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_con_bord_h',
						[
							'label' => __( 'Border (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con:hover .ts-action-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_color_h',
						[
							'label' => __( 'Icon Color (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-action-con:hover .ts-action-icon' => '--ts-icon-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				/* Active tab */

				$this->start_controls_tab(
					'al_active_tab',
					[
						'label' => __( 'Active', 'voxel-elementor' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_acw_border_shadow_a',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .active.ts-action-con',
						]
					);

					$this->add_control(
						'ts_item_colors_a',
						[
							'label' => __( 'Item colors', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_initial_color_a',
						[
							'label' => __( 'Text color (Active)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .active.ts-action-con' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_initial_bg_a',
						[
							'label' => __( 'Background color (Active)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .active.ts-action-con' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_initial_border_a',
						[
							'label' => __( 'Border color (Active)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .active.ts-action-con' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_con_active',
						[
							'label' => __( 'Icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_acw_icon_con_bg_a',
						[
							'label' => __( 'Background (Active)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .active.ts-action-con .ts-action-icon' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_con_bord_a',
						[
							'label' => __( 'Border (Active)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .active.ts-action-con .ts-action-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_acw_icon_color_a',
						[
							'label' => __( 'Icon Color (Active)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .active.ts-action-con .ts-action-icon' => '--ts-icon-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_tooltips',
			[
				'label' => __( 'Tooltips', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'tooltip_bottom',
				[
					'label' => __( 'Display below item?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'none',
					'selectors' => [
						'{{WRAPPER}} .ts-action::after' => 'top: calc(100% + 5px); bottom: auto;',
					],

				]
			);

			$this->add_control(
				'ts_tooltip_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-action::after' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_tooltip_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-action::after',
				]
			);
			$this->add_control(
				'ts_tooltip_bg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-action::after' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_tooltip_radius',
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
						'{{WRAPPER}} .ts-action::after' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);



		$this->end_controls_section();





	}

	protected function render( $instance = [] ) {
		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/advanced-list.php' );
	}

	public function get_style_depends() {
		return [ 'vx:action.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
