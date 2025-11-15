<?php

use \Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;

if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="vx-single-customer" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<div class="vx-card-ui">
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Customer</p>
			</div>
			<div class="vx-card-content">
				<div class="vx-group">
					<span v-html="config.customer.avatar_markup"></span>
					<a :href="config.customer.edit_link">{{ config.customer.display_name }}</a>
				</div>
			</div>
		</div>
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Customer ID</p>
			</div>
			<div class="vx-card-content">
				<a :href="config.customer.edit_link">#{{ config.customer.id }}</a>
			</div>
		</div>
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Email</p>
			</div>
			<div class="vx-card-content">
				<a :href="'mailto:'+config.customer.email">{{ config.customer.email }}</a>
			</div>
		</div>

		<div class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Plan details</p>
			</div>
			<div class="vx-card-content">
				<table class="form-table">
					<tbody>
						<tr>
							<th>Plan</th>
							<td><strong><a :href="config.plan.edit_link">{{ config.plan.label }}</a></strong></td>
						</tr>

						<?php if (
							$membership->get_type() === 'order'
							&& ( $order = $membership->get_order() )
							&& ( $payment_method = $membership->get_payment_method() )
						): ?>
							<tr>
								<th>Status</th>
								<td><?= $order->get_status_label() ?></td>
							</tr>
							<tr>
								<th>Pricing</th>
								<td>
									<?= sprintf(
										'<a href="%s"><span class="price-amount">%s</span> %s</a>',
										esc_url( $order->get_backend_link() ),
										\Voxel\currency_format( $membership->get_amount(), $membership->get_currency(), false ),
										\Voxel\interval_format( $membership->get_interval(), $membership->get_frequency() )
									) ?>
								</td>
							</tr>
							<tr>
								<th>Order</th>
								<td>
									<?= sprintf(
										'<a href="%s">#%d</a>',
										esc_url( $order->get_backend_link() ),
										$order->get_id()
									) ?>
								</td>
							</tr>
							<?php if ( $created_at = $order->get_created_at() ): ?>
								<tr>
									<th>Created</th>
									<td><?= \Voxel\datetime_format(
										$created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )
									) ?></td>
								</tr>
							<?php endif ?>
						<?php elseif ( $membership->get_type() === 'default' ): ?>
							<?php if ( $plan->get_key() === 'default' ): ?>
								<tr>
									<th></th>
									<td>This user does not have an active paid membership plan.</td>
								</tr>
								<tr>
									<th>Eligible for free trial?</th>
									<td><?= $customer->is_eligible_for_free_trial() ? 'Yes' : 'No' ?></td>
								</tr>
							<?php else: ?>
								<tr>
									<th></th>
									<td>This membership plan was manually assigned to this user.</td>
								</tr>
							<?php endif ?>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</div>

		<div>
			<a class="button" href="#" @click.prevent="state.show_edit_plan = !state.show_edit_plan">Edit plan</a>
		</div>

		<div v-if="state.show_edit_plan" class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Edit plan</p>
			</div>
			<div class="vx-card-content">
				<form @submit.prevent="updatePlan">
					<table class="form-table">
						<tbody>
							<template v-if="config.membership.type === 'order' && config.membership.canceled">
								<tr>
									<td>
										<p>Subscription has been cancelled permanently. To assign a new plan, unlink the existing order.</p>
									</td>
									<td>
										<button type="submit" class="button button-primary" :class="{'vx-disabled': state.updating_plan}">
											Unlink order
										</button>
									</td>
								</tr>
							</template>
							<template v-else>
								<tr>
									<th>Plan</th>
									<td>
										<select v-model="config.edit.plan" style="width: 250px;">
											<?php foreach ( \Voxel\Plan::all() as $plan ): ?>
												<option value="<?= esc_attr( $plan->get_key() ) ?>"><?= esc_html( $plan->get_label() ) ?></option>
											<?php endforeach ?>
										</select>
									</td>
								</tr>
								<template v-if="config.edit.plan === 'default' && config.membership.type === 'default'">
									<tr>
										<th></th>
										<td>
											<label>
												<input type="checkbox" v-model="config.edit.trial_allowed">
												Eligible for free trial?
											</label>
										</td>
									</tr>
								</template>
								<?php if ( Payment_Service::get_active()?->get_key() === 'stripe' ): ?>
									<template v-if="config.edit.plan !== 'default' && config.membership.type === 'default'">
										<tr>
											<th></th>
											<td style="display: grid; gap: 5px;">
												<label>
													<input v-model="config.edit.stripe_map.enabled" type="checkbox">
													Link to a Stripe subscription
												</label>
												<div v-if="config.edit.stripe_map.enabled">
													<input type="text" placeholder="Enter ID e.g. sub_xxxxxxxxxxx"
														style="min-width: 250px;" v-model="config.edit.stripe_map.transaction_id">
													<ul style="margin: 5px 0 10px 15px;font-size: 13px;list-style: disc;">
														<li>Subscription must belong to the selected user.</li>
														<li>Subscription must not be linked to an existing order in <b>Ecommerce > Orders</b>.</li>
													</ul>
												</div>
											</td>
										</tr>
									</template>
								<?php endif ?>
								<tr>
									<th></th>
									<td>
										<button type="submit" class="button button-primary" :class="{'vx-disabled': state.updating_plan}">
											Save changes
										</button>
									</td>
								</tr>
							</template>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
