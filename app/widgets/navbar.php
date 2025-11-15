<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navbar extends Base_Widget {

	public function get_name() {
		return 'ts-navbar';
	}

	public function get_title() {
		return __( 'Navbar (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {



		/*
		=========================
		Navbar(27) widget options
		=========================
		*/






		/*
		=======
		Source
		=======
		*/

		$this->start_controls_section(
			'ts_navbar_source',
			[
				'label' => __( 'Source', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'navbar_choose_source',
				[
					'label' => __( 'Choose source', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'add_links_manually',
					'options' => [
						'add_links_manually' => __( 'Add links manually', 'voxel-elementor' ),
						'select_wp_menu'  => __( 'Select existing menu', 'voxel-elementor' ),
						'template_tabs'  => __( 'Link to Template Tabs widget', 'voxel-elementor' ),
						'search_form'  => __( 'Link to Search Form widget', 'voxel-elementor' ),
					],
				]
			);

			$this->add_control(
				'ts_choose_menu',
				[
					'label' => __( 'Choose menu', 'voxel-elementor' ),
					'description' => 'Choose the navigation menu to display',
					'type' => \Elementor\Controls_Manager::SELECT,
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],
					'options' => get_registered_nav_menus(),
					'default' => 'voxel-desktop-menu',
				]
			);

			$this->add_control(
				'ts_choose_mobile_menu',
				[
					'label' => __( 'Hamburger menu', 'voxel-elementor' ),
					'description' => 'Choose the menu which is displayed when hamburger icon is clicked',
					'type' => \Elementor\Controls_Manager::SELECT,
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],
					'options' => get_registered_nav_menus(),
					'default' => 'voxel-mobile-menu',
				]
			);

			$this->add_control( 'ts_tabs_widget', [
				'label' => __( 'Link to a Template Tabs widget', 'voxel-elementor' ),
				'description' => 'Navbar will be automatically populated with links to each tab added in the Template Tabs widget.',
				'type' => 'voxel-relation',
				'vx_group' => 'tabsToNavbar',
				'vx_target' => 'elementor-widget-ts-template-tabs',
				'vx_side' => 'right',
				'condition' => [ 'navbar_choose_source' => 'template_tabs' ],
			] );

			$this->add_control( 'ts_search_widget', [
				'label' => __( 'Link to a Search Form widget', 'voxel-elementor' ),
				'description' => 'Navbar will be automatically populated with links to each post type used in the Search Form widget.',
				'type' => 'voxel-relation',
				'vx_group' => 'searchToNavbar',
				'vx_target' => 'elementor-widget-ts-search-form',
				'vx_side' => 'right',
				'condition' => [ 'navbar_choose_source' => 'search_form' ],
			] );

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_navbar_settings',
			[
				'label' => __( 'Settings', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);


			$this->add_control(
				'ts_navbar_orientation',
				[
					'label' => __( 'Orientation', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'horizontal',
					'options' => [
						'horizontal'  => __( 'Horizontal', 'voxel-elementor' ),
						'vertical' => __( 'Vertical', 'voxel-elementor' ),
					],
				]
			);

			$this->add_control(
				'ts_collapsed',
				[
					'label' => __( 'Collapsible?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'condition' => [ 'ts_navbar_orientation' => 'vertical' ],
				]
			);

			$this->add_responsive_control(
				'collapsible_min_width',
				[
					'label' => __( 'Collapsed width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'condition' => [ 'ts_collapsed' => 'yes' ],
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-nav-collapsed' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'collapsible_max_width',
				[
					'label' => __( 'Expanded width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'condition' => [ 'ts_collapsed' => 'yes' ],
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}}:hover .ts-nav-collapsed' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_navbar_justify',
				[
					'label' => __( 'Justify', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'condition' => [ 'ts_navbar_orientation' => 'horizontal' ],
					'options' => [
						'left'  => __( 'Left', 'voxel-elementor' ),
						'center' => __( 'Center', 'voxel-elementor' ),
						'right' => __( 'Right', 'voxel-elementor' ),
						'space-between' => __( 'Space between', 'voxel-elementor' ),
						'space-around' => __( 'Space around', 'voxel-elementor' ),
					],

					'selectors' => [
						'{{WRAPPER}} .ts-nav' => 'justify-content: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'ts_burger_settings',
				[
					'label' => __( 'Hamburger menu', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],

				]
			);

			$this->add_control(
				'hamburger_title',
				[
					'label' => esc_html__( 'Menu title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Menu', 'voxel-elementor' ),
					'placeholder' => esc_html__( 'Type text', 'voxel-elementor' ),
				]
			);


			$this->add_control(
				'show_burger_desktop',
				[
					'label' => __( 'Show on desktop', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],
					'selectors' => [
						'(desktop){{WRAPPER}} .ts-mobile-menu' => 'display: flex;',
						'(desktop){{WRAPPER}} .ts-wp-menu .menu-item' => 'display: none;',
					],
				]
			);

			$this->add_control(
				'show_burger_tablet',
				[
					'label' => __( 'Show on tablet and mobile', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'return_value' => 'yes',
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],
					'default' => 'yes',
					'selectors' => [
						'(tablet){{WRAPPER}} .ts-mobile-menu' => 'display: flex;',
						'(tablet){{WRAPPER}} .ts-wp-menu .menu-item' => 'display: none;',
					],
				]
			);

			$this->add_control(
				'show_menu_label',
				[
					'label' => __( 'Show label?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],
					'return_value' => 'yes',
					'default' => 'no',
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
				'ts_mobile_menu_icon',
				[
					'label' => __( 'Hamburger', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [ 'navbar_choose_source' => 'select_wp_menu' ],
				]
			);





			$this->add_control(
				'ts_close_ico',
				[
					'label' => __( 'Close icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

		$this->end_controls_section();



		/*
		================
		Content repeater
		=================
		*/

		$this->start_controls_section(
			'ts_navbar_content',
			[
				'label' => __( 'Content', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [ 'navbar_choose_source' => 'add_links_manually' ],
			]
		);


			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
				'ts_navbar_item',
				[
					'label' => __( 'Navbar item', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',

				]
			);


			$repeater->add_control(
				'ts_navbar_item_text',
				[
					'label' => __( 'Title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Action', 'voxel-elementor' ),
					'placeholder' => __( 'Action title', 'voxel-elementor' ),
				]
			);

			$repeater->add_control(
				'ts_navbar_item_icon',
				[
					'label' => __( 'Icon', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
				]
			);

			$repeater->add_control(
				'ts_navbar_item_link',
				[
					'label' => __( 'Link', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::URL,
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
				'navbar_item__active',
				[
					'label' => __( 'Active?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'On', 'voxel-elementor' ),
					'label_off' => __( 'Off', 'voxel-elementor' ),
					'return_value' => 'current-menu-item',

				]
			);




			$this->add_control(
				'ts_navbar_items',
				[
					'label' => __( 'Items', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $repeater->get_controls(),
					'title_field' => '{{{ ts_navbar_item_text }}}',
				]
			);

		$this->end_controls_section();





		/*
		===============
		Navbar: General
		===============
		*/

		$this->start_controls_section(
			'ts_nav_style',
			[
				'label' => __( 'Navbar: General', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'style_tabs'
			);
				/* Normal tab */

				$this->start_controls_tab(
					'style_normal_tab',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'ts_comp_text',
						[
							'label' => __( 'Menu item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_content_typography',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-item-link > span',
						]
					);

					$this->add_responsive_control(
						'ts_navbar_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link > span' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_navbar_link_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link' => 'background-color: {{VALUE}}',
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
								'{{WRAPPER}} .ts-item-link' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-item-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_navbar_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-nav-menu .ts-item-link',
						]
					);

					$this->add_responsive_control(
						'ts_navbar_border_radius',
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
								'{{WRAPPER}} .ts-item-link' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
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
								'{{WRAPPER}}  .ts-item-link' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);



					$this->add_control(
						'ts_comp_icon_heading',
						[
							'label' => __( 'Menu item icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_action_icon_show',
						[
							'label' => __( 'Show icon', 'voxel-elementor' ),
							'description' => __( 'Desktop only', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SELECT,
							'default' => 'flex',
							'options' => [
								'flex'  => __( 'Yes', 'voxel-elementor' ),
								'none' => __( 'No', 'voxel-elementor' ),
							],

							'selectors' => [
								'{{WRAPPER}} .menu-item .ts-item-icon' => 'display: {{VALUE}}',
							],
						]
					);


					$this->add_responsive_control(
						'action_icon_orient',
						[
							'label' => __( 'Icon on top?', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SWITCHER,
							'label_on' => __( 'Yes', 'voxel-elementor' ),
							'label_off' => __( 'No', 'voxel-elementor' ),
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .ts-item-link' => 'flex-direction: column;',
								'{{WRAPPER}} .ts-down-icon' => 'display: none;',
							],
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
							'selectors' => [
								'{{WRAPPER}} .ts-item-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
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
							'selectors' => [
								'{{WRAPPER}} .ts-item-icon' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_action_icon_con_bg',
						[
							'label' => __( 'Container background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-icon' => 'background-color: {{VALUE}}',
							],
						]
					);



					$this->add_responsive_control(
						'ts_action_icon_margin_bottom',
						[
							'label' => __( 'Container bottom margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 50,
									'step' => 1,
								],
							],
							'condition' => [ 'action_icon_orient' => 'yes' ],
							'selectors' => [
								'{{WRAPPER}}  .ts-item-link .ts-item-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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
								'{{WRAPPER}} .ts-item-icon' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_action_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-icon' => '--ts-icon-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'ts_menu_hscroll',
						[
							'label' => __( 'Horizontal scroll', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_hscroll_color',
						[
							'label' => __( 'Scroll background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-nav-horizontal.min-scroll' => '--ts-scroll-color: {{VALUE}}',
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





				$this->end_controls_tab();

				/* Hover tab */

				$this->start_controls_tab(
					'style_hover_tab',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_comp_text_hover',
						[
							'label' => __( 'Menu item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_navbar_color_hover',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link:hover > span' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_navbar_link_bg_hover',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_navbar_link_border_hover',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link:hover' => 'border-color: {{VALUE}}',
							],
						]
					);



					$this->add_control(
						'ts_dropdown_icon_color_hover',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link:hover .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_comp_icon_heading_hover',
						[
							'label' => __( 'Menu item icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_action_icon_color_hover',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link:hover .ts-item-icon' => '--ts-icon-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_action_icon_con_bg_hover',
						[
							'label' => __( 'Icon container background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-item-link:hover .ts-item-icon' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				/* Current item tab */

				$this->start_controls_tab(
					'style_active_tab',
					[
						'label' => __( 'Current', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'ts_comp_text_current',
						[
							'label' => __( 'Menu item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_content_typography_c',
							'label' => __( 'Typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} li.current-menu-item > .ts-item-link > span',
						]
					);

					$this->add_control(
						'ts_navbar_color_current',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .current-menu-item .ts-item-link > span' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_navbar_link_bg_current',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .current-menu-item  .ts-item-link' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_navbar_link_border_current',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .current-menu-item .ts-item-link' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_dropdown_icon_color_current',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .current-menu-item .ts-down-icon' => 'border-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'ts_comp_icon_heading_current',
						[
							'label' => __( 'Menu item icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_action_icon_color_current',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .current-menu-item .ts-item-icon' => '--ts-icon-color: {{VALUE}} !important;',
							],
						]
					);

					$this->add_control(
						'ts_action_icon_con_bg_current',
						[
							'label' => __( 'Icon container background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .current-menu-item .ts-item-icon, {{WRAPPER}} .current-menu-item .ts-item-link:hover .ts-item-icon' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'current_ico_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .current-menu-item .ts-item-icon, {{WRAPPER}} .current-menu-item .ts-item-link:hover .ts-item-icon',
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
				'custom_pg_width',
				[
					'label' => __( 'Min width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'description' => __( 'Does not affect mobile', 'voxel-elementor' ),
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 800,
							'step' => 1,
						],
					],
					'condition' => [ 'custom_popup_enable' => 'yes' ],
					'selectors' => [
						'{{WRAPPER}} .ts-field-popup' => 'min-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'custom_max_width',
				[
					'label' => __( 'Max width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'description' => __( 'Does not affect mobile', 'voxel-elementor' ),
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 800,
							'step' => 1,
						],
					],
					'condition' => [ 'custom_popup_enable' => 'yes' ],
					'selectors' => [
						'{{WRAPPER}} .ts-field-popup' => 'max-width: {{SIZE}}{{UNIT}};',
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



			$this->add_control(
				'ts_popup_term_columns',
				[
					'label' => __( 'Columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'custom_menu_cols',
				[
					'label' => __( 'Multi column popup menu?', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'voxel-elementor' ),
					'label_off' => __( 'Hide', 'voxel-elementor' ),
					'return_value' => 'yes',
				]
			);

			$this->add_responsive_control(
				'set_menu_cols',
				[
					'label' => __( 'Menu columns', 'voxel-elementor' ),
					'description' => __( 'We recommend increasing popup min width before if you plan to display the menu in multiple columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 6,
					'step' => 1,
					'default' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-term-dropdown-list' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); display: grid;',
					],
					'condition' => [ 'custom_menu_cols' => 'yes' ],
				]
			);




		$this->end_controls_section();



	}

	protected function render( $instance = [] ) {
		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/navbar.php' );

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_static_popups();' );
		}
	}



	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
