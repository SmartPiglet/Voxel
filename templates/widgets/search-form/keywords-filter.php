<script type="text/html" id="search-form-keywords-filter">
	<template v-if="filter.props.display_as === 'inline'">
		<div class="ts-form-group ts-inline-filter">
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-input-icon flexify">
				<span v-html="filter.icon"></span>
				<input
					ref="input"
					v-model="value"
					type="text"
					:placeholder="filter.props.placeholder"
					class="inline-input"
					@keyup.enter="saveValue(); onEnter();"
					@blur="saveValue"
				>
			</div>
		</div>
	</template>
	<form-group v-else :popup-key="filter.id" ref="formGroup" @save="onPopupSave" @blur="saveValue" @clear="onPopupClear" :wrapper-class="repeaterId">
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = filter.id" :class="{'ts-filled': filter.value !== null}">
				<span v-html="filter.icon"></span>
				<div class="ts-filter-text">{{ filter.value ? filter.value : filter.props.placeholder }}</div>
			</div>
		</template>
		<template #popup>
			<div class="">
				<div class="ts-input-icon ts-sticky-top flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_icon_in') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input
						ref="input"
						v-model="value"
						type="text"
						:placeholder="filter.props.placeholder"
						class="autofocus border-none"
						@keyup.enter="onPopupSave"
					>
				</div>
			</div>
		</template>
	</form-group>
</script>
