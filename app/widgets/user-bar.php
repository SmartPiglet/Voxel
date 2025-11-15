<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User_Bar extends Base_Widget {

	public function get_name() {
		return 'ts-user-bar';
	}

	public function get_title() {
		return __( 'User bar (VX)', 'voxel-elementor' );
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {


		$this->start_controls_section(
			'user_area_repeater',
			[
				'label' => __( 'User area components', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
				'ts_component_heading',
				[
					'label' => __( 'Component details', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',

				]
			);

			$repeater->add_control(
				'ts_component_type',
				[
					'label' => __( 'Component type', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'none',
					'options' => [
						'notifications'  => __( 'Notifications', 'voxel-elementor' ),
						'cart' => __( 'Cart', 'voxel-elementor' ),
						'messages' => __( 'Messages', 'voxel-elementor' ),
						'user_menu' => __( 'User Menu', 'voxel-elementor' ),
						'select_wp_menu' => __( 'Menu', 'voxel-elementor' ),
						'link' => __( 'Custom link', 'voxel-elementor' ),
					],
				]
			);

			$repeater->add_control(
				'ts_choose_menu',
				[
					'label' => __( 'Choose menu', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'condition' => [ 'ts_component_type' => [ 'select_wp_menu', 'user_menu' ] ],
					'options' => get_registered_nav_menus(),
				]
			);

			$repeater->add_control(
				'choose_component_icon',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [
						'value' => 'las la-bell',
						'library' => 'la-solid',
					],
				]
			);

			$repeater->add_control(
				'component_url',
				[
					'label' => __( 'Link', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::URL,
					'condition' => [ 'ts_component_type' => 'link' ],
					'placeholder' => __( 'https://your-link.com', 'voxel-elementor' ),
					'show_external' => true,
					'default' => [
						'url' => '',
						'is_external' => true,
						'nofollow' => true,
					],
				]
			);


			$repeater->add_control(
				'component_title',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => __( 'Type your title here', 'voxel-elementor' ),
					'condition' => [ 'ts_component_type' => 'link' ],
				]
			);


			$repeater->add_control(
				'messages_title',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => __( 'Type your title here', 'voxel-elementor' ),
					'default' => __( 'Messages', 'voxel-elementor' ),
					'condition' => [ 'ts_component_type' => 'messages' ],
				]
			);

			$repeater->add_control(
				'wp_menu_title',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => __( 'Type your title here', 'voxel-elementor' ),
					'default' => __( 'Menu', 'voxel-elementor' ),
					'condition' => [ 'ts_component_type' => 'select_wp_menu' ],
				]
			);

			$repeater->add_control(
				'notifications_title',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => __( 'Type your title here', 'voxel-elementor' ),
					'default' => __( 'Notifications', 'voxel-elementor' ),
					'condition' => [ 'ts_component_type' => 'notifications' ],
				]
			);
			$repeater->add_control(
				'cart_title',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => __( 'Type your title here', 'voxel-elementor' ),
					'default' => __( 'Cart', 'voxel-elementor' ),
					'condition' => [ 'ts_component_type' => 'cart' ],
				]
			);

			$repeater->add_control(
				'label_visibility',
				[
					'label' => __( 'Enable label visibility', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'return_value' => 'yes',
					'default' => 'no'
				]
			);

			// $repeater->add_control(
			// 	'label_visibility_desktop',
			// 	[
			// 		'label' => __( 'Show on Desktop', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => __( 'Show', 'voxel-elementor' ),
			// 		'label_off' => __( 'Hide', 'voxel-elementor' ),
			// 		'return_value' => 'flex',
			// 		'default' => 'none',
			// 		'selectors' => [
			// 			'(desktop){{WRAPPER}} {{CURRENT_ITEM}} .ts_comp_label' => 'display: {{VALUE}}',
			// 		],
			// 		'condition' => [ 'label_visibility' => 'yes' ],
			// 	]
			// );
			$repeater->add_control(
				'label_visibility_desktop',
				[
					'label' => __( 'Show on desktop', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'flex'  => __( 'Show', 'voxel-elementor' ),
						'none' => __( 'Hide', 'voxel-elementor' ),
					],

					'selectors' => [
						'(desktop){{WRAPPER}} {{CURRENT_ITEM}} .ts_comp_label' => 'display: {{VALUE}}',
					],
					'condition' => [ 'label_visibility' => 'yes' ],
				]
			);

			// $repeater->add_control(
			// 	'label_visibility_tablet',
			// 	[
			// 		'label' => __( 'Show on Tablet', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => __( 'Show', 'voxel-elementor' ),
			// 		'label_off' => __( 'Hide', 'voxel-elementor' ),
			// 		'return_value' => 'flex',
			// 		'default' => 'none',
			// 		'selectors' => [
			// 			'(tablet){{WRAPPER}} {{CURRENT_ITEM}} .ts_comp_label' => 'display: {{VALUE}}',
			// 		],
			// 		'condition' => [ 'label_visibility' => 'yes' ],
			// 	]
			// );

			$repeater->add_control(
				'label_visibility_tablet',
				[
					'label' => __( 'Show on tablet', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'flex'  => __( 'Show', 'voxel-elementor' ),
						'none' => __( 'Hide', 'voxel-elementor' ),
					],

					'selectors' => [
						'(tablet){{WRAPPER}} {{CURRENT_ITEM}} .ts_comp_label' => 'display: {{VALUE}}',
					],
					'condition' => [ 'label_visibility' => 'yes' ],
				]
			);

			// $repeater->add_control(
			// 	'label_visibility_mobile',
			// 	[
			// 		'label' => __( 'Show on Mobile', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => __( 'Show', 'voxel-elementor' ),
			// 		'label_off' => __( 'Hide', 'voxel-elementor' ),
			// 		'return_value' => 'flex',
			// 		'default' => 'none',
			// 		'selectors' => [
			// 			'(mobile){{WRAPPER}} {{CURRENT_ITEM}} .ts_comp_label' => 'display: {{VALUE}}',
			// 		],
			// 		'condition' => [ 'label_visibility' => 'yes' ],
			// 	]
			// );

			$repeater->add_control(
				'label_visibility_mobile',
				[
					'label' => __( 'Show on mobile', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'flex'  => __( 'Show', 'voxel-elementor' ),
						'none' => __( 'Hide', 'voxel-elementor' ),
					],

					'selectors' => [
						'(mobile){{WRAPPER}} {{CURRENT_ITEM}} .ts_comp_label' => 'display: {{VALUE}}',
					],
					'condition' => [ 'label_visibility' => 'yes' ],
				]
			);

			$repeater->add_control(
				'component_visibility',
				[
					'label' => __( 'Component visibility', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'return_value' => 'yes',
					'default' => 'no'
				]
			);

			$repeater->add_control(
				'user_bar_visibility_desktop',
				[
					'label' => __( 'Show on desktop', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'flex'  => __( 'Show', 'voxel-elementor' ),
						'none' => __( 'Hide', 'voxel-elementor' ),
					],

					'selectors' => [
						'(desktop){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: {{VALUE}}',
					],
					'condition' => [ 'component_visibility' => 'yes' ],
				]
			);

			// $repeater->add_control(
			// 	'user_bar_visibility_desktop',
			// 	[
			// 		'label' => __( 'Show on Desktop', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => __( 'Show', 'voxel-elementor' ),
			// 		'label_off' => __( 'Hide', 'voxel-elementor' ),
			// 		'return_value' => 'flex',
			// 		'default' => 'none',
			// 		'selectors' => [
			// 			'(desktop){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: {{VALUE}}',
			// 		],
			// 		'condition' => [ 'component_visibility' => 'yes' ],
			// 	]
			// );


			$repeater->add_control(
				'user_bar_visibility_tablet',
				[
					'label' => __( 'Show on tablet', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'flex'  => __( 'Show', 'voxel-elementor' ),
						'none' => __( 'Hide', 'voxel-elementor' ),
					],

					'selectors' => [
						'(tablet){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: {{VALUE}}',
					],
					'condition' => [ 'component_visibility' => 'yes' ],
				]
			);


			// $repeater->add_control(
			// 	'user_bar_visibility_tablet',
			// 	[
			// 		'label' => __( 'Show on tablet', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => __( 'Show', 'voxel-elementor' ),
			// 		'label_off' => __( 'Hide', 'voxel-elementor' ),
			// 		'return_value' => 'flex',
			// 		'default' => 'none',
			// 		'selectors' => [
			// 			'(tablet){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: {{VALUE}}',
			// 		],
			// 		'condition' => [ 'component_visibility' => 'yes' ],
			// 	]
			// );


			$repeater->add_control(
				'user_bar_visibility_mobile',
				[
					'label' => __( 'Show on mobile', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'flex'  => __( 'Show', 'voxel-elementor' ),
						'none' => __( 'Hide', 'voxel-elementor' ),
					],

					'selectors' => [
						'(mobile){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: {{VALUE}}',
					],
					'condition' => [ 'component_visibility' => 'yes' ],
				]
			);


			// $repeater->add_control(
			// 	'user_bar_visibility_mobile',
			// 	[
			// 		'label' => __( 'Show on Mobile', 'voxel-elementor' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => __( 'Show', 'voxel-elementor' ),
			// 		'label_off' => __( 'Hide', 'voxel-elementor' ),
			// 		'return_value' => 'flex',
			// 		'default' => 'none',
			// 		'selectors' => [
			// 			'(mobile){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: {{VALUE}}',
			// 		],
			// 		'condition' => [ 'component_visibility' => 'yes' ],
			// 	]
			// );




			$this->add_control(
				'ts_userbar_items',
				[
					'label' => __( 'Items', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $repeater->get_controls(),
					'title_field' => '{{{ ts_component_type }}}',
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
				'ts_nav_dropdown_icon',
				[
					'label' => __( 'Down arrow', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_arrow_right',
				[
					'label' => __( 'Right arrow', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_arrow_left',
				[
					'label' => __( 'Left arrow', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
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
				'ts_trash_ico',
				[
					'label' => __( 'Trash icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_envelop_ico',
				[
					'label' => __( 'Inbox icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$this->add_control(
				'ts_load_ico',
				[
					'label' => __( 'Load more icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

		$this->end_controls_section();

		/* User area action styling */

		$this->start_controls_section(
			'ts_action_styling',
			[
				'label' => __( 'User area: General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_action_styling_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_action_styling_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_action_justify',
						[
							'label' => __( 'Align items', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-user-area > ul' => 'justify-content: {{VALUE}}',
							],
						]
					);






					$this->add_control(
						'ts_comp_items',
						[
							'label' => __( 'Item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_comp_orientation',
						[
							'label' => __( 'Vertical orientation?', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'label_on' => __( 'Yes', 'voxel-elementor' ),
							'label_off' => __( 'No', 'voxel-elementor' ),
							'return_value' => 'column',
							'default' => 'initial',
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'flex-direction: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_comp_col_align',
						[
							'label' => __( 'Align item content', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SELECT,
							'default' => 'left',
							'options' => [
								'left'  => __( 'Left', 'voxel-elementor' ),
								'center' => __( 'Center', 'voxel-elementor' ),
								'right' => __( 'Right', 'voxel-elementor' ),
							],

							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'align-items: {{VALUE}}',
							],
							'condition' => [ 'ts_comp_orientation' => 'column' ],
						]
					);

					$this->add_responsive_control(
						'ts_link_gap',
						[
							'label' => __( 'Gap', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 50,
									'step' => 1,
								],
							],

							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_link_margin',
						[
							'label' => __( 'Margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_link_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_navbar_link_bg',
						[
							'label' => __( 'Item background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_navbar_link_border',
						[
							'label' => __( 'Item border radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_navbar_link_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-user-area > ul > li > a',
						]
					);

					$this->add_responsive_control(
						'ts_action_icon_margin',
						[
							'label' => __( 'Item content gap', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 50,
									'step' => 1,
								],
							],

							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_comp_icon_heading',
						[
							'label' => __( 'Item icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_action_icon_con_size',
						[
							'label' => __( 'Container size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 30,
									'max' => 80,
									'step' => 1,
								],
							],
							'default' => [
								'unit' => 'px',
								'size' => 40,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area .ts-comp-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_action_icon_con_radius',
						[
							'label' => __( 'Container border radius', 'voxel-elementor' ),
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
								'size' => 40,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area .ts-comp-icon' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_action_icon_con_bg',
						[
							'label' => __( 'Container background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area .ts-comp-icon' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_action_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
							'description' => __( 'Must be equal or smaller than icon container', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 20,
									'max' => 50,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area .ts-comp-icon' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_action_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area .ts-comp-icon' => '--ts-icon-color: {{VALUE}}',
							],
						]
					);




					$this->add_control(
						'ts_action_indicator_color',
						[
							'label' => __( 'Unread indicator color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area span.unread-indicator' => 'background: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_action_indicator_margin',
						[
							'label' => __( 'Indicator top margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 50,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area span.unread-indicator' => 'top: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_indicator_size',
						[
							'label' => __( 'Indicator size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 50,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area span.unread-indicator' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_action_avatar',
						[
							'label' => __( 'Avatar', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_action_avatar_size',
						[
							'label' => __( 'Avatar size', 'voxel-elementor' ),
							'description' => __( 'Must be equal or smaller than icon container', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 20,
									'max' => 50,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li.ts-user-area-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_action_avatar_radius',
						[
							'label' => __( 'Avatar radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-user-area > ul > li.ts-user-area-avatar img' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_comp_item_text',
						[
							'label' => __( 'Item label', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_action_text',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-user-area .ts_comp_label',
						]
					);

					$this->add_control(
						'ts_action_text_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area .ts_comp_label' => 'color: {{VALUE}}',
							],
						]
					);


					$this->add_control(
						'ts_comp_item_chevron',
						[
							'label' => __( 'Chevron', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_dropdown_icon_color',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'ts_hide_chevron',
						[

							'label' => __( 'Hide chevron', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'label_on' => __( 'Hide', 'voxel-elementor' ),
							'label_off' => __( 'Show', 'voxel-elementor' ),
							'return_value' => 'yes',

							'selectors' => [
								'{{WRAPPER}} .ts-down-icon' => 'display: none !important;',
							],
						]
					);






				$this->end_controls_tab();

				/* Hover tab */

				$this->start_controls_tab(
					'ts_action_styling_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_navbar_link_bg_h',
						[
							'label' => __( 'Item background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '#fff',
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_action_icon_con_bg_hover',
						[
							'label' => __( 'Icon container background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a:hover .ts-comp-icon' => 'background-color: {{VALUE}}',
							],
						]
					);


					$this->add_control(
						'ts_action_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a:hover .ts-comp-icon' => '--ts-icon-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_action_text_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a:hover .ts_comp_label' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_navbar_link_shadow_h',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-user-area > ul > li > a:hover',
						]
					);

					$this->add_control(
						'ts_dropdown_icon_color_h',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-user-area > ul > li > a:hover .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();
			$this->end_controls_tabs();

		$this->end_controls_section();


		$this->start_controls_section(
					'custom_popup',
					[
						'label' => __( 'Popups: Custom style', 'voxel-elementor' ),
						'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

					$this->add_control(
						'custom_popup_enable',
						[
							'label' => __( 'Enable custom style', 'voxel-elementor' ),
							'description' => __( 'In wp-admin > templates > Style kits > Popup styles you can control the global popup styles that affect all the popups on the site. Enabling this option will override some of those styles only for this specific widget.', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'label_on' => __( 'Yes', 'voxel-elementor' ),
							'label_off' => __( 'No', 'voxel-elementor' ),
						]
					);

					$this->add_control(
						'custm_pg_backdrop',
						[
							'label' => __( 'Backdrop background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'condition' => [ 'custom_popup_enable' => 'yes' ],
							'selectors' => [
								'{{WRAPPER}}-wrap > div:after' => 'background-color: {{VALUE}} !important',
							],
						]
					);


					$this->add_control(
						'popup_pointer_events',
						[
							'label' => __( 'Enable pointer events for backdrop?', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'return_value' => 'none',
							'selectors' => [
								'{{WRAPPER}}-wrap > div:after' => 'pointer-events:all;',
							],
							'condition' => [ 'custom_popup_enable' => 'yes' ],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'pg_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-field-popup',
							'condition' => [ 'custom_popup_enable' => 'yes' ],
						]
					);

					$this->add_responsive_control(
						'custom_pg_top_margin',
						[
							'label' => __( 'Top / Bottom margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'description' => __( 'Does not affect mobile', 'voxel-elementor' ),
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 200,
									'step' => 1,
								],
							],
							'condition' => [ 'custom_popup_enable' => 'yes' ],
							'selectors' => [
								'{{WRAPPER}} .ts-field-popup-container' => 'margin: {{SIZE}}{{UNIT}} 0;',
							],
						]
					);







					$this->add_control(
						'custom_max_height',
						[
							'label' => __( 'Max height', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'description' => __( 'Does not affect mobile', 'voxel-elementor' ),
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 800,
									'step' => 1,
								],
							],
							'condition' => [ 'custom_popup_enable' => 'yes' ],
							'selectors' => [
								'{{WRAPPER}} .ts-popup-content-wrapper' => 'max-height: {{SIZE}}{{UNIT}};',
							],
						]
					);






				$this->end_controls_section();




	}

	protected function render( $instance = [] ) {
		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/user-bar.php' );

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_static_popups();' );
		}
	}



	public function get_script_depends() {
		return [
			'vx:user-bar.js',
		];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
