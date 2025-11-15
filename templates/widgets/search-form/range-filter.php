<script type="text/html" id="search-form-range-filter">
	<template v-if="filter.props.display_as === 'minmax' && filter.props.handles === 'double'">
		<div class="ts-form-group" :class="{'vx-inert':isPending, hidden: isAdaptive && !adaptive.active}">
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-minmax" :class="{'vx-inert': isAdaptive && adaptive.disabled}">
				<input
					type="number"
					v-model.lazy="value[0]"
					@change="saveInputs(this)"
					:placeholder="adaptive.min"
					:min="adaptive.min"
					:max="typeof value[1] === 'number' ? value[1] : adaptive.max"
					:step="filter.props.step_size"
					class="inline-input input-no-icon">
				<input
					type="number"
					v-model.lazy="value[1]"
					@change="saveInputs(this)"
					:placeholder="adaptive.max"
					:min="typeof value[0] === 'number' ? value[0] : adaptive.min"
					:max="adaptive.max"
					:step="filter.props.step_size"
					class="inline-input input-no-icon">
			</div>
		</div>
	</template>
	<template v-else-if="filter.props.display_as === 'inline'">
		<div class="ts-form-group ts-inline-filter" :class="{'vx-inert':isPending, hidden: isAdaptive && !adaptive.active}">
			<label v-if="$root.config.showLabels">{{ filter.label }}</label>
			<div class="range-slider-wrapper" ref="sliderWrapper" :class="{'vx-inert': isAdaptive && adaptive.disabled}">
				<div class="range-value">{{ popupDisplayValue }}</div>
			</div>
		</div>
	</template>
	<form-group v-else :popup-key="filter.id" ref="formGroup" @save="onSave" @blur="saveValue" @clear="onClear" :wrapper-class="repeaterId" :class="{'vx-inert':isPending, hidden: isAdaptive && !adaptive.active}">
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div
				class="ts-filter ts-popup-target"
				@mousedown="$root.activePopup = filter.id; onEntry();"
				:class="{'ts-filled': filter.value !== null}"
			>
				<span v-html="filter.icon"></span>
				<div class="ts-filter-text">
					{{ filter.value ? displayValue : filter.props.placeholder }}
				</div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<div class="ts-form-group">
				<label>{{ filter.label }}<small v-if="filter.description">{{ filter.description }}</small></label>
				<div class="range-slider-wrapper" ref="sliderWrapper" :class="{'vx-inert': isAdaptive && adaptive.disabled}">
					<div class="range-value">{{ popupDisplayValue }}</div>
				</div>
			</div>
		</template>
	</form-group>
</script>
