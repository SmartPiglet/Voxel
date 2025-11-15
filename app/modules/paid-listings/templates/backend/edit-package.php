<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="vx-single-customer vx-single-package">
	<div class="vx-card-ui">
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Customer</p>
			</div>
			<div class="vx-card-content">
				<div class="vx-group">
					<span>
						<?= $customer->get_avatar_markup() ?>
					</span>
					<a href="<?= esc_url( $customer->get_edit_link() ) ?>">
						<?= esc_html( $customer->get_display_name() ) ?>
					</a>
				</div>
			</div>
		</div>
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Customer ID</p>
			</div>
			<div class="vx-card-content">
				<a href="<?= esc_url( $customer->get_edit_link() ) ?>">
					#<?= esc_html( $customer->get_id() ) ?>
				</a>
			</div>
		</div>
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Assigned Plan ID</p>
			</div>
			<div class="vx-card-content">
				#<?= $package->get_id() ?>
			</div>
		</div>

		<div class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Details</p>
			</div>
			<div class="vx-card-content">
				<table class="form-table">
					<tbody>
						<tr>
							<th>Plan</th>
							<td>
								<?php if ( $plan ): ?>
									<a href="<?= esc_url( $plan->get_edit_link() ) ?>">
										<b><?= esc_html( $plan->get_label() ) ?></b>
									</a>
								<?php else: ?>
									&mdash;
								<?php endif ?>
							</td>
						</tr>
						<tr>
							<th>Status</th>
							<td>
								<div class="order-status order-status-<?= esc_attr( $order->get_status() ) ?>">
									<?= esc_html( $order->get_status_label() ) ?>
								</div>
							</td>
						</tr>
						<?php if ( $order->get_total() !== null ): ?>
							<tr>
								<th>Pricing</th>
								<td>
									<span class="price-amount">
										<?= \Voxel\currency_format( $order->get_total(), $order->get_currency(), false ) ?>
										<?php if ( $payment_method && $payment_method->is_subscription() && ( $interval = $payment_method->get_billing_interval() ) ): ?>
											<?= \Voxel\interval_format( $interval['interval'], $interval['interval_count'] ) ?>
										<?php endif ?>
									</span>
								</td>
							</tr>
						<?php endif ?>
						<tr>
							<th>Order</th>
							<td>
								<a href="<?= esc_url( $order->get_backend_link() ) ?>">
									#<?= $order->get_id() ?>
								</a>
							</td>
						</tr>
						<?php if ( $created_at = $order->get_created_at() ): ?>
							<tr>
								<th>Created</th>
								<td>
									<?= \Voxel\datetime_format( $created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ?>
								</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</div>

		<?php if ( ! empty( $limits ) ): ?>
			<?php foreach ( $limits as $limit ):
				$total = $limit['total'];
				$used = min( $limit['usage']['count'], $limit['total'] );
				$recents = $package->get_recent_posts_for_limit( $limit, 0, 25 );
				?>
				<div class="vx-card full no-wp-style" id="package-usage">
					<div class="vx-card-head">
						<p>
							<?= esc_html( $package->get_label_for_limit( $limit ) ) ?>
							<span style="float: right;"><?= $used ?>/<?= $total ?> used</span>
						</p>
					</div>
					<div class="vx-card-content">
						<table class="form-table">
							<tbody>
								<tr>
									<th>Total slots</th>
									<td><?= $total ?></td>
								</tr>
								<tr>
									<th>Used slots</th>
									<td><?= $used ?></td>
								</tr>
								<tr>
									<th>Marks verified?</th>
									<td><?= $limit['mark_verified'] ? 'Yes' : 'No' ?></td>
								</tr>
								<tr>
									<th>Priority level</th>
									<td>
										<?php if ( $limit['priority']['enabled'] && $limit['priority']['value'] >= 1 ): ?>
											<?= match( $limit['priority']['value'] ) {
												1 => 'Default',
												2 => 'High',
												default => 'Custom: '.$limit['priority']['value'],
											} ?>
										<?php else: ?>
											Default
										<?php endif ?>
									</td>
								</tr>
								<tr>
									<th>Expiration</th>
									<td>
										<?php if ( $limit['expiration']['mode'] === 'fixed_days' && $limit['expiration']['fixed_days'] !== null ): ?>
											<?= sprintf( 'Expires after %d days', $limit['expiration']['fixed_days'] ) ?>
										<?php else: ?>
											None
										<?php endif ?>
									</td>
								</tr>
								<?php if ( ! empty( $recents['list'] ) ): ?>
									<tr>
										<th></th>
										<td>
											<details>
												<summary>View recent submissions</summary>
												<ul>
													<?php foreach ( $recents['list'] as $recent ): ?>
														<li>
															<?php if ( $post = \Voxel\Post::get( $recent['id'] ) ): ?>
																<a href="<?= esc_url( get_edit_post_link( $post->get_id() ) ) ?>">
																	<b><?= $post->get_display_name() ?></b>
																</a>
																<span style="opacity: .5; padding-left: 5px;">ID: <?= $post->get_id() ?></span>
																<div><?= esc_html( $recent['description'] ) ?></div>
															<?php else: ?>
																<b>(deleted)</b>
																<?php if ( is_numeric( $recent['id'] ?? null ) ): ?>
																	<span style="opacity: .5; padding-left: 5px;">ID: <?= $recent['id'] ?></span>
																<?php endif ?>
																<div><?= esc_html( $recent['description'] ) ?></div>
															<?php endif ?>
														</li>
													<?php endforeach ?>
												</ul>
											</details>
										</td>
									</tr>
								<?php endif ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endforeach ?>

			<div>
				<a class="button" href="#"
					onclick="event.preventDefault();document.getElementById('modify-limits').classList.toggle('hidden')"
				>Edit limits</a>
			</div>

			<div id="modify-limits" class="vx-card full no-wp-style hidden">
				<div class="vx-card-content">
					<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>">
						<table class="form-table">
							<tbody>
								<tr>
									<th><b>Post type(s)</b></th>
									<td><b>Submission limit</b></td>
								</tr>
								<?php foreach ( $limits as $limit_index => $limit ):
									$total = $limit['total'];
									$used = min( $limit['usage']['count'], $limit['total'] );
									?>
									<tr>
										<th><?= esc_html( $package->get_label_for_limit( $limit ) ) ?></th>
										<td>
											<input type="number" name="limits[<?= esc_attr( $limit_index ) ?>]"
												required min="<?= esc_attr( $used ) ?>" value="<?= esc_attr( $total ) ?>">
										</td>
									</tr>
								<?php endforeach ?>
								<tr>
									<th></th>
									<td>
										<input type="hidden" name="package_id" value="<?= esc_attr( $package->get_id() ) ?>">
										<input type="hidden" name="action" value="paid_listings.edit_package">
										<?php wp_nonce_field( 'paid_listings.edit_package' ) ?>
										<button type="submit" class="button button-primary">Save changes</button>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		<?php endif ?>
	</div>
</div>
