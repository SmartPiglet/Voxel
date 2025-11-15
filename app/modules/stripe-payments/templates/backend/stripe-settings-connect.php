<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'settings.stripe_connect.enabled',
			'label' => 'Enable marketplace functionality',
			'classes' => 'x-col-12',
		] ) ?>

	</div>
</div>

<template v-if="settings.stripe_connect.enabled">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Stripe payments</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'settings.stripe_connect.charge_type',
				'label' => 'Charge type',
				'classes' => 'x-col-6',
				'choices' => [
					'destination_charges' => 'Destination charges',
					'separate_charges_and_transfers' => 'Separate charges and transfers',
				],
				'description' => "Destination charges: Creates a charge on the platform and immediately transfers funds to the connected vendor account after fees are applied.
 - No cart support: Vendor products can only be purchased individually.

Separate Charges and Transfers: Creates a charge on the platform. Funds (after fees) are split and transferred to the connected vendor accounts. The charge on your platform account is decoupled from the transfer(s) to your connected accounts.
 - Supports cart: Products from different vendors can be added to cart and purchased within a single checkout session.",
			] ) ?>

			<template v-if="settings.stripe_connect.charge_type === 'destination_charges'">
				<?php \Voxel\Utils\Form_Models\Select_Model::render( [
					'v-model' => 'settings.stripe_connect.settlement_merchant',
					'label' => 'Settlement merchant <a style="float:right;" href="https://docs.stripe.com/connect/destination-charges?platform=web&ui=stripe-hosted#settlement-merchant" target="_blank">Learn more</a>',
					'classes' => 'x-col-6',
					'choices' => [
						'platform' => 'Platform',
						'vendor' => 'Vendor',
					],
					'description' => "The settlement merchant determines whose information is used to make the charge. This includes the statement descriptor (either the platform’s or the connected vendor account's) that’s displayed on the customer’s credit card or bank statement for that charge.

Platform: Your platform is responsible for the compliance and legal obligations related to the transactions processed on behalf of connected vendor accounts.
 - Does not support vendors outside the platform's region.

Vendor: The vendor is responsible for their own compliance and legal obligations related to the transactions processed through their account.
 - Supports cross-region vendors."
										] ) ?>
			</template>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Stripe subscriptions</h3>
		</div>
		<div class="x-row">
			<div class="ts-form-group x-col-6">
				<label>Charge type</label>
				<select v-model="settings.stripe_connect.subscriptions.charge_type" class="vx-disabled">
					<option value="destination_charges">Destination charges</option>
				</select>
			</div>

			<template v-if="settings.stripe_connect.subscriptions.charge_type === 'destination_charges'">
				<?php \Voxel\Utils\Form_Models\Select_Model::render( [
					'v-model' => 'settings.stripe_connect.subscriptions.settlement_merchant',
					'label' => 'Settlement merchant <a style="float:right;" href="https://docs.stripe.com/connect/destination-charges?platform=web&ui=stripe-hosted#settlement-merchant" target="_blank">Learn more</a>',
					'classes' => 'x-col-6',
					'choices' => [
						'platform' => 'Platform',
						'vendor' => 'Vendor',
					],
					'description' => "The settlement merchant determines whose information is used to make the charge. This includes the statement descriptor (either the platform’s or the connected vendor account's) that’s displayed on the customer’s credit card or bank statement for that charge.

Platform: Your platform is responsible for the compliance and legal obligations related to the transactions processed on behalf of connected vendor accounts.
- Does not support vendors outside the platform's region.

Vendor: The vendor is responsible for their own compliance and legal obligations related to the transactions processed through their account.
- Supports cross-region vendors.",
										] ) ?>
			</template>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Vendor fees on product sales</h3>
		</div>
		<div class="x-row">
			<div class="x-col-12 field-container">
				<template v-if="settings.stripe_connect.vendor_fees.length">
					<template v-for="fee, index in settings.stripe_connect.vendor_fees">
						<div class="single-field wide" :class="{open: state.activeVendorFee === fee}">
							<div class="field-head" @click="state.activeVendorFee = state.activeVendorFee === fee ? null : fee">
								<p class="field-name">{{ fee.label || '(untitled)' }}</p>
								<p class="field-type">
									<template v-if="fee.type === 'fixed' && fee.fixed_amount !== null && fee.fixed_amount !== ''">
										Fee: {{ $root.currencyFormat( fee.fixed_amount ) }}
									</template>
									<template v-else-if="fee.type === 'percentage' && fee.percentage_amount !== null && fee.percentage_amount !== ''">
										Fee: {{ fee.percentage_amount }}%
									</template>
									<template v-else>
										Configure fee
									</template>
									<span style="display: none;">{{ fee.key }}</span>
								</p>
								<div class="field-actions">
									<span class="field-action all-center">
										<a href="#" @click.stop.prevent="settings.stripe_connect.vendor_fees.splice(index, 1)">
											<i class="lar la-trash-alt icon-sm"></i>
										</a>
									</span>
								</div>
							</div>
							<div v-if="state.activeVendorFee === fee" class="field-body">
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'v-model' => 'fee.label',
										'label' => 'Label',
										'classes' => 'x-col-4',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'v-model' => 'fee.type',
										'label' => 'Fee type',
										'classes' => 'x-col-4',
										'choices' => [
											'fixed' => 'Fixed amount',
											'percentage' => 'Percentage',
										],
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Number_Model::render( [
										'v-model' => 'fee.fixed_amount',
										'v-if' => 'fee.type === \'fixed\'',
										'label' => sprintf(
											'Amount (%s)',
											\Voxel\get_primary_currency()
										),
										'classes' => 'x-col-4',
										'min' => 0,
										'step' => 'any',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Number_Model::render( [
										'v-model' => 'fee.percentage_amount',
										'v-if' => 'fee.type === \'percentage\'',
										'label' => 'Percentage',
										'classes' => 'x-col-4',
										'min' => 0,
										'max' => 100,
										'step' => 'any',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'v-model' => 'fee.apply_to',
										'label' => 'Apply this fee to',
										'classes' => 'x-col-12',
										'choices' => [
											'all' => 'Every vendor',
											'custom' => 'Specific vendors',
										],
									] ) ?>

									<template v-if="fee.apply_to === 'custom'">
										<template v-if="fee.conditions.length">
											<template v-for="condition, conditionIndex in fee.conditions">
												<div class="ts-form-group x-col-4">
													<label v-if="conditionIndex === 0">Source</label>
													<label v-else>Or</label>
													<select v-model="condition.source">
														<option value="vendor_plan">Vendor plan</option>
														<option value="vendor_role">Vendor role</option>
														<option value="vendor_id">Vendor ID</option>
													</select>
												</div>
												<div class="ts-form-group x-col-4">
													<label>Comparison</label>
													<select v-model="condition.comparison">
														<option value="equals">Equals</option>
														<option value="not_equals">Does not equal</option>
													</select>
												</div>
												<div class="ts-form-group x-col-4">
													<label>
														Value
														<a style="float:right;" href="%s" @click.prevent="fee.conditions.splice(conditionIndex, 1)">
															Remove
														</a>
													</label>
													<template v-if="condition.source === 'vendor_plan'">
														<select v-model="condition.value">
															<?php foreach ( \Voxel\Modules\Paid_Memberships\Plan::all() as $plan ): ?>
																<option value="<?= esc_attr( $plan->get_key() ) ?>">
																	<?= esc_html( $plan->get_label() ) ?>
																</option>
															<?php endforeach ?>
														</select>
													</template>
													<template v-else-if="condition.source === 'vendor_role'">
														<select v-model="condition.value">
															<?php foreach ( wp_roles()->roles as $role_key => $role ): ?>
																<option value="<?= esc_attr( $role_key ) ?>">
																	<?= esc_html( $role['name'] ) ?>
																</option>
															<?php endforeach ?>
														</select>
													</template>
													<template v-else-if="condition.source === 'vendor_id'">
														<input type="number" v-model="condition.value">
													</template>
												</div>
											</template>
										</template>

										<div class="x-col-12">
											<div class="add-field">
												<div
													class="ts-button ts-outline"
													@click.prevent="fee.conditions.push( {
														source: 'vendor_plan',
														comparison: 'equals',
														value: '',
													} )"
												>
													<p class="field-name">Add condition</p>
												</div>
											</div>
										</div>
									</template>
								</div>
							</div>
						</div>
					</template>
				</template>
				<div v-else class="ts-form-group">
					<p>You have not added any vendor fees yet.</p>
				</div>
			</div>

			<div class="x-col-12">
				<div class="add-field">
					<div
						class="ts-button ts-outline"
						@click.prevent="settings.stripe_connect.vendor_fees.push( state.activeVendorFee = {
							key: $root.$w.Voxel_Backend.helpers.randomId(8),
							label: 'Custom fee',
							type: 'fixed',
							fixed_amount: null,
							percentage_amount: null,
							apply_to: 'all',
							conditions: [],
						} )"
					>
						<p class="field-name">Create fee</p>
					</div>
				</div>
			</div>

			<!-- <div class="x-col-12">
				<pre debug>{{ settings.stripe_connect }}</pre>
			</div> -->
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Shipping</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'settings.stripe_connect.shipping.responsibility',
				'label' => 'Shipping responsibility',
				'classes' => 'x-col-12',
				'choices' => [
					'platform' => 'Platform: The platform handles shipping for all vendor products',
					'vendor' => 'Vendor: Each vendor handles their own shipping',
				],
			] ) ?>
		</div>
	</div>

	<template v-if="settings.mode === 'sandbox' && settings.sandbox.api_key">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Webhooks</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.stripe_connect.webhook.sandbox.id',
					'label' => 'Sandbox webhook ID',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.stripe_connect.webhook.sandbox.secret',
					'label' => 'Sandbox webhook secret',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<div v-if="!(settings.stripe_connect.webhook.sandbox.id || settings.stripe_connect.webhook.sandbox.secret)" class="ts-form-group x-col-12">
					<a href="#" class="ts-button ts-outline"
						@click.prevent="setupConnectWebhook($event, 'sandbox')">
						Create webhook
					</a>
				</div>
			</div>
		</div>
	</template>
	<template v-if="settings.mode === 'live' && settings.live.api_key">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Webhooks</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.stripe_connect.webhook.live.id',
					'label' => 'Webhook ID',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.stripe_connect.webhook.live.secret',
					'label' => 'Webhook secret',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<div v-if="!(settings.stripe_connect.webhook.live.id || settings.stripe_connect.webhook.live.secret)" class="ts-form-group x-col-12">
					<a href="#" class="ts-button ts-outline"
						@click.prevent="setupConnectWebhook($event, 'live')">
						Create webhook
					</a>
				</div>
			</div>
		</div>
	</template>
</template>
