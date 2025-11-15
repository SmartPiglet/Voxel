<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class File_Field {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'ts_form_file',
			[
				'label' => __( 'Form: File/Gallery', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$widget->start_controls_tabs(
				'file_field_tabs'
			);

				/* Normal tab */

				$widget->start_controls_tab(
					'file_field_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$widget->add_responsive_control(
						'ts_file_col_gap',
						[
							'label' => __( 'Item gap', 'voxel-elementor' ),
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
								'{{WRAPPER}} .inline-file-field .ts-file-list' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);





					$widget->add_control(
						'ts_file_add',
						[
							'label' => __( 'Select files', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_file_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input a i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-file-field .pick-file-input a svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_responsive_control(
						'ts_file_icon_size',
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
								'{{WRAPPER}} .inline-file-field .pick-file-input a i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .inline-file-field .pick-file-input a svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_control(
						'ts_file_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_file_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .inline-file-field .pick-file-input',
						]
					);

					$widget->add_responsive_control(
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
								'{{WRAPPER}} .inline-file-field .pick-file-input' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_file_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .inline-file-field .pick-file-input a',
						]
					);

					$widget->add_control(
						'ts_file_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input a'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_file_added',
						[
							'label' => __( 'Added file/image', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_responsive_control(
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
								'{{WRAPPER}} .inline-file-field .ts-file' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_control(
						'ts_added_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-file'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_added_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-file-info i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-file-field .ts-file-info svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_responsive_control(
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
								'{{WRAPPER}} .inline-file-field .ts-file-info i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .inline-file-field .ts-file-info svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_added_text',
							'label' => __( 'Typography' ),
							'selector' => '{{WRAPPER}} .inline-file-field .ts-file-info code',
						]
					);

					$widget->add_control(
						'ts_added_text_color',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-file-info code'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_remove_file',
						[
							'label' => __( 'Remove/Check button', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_rmf_bg',
						[
							'label' => __( 'Background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-remove-file'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_rmf_bg_h',
						[
							'label' => __( 'Background (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-remove-file:hover'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_rmf_color',
						[
							'label' => __( 'Color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-remove-file i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-file-field .ts-remove-file svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_rmf_color_h',
						[
							'label' => __( 'Color (Hover)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .ts-remove-file:hover i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-file-field .ts-remove-file:hover svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_responsive_control(
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
								'{{WRAPPER}} .inline-file-field .ts-remove-file' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_responsive_control(
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
								'{{WRAPPER}} .inline-file-field .ts-remove-file' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_responsive_control(
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
								'{{WRAPPER}} .inline-file-field .ts-remove-file i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .inline-file-field .ts-remove-file svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);





				$widget->end_controls_tab();


				/* Hover tab */

				$widget->start_controls_tab(
					'ts_file_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$widget->add_control(
						'ts_file_add_h',
						[
							'label' => __( 'Select files', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_file_icon_color_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input a:hover i'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .inline-file-field .pick-file-input a:hover svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_file_bg_h',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input:hover'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_file_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input:hover'
								=> 'border-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_file_color_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .inline-file-field .pick-file-input a:hover'
								=> 'color: {{VALUE}}',
							],

						]
					);


				$widget->end_controls_tab();

			$widget->end_controls_tabs();



		$widget->end_controls_section();

	}
}
