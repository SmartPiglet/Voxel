<?php

namespace Voxel\Modules\Elementor\Controllers;

use Voxel\Modules\Elementor as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Widget_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'elementor/element/common/_section_style/after_section_end', '@register_widget_settings' );
	}

	protected function register_widget_settings( $widget ) {
		$widget->start_controls_section( '_voxel_widget_settings', [
			'label' => __( 'Widget options', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );



		/* Container sticky options */
		$widget->add_control( 'sticky_option', [
			'label' => __( 'Sticky position', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$widget->add_control( 'sticky_container', [
			'label' => __( 'Enable?', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'sticky',
		] );

		$widget->add_control(
			'sticky_container_desktop',
			[
				'label' => __( 'Enable on desktop', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'sticky'  => __( 'Enable', 'voxel-backend' ),
					'initial' => __( 'Disable', 'voxel-backend' ),
				],

				'selectors' => [
					'(desktop){{WRAPPER}}' => 'position: {{VALUE}}',
				],
				'condition' => [ 'sticky_container' => 'sticky' ],
			]
		);

		$widget->add_control(
			'sticky_container_tablet',
			[
				'label' => __( 'Enable on tablet', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'sticky'  => __( 'Enable', 'voxel-backend' ),
					'initial' => __( 'Disable', 'voxel-backend' ),
				],

				'selectors' => [
					'(tablet){{WRAPPER}}' => 'position: {{VALUE}}',
				],
				'condition' => [ 'sticky_container' => 'sticky' ],
			]
		);

		$widget->add_control(
			'sticky_container_mobile',
			[
				'label' => __( 'Enable on mobile', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'sticky'  => __( 'Enable', 'voxel-backend' ),
					'initial' => __( 'Disable', 'voxel-backend' ),
				],

				'selectors' => [
					'(mobile){{WRAPPER}}' => 'position: {{VALUE}}',
				],
				'condition' => [ 'sticky_container' => 'sticky' ],
			]
		);



		$widget->add_responsive_control( 'sticky_top_value', [
			'label' => __( 'Top', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vh'],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 500,
					'step' => 1,
				],
			],
			'selectors' => [
				'{{WRAPPER}}' => 'top: {{SIZE}}{{UNIT}};',
			],
			'condition' => [ 'sticky_container' => 'sticky' ],
		] );

		$widget->add_responsive_control( 'sticky_left_value', [
			'label' => __( 'Left', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vh'],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 500,
					'step' => 1,
				],
			],
			'selectors' => [
				'{{WRAPPER}}' => 'left: {{SIZE}}{{UNIT}};',
			],
			'condition' => [ 'sticky_container' => 'sticky' ],
		] );

		$widget->add_responsive_control( 'sticky_right_value', [
			'label' => __( 'Right', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vh'],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 500,
					'step' => 1,
				],
			],
			'selectors' => [
				'{{WRAPPER}}' => 'right: {{SIZE}}{{UNIT}};',
			],
			'condition' => [ 'sticky_container' => 'sticky' ],
		] );

		$widget->add_responsive_control( 'sticky_bottom_value', [
			'label' => __( 'Bottom', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vh'],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 500,
					'step' => 1,
				],
			],
			'selectors' => [
				'{{WRAPPER}}' => 'bottom: {{SIZE}}{{UNIT}};',
			],
			'condition' => [ 'sticky_container' => 'sticky' ],
		] );

		$widget->end_controls_section();
	}
}
