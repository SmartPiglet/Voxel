<script type="text/html" id="search-form-stepper-filter">
	<template v-if="filter.props.display_as === 'inline'">
		<div class="ts-form-group ts-inline-filter">
			<label>{{ filter.label }}</label>
			<div class="ts-stepper-input flexify">
				<button type="button" class="ts-stepper-left ts-icon-btn inline-btn-ts" @click.prevent="decrement(); debouncedSave();">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
				</button>
				<input
					ref="input"
					v-model="value"
					type="number"
					class="ts-input-box"
					:min="filter.props.range_start"
					:max="filter.props.range_end"
					:step="filter.props.step_size"
					placeholder="<?= esc_attr( _x( 'Any', 'stepper filter', 'voxel' ) ) ?>"
					@change="saveValue"
				>
				<button type="button" class="ts-stepper-right ts-icon-btn inline-btn-ts" @click.prevent="increment(); debouncedSave();">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
				</button>
			</div>
		</div>
	</template>
	<form-group v-else :popup-key="filter.id" ref="formGroup" @save="onSave" @blur="saveValue" @clear="onClear" :wrapper-class="repeaterId">
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
	 		<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = filter.id" :class="{'ts-filled': filter.value !== null}">
				<span v-html="filter.icon"></span>
	 			<div class="ts-filter-text">
					{{ filter.value ? filter.value : filter.props.placeholder }}
	 			</div>
	 			<div class="ts-down-icon"></div>
	 		</div>
	 	</template>
		<template #popup>
			<div class="ts-form-group">
				<label>
					{{ filter.label }}
					<small v-if="filter.description">{{ filter.description }}</small>
				</label>

				<div class="ts-stepper-input flexify">
					<button class="ts-stepper-left ts-icon-btn" @click.prevent="decrement">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
					</button>
					<input
						ref="input"
						v-model="value"
						type="number"
						class="ts-input-box"
						:min="filter.props.range_start"
						:max="filter.props.range_end"
						:step="filter.props.step_size"
						placeholder="<?= esc_attr( _x( 'Any', 'stepper filter', 'voxel' ) ) ?>"
					>
					<button class="ts-stepper-right ts-icon-btn" @click.prevent="increment">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
					</button>
				</div>
			</div>
		</template>
	</form-group>
</script>
