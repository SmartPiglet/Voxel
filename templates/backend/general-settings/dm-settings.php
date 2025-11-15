<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Message length</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.messages.maxlength',
			'label' => 'Maximum message length (in characters)',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Uploads</h3>
	</div>

	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.messages.files.enabled',
			'label' => 'Enable file uploads',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="config.messages.files.enabled">
			<?php \Voxel\Utils\Form_Models\Number_Model::render( [
				'v-model' => 'config.messages.files.max_size',
				'label' => 'Max file size (kB)',
				'classes' => 'x-col-6',

			] ) ?>

			<?php \Voxel\Utils\Form_Models\Number_Model::render( [
				'v-model' => 'config.messages.files.max_count',
				'label' => 'Max file count',
				'classes' => 'x-col-6',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
				'v-model' => 'config.messages.files.allowed_file_types',
				'label' => 'Allowed file types',
				'classes' => 'x-col-12',
				'choices' => array_combine( get_allowed_mime_types(), get_allowed_mime_types() ),
			] ) ?>
		</template>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Real time</h3>
	</div>
	<div class="x-row">
		<?php $check_activity_url = trailingslashit( get_template_directory_uri() ) . 'app/modules/direct-messages/check-activity.php'; ?>
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.messages.enable_real_time',
			'label' => 'Update chats in real-time',
			'classes' => 'x-col-12',
			'infobox' => <<<HTML
			This feature relies on a small helper file located at:
			<code class="ts-snippet" style="display:block;word-break:break-all;padding:5px;margin:5px 0;font-size:12px;">{$check_activity_url}</code>
			Some web hosts block access to certain files by default. If you notice the feature
			isn't working, please check with your hosting provider to make sure this file can be
			reached from the web.
			HTML,
			'infobox_class' => 'wide-xxl',
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.messages.enable_seen',
			'label' => 'Show "Seen" badge',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>Storage</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.messages.persist_days',
			'label' => 'Delete messages older than (days)',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
