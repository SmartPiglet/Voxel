<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="auth-select-field">
	<form-group wrapper-class="prmr-popup" :popup-key="field.id+':'+index" ref="formGroup" @save="onSave" @blur="saveValue" @clear="onClear">
		<template #trigger>
			<label>
				{{ field.label }}
				<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
				<div class="vx-dialog" v-if="field.description">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
					<div class="vx-dialog-content min-scroll">
						<p>{{ field.description }}</p>
					</div>
				</div>
			</label>
			<div class="ts-filter">
			    <select v-model="field.value" :required="field.required">
			        <option v-if="!field.required" :value="null">{{ field.props.placeholder || field.label }}</option>
			        <option v-for="choice in field.props.choices" :value="choice.value">{{ choice.label }}</option>
			    </select>
			    <div class="ts-down-icon"></div>
			</div>
		</template>
	</form-group>
</script>
