<?php

namespace Voxel\Widgets\Option_Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup_Menu {

	public static function controls( $widget ) {

		$widget->start_controls_section(
			'ts_sf_popup_list',
			[
				'label' => __( 'Popup: Menu styling', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$widget->start_controls_tabs(
				'ts_popup_list_tabs'
			);

				/* Normal tab */

				$widget->start_controls_tab(
					'ts_sfl_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);




					$widget->add_control(
						'ts_popup_term_list_item',
						[
							'label' => __( 'Item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);


					$widget->add_control(
						'ts_popup_term_padding',
						[
							'label' => __( 'Item padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);



					$widget->add_responsive_control(
						'ts_term_max_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
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
								'.ts-field-popup .ts-term-dropdown li > a' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$widget->add_control(
						'pg_menu_separator',
						[
							'label' => __( 'Separator color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li' => 'border-color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_h_item_title',
						[
							'label' => __( 'Title', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_popup_term_title',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li > a span'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_popup_term_title_typo',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => '.ts-field-popup .ts-term-dropdown li > a span',
						]
					);


					$widget->add_control(
						'ts_h_item_icon',
						[
							'label' => __( 'Icon', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);

					$widget->add_control(
						'ts_popup_term_icon',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-icon'
								=> '--ts-icon-color: {{VALUE}};',
							],

						]
					);

					$widget->add_responsive_control(
						'ts_popup_term_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 40,
									'step' => 1,
								],
							],
							'selectors' => [
								'.ts-field-popup .ts-term-icon' => '--ts-icon-size: {{SIZE}}{{UNIT}};',
							],
						]
					);






					$widget->add_responsive_control(
						'ts_icon_right_margin',
						[
							'label' => __( 'Spacing', 'voxel-elementor' ),
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
								'.ts-field-popup .ts-term-dropdown li > a' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$widget->add_control(
						'ts_item_chevron',
						[
							'label' => __( 'Chevron', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);




					$widget->add_control(
						'ts_chevron_icon_color',
						[
							'label' => __( 'Chevron color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-right-icon, .ts-field-popup .ts-left-icon' => 'border-color: {{VALUE}}',
								'.ts-field-popup .pika-label:after' => 'border-color: {{VALUE}}',
							],
						]
					);





				$widget->end_controls_tab();


				/* Hover tab */

				$widget->start_controls_tab(
					'ts_sfl_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$widget->add_control(
						'ts_term_item_hover',
						[
							'label' => __( 'Term item', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::HEADING,
							'separator' => 'before',
						]
					);
					$widget->add_control(
						'ts_popup_term_bg_h',
						[
							'label' => __( 'List item background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li > a:hover, .ts-field-popup .ts-term-dropdown li > a:focus'
								=> 'background: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_popup_term_title_hover',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li > a:hover span'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_popup_term_icon_hover',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li > a:hover .ts-term-icon'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);





				$widget->end_controls_tab();

				$widget->start_controls_tab(
					'ts_popup_term_selected',
					[
						'label' => __( 'Selected', 'voxel-elementor' ),
					]
				);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_popup_term_title_typo_s',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => '.ts-field-popup .ts-term-dropdown li.ts-selected > a span',
						]
					);


					$widget->add_control(
						'ts_popup_term_title_s',
						[
							'label' => __( 'Title color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li.ts-selected > a span'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$widget->add_control(
						'ts_popup_term_icon_s',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,


							'selectors' => [
								'.ts-field-popup .ts-term-dropdown li.ts-selected > a .ts-term-icon'
								=> '--ts-icon-color: {{VALUE}}',
							],

						]
					);

				$widget->end_controls_tab();
				$widget->start_controls_tab(
					'ts_popup_term_parent',
					[
						'label' => __( 'Parent', 'voxel-elementor' ),
					]
				);

					$widget->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_popup_term_parent_typo',
							'label' => __( 'Title typography', 'voxel-elementor' ),
							'selector' => '.ts-field-popup .ts-term-dropdown li.ts-parent-item span',
						]
					);

				$widget->end_controls_tab();

			$widget->end_controls_tabs();

		$widget->end_controls_section();


	}

}
