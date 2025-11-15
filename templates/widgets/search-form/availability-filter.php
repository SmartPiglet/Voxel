<script type="text/html" id="search-form-availability-filter">
	<template v-if="filter.props.inputMode === 'date-range'">
		<div v-for="preset in filter.props.presets" class="ts-form-group" :class="$attrs.class">
			<label v-if="$root.config.showLabels">{{ filter.label }}</label>
			<div class="ts-filter" :class="{'ts-filled': filter.value === preset.key}" @click.prevent="selectPreset( preset.key )">
				<!-- <span v-html="filter.icon"></span> -->
				<div class="ts-filter-text">
					<span>{{ preset.label }}</span>
				</div>
			</div>
		</div>
	</template>

	<form-group
		:popup-key="filter.id"
		ref="formGroup"
		@save="onSave"
		@blur="saveValue"
		@clear="onClear"
		:wrapper-class="[filter.props.inputMode === 'date-range' ? 'ts-availability-wrapper xl-height xl-width' : 'md-width xl-height', repeaterId].join(' ')"
		:class="$attrs.class"
	>
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div
				v-if="filter.props.inputMode === 'single-date'"
				class="ts-filter ts-popup-target"
				@mousedown="$root.activePopup = filter.id"
				:class="{'ts-filled': filter.value !== null}"
			>
				<span v-html="filter.icon"></span>
				<div class="ts-filter-text">{{ filter.value ? displayValue.start : filter.props.l10n.pickDate }}</div>
				<div class="ts-down-icon"></div>
			</div>
			<template v-else>
				<div
					v-if="filter.props.presets.length"
					class="ts-filter ts-popup-target"
					@mousedown="openRangePicker('start')"
					:class="{'ts-filled': isUsingCustomRange()}"
				>
					<span v-html="filter.icon"></span>
					<div class="ts-filter-text">{{ isUsingCustomRange() ? displayValue.start+' - '+displayValue.end : filter.props.l10n.pickDate }}</div>
					<div class="ts-down-icon"></div>
				</div>
				<template v-else>
					<div
						class="ts-filter ts-popup-target"
						@mousedown="openRangePicker('start')"
						:class="{'ts-filled': isUsingCustomRange()}"
					>
						<span v-html="filter.icon"></span>
						<div class="ts-filter-text">{{ isUsingCustomRange() ? displayValue.start+' - '+displayValue.end : filter.props.l10n.pickDate }}</div>
						<div class="ts-down-icon"></div>
					</div>
					
				</template>
			</template>
		</template>
		<template #popup>
			<date-picker
				v-if="filter.props.inputMode === 'single-date'"
				ref="picker"
				:filter="filter"
				:parent="this"
			></date-picker>
			<range-picker
				v-else
				ref="picker"
				:filter="filter"
				:parent="this"
			></range-picker>
		</template>
	</form-group>
</script>

<script type="text/html" id="availability-date-range-picker">
	<div class="ts-popup-head flexify">
		<div class="ts-popup-name flexify">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
			<span>
				<span

					:class="{chosen: activePicker === 'start'}"
					@click.prevent="activePicker = 'start'"
				>
					{{ startLabel }}
				</span>
				<span v-if="value.start"> &mdash; </span>
				<span

					v-if="value.start"
					:class="{chosen: activePicker === 'end'}"
					@click.prevent="activePicker = 'end'"
				>
					{{ endLabel }}
				</span>
			</span>
		</div>
	</div>
	<div class="ts-booking-date ts-booking-date-range ts-form-group" ref="calendar">
		<input type="hidden" ref="input">
	</div>
</script>

<script type="text/html" id="availability-date-picker">
	<div class="ts-booking-date ts-booking-date-single ts-form-group" ref="calendar">
		<input type="hidden" ref="input">
	</div>
</script>
