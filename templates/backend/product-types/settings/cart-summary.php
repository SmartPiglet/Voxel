<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<!-- <div class="ts-group-head">
		<h3>Guest customers</h3>
	</div> -->
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'config.cart_summary.guest_customers.behavior',
			'label' => 'Checkout behavior for guest customers',
			'classes' => 'x-col-12',
			'choices' => [
				'require_account' => 'Require account: Customer must have an account to place order',
				'proceed_with_email' => 'Proceed with email: Customer can place order by providing an email address',
			],
		] ) ?>

		<template v-if="config.cart_summary.guest_customers.behavior === 'proceed_with_email'">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.cart_summary.guest_customers.proceed_with_email.require_verification',
				'label' => 'Require email verification',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.cart_summary.guest_customers.proceed_with_email.require_tos',
				'label' => 'Show Terms and Privacy Policy agreement checkbox',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.cart_summary.guest_customers.proceed_with_email.email_account_details',
				'label' => 'Send an email to the customer with their account details',
				'classes' => 'x-col-12',
			] ) ?>
		</template>
	</div>
</div>
