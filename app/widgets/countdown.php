<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Countdown extends Base_Widget {

	public function get_name() {
		return 'ts-countdown';
	}

	public function get_title() {
		return __( 'Countdown (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {



		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

				$this->add_control(
					'due_date',
					[
						'label' => __( 'Due Date', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::DATE_TIME,
						'picker_options' => [
							'dateFormat' => 'Y-m-d H:i:s',
						],
						'default' => date("Y-m-d H:i:s", strtotime("+1 day")),
					]
				);

				$this->add_control(
					'countdown_ended_text',
					[
						'label' => __( 'Countdown Ended Text', 'voxel-elementor' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'The countdown has ended.', 'voxel-elementor' ),
					]
				);

				$this->add_control(
					'ts_hide_sec',
					[
						'label' => __( 'Hide seconds', 'voxel-elementor' ),

						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'equal',
						'selectors' => [
							'{{WRAPPER}} .countdown-timer li:nth-child(4)' => 'display: none !important;',
						],
					]
				);

				$this->add_control(
					'ts_hide_min',
					[
						'label' => __( 'Hide minutes', 'voxel-elementor' ),

						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'equal',
						'selectors' => [
							'{{WRAPPER}} .countdown-timer li:nth-child(3)' => 'display: none !important;',
						],
					]
				);

				$this->add_control(
					'ts_hide_hours',
					[
						'label' => __( 'Hide hours', 'voxel-elementor' ),

						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'equal',
						'selectors' => [
							'{{WRAPPER}} .countdown-timer li:nth-child(2)' => 'display: none !important;',
						],
					]
				);

				$this->add_control(
					'ts_hide_days',
					[
						'label' => __( 'Hide days', 'voxel-elementor' ),

						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'equal',
						'selectors' => [
							'{{WRAPPER}} .countdown-timer li:nth-child(1)' => 'display: none !important;',
						],
					]
				);


		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Style', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				  'ts_disable_animation',
				  [
					  'label' => __( 'Disable reveal animation', 'voxel-elementor' ),

					  'type' => \Elementor\Controls_Manager::SWITCHER,
					  'return_value' => 'equal',
					  'selectors' => [
						  '{{WRAPPER}} .countdown-timer span' => 'animation: none;',
					  ],
				  ]
			 );

			$this->add_control(
				  'ts_ct_inline',
				  [
					  'label' => __( 'Horizontal orientation', 'voxel-elementor' ),

					  'type' => \Elementor\Controls_Manager::SWITCHER,
					  'return_value' => 'equal',
					  'selectors' => [
						  '{{WRAPPER}} .countdown-timer li' => 'flex-direction: row;',
					  ],
				  ]
			 );

		   $this->add_responsive_control(
			  'ts_ct_spacing',
			  [
				  'label' => __( 'Item spacing', 'voxel-elementor' ),
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
					  '{{WRAPPER}} .countdown-timer' => 'gap: {{SIZE}}{{UNIT}};',
				  ],
			  ]
			 );

			$this->add_responsive_control(
			 'ts_ct_spacing_content',
				 [
					 'label' => __( 'Content spacing', 'voxel-elementor' ),
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
						 '{{WRAPPER}} .countdown-timer li' => 'gap: {{SIZE}}{{UNIT}};',
					 ],
				 ]
			);


			 $this->add_control(
				 'text_color',
				 [
					 'label' => __( 'Text Color', 'voxel-elementor' ),
					 'type' => \Elementor\Controls_Manager::COLOR,
					 'selectors' => [
						 '{{WRAPPER}} .countdown-timer p' => 'color: {{VALUE}};',
					 ],
				 ]
			 );

			 $this->add_control(
				 'number_color',
				 [
					 'label' => __( 'Number Color', 'voxel-elementor' ),
					 'type' => \Elementor\Controls_Manager::COLOR,
					 'selectors' => [
						 '{{WRAPPER}} .countdown-timer span' => 'color: {{VALUE}};',
					 ],
				 ]
			 );

			 $this->add_control(
				 'ended_color',
				 [
					 'label' => __( 'Ended Color', 'voxel-elementor' ),
					 'type' => \Elementor\Controls_Manager::COLOR,
					 'selectors' => [
						 '{{WRAPPER}} .countdown-ended' => 'color: {{VALUE}};',
					 ],
				 ]
			 );

			$this->add_group_control(
				  \Elementor\Group_Control_Typography::get_type(),
				  [
					 'name' => 'text_typography',
					 'label' => __( 'Text Typography', 'voxel-elementor' ),

					 'selector' => '{{WRAPPER}} .countdown-timer p',

				 ]
			 );

			 $this->add_group_control(
				  \Elementor\Group_Control_Typography::get_type(),
				  [
					 'name' => 'number_typography',
					 'label' => __( 'Number Typography', 'voxel-elementor' ),
					 'selector' => '{{WRAPPER}} .countdown-timer span',

				 ]
			 );

			  $this->add_group_control(
				  \Elementor\Group_Control_Typography::get_type(),
				  [
					 'name' => 'ended_typography',
					 'label' => __( 'Ended Typography', 'voxel-elementor' ),
					 'selector' => '{{WRAPPER}} .countdown-ended',

				 ]
			 );






		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$settings = $this->get_settings_for_display();
		$due_date = esc_attr( $settings['due_date'] );
		$countdown_ended_text = esc_attr($settings['countdown_ended_text']);

		try {
			$post = \Voxel\get_current_post();
			$due = new \DateTime( $this->get_settings_for_display('due_date'), wp_timezone() );
			$diff = $due->diff( \Voxel\now() );
			$config = [
				'days' => $diff->invert ? $diff->days : 0,
				'hours' => $diff->invert ? $diff->h : 0,
				'minutes' => $diff->invert ? $diff->i : 0,
				'seconds' => $diff->invert ? $diff->s : 0,
				'due' => $due->getTimestamp(),
				'now' => \Voxel\now()->getTimestamp(),
			];
		} catch ( \Exception $e ) {
			return;
		}

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/countdown.php' );

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_countdowns();' );
		}
	}

	public function get_script_depends() {
		return [
			'vx:countdown.js',
		];
	}

	public function get_style_depends() {
		return [ 'vx:countdown.css' ];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
