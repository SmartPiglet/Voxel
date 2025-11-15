<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>General</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'settings.currency',
			'label' => 'Currency',
			'choices' => \Voxel\Utils\Currency_List::only( $this->get_supported_currencies() ),
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'settings.mode',
			'label' => 'Mode',
			'classes' => 'x-col-12',
			'choices' => [
				'live' => 'Production',
				'sandbox' => 'Sandbox (test mode)',
			],
		] ) ?>

		<template v-if="settings.mode === 'sandbox'">
			<?php \Voxel\Utils\Form_Models\Password_Model::render( [
				'v-model' => 'settings.sandbox.api_key',
				'label' => 'Sandbox API key',
				'classes' => 'x-col-12',
				'infobox' => 'You can access your Stripe secret API key by navigating to: Stripe Dashboard → Developers → API keys.',
			] ) ?>
		</template>
		<template v-else>
			<?php \Voxel\Utils\Form_Models\Password_Model::render( [
				'v-model' => 'settings.live.api_key',
				'label' => 'API key',
				'classes' => 'x-col-12',
				'infobox' => 'You can access your Stripe secret API key by navigating to: Stripe Dashboard → Developers → API keys.',
			] ) ?>
		</template>
	</div>
</div>

<template v-if="settings.mode === 'sandbox'">
	<template v-if="settings.sandbox.api_key">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Webhooks</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.sandbox.webhook.id',
					'label' => 'Sandbox webhook ID',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<div class="ts-form-group x-col-6">
					<span class="vx-info-box wide" style="float: right;">
						<?php \Voxel\svg( 'info.svg' ) ?>
						<div style="width: 400px;">
							<b>For local setups:</b> Run the <a href="https://stripe.com/docs/stripe-cli" target="_blank">Stripe CLI</a> with
							<pre class="ts-snippet" style="word-break:break-all;"><span class="ts-green">stripe</span> listen <span class="ts-italic">--forward-to="<?= home_url('?vx=1&action=stripe.webhooks') ?>"</span></pre>
							Then, paste the generated webhook signing secret in the field below.
						</div>
					</span>
					<label>Sandbox webhook secret</label>
					<field-key
						v-model="settings.sandbox.webhook.secret"
						placeholder="Not configured"
						:editable="true"
						:unlocked="false"
					></field-key>
				</div>

				<div v-if="!(settings.sandbox.webhook.id || settings.sandbox.webhook.secret)" class="ts-form-group x-col-12">
					<a href="#" class="ts-button ts-outline"
						@click.prevent="setupWebhook($event, 'sandbox')">
						Create webhook
					</a>
				</div>
			</div>
		</div>

		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Customer portal</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
					'v-model' => 'settings.sandbox.customer_portal.invoice_history',
					'label' => 'Show invoice history',
					'infobox' => 'Customer can view their billing history in the portal.',
					'classes' => 'x-col-6',
				] ) ?>

				<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
					'v-model' => 'settings.sandbox.customer_portal.customer_update.enabled',
					'label' => 'Allow updating details',
					'infobox' => 'Customer can update their personal details in the portal.',
					'classes' => 'x-col-6',
				] ) ?>

				<template v-if="settings.sandbox.customer_portal.customer_update.enabled">
					<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
						'v-model' => 'settings.sandbox.customer_portal.customer_update.allowed_updates',
						'label' => 'Updateable details',
						'classes' => 'x-col-12',
						'columns' => 'two',
						'choices' => [
							'name' => 'Name',
							'email' => 'Email',
							'address' => 'Billing address',
							'shipping' => 'Shipping address',
							'phone' => 'Phone numbers',
							'tax_id' => 'Tax IDs',
						],
					] ) ?>
				</template>

				<div class="ts-form-group x-col-12">
					<a href="#" class="ts-button ts-outline"
						@click.prevent="setupCustomerPortal($event, 'sandbox')">
						Save configuration
					</a>
				</div>

				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.sandbox.customer_portal.id',
					'label' => 'Configuration ID',
					'classes' => 'x-col-12 hidden',
					'placeholder' => 'Not configured',
				] ) ?>
			</div>
		</div>
	</template>
</template>
<template v-else>
	<template v-if="settings.live.api_key">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Webhooks</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.live.webhook.id',
					'label' => 'Webhook ID',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.live.webhook.secret',
					'label' => 'Webhook secret',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<div v-if="!(settings.live.webhook.id || settings.live.webhook.secret)" class="ts-form-group x-col-12">
					<a href="#" class="ts-button ts-outline"
						@click.prevent="setupWebhook($event, 'live')">
						Create webhook
					</a>
				</div>
			</div>
		</div>

		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Customer portal</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
					'v-model' => 'settings.live.customer_portal.invoice_history',
					'label' => 'Show invoice history',
					'infobox' => 'Customer can view their billing history in the portal.',
					'classes' => 'x-col-6',
				] ) ?>

				<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
					'v-model' => 'settings.live.customer_portal.customer_update.enabled',
					'label' => 'Allow updating details',
					'infobox' => 'Customer can update their personal details in the portal.',
					'classes' => 'x-col-6',
				] ) ?>

				<template v-if="settings.live.customer_portal.customer_update.enabled">
					<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
						'v-model' => 'settings.live.customer_portal.customer_update.allowed_updates',
						'label' => 'Updateable details',
						'classes' => 'x-col-12',
						'columns' => 'two',
						'choices' => [
							'name' => 'Name',
							'email' => 'Email',
							'address' => 'Billing address',
							'shipping' => 'Shipping address',
							'phone' => 'Phone numbers',
							'tax_id' => 'Tax IDs',
						],
					] ) ?>
				</template>

				<div class="ts-form-group x-col-12">
					<a href="#" class="ts-button ts-outline"
						@click.prevent="setupCustomerPortal($event, 'live')">
						Save configuration
					</a>
				</div>

				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.live.customer_portal.id',
					'label' => 'Configuration ID',
					'classes' => 'x-col-12 hidden',
					'placeholder' => 'Not configured',
				] ) ?>
			</div>
		</div>
	</template>
</template>