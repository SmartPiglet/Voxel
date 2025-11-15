<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'settings.subscriptions.billing_address_collection',
			'label' => 'Billing address collection',
			'classes' => 'x-col-12',
			'choices' => [
				'auto' => 'Automatic: Collect billing address when necessary',
				'required' => 'Required: Always collect billing address',
			],
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'settings.subscriptions.tax_id_collection.enabled',
			'label' => 'Collect Tax ID in <a href="https://stripe.com/docs/tax/checkout/tax-ids#supported-types" target="_blank">supported countries</a>',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'settings.subscriptions.phone_number_collection.enabled',
			'label' => 'Phone number collection',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'settings.subscriptions.promotion_codes.enabled',
			'label' => sprintf( 'Allow <a href="%s" target="_blank">promotion codes</a> in checkout', esc_url( \Voxel\Modules\Stripe_Payments\Stripe_Client::dashboard_url( '/coupons' ) ) ),
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
