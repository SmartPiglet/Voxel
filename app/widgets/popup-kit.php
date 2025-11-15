<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Kit extends Base_Widget {

	public function get_name() {
		return 'ts-test-widget-1';
	}

	public function get_title() {
		return __( 'Popup Kit (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		/*
		==============
		Popup: General
		==============
		*/



		$this->apply_controls( Option_Groups\Popup_General::class );

		/*
		===================
		Popup: Head
		===================
		*/



		$this->apply_controls( Option_Groups\Popup_Head::class );




		/*
		==============
		Popup: Controller
		==============
		*/



		$this->apply_controls( Option_Groups\Popup_Controller::class );

		/*
		==============
		Popup: Label and description
		==============
		*/



		$this->apply_controls( Option_Groups\Popup_Label::class );


		/*
		===================
		Popup: Menu styling
		===================
		*/

		$this->apply_controls( Option_Groups\Popup_Menu::class );


		/*
		===================
		Popup: Cart
		===================
		*/

		$this->start_controls_section(
			'cart_styling',
			[
				'label' => __( 'Popup: Cart', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'cart_spacing',
				[
					'label' => __( 'Item spacing', 'voxel-elementor' ),
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
						'.ts-field-popup .ts-cart-list' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'cart_item_spacing',
				[
					'label' => __( 'Item content spacing', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-field-popup .ts-cart-list li' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_cart_img_size',
				[
					'label' => __( 'Picture size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 16,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'.ts-field-popup .cart-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'ts_cart_img_radius',
				[
					'label' => __( 'Picture radius', 'voxel-elementor' ),
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
						'.ts-field-popup .cart-image img' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_title_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '.ts-field-popup .cart-item-details a',
				]
			);

			$this->add_control(
				'ts_title_color',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .cart-item-details a' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_subtitle_typo',
					'label' => __( 'Subtitle typography', 'voxel-elementor' ),
					'selector' => '.ts-field-popup .cart-item-details span',
				]
			);

			$this->add_control(
				'ts_subtitle_color',
				[
					'label' => __( 'Subtitle Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .cart-item-details span' => 'color: {{VALUE}}',
					],
				]
			);




		$this->end_controls_section();

		/*
		===================
		Popup: Subtotal
		===================
		*/

		$this->start_controls_section(
			'prform_calculator',
			[
				'label' => __( 'Popup: Subtotal', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'calc_text_total',
					'label' => __( 'Typography (Total)' ),
					'selector' => '.ts-field-popup .cart-subtotal span',
				]
			);

			$this->add_control(
				'calc_text_color_total',
				[
					'label' => __( 'Text color (Total)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .cart-subtotal span'
						=> 'color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		/*
		==============
		Popup: Empty/No results
		==============
		*/

		$this->start_controls_section(
			'ts_popup_noresults',
			[
				'label' => __( 'Popup: No results', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);





			$this->add_control(
				'ts_empty_icon_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 50,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 35,
					],
					'selectors' => [
						'.ts-field-popup .ts-empty-user-tab' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$this->add_control(
				'ts_empty_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .ts-empty-user-tab' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'ts_empty_title_color',
				[
					'label' => __( 'Title color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .ts-empty-user-tab p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_empty_title_text',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '.ts-field-popup .ts-empty-user-tab p',
				]
			);

		$this->end_controls_section();






		/*
		===================
		Popup: Checkbox
		===================
		*/

		$this->apply_controls( Option_Groups\Popup_Checkbox::class );

		/*
		===================
		Popup: Radio
		===================
		*/

		$this->apply_controls( Option_Groups\Popup_Radio::class );


		/*
		===================
		Popup: Input styling
		===================
		*/



		$this->apply_controls( Option_Groups\Popup_Input::class );

		/*
		===================
		Popup: Popup: File gallery
		===================
		*/


		$this->start_controls_section(
			'ts_form_file',
			[
				'label' => __( 'Popup: File/Gallery', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$this->start_controls_tabs(
				'file_field_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'file_field_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

				

					$this->add_responsive_control(
						'ts_file_col_gap',
						[
							'label' => __( 'Item gap', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'.ts-field-popup px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'.ts-field-popup .ts-file-list' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);




					$this->add_control(
						'ts_file_add',
						[
							'label' => __( 'Select files', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_file_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_file_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'min' => 0,
								'max' => 100,
								'step' => 1,
							],
							'selectors' => [
								'.ts-field-popup .pick-file-input' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_file_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_file_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '.pick-file-input',
						]
					);

					$this->add_responsive_control(
						'ts_file_radius',
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
								'.ts-field-popup .pick-file-input' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_file_text',
							'label' => __( 'Typography' ),
							'selector' => '.pick-file-input a',
						]
					);

					$this->add_control(
						'ts_file_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input a'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_file_added',
						[
							'label' => __( 'Added file/image', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_responsive_control(
						'ts_added_radius',
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
								'.ts-field-popup .ts-file' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_added_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-file'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_added_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-file-info'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_added_icon_size',
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
								'.ts-field-popup .ts-file-info' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_added_text',
							'label' => __( 'Typography' ),
							'selector' => '.ts-file-info code',
						]
					);

					$this->add_control(
						'ts_added_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-file-info code'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_remove_file',
						[
							'label' => __( 'Remove/Check button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_rmf_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-remove-file'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_rmf_bg_h',
						[
							'label' => __( 'Background (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-remove-file:hover'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_rmf_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-remove-file'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_rmf_color_h',
						[
							'label' => __( 'Color (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-remove-file:hover'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_rmf_radius',
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
								'.ts-field-popup .ts-remove-file' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_rmf_size',
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
								'.ts-field-popup .ts-remove-file' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_rmf_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
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
								'.ts-field-popup .ts-remove-file' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);





				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_file_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_file_add_h',
						[
							'label' => __( 'Select files', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_file_icon_color_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input a:hover'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_file_bg_h',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input:hover'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_file_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input:hover'
								=> 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_file_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .pick-file-input a:hover'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();



		$this->end_controls_section();



		$this->start_controls_section(
			'ts_sf_popup_number',
			[
				'label' => __( 'Popup: Number', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


				$this->add_control(
					'ts_popup_number',
					[
						'label' => __( 'Number popup', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);


				$this->add_control(
					'popup_number_input_size',
					[
						'label' => __( 'Input value size', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px'],
						'range' => [
							'px' => [
								'min' => 13,
								'max' => 30,
								'step' => 1,
							],
						],
						'default' => [
							'unit' => 'px',
							'size' => 20,
						],
						'selectors' => [
							'.ts-field-popup .ts-stepper-input input' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_popup_range',
			[
				'label' => __( 'Popup: Range slider', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'ts_popup_range',
				[
					'label' => __( 'Range slider', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'ts_popup_range_size',
				[
					'label' => __( 'Range value size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 13,
							'max' => 30,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 20,
					],
					'selectors' => [
						'.ts-field-popup .range-slider-wrapper .range-value' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'ts_popup_range_val',
				[
					'label' => __( 'Range value color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .range-slider-wrapper .range-value'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'ts_popup_range_bg',
				[
					'label' => __( 'Range background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .noUi-target'
						=> 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'ts_popup_range_bg_selected',
				[
					'label' => __( 'Selected range background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .noUi-connect'
						=> 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'ts_popup_range_handle',
				[
					'label' => __( 'Handle background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .noUi-handle' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'ts_popup_range_handle_border',
					'label' => __( 'Handle border', 'voxel-elementor' ),
					'selector' => '.ts-field-popup .noUi-handle',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_popup_switch',
			[
				'label' => __( 'Popup: Switch', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

				$this->add_control(
					'ts_popup_switch',
					[
						'label' => __( 'Switch slider', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'ts_popup_switch_bg',
					[
						'label' => __( 'Switch slider background (Inactive)', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.ts-field-popup .onoffswitch .onoffswitch-label'
							=> 'background-color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'ts_popup_switch_bg_active',
					[
						'label' => __( 'Switch slider background (Active)', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.ts-field-popup .onoffswitch .onoffswitch-checkbox:checked + .onoffswitch-label'
							=> 'background-color: {{VALUE}}',
						],

					]
				);

				$this->add_control(
					'ts_field_switch_bg_handle',
					[
						'label' => __( 'Handle background', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.ts-field-popup .onoffswitch .onoffswitch-label:before'
							=> 'background-color: {{VALUE}}',
						],

					]
				);



		$this->end_controls_section();





		/*
		===================
		Popup: Icon button
		===================
		*/

		$this->apply_controls( Option_Groups\Popup_Icon_Button::class );


		/*
		===================
		Popup: Datepicker head
		===================
		*/


		$this->start_controls_section(
			'ts_datepicker_head',
			[
				'label' => __( 'Popup: Datepicker head', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'dh_title',
				[
					'label' => __( 'Title', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'dh_icon_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'.datepicker-head h3' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'dh_icon_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.datepicker-head h3' => '--ts-icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'dh_icon_margin',
				[
					'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 20,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'.datepicker-head h3' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$this->add_control(
				'dh_title_color',
				[
					'label' => __( 'Title color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.datepicker-head h3' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'dh_title_typo',
					'label' => __( 'Title typography', 'voxel-elementor' ),
					'selector' => '.datepicker-head h3',
				]
			);

			$this->add_control(
				'dh_subtitle',
				[
					'label' => __( 'Subitle', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'dh_subtitle_color',
				[
					'label' => __( 'Subtitle color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.datepicker-head p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'dh_subtitle_typo',
					'label' => __( 'Subtitle typography', 'voxel-elementor' ),
					'selector' => '.datepicker-head p',
				]
			);

		$this->end_controls_section();

		/*
		===================
		Popup: Datepicker head
		===================
		*/

		$this->start_controls_section(
			'ts_datepicker_tooltip',
			[
				'label' => __( 'Popup: Datepicker tooltips', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'dht_bgcolor',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.pika-tooltip ' => 'background-color: {{VALUE}}',

					],
				]
			);

			$this->add_control(
				'dht_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.pika-tooltip ' => 'color: {{VALUE}}',

					],
				]
			);

			$this->add_responsive_control(
				'dht_radius',
				[
					'label' => __( 'Border radius', 'voxel-elementor' ),
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
						'.pika-tooltip' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		/*
		==============
		Popup: Calendar
		==============
		*/

		$this->apply_controls( Option_Groups\Popup_Calendar::class );

		/*
		==============
		Popup: Notifications
		==============
		*/



		$this->apply_controls( Option_Groups\Popup_Notifications::class );

		/*
		==============
		Popup: Conversation
		==============
		*/



		// $this->apply_controls( Option_Groups\Popup_Conversation::class );

		/*
		==============
		Popup: Textarea
		==============
		*/

		$this->start_controls_section(
			'ts_popup_textarea',
			[
				'label' => __( 'Popup: Textarea', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_popup_textarea_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_textarea_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_popup_x_heading',
						[
							'label' => __( 'Textarea', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_sf_popup_textarea_height',
						[
							'label' => __( 'Textarea height', 'voxel-elementor' ),
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
								'.ts-field-popup textarea' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'popup_textarea_font',
							'label' => __( 'Typography' ),
							'selector' => '.ts-field-popup textarea',
						]
					);


					$this->add_control(
						'popup_textarea_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup textarea' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'popup_textarea_bg_filled',
						[
							'label' => __( 'Background color (Focus)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup textarea:focus' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'popup_textarea_value_col',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup textarea' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_textarea_plc_color',
						[
							'label' => __( 'Placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup textarea::-webkit-input-placeholder' => 'color: {{VALUE}}',
								'.ts-field-popup textarea:-moz-placeholder' => 'color: {{VALUE}}',
								'.ts-field-popup textarea::-moz-placeholder' => 'color: {{VALUE}}',
								'.ts-field-popup textarea:-ms-input-placeholder' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_textarea_padding',
						[
							'label' => __( 'Textarea padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'.ts-field-popup textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_popup_textarea_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '.ts-field-popup textarea',
						]
					);


				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_textarea_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);


					$this->add_control(
						'ts_popup_textarea_h',
						[
							'label' => __( 'Textarea', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$this->add_control(
						'ts_sf_popup_textarea_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup textarea:hover' => 'background: {{VALUE}}',
							],

						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

	

		$this->start_controls_section(
			'alertstyle',
			[
				'label' => __( 'Popup: Alert', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'alert_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '.ts-notice',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'pg_alert_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '.ts-notice',
				]
			);

			$this->add_responsive_control(
				'alert_radius',
				[
					'label' => __( 'Border radius', 'voxel-elementor' ),
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
						'.ts-notice' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'alertbg',
				[
					'label' => __( 'Background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-notice' => 'background-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'alertdiv',
				[
					'label' => __( 'Divider color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.a-btn' => ' --alert-divider: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'alt_text',
					'label' => __( 'Typography' ),
					'selector' => '.ts-notice .alert-msg',
				]
			);

			$this->add_control(
				'alert_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-notice .alert-msg' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'alert_info_color',
				[
					'label' => __( 'Info icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-notice' => '--al-info: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'alert_warning_color',
				[
					'label' => __( 'Error icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-notice' => '--al-error: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'alert_success_color',
				[
					'label' => __( 'Success icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-notice' => '--al-success: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'al_link_text',
					'label' => __( 'Link Typography' ),
					'selector' => '.a-btn a',
				]
			);

			$this->add_control(
				'alert_link_color',
				[
					'label' => __( 'Link color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.a-btn a' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'alert_link_color_h',
				[
					'label' => __( 'Link color (Hover)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.a-btn a:hover' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'alert_link_bgcolor',
				[
					'label' => __( 'Link background (Hover)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.a-btn a:hover' => 'background-color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();


	}

	protected function render( $instance = [] ) {
		require locate_template( 'templates/widgets/popup-kit.php' );
	}

	public function get_style_depends() {
		return [ 'vx:forms.css', 'vx:popup-kit.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
