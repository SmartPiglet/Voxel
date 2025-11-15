<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$stripe_dashboard_url = \Voxel\is_test_mode() ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';
?>
<div id="migrate-legacy-plan" v-cloak class="vx-single-customer"
	data-config="<?= esc_attr( wp_json_encode( [
		'customer' => [
			'id' => $customer->get_id(),
		],
		'_wpnonce' => wp_create_nonce( 'paid_members.migrate_legacy_plan' ),
	] ) ) ?>">
	<div class="vx-card-ui">
		<div class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Legay plan details</p>
			</div>
			<div class="vx-card-content">
				<table class="form-table">
					<tbody>
						<tr>
							<th>Customer</th>
							<td>
								<div class="vx-group">
									<?= $customer->get_avatar_markup() ?>
									<a href="<?= esc_url( $customer->get_edit_link() ) ?>">
										<?= esc_html( $customer->get_display_name() ) ?>
									</a>
								</div>
							</td>
						</tr>
						<tr>
							<th>Plan</th>
							<td>
								<a href="<?= esc_url( $legacy_plan->get_selected_plan()->get_edit_link() ) ?>">
									<b><?= esc_html( $legacy_plan->get_selected_plan()->get_label() ) ?></b>
								</a>
							</td>
						</tr>
						<tr>
							<th>Billing mode</th>
							<td>Subscription</td>
						</tr>
						<tr>
							<th>Subscription ID</th>
							<td>
								<?= sprintf(
									'<a href="%s" target="_blank">%s %s</a>',
									$stripe_dashboard_url . 'subscriptions/' . $legacy_plan->get_subscription_id(),
									$legacy_plan->get_subscription_id(),
									'<i class="las la-external-link-alt"></i>'
								) ?>
							</td>
						</tr>
						<tr>
							<th>Price</th>
							<td>
								<?= \Voxel\currency_format( $legacy_plan->get_amount(), $legacy_plan->get_currency() ) ?>
								<?php if ( $legacy_plan->get_interval() && $legacy_plan->get_interval_count() ): ?>
									<?= \Voxel\interval_format( $legacy_plan->get_interval(), $legacy_plan->get_interval_count() ) ?>
								<?php endif ?>
							</td>
						</tr>
						<tr>
							<th>Status</th>
							<td>
								<?= ucwords( str_replace( '_', ' ', $legacy_plan->get_status() ) ) ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="vx-card full no-wp-style">
			<div class="vx-card-content">
				<form @submit.prevent="onSubmit">
					<table class="form-table">
						<tbody>
							<tr>
								<th>Migration strategy</th>
								<td>
									<select v-model="form.strategy">
										<option value="paid_membership_plan">Convert to paid membership plan</option>
										<option value="paid_listing_plan">Convert to paid listing plan</option>
										<option value="unset">Do not assign a plan</option>
									</select>
								</td>
							</tr>
							<tr v-if="form.strategy === 'paid_listing_plan'">
								<th>Listing plan</th>
								<td>
									<select v-model="form.listing_plan">
										<option :value="null">Select plan</option>
										<?php foreach ( \Voxel\Modules\Paid_Listings\Listing_Plan::all() as $plan ): ?>
											<option value="<?= esc_attr( $plan->get_key() ) ?>">
												<?= esc_html( $plan->get_label() ) ?>
											</option>
										<?php endforeach ?>
									</select>
								</td>
							</tr>
							<tr :class="{'vx-disabled':processing}">
								<th></th>
								<td>
									<button type="submit" class="button button-primary">Migrate</button>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
		<details style="grid-column: 1 / -1;">
			<summary style="opacity: .3; text-align: right;">Debug data</summary>
			<pre debug style="width: 100%; box-sizing: border-box;"><?= esc_html( wp_json_encode( $legacy_plan->to_array(), JSON_PRETTY_PRINT ) ) ?></pre>
		</details>
	</div>
</div>

<script>
	jQuery( () => {
		const el = document.getElementById('migrate-legacy-plan');
		const config = JSON.parse( el.dataset.config );
		const app = Vue.createApp( {
			el: el,
			data() {
				return {
					form: {
						user_id: config.customer.id,
						strategy: 'paid_membership_plan',
						listing_plan: null,
					},
					processing: false,
				};
			},
			methods: {
				onSubmit() {
					this.processing = true;
					jQuery.post( `${Voxel_Config.ajax_url}&action=paid_members.migrate_legacy_subscription`, {
						config: JSON.stringify( this.form ),
						_wpnonce: config._wpnonce,
					} ).always( response => {
						if ( response.success ) {
							location.href = response.redirect_to;
						} else {
							this.processing = false;
					    	Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				},
			},
		} );

		app.mount('#migrate-legacy-plan');
	} );
</script>