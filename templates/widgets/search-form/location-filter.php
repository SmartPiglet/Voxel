<script type="text/html" id="search-form-location-filter">
	<template v-if="filter.props.display_as === 'inline'">
		<div class="ts-form-group ts-inline-filter" :class="[loading?'vx-pending':'', $attrs.class]">
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-input-icon flexify" ref="addressWrapper">
				<span v-html="filter.icon"></span>
				<a @click.prevent="useMyLocation" href="#" class="inline-user-location ts-icon-btn" aria-label="<?= esc_attr( _x( 'Share your location', 'location filter', 'voxel' ) ) ?>">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_mylocation_icon') ) ?: \Voxel\svg( 'current-location-icon.svg' ) ?>
				</a>
			</div>
		</div>
		<?php require locate_template( 'templates/widgets/search-form/location-filter/proximity.php' ) ?>
	</template>
	<template v-else>
		<form-group :popup-key="filter.id" ref="formGroup" @save="onSave" @blur="saveValue" @clear="onClear" prevent-blur=".pac-container, .ts-autocomplete-dropdown" :class="$attrs.class" :wrapper-class="repeaterId">
			<template #trigger>
				<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
				<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = filter.id; onOpen();" :class="{'ts-filled': filter.value !== null}">
					<span v-html="filter.icon"></span>
					<div class="ts-filter-text">{{ filter.value ? displayValue : filter.props.placeholder }}</div>
					<div class="ts-down-icon"></div>
				</div>
			</template>
			<template #popup>
				<div class="uib b-bottom ts-sticky-top" :class="{'vx-pending': loading}">
					<div class="ts-input-icon flexify ts-sticky-top" ref="addressWrapper">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_location_icon') ) ?: \Voxel\svg( 'marker.svg' ) ?>
					</div>
				</div>
				<div class="ts-form-group elementor-column elementor-col-100" :class="{'vx-pending': loading}">
					<a @click.prevent="useMyLocation" href="#" class="ts-btn ts-btn-4">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_mylocation_icon') ) ?: \Voxel\svg( 'current-location-icon.svg' ) ?>
						<p><?= _x( 'Use my current location', 'location filter', 'voxel' ) ?></p>
					</a>
				</div>
			</template>
		</form-group>
		<?php require locate_template( 'templates/widgets/search-form/location-filter/proximity.php' ) ?>
	</template>
</script>