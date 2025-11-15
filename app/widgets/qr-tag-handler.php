<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qr_Tag_Handler extends Base_Widget {

	public function get_name() {
		return 'ts-qr-tag-handler';
	}

	public function get_title() {
		return __( 'QR Tag Handler (VX)', 'voxel-elementor' );
	}



	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'sf_success',
			[
				'label' => __( 'Form: Post submitted/Updated', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'sf_welc_align',
				[
					'label' => __( 'Align icon', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'center',
					'options' => [
						'flex-start'  => __( 'Left', 'voxel-elementor' ),
						'center' => __( 'Center', 'voxel-elementor' ),
						'flex-end' => __( 'Right', 'voxel-elementor' ),
					],
					'selectors' => [
						'{{WRAPPER}} .ts-edit-success' => 'align-items: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'sf_welc_align_text',
				[
					'label' => __( 'Text align', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'center',
					'options' => [
						'left'  => __( 'Left', 'voxel-elementor' ),
						'center' => __( 'Center', 'voxel-elementor' ),
						'right' => __( 'Right', 'voxel-elementor' ),
					],
					'selectors' => [
						'{{WRAPPER}} .ts-edit-success' => 'text-align: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'sf_success_icon_heading',
				[
					'label' => __( 'Icon', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'sf_welc_ico_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
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
						'{{WRAPPER}} .ts-edit-success > i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-edit-success > svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'sf_welc_ico_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-edit-success > i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-edit-success > svg' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'sf_welc_heading',
				[
					'label' => __( 'Heading', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'sf_welc_heading_t',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-edit-success h4',
				]
			);

			$this->add_responsive_control(
				'sf_welc_heading_col',
				[
					'label' => __( 'Color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-edit-success h4' => 'color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_styling_buttons',
			[
				'label' => __( 'Form: Primary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_sf_buttons_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_sf_buttons_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);


					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'ts_submit_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.create-btn',
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-2.create-btn' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_sf_form_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.create-btn',
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_radius',
						[
							'label' => __( 'Border radius', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-2.create-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_form_btn_shadow',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.create-btn',
						]
					);


					$this->add_responsive_control(
						'ts_sf_form_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn' => 'color: {{VALUE}}',
							],

						]
					);


					$this->add_responsive_control(
						'ts_sf_form_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn' => 'background: {{VALUE}}',
							],

						]
					);



					$this->add_responsive_control(
						'ts_sf_form_btn_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-2.create-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-2.create-btn svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2.create-btn svg' => 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_margin',
						[
							'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
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
								'{{WRAPPER}} .ts-btn-2.create-btn' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'save_icon',
						[
							'label' => __( 'Success icon', 'text-domain' ),
							'type' => \Elementor\Controls_Manager::ICONS,
						]
					);

					$this->add_control(
						'view_icon',
						[
							'label' => __( 'View order icon', 'text-domain' ),
							'type' => \Elementor\Controls_Manager::ICONS,
						]
					);

				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_sf_buttons_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'ts_sf_form_btn_t_hover',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_bg_hover',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn:hover' => 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_bo_hover',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'ts_sf_form_btn_shadow_h',
							'label' => __( 'Box Shadow', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2.create-btn:hover',
						]
					);

					$this->add_responsive_control(
						'ts_sf_form_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2.create-btn:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2.create-btn:hover svg' => 'fill: {{VALUE}}',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		try {
			$order = \Voxel\Order::find( [
				'id' => $_GET['order'] ?? '',
				'status' => \Voxel\Order::STATUS_COMPLETED,
			] );

			if ( ! $order ) {
				throw new \Exception( 'Order not found.' );
			}

			$product_type = $order->get_product_type();

			$tag = $product_type->tags->get_tag( $_GET['tag'] ?? '' );
			if ( ! ( $tag && $tag->has_qr_code() ) ) {
				throw new \Exception( 'QR code is not valid.' );
			}

			$details = $order->get_details();
			$qrcode = $details['qrcodes'][ $tag->get_key() ] ?? null;
			if ( ! $qrcode ) {
				throw new \Exception( 'QR code has expired.' );
			}

			if ( empty( $qrcode['code'] ) || $qrcode['code'] !== ( $_GET['code'] ?? null ) ) {
				throw new \Exception( 'QR code has expired.' );
			}

			if ( $product_type->config( 'settings.tags.qr_limit' ) === 'once' && $qrcode['applied'] ) {
				throw new \Exception( 'This QR code can only be used once.' );
			}

			$current_tag = $order->get_tag();
			if ( ! ( $current_tag && $current_tag->get_key() === $tag->get_key() ) ) {
				$qrcode['applied'] = true;
				$details['qrcodes'][ $tag->get_key() ] = $qrcode;
				$details['tag'] = $tag->get_key();
				$order->update( 'details', $details );
				$order->note( \Voxel\Order_Note::QR_TAG_APPLIED, [ 'tag' => $tag->get_key() ] );
			}

			?>

			<div class="ts-edit-success flexify" style="max-width: none;">
			 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('save_icon') ) ?: \Voxel\svg( 'checkmark-circle.svg' ) ?>
			  <h4><?= sprintf( 'Order #%d has been set to <b>%s</b>', $order->get_id(), $tag->get_label() ) ?></h4>
			  <!-- <p>{{ submission.message }}</p> -->
			  <div class="es-buttons flexify">
			    <a href="<?= esc_url( $order->get_link() ) ?>" class="ts-btn ts-btn-2 ts-btn-large create-btn">Open order<?= \Voxel\get_icon_markup( $this->get_settings_for_display('view_icon') ) ?: \Voxel\svg( 'eye.svg' ) ?>
			    </a>

			  </div>
			</div>
		<!-- 	<div class="ts-panel qrwidget">
				<div class="ac-head">
				   <i class="las la-qrcode"></i>
				   <p>Tag order via QR code</p>
				</div>
				<div class="ac-body">
					<p><?= sprintf( 'Order <b>#%d</b> has been set to <b>%s</b>', $order->get_id(), $tag->get_label() ) ?></p>
					<div class="ac-bottom">
						<ul class="simplify-ul current-plan-btn">
							<li>
								<a href="<?= esc_url( $order->get_link() ) ?>" class="ts-btn ts-btn-1"><?= \Voxel\svg( 'checkmark-circle.svg' ) ?>View order</a>
							</li>
						</ul>
					</div>
				</div>

			</div> -->
			<?php
		} catch ( \Exception $e ) {
			$message = $e->getMessage(); ?>

				<div class="ts-edit-success flexify">
				  <i class="las la-info-circle"></i>
				  <h4><?= $message ?></h4>

				</div>
			<?php
		}
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
