<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Price extends Base_Widget {

	public function get_name() {
		return 'ts-product-price';
	}

	public function get_title() {
		return __( 'Product price (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 'ts_chart_settings', [
			'label' => __( 'Chart', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'price_typo',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .vx-price',
				]
			);

			$this->add_responsive_control(
				'ts_price_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-price' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'ts_strike_col_text',
				[
					'label' => __( 'Linethrough text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-price s' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'ts_strike_col',
				[
					'label' => __( 'Linethrough line color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-price s' => 'text-decoration-color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'ts_strike_width',
				[
					'label' => __( 'Linethrough line width', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .vx-price s' => 'text-decoration-thickness: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$this->add_responsive_control(
				'ts_price_nostock',
				[
					'label' => __( 'Out of stock color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .vx-price.no-stock' => 'color: {{VALUE}}',
					],

				]
			);
		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$post = \Voxel\get_current_post();
		if ( ! $post ) {
			return;
		}

		$field = $post->get_field( 'product' );
		if ( ! ( $field && $field->get_type() === 'product' ) ) {
			return;
		}

		try {
			$field->check_product_form_validity();
			$is_available = true;
		} catch ( \Exception $e ) {
			$is_available = false;

			if ( $e->getCode() === \Voxel\PRODUCT_ERR_OUT_OF_STOCK ) {
				$error_message = _x( 'Out of stock', 'product price widget', 'voxel' );
			} else {
				$error_message = _x( 'Unavailable', 'product price widget', 'voxel' );
			}
		}

		$suffix = '';
		if ( $is_available ) {
			$reference_date = $GLOBALS['_availability_start_date'] ?? \Voxel\now();
			$regular_price = $field->get_minimum_price_for_date( $reference_date, [
				'with_discounts' => false,
				'addons' => $GLOBALS['_addon_filters'] ?? null,
			] );
			$discount_price = $field->get_minimum_price_for_date( $reference_date, [
				'with_discounts' => true,
				'addons' => $GLOBALS['_addon_filters'] ?? null,
			] );
			$currency = \Voxel\get_primary_currency();

			$product_type = $field->get_product_type();
			if ( $booking = $field->get_product_field('booking') ) {
				if ( $booking->get_booking_type() === 'days' && ( $field->get_value()['booking']['booking_mode'] ?? null ) === 'date_range' ) {
					if ( $product_type->config('modules.booking.date_ranges.count_mode') === 'nights' ) {
						$suffix = _x( ' / night', 'product price', 'voxel' );
					} else {
						$suffix = _x( ' / day', 'product price', 'voxel' );
					}
				}
			}

			if ( $subscription_interval = $field->get_product_field('subscription-interval') ) {
				$interval = $field->get_value()['subscription'];
				$suffix = '';
				if ( $formatted_interval = \Voxel\interval_format( $interval['unit'], $interval['frequency'] ) ) {
					$suffix = sprintf( ' / %s', $formatted_interval );
				}
			}
		}

		wp_print_styles( $this->get_style_depends() );
		require locate_template( 'templates/widgets/product-price.php' );
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
