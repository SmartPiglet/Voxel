<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Label {

	public static function controls( $widget ) {
		$widget->start_controls_section(
			'popup_label_section',
			[
				'label' => __( 'Popup: Label and description', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$widget->add_control(
				'ts_filter_label',
				[
					'label' => __( 'Label', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_filter_l_typo',
					'label' => __( 'Typography' ),
					'selector' => '.ts-field-popup .ts-form-group label',
				]
			);


			$widget->add_responsive_control(
				'ts_filter_l_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'.ts-field-popup .ts-form-group label' => 'color: {{VALUE}}',
					],

				]
			);

			$widget->add_control(
				'ts_filter_desc',
				[
					'label' => __( 'Field description', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);


			$widget->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_filter_d_t',
					'label' => __( 'Typography' ),
					'selector' => '.ts-field-popup .ts-form-group small',
				]
			);


			$widget->add_responsive_control(
				'ts_filter_d_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						' .ts-field-popup .ts-form-group small' => 'color: {{VALUE}}',
					],

				]
			);

		$widget->end_controls_section();

	}
}
