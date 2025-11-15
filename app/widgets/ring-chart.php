<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ring_Chart extends Base_Widget {

	public function get_name() {
		return 'ts-ring-chart';
	}

	public function get_title() {
		return __( 'Ring chart (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_action_content',
			[
				'label' => __( 'Content', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);


		$this->add_control(
			'ts_chart_position',
			[
				'label' => __( 'Justify', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start'  => __( 'Left', 'voxel-elementor' ),
					'center' => __( 'Center', 'voxel-elementor' ),
					'flex-end' => __( 'Right', 'voxel-elementor' )
				],
				'selectors' => [
					'{{WRAPPER}} .circle-chart-wrapper' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'ts_chart_value',
			[
				'label' => __( 'Value', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 100,
				'step' => 0.01,
				'default' => 0
			]
		);

		$this->add_control(
			'ts_chart_value_suffix',
			[
				'label' => __( 'Suffix', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'ts_chart_size',
			[
				'label' => __( 'Circle size', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 300,
				'step' => 1,
				'default' => 100
			]
		);

		$this->add_control(
			'ts_chart_stroke_width',
			[
				'label' => __( 'Stroke width', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 5,
				'step' => 1,
				'default' => 2
			]
		);

		$this->add_responsive_control(
			'ts_chart_animation_duration',
			[
				'label' => __( 'Animation duration', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 5,
				'step' => 0.01,
				'default' => 3,
				'selectors' => [
					'{{WRAPPER}} .circle-chart__circle' => 'animation-duration: {{VALUE}}s;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_chart_style',
			[
				'label' => __( 'Circle', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'ts_chart_cirle_color',
			[
				'label' => __( 'Circle Color', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#efefef'
			]
		);

		$this->add_control(
			'ts_chart_fill_color',
			[
				'label' => __( 'Circle Fill Color', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#00acc1'
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_chart_value_typography',
			[
				'label' => __( 'Value', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'chart_value_typography',
				'label' => __( 'Typography', 'voxel-elementor' ),
				'selector' => '{{WRAPPER}} .circle-chart .chart-value',
			]
		);

		$this->add_control(
			'ts_chart_value_color',
			[
				'label' => __( 'Color', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .circle-chart .chart-value' => 'color: {{VALUE}}',
				]
			]
		);

		$this->end_controls_section();



	}

	protected function render( $instance = [] ) {
		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/ring-chart.php' );
	}

	public function get_style_depends() {
		return [ 'vx:ring-chart.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
