<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Paddle</h3>
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
				'infobox' => 'You can access your Paddle secret API key by navigating to: Paddle Dashboard → Developer Tools → Authentication → API Keys.',
			] ) ?>

			<template v-if="settings.sandbox.api_key">
				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.sandbox.webhook.id',
					'label' => 'Sandbox webhook ID',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<?php \Voxel\Utils\Form_Models\Key_Model::render( [
					'v-model' => 'settings.sandbox.webhook.secret',
					'label' => 'Sandbox webhook secret',
					'classes' => 'x-col-6',
					'placeholder' => 'Not configured',
				] ) ?>

				<div v-if="!(settings.sandbox.webhook.id || settings.sandbox.webhook.secret)" class="ts-form-group x-col-12">
					<a href="#" @click.prevent="setupWebhook($event, 'sandbox')" class="ts-button ts-outline">Create webhook</a>
				</div>
			</template>
		</template>
		<template v-else>
			<?php \Voxel\Utils\Form_Models\Password_Model::render( [
				'v-model' => 'settings.live.api_key',
				'label' => 'API key',
				'classes' => 'x-col-12',
				'infobox' => 'You can access your Paddle secret API key by navigating to: Paddle Dashboard → Developer Tools → Authentication → API Keys.',
			] ) ?>

			<template v-if="settings.live.api_key">
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
					<a href="#" @click.prevent="setupWebhook($event, 'live')" class="ts-button ts-outline">Create webhook</a>
				</div>
			</template>
		</template>
	</div>
</div>
