<script type="text/html" id="create-post-date-field">
	<form-group
		:popup-key="field.id+':'+index"
		ref="formGroup"
		@save="onSave"
		@blur="saveValue"
		@clear="onClear"
		wrapper-class="md-width xl-height"
	>
		<template #trigger>
			<template v-if="field.props.enable_timepicker">
				<label>
					{{ field.label }}
					<slot name="errors"></slot>
					<div class="vx-dialog" v-if="field.description">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
						<div class="vx-dialog-content min-scroll">
							<p>{{ field.description }}</p>
						</div>
					</div>
				</label>

					<div class="form-field-grid medium">
						<div class="ts-form-group vx-2-3">
							<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value.date !== null}" @mousedown="$root.activePopup = field.id+':'+index">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
								<div class="ts-filter-text">
									{{ displayValue || field.props.placeholder }}
								</div>
							</div>
						</div>
						<div class="ts-form-group vx-1-3">
							<input placeholder="Time" type="time" v-model="field.value.time" class="ts-filter" onfocus="this.showPicker()">
						</div>
					</div>

			</template>
			<template v-else>
				<label>
					{{ field.label }}
					<slot name="errors"></slot>
					<div class="vx-dialog" v-if="field.description">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
						<div class="vx-dialog-content min-scroll">
							<p>{{ field.description }}</p>
						</div>
					</div>
				</label>
				<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value.date !== null}" @mousedown="$root.activePopup = field.id+':'+index">
			 		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
					<div class="ts-filter-text">
						{{ displayValue || field.props.placeholder }}
					</div>
				</div>
			</template>
		</template>
		<template #popup>
			<date-picker ref="picker" :field="field" :parent="this"></date-picker>
		</template>
	</form-group>
</script>
