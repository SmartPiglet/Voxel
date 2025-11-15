<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<ul class="inner-tabs">
	<li :class="{'current-item': subtab === 'general'}">
		<a href="#" @click.prevent="setTab('membership', 'general')">Registration</a>
	</li>
	<li :class="{'current-item': subtab === 'recaptcha'}">
		<a href="#" @click.prevent="setTab('membership', 'recaptcha')">Recaptcha</a>
	</li>
	<li :class="{'current-item': subtab === 'login_with_google'}">
		<a href="#" @click.prevent="setTab('membership', 'login_with_google')">Login with Google</a>
	</li>
</ul>
<template v-if="subtab === 'general'">
	<div class="ts-group">
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.membership.require_verification',
				'label' => 'Require email verification',
				'classes' => 'x-col-12',
			] ) ?>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'config.membership.username_behavior',
				'label' => 'Username field',
				'classes' => 'x-col-12',
				'choices' => [
					'display_as_field' => 'Show: Display username as a field in the registration form',
					'generate_from_email' => 'Hide: Generate username automatically from the user email',
				],
			] ) ?>
		</div>
	</div>
</template>
<template v-else-if="subtab === 'recaptcha'">
	<div class="ts-group">
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.recaptcha.enabled',
				'label' => 'Enable reCAPTCHA',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Text_Model::render( [
				'v-model' => 'config.recaptcha.key',
				'label' => 'Site key',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Password_Model::render( [
				'v-model' => 'config.recaptcha.secret',
				'label' => 'Secret key',
				'classes' => 'x-col-12',
				'autocomplete' => 'new-password',
			] ) ?>

			<div class="ts-form-group x-col-12">
				<p>Configure Google reCAPTCHA in the <a href="https://www.google.com/recaptcha/admin" target="_blank">v3 Admin Console</a></p>
			</div>
		</div>
	</div>
</template>
<template v-else-if="subtab === 'login_with_google'">
	<div class="ts-group">
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.auth.google.enabled',
				'label' => 'Enable Login with Google',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Text_Model::render( [
				'v-model' => 'config.auth.google.client_id',
				'label' => 'Client ID',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Password_Model::render( [
				'v-model' => 'config.auth.google.client_secret',
				'label' => 'Client secret',
				'classes' => 'x-col-12',
				'autocomplete' => 'new-password',
			] ) ?>

			<div class="ts-form-group x-col-12">
				<details>
					<summary>Setup guide</summary>
					<p><b>How to get Google Client ID and Client Secret</b></p>
					<ol>
						<li>Go to the <a href="https://console.developers.google.com/apis" target="_blank">Google Developers Console</a></li>
						<li>Click <b>Select a project ➝ New Project</b></li>
					</ol>
					<p><b>Configure OAuth consent & register your app</b></p>
					<ol>
						<li>In the Google Cloud console, go to <b>Menu ➝ APIs & Services ➝ OAuth consent screen</b></li>
						<li>Select <b>External</b> user type for your app, then click <b>Create</b></li>
						<li>Complete the app registration form, then click <b>Save and Continue</b></li>
						<li>Review your app registration summary. To make changes, click <b>Edit</b>. If the app registration looks OK, click <b>Back to Dashboard.</b></li>
					</ol>
					<p><b>Credentials</b></p>
					<ol>
						<li>In the Google Cloud console, go to <b>APIs & Services ➝ Credentials</b></li>
						<li>Press <b>Create credentials ➝ OAuth Client ID</b></li>
						<li>Select <b>Web application</b> type</li>
						<li>Under <b>Authorized Javascript origins</b> enter your site URL: <pre class="ts-snippet"><?= home_url('/') ?></pre></li>
						<li>Under <b>Authorized redirect URIs</b> enter: <pre class="ts-snippet"><?= home_url('/?vx=1&action=auth.google.login ') ?></pre></li>
						<li>Copy the generated <b>Client ID</b> and <b>Client Secret</b> and paste them in the section above</li>
					</ol>
				</details>
			</div>
		</div>
	</div>
</template>
