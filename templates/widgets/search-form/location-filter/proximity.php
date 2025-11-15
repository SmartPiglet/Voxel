<template v-if="filter.props.display_proximity_as === 'inline'">
	<div v-show="filter.value !== null && filter.value.indexOf(visibleAreaLabel) === -1" class="ts-form-group ts-inline-filter switcher-label" :class="[loading?'vx-pending':'', $attrs.class]">
		<label class="ts-keep-visible"  @click.prevent="value.method = value.method === 'area' ? 'radius' : 'area';"
			:style="value.method !== 'radius' ? 'padding-bottom: 0;' : ''">
			<div class="switch-slider">
				<div class="onoffswitch">
					<input :checked="value.method === 'radius'" type="checkbox" class="onoffswitch-checkbox" tabindex="0">
					<label class="onoffswitch-label"></label>
				</div>
			</div>
			<?= _x( 'Enable proximity search', 'location filter', 'voxel' ) ?></label>

			<div v-show="value.method === 'radius'">
				<div class="range-slider-wrapper" ref="sliderWrapper">
					<div class="range-value">
						{{ value.radius }}
						<template v-if="units === 'mi'"><?= _x( 'mi', 'location filter', 'voxel' ) ?></template>
						<template v-else><?= _x( 'km', 'location filter', 'voxel' ) ?></template>
					</div>
				</div>
			</div>
	</div>
</template>
<template v-else-if="filter.props.display_proximity_as === 'none'">
</template>
<template v-else>
	<form-group
		:popup-key="filter.id+':proximity'"
		v-if="filter.value !== null && filter.value.indexOf(visibleAreaLabel) === -1"
		ref="proximity"
		@save="onSave"
		@blur="saveValue"
		@clear="onClearProximity"
		:class="$attrs.class"
		:wrapper-class="repeaterId"
	>
		<template #trigger>
			<label v-if="$root.config.showLabels" class=""><?= _x( 'Distance', 'location filter', 'voxel' ) ?></label>
			<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = filter.id+':proximity'; onOpenProximity();" :class="{'ts-filled': !!displayDistance}">
				<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_mylocation_icon') ) ?: \Voxel\svg( 'current-location-icon.svg' ) ?></span>
				<div class="ts-filter-text">
					<template v-if="displayDistance">{{ displayDistance }}</template>
					<template v-else><?= _x( 'Distance', 'location filter', 'voxel' ) ?></template>
				</div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<div class="ts-form-group" :class="{'vx-pending': loading}">
				<label><?= _x( 'Enable proximity search', 'location filter', 'voxel' ) ?></label>
				<div class="switch-slider">
					<div class="onoffswitch">
						<input :checked="value.method === 'radius'" type="checkbox" class="onoffswitch-checkbox" tabindex="0">
						<label class="onoffswitch-label" @click.prevent="value.method = value.method === 'area' ? 'radius' : 'area'"></label>
					</div>
				</div>
				<div v-show="value.method === 'radius'">
					<div class="range-slider-wrapper" ref="sliderWrapper">
						<div class="range-value">
							{{ value.radius }}
							<template v-if="units === 'mi'"><?= _x( 'mi', 'location filter', 'voxel' ) ?></template>
							<template v-else><?= _x( 'km', 'location filter', 'voxel' ) ?></template>
						</div>
					</div>
				</div>
			</div>
		</template>
	</form-group>
</template>
