<?php

namespace Voxel\Modules\Elementor\Controllers;

use Voxel\Modules\Elementor as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Container_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'elementor/element/container/section_layout/after_section_end', '@register_container_settings' );
		$this->on( 'elementor/frontend/container/before_render', '@before_render' );
	}

	protected function register_container_settings( $container ) {
		$container->start_controls_section( '_voxel_container_settings', [
			'label' => __( 'Container options', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );

		/* Container sticky options */
		$container->add_control( 'sticky_option', [
			'label' => __( 'Sticky position', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$container->add_control( 'sticky_container', [
			'label' => __( 'Enable?', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'sticky',
		] );

		$container->add_control(
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

		$container->add_control(
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

		$container->add_control(
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



		$container->add_responsive_control( 'sticky_top_value', [
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

		$container->add_responsive_control( 'sticky_left_value', [
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

		$container->add_responsive_control( 'sticky_right_value', [
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

		$container->add_responsive_control( 'sticky_bottom_value', [
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

		$container->add_control( 'con_inline_flex', [
			'label' => __( 'Inline Flex', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$container->add_responsive_control( 'enable_inline_flex', [
			'label' => __( 'Enable?', 'voxel-backend' ),
			'description' => __( 'Changes container display to inline flex and applies auto width', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'selectors' => [
				'{{WRAPPER}}' => 'display: inline-flex; width: auto;',
			],
		] );



		$container->add_control( 'con_calc_height_heading', [
			'label' => __( 'Other', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$container->add_responsive_control(
			'enable_con_calc_h',
			[
				'label' => __( 'Calculate min height?', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'voxel-backend' ),
				'label_off' => __( 'Hide', 'voxel-backend' ),
				'return_value' => 'yes',
				'default' => 'no'
			]
		);



		$container->add_responsive_control(
			'mcon_calc_height',
			[
				'label' => esc_html__( 'Calculation', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'calc()', 'voxel-backend' ),
				'description' => __( 'Use CSS calc() to calculate min-height e.g calc(100vh - 215px).', 'voxel-backend' ),
				'selectors' => [
					'{{WRAPPER}}' => 'min-height: {{VALUE}};',
				],
				'condition' => [ 'enable_con_calc_h' => 'yes' ],
			]
		);

		$container->add_responsive_control(
			'enable_con_calc_mh',
			[
				'label' => __( 'Calculate max height?', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'voxel-backend' ),
				'label_off' => __( 'Hide', 'voxel-backend' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);



		$container->add_responsive_control(
			'mcon_calc_mheight',
			[
				'label' => esc_html__( 'Calculation', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'calc()', 'voxel-backend' ),
				'description' => __( 'Use CSS calc() to calculate max-height e.g calc(100vh - 215px).', 'voxel-backend' ),
				'selectors' => [
					'{{WRAPPER}}' => 'max-height: {{VALUE}}; overflow-y: overlay; overflow-x: hidden;',
				],
				'condition' => [ 'enable_con_calc_mh' => 'yes' ],
			]
		);

		$container->add_control(
			'horizontal_scroll_color',
			[
				'label' => __( 'Scrollbar color', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--ts-scroll-color: {{VALUE}}',
				],
				'condition' => [ 'enable_con_calc_mh' => 'yes' ],
			]
		);



		$container->add_responsive_control(
			'mcon_calc_width',
			[
				'label' => esc_html__( 'Calculation', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'calc()', 'voxel-backend' ),
				'description' => __( 'Use CSS calc() to calculate width e.g calc(100vh - 215px).', 'voxel-backend' ),
				'selectors' => [
					'{{WRAPPER}}' => 'width: {{VALUE}};',
				],
				'condition' => [ 'enable_con_calc_w' => 'yes' ],
			]
		);

		$container->add_responsive_control(
			'enable_blur',
			[
				'label' => __( 'Backdrop blur?', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'voxel-backend' ),
				'label_off' => __( 'Hide', 'voxel-backend' ),
				'return_value' => 'yes',
				'default' => 'no'
			]
		);

		$container->add_responsive_control(
			'ts_blur_backdrop',
			[
				'label' => __( 'Strength', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'min' => 0,
					'max' => 100,
					'step' => 1,
				],
				'selectors' => [
					'{{WRAPPER}}' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}); -webkit-backdrop-filter: blur({{SIZE}}{{UNIT}});',
				],
				'condition' => [ 'enable_blur' => 'yes' ],
			]
		);



		$container->end_controls_section();


		$container->start_controls_section( 'canvas_width_vx', [
			'label' => __( 'Editor preview width', 'voxel-backend' ),
			'tab' => 'layout',
		] );

			$container->add_responsive_control( 'edit_canvas_value', [
				'label' => __( 'Width', 'voxel-backend' ),
				'description' => __( 'Change the width of the canvas, useful when designing preview cards', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px'],
				'range' => [
					'px' => [
						'min' => 350,
						'max' => 1200,
						'step' => 1,
					],
				],
				'selectors' => [
					'.vx-viewport-card' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			] );



		$container->end_controls_section();
	}

	protected function before_render( $container ) {
		if ( $container->get_settings('enable_con_calc_mh') === 'yes' ) {
			$container->add_render_attribute( '_wrapper', 'class', 'min-scroll' );
		}
	}
}
