<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="auth-date-field">
	<form-group ref="formGroup" :popup-key="'field:'+field.key" @save="saveValue(); $refs.formGroup.blur();" @blur="saveValue();" @clear="$refs.picker.reset()" wrapper-class="md-width xl-height">
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
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value.date !== null}" @mousedown="$root.activePopup = 'field:'+field.key">
		 		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
				<div class="ts-filter-text">
					{{ displayValue || field.props.placeholder }}
				</div>
			</div>
		</template>
		<template #popup>
			<date-picker ref="picker" :field="field" :parent="this"></date-picker>
		</template>
	</form-group>
</script>
