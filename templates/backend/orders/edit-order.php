<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="vx-single-order">
	<div class="vx-card-ui vx-order-details">
		<div class="vx-card no-wp-style full">
			<div class="vx-card-head">
				Order details
			</div>
			<div class="vx-card-content">
				<table class="form-table">
					<tbody>
						<tr>
							<th>Order ID</th>
							<td><strong>#<?= $order->get_id() ?></strong></td>
						</tr>
						<tr>
							<th>Order status</th>
							<td>
								<span class="order-status order-status-<?= esc_attr( $order->get_status() ) ?>">
									<?= esc_html( $order->get_status_label() ) ?>
								</span>
							</td>
						</tr>
						<?php if ( $customer ): ?>
							<tr>
								<th>Customer</th>
								<td><?= sprintf( '%s<span class="item-title"><a href="%s">%s</a></span>',
									$customer->get_avatar_markup(32),
									esc_url( $customer->get_edit_link() ),
									esc_html( $customer->get_display_name() )
								) ?></td>
							</tr>
						<?php endif ?>
						<?php if ( $order->has_vendor() && $vendor ): ?>
							<tr>
								<th>Vendor</th>
								<td><?= sprintf( '%s<span class="item-title"><a href="%s">%s</a></span>',
									$vendor->get_avatar_markup(32),
									esc_url( $vendor->get_edit_link() ),
									esc_html( $vendor->get_display_name() )
								) ?></td>
							</tr>
						<?php endif ?>
						<?php if ( is_numeric( $order_amount ) ): ?>
							<tr>
								<th>Amount</th>
								<td>
									<?= \Voxel\currency_format( $order_amount, $order->get_currency(), false ) ?>
								</td>
							</tr>
						<?php endif ?>
						<?php if ( $payment_method !== null ): ?>
							<tr>
								<th>Payment method</th>
								<td><?= $payment_method->get_label() ?></td>
							</tr>
						<?php endif ?>

						<?php if ( ! empty( $parent_order ) ): ?>
							<tr>
								<th>Parent order</th>
								<td>
									<a href="<?= esc_url( $parent_order->get_backend_link() ) ?>">#<?= $parent_order->get_id() ?></a>
								</td>
							</tr>
						<?php endif ?>

						<?php if ( ! empty( $child_orders ) ): ?>
							<tr>
								<th>Suborders</th>
								<td>
									<?php foreach ( $child_orders as $child_order ): ?>
										<a href="<?= esc_url( $child_order->get_backend_link() ) ?>">#<?= $child_order->get_id() ?></a>
									<?php endforeach ?>
								</td>
							</tr>
						<?php endif ?>

						<?php if ( $created_at = $order->get_created_at() ): ?>
							<tr>
								<th>Created</th>
								<td><?= \Voxel\datetime_format( $created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ?></td>
							</tr>
						<?php endif ?>

						<?php do_action( 'voxel/backend/view_order/details/after', $order ) ?>
					</tbody>
				</table>
			</div>
		</div>

		<?php if ( ! empty( $order_items ) ): ?>
			<div class="vx-card no-wp-style full">
				<div class="vx-card-head">
					Order items
				</div>
				<div class="vx-card-content">
					<table class="form-table">
						<tbody>
							<?php foreach ( $order_items as $index => $item ): ?>
								<tr>
									<th>
										<?php if ( $product_link = $item->get_product_link() ): ?>
											<strong>
												<a href="<?= esc_url( $product_link ) ?>">
													<?= esc_html( $item->get_product_label() ) ?>
												</a>
											</strong>
										<?php else: ?>
											<strong>
												<?= esc_html( $item->get_product_label() ) ?>
											</strong>
										<?php endif ?>

										<?php if ( $item->get_quantity() !== null ): ?>
											&times; <?= esc_html( $item->get_quantity() ) ?>
										<?php endif ?>
										<?php if ( ! empty( $item->get_product_description() ) ): ?>
											<p>
												<?= esc_html( $item->get_product_description() ) ?>
											</p>
										<?php endif ?>
									</th>
									<th style="text-align: right;"><?= \Voxel\currency_format( $item->get_subtotal(), $item->get_currency(), false ) ?></th>
								</tr>
							<?php endforeach ?>

							<?php if ( is_numeric( $order->get_subtotal() ) ): ?>
								<tr>
									<th>Subtotal</th>
									<th style="text-align: right;">
										<?= \Voxel\currency_format( $order->get_subtotal(), $order->get_currency(), false ) ?>
									</th>
								</tr>
							<?php endif ?>

							<?php if ( is_numeric( $order->get_tax_amount() ) ): ?>
								<tr>
									<th>Tax</th>
									<th style="text-align: right;">
										<?= \Voxel\currency_format( $order->get_tax_amount(), $order->get_currency(), false ) ?>
									</th>
								</tr>
							<?php endif ?>

							<?php if ( is_numeric( $order->get_total() ) ): ?>
								<tr>
									<th><strong>Total</strong></th>
									<th style="text-align: right; font-weight: 600;">
										<?= \Voxel\currency_format( $order->get_total(), $order->get_currency(), false ) ?>
									</th>
								</tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif ?>

		<?php if ( $customer ): ?>
			<div class="vx-card no-wp-style full">
				<div class="vx-card-head">
					Customer details
				</div>
				<div class="vx-card-content">
					<table class="form-table">
						<tbody>
							<tr>
								<th>User</th>
								<td><?= sprintf( '%s<span class="item-title"><a href="%s">%s</a></span>',
									$customer->get_avatar_markup(32),
									esc_url( $customer->get_edit_link() ),
									esc_html( $customer->get_display_name() )
								) ?></td>
							</tr>
							<tr>
								<th>Email</th>
								<td><?= sprintf( '<a href="mailto:%s">%s</a>', esc_attr( $customer->get_email() ), esc_html( $customer->get_email() ) ) ?></td>
							</tr>

							<?php do_action( 'voxel/backend/view_order/customer_details/after', $order, $customer ) ?>

							<?php $customer_details = $order->get_customer_details() ?>
							<?php if ( ! empty( $customer_details ) ): ?>
								<tr><th></th></tr>
								<tr>
									<th><strong>Checkout details</strong></th>
								</tr>
								<?php foreach ( $customer_details as $detail ): ?>
									<tr>
										<th><?= esc_html( $detail['label'] ) ?></th>
										<td><?= esc_html( $detail['content'] ) ?></td>
									</tr>
								<?php endforeach ?>
							<?php endif ?>

							<?php $order_notes = $order->get_order_notes() ?>
							<?php if ( ! empty( $order_notes ) ): ?>
								<tr><th></th></tr>
								<tr>
									<th><strong>Order notes</strong></th>
								</tr>
								<tr>
									<th colspan="2"><?= esc_html( $order_notes ) ?></th>
								</tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif ?>


		<?php if ( $order->has_vendor() && $vendor ): ?>
			<div class="vx-card no-wp-style full">
				<div class="vx-card-head">
					Vendor details
				</div>
				<div class="vx-card-content">
					<table class="form-table">
						<tbody>
							<tr>
								<th>User</th>
								<td><?= sprintf( '%s<span class="item-title"><a href="%s">%s</a></span>',
									$vendor->get_avatar_markup(32),
									esc_url( $vendor->get_edit_link() ),
									esc_html( $vendor->get_display_name() )
								) ?></td>
							</tr>
							<tr>
								<th>Email</th>
								<td><?= sprintf( '<a href="mailto:%s">%s</a>', esc_attr( $vendor->get_email() ), esc_html( $vendor->get_email() ) ) ?></td>
							</tr>

							<?php do_action( 'voxel/backend/view_order/vendor_details/after', $order, $vendor ) ?>

							<?php if ( ! empty( $vendor_fees ) ): ?>
								<tr><th></th></tr>
								<tr><th></th></tr>
								<tr>
									<th><strong>Vendor fees</strong></th>
								</tr>
								<?php if ( ! empty( $vendor_fees['breakdown'] ?? [] ) ): ?>
									<?php foreach ( $vendor_fees['breakdown'] as $fee ): ?>
										<tr>
											<th><?= esc_html( $fee['label'] ) ?></th>
											<th style="text-align: right;"><?= esc_html( $fee['content'] ) ?></th>
										</tr>
									<?php endforeach ?>
								<?php endif ?>

								<?php if ( is_numeric( $vendor_fees['total'] ?? null ) ): ?>
									<tr>
										<th><strong>Total</strong></th>
										<th style="text-align: right; font-weight: 600;">
											<?= \Voxel\currency_format( $vendor_fees['total'], $order->get_currency(), false ) ?>
										</th>
									</tr>
								<?php endif ?>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif ?>
	</div>
	<div class="vx-card-ui">
		<div class="vx-card ">
			<div class="vx-card-content vx-card-btns">
				<a class="vx-card-btn ts-button" href="<?= esc_url( $order->get_link() ) ?>"  target="_blank">
					Open in frontend
					<i class="las la-external-link-alt"></i>
				</a>

				<details>
					<summary>More actions</summary>
					<div class="vx-card-content vx-card-btns" style="padding: 0; margin-top: 10px;">
						<a
							class="vx-card-btn ts-button"
							onclick="return confirm('This action cannot be undone. Proceed anyway?');"
							style="color: #e11a1a; border-color: #e11a1a; background: transparent;"
							href="<?= esc_url( home_url( sprintf( '/?vx=1&action=backend.orders.delete_order&order_id=%d&_wpnonce=%s', $order->get_id(), wp_create_nonce( 'voxel_backend_delete_order' ) ) ) ) ?>"
						>Delete this order</a>
					</div>
				</details>
			</div>
		</div>
	</div>
</div>
