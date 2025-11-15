<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Input {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'ts_sf_popup_input',
			[
				'label' => __( 'Popup: Input styling', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



					$widget->add_control(
						'ts_popup_input',
						[
							'label' => __( 'Input', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_sf_popup_input_height',
						[
							'label' => __( 'Input height', 'voxel-elementor' ),
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
								'.ts-field-popup input' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);





					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_sf_popup_input_font',
							'label' => __( 'Typography' ),
							'selector' => '.ts-field-popup input',
						]
					);

					$widget->add_control(
						'ts_input_padding_noico',
						[
							'label' => __( 'Input padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'.ts-field-popup input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$widget->add_control(
						'ts_input_padding',
						[
							'label' => __( 'Input padding (Input with icon)', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'.ts-field-popup .ts-input-icon input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);





					$widget->add_control(
						'ts_sf_popup_input_value_col',
						[
							'label' => __( 'Input value color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup input' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_sf_popup_input_placeholder_color',
						[
							'label' => __( 'Input placeholder color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup input::-webkit-input-placeholder' => 'color: {{VALUE}}',
								'.ts-field-popup input:-moz-placeholder' => 'color: {{VALUE}}',
								'.ts-field-popup input::-moz-placeholder' => 'color: {{VALUE}}',
								'.ts-field-popup input:-ms-input-placeholder' => 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_sf_input_popup_icon',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-input-icon' => '--ts-icon-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_popup_input_icon_size',
						[
							'label' => __( 'Input icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 40,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'.ts-field-popup .ts-input-icon' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$widget->add_control(
						'ts_popup_input_icon_size_m',
						[
							'label' => __( 'Input icon left margin', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 40,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'.ts-field-popup .ts-input-icon > i, .ts-field-popup .ts-input-icon > svg' => 'left: {{SIZE}}{{UNIT}};',

							],
						]
					);








		$widget->end_controls_section();

	}

}
