<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Review_Stats extends Base_Widget {

	public function get_name() {
		return 'ts-review-stats';
	}

	public function get_title() {
		return __( 'Review stats (VX)', 'voxel-elementor' );
	}


	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_rs_content',
			[
				'label' => __( 'Settings', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'stat_mode',
				[
					'label' => esc_html__( 'Show stats for', 'textdomain' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'overall',
					'options' => [
						'overall' => esc_html__( 'Overall score', 'textdomain' ),
						'by_category' => esc_html__( 'Scores by category', 'textdomain' ),
					],

				]
			);
		$this->end_controls_section();

		$this->start_controls_section(
			'ts_rs_grid',
			[
				'label' => __( 'Reviews grid', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);



			$this->add_responsive_control(
				'ts_rs_column_no',
				[
					'label' => __( 'Number of columns', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 6,
					'step' => 1,
					'default' => 1,
					'selectors' => [
						'{{WRAPPER}} .ts-review-bars' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
					],
				]
			);




			$this->add_responsive_control(
				'ts_rs_col_gap',
				[
					'label' => __( 'Item gap', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-review-bars' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);
		$this->end_controls_section();

		$this->start_controls_section(
			'ts_rs_settings',
			[
				'label' => __( 'Review stats', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'ts_review_icon_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 16,
							'max' => 80,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-bar-data i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-bar-data svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'ts_review_icon_spacing',
				[
					'label' => __( 'Icon right spacing', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-bar-data i' => 'margin-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-bar-data svg' => 'margin-right: {{SIZE}}{{UNIT}};',
					],
				]
			);



			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_review_typo',
					'label' => __( 'Label typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-bar-data p',
				]
			);

			$this->add_control(
				'ts_review_typo_color',
				[
					'label' => __( 'Label color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-bar-data p' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'ts_review_score',
					'label' => __( 'Score typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-bar-data p span',
				]
			);

			$this->add_control(
				'ts_review_score_color',
				[
					'label' => __( 'Score color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-bar-data p span' => 'color: {{VALUE}}',
					],
				]
			);



			$this->add_control(
				'ts_review_chart_bg',
				[
					'label' => __( 'Chart background color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-bar-chart' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'ts_chart_height',
				[
					'label' => __( 'Chart height', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-bar-chart' => 'height: {{SIZE}}{{UNIT}};',

					],
				]
			);

			$this->add_responsive_control(
				'ts_chart_rad',
				[
					'label' => __( 'Chart radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-bar-chart' => 'border-radius: {{SIZE}}{{UNIT}};',

					],
				]
			);





		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$post = \Voxel\get_current_post();
		if ( ! ( $post && $post->post_type ) ) {
			return;
		}

		$stat_mode = $this->get_settings('stat_mode') ?: 'overall';

		$rating_levels = $post->post_type->reviews->get_rating_levels();
		$stats = $post->repository->get_review_stats();
		if ( ! isset( $stats['by_category'] ) ) {
			$stats = \Voxel\cache_post_review_stats( $post->get_id() );
		}

		$pct = [
			'excellent' => 0,
			'very_good' => 0,
			'good' => 0,
			'fair' => 0,
			'poor' => 0,
		];

		if ( $stats['total'] > 0 ) {
			$pct['excellent'] = round( ( ( $stats['by_score'][2] ?? 0 ) / $stats['total'] ) * 100 );
			$pct['very_good'] = round( ( ( $stats['by_score'][1] ?? 0 ) / $stats['total'] ) * 100 );
			$pct['good']      = round( ( ( $stats['by_score'][0] ?? 0 ) / $stats['total'] ) * 100 );
			$pct['fair']      = round( ( ( $stats['by_score'][-1] ?? 0 ) / $stats['total'] ) * 100 );
			$pct['poor']      = round( ( ( $stats['by_score'][-2] ?? 0 ) / $stats['total'] ) * 100 );
		}

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/review-stats.php' );
	}

	public function get_style_depends() {
		return [ 'vx:review-stats.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
