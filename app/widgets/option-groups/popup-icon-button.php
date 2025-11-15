<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Icon_Button {

	public static function controls( $widget ) {

		$widget->start_controls_section(
			'pg_icon_button',
			[
				'label' => __( 'Popup: Icon button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$widget->start_controls_tabs(
				'pg_icon_button_tabs'
			);

				/* Normal tab */

				$widget->start_controls_tab(
					'pg_icon_button_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$widget->add_control(
						'ib_styling',
						[
							'label' => __( 'Button styling', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);



					$widget->add_control(
						'ts_number_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-icon-btn i'
								=> 'color: {{VALUE}}',
								'.ts-field-popup .ts-icon-btn svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_number_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-icon-btn' => 'background-color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_number_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '.ts-field-popup .ts-icon-btn',
						]
					);

					$widget->add_responsive_control(
						'ts_number_btn_radius',
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
								'.ts-field-popup .ts-icon-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);





				$widget->end_controls_tab();


				/* Hover tab */

				$widget->start_controls_tab(
					'pg_icon_button_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$widget->add_control(
						'ts_popup_number_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-icon-btn:hover i'
								=> 'color: {{VALUE}};',
								'.ts-field-popup .ts-icon-btn:hover svg'
								=> 'fill: {{VALUE}};',
							],

						]
					);

					$widget->add_control(
						'ts_number_btn_bg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-icon-btn:hover'
								=> 'background-color: {{VALUE}};',
							],

						]
					);

					$widget->add_control(
						'ts_button_border_c_h',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-icon-btn:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$widget->end_controls_tab();

			$widget->end_controls_tabs();

		$widget->end_controls_section();
	}

}
