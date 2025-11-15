<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>General</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Text_Model::render( [
			'v-model' => 'config.settings.label',
			'label' => 'Label',
			'classes' => 'x-col-6',
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Key_Model::render( [
			'v-model' => 'config.settings.key',
			'label' => 'Key',
			'editable' => false,
			'classes' => 'x-col-6',
		] ) ?>
	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>Registration</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.registration.enabled',
			'label' => 'Enable user registration for this role',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="config.registration.enabled">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.registration.allow_social_login',
				'label' => 'Enable social login for this role',
				'infobox' => 'Allows visitors to register for this role through social login',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'config.registration.after_registration',
				'label' => 'After registration is complete',
				'classes' => 'x-col-12',
				'choices' => [
					'welcome_step' => 'Show welcome screen',
					'redirect_back' => 'Redirect back where the user left off',
					'custom_redirect' => 'Custom redirect',
				],
			] ) ?>

			<div v-if="config.registration.after_registration === 'custom_redirect'" class="ts-form-group x-col-12">
				<label>Custom redirect URL</label>
				<dtag-input
					v-model="config.registration.custom_redirect"
					:tag-groups="{site: { label: 'Site', type: 'site' }, user: { label: 'User', type: 'user' }}"
				></dtag-input>
			</div>
		</template>
	</div>
</div>