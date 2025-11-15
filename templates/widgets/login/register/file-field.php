<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="auth-file-field">
	<div class="ts-form-group ts-file-upload inline-file-field" @dragenter="dragActive = true">
		<div class="drop-mask" v-show="dragActive" @dragleave.prevent="dragActive = false" @drop.prevent="onDrop" @dragenter.prevent @dragover.prevent></div>
		<label>
			{{ field.label }}
			<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scrollt">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>
		<div class="ts-file-list">
			<div class="pick-file-input">
				<a href="#" @click.prevent="$refs.input.click()">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?>
					<?= _x( 'Upload', 'file field', 'voxel' ) ?>
				</a>
			</div>
			<template v-for="file, index in field.value">
				<div class="ts-file" :style="getStyle(file)" :class="{'ts-file-img': previewImages && file.type.startsWith('image/')}">
					<div class="ts-file-info">
						<?= \Voxel\get_svg( 'cloud-upload' ) ?>
						<code>{{ file.name }}</code>
					</div>
					<a href="#" @click.prevent="field.value.splice(index,1)" class="ts-remove-file flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
					</a>
				</div>
			</template>
		</div>
		<input ref="input" type="file" class="hidden" :multiple="field.props.maxCount > 1" :accept="accepts">
	</div>
</script>
