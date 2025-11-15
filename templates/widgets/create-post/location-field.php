<script type="text/html" id="create-post-location-field">
	<div class="ts-form-group ts-location-field form-field-grid">
		<div class="ts-form-group">
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
			<div class="ts-input-icon flexify">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_location_icon') ) ?: \Voxel\svg( 'marker.svg' ) ?>
				<input
					ref="addressInput"
					:value="field.value.address"
					:placeholder="field.props.placeholder"
					type="text"
					class="ts-filter"
				>
			</div>
		</div>
		<div class="ts-form-group">
			<a href="#" class="ts-btn ts-btn-4 form-btn ts-btn-large" @click.prevent="geolocate">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_mylocation_icon') ) ?: \Voxel\svg( 'current-location-icon.svg' ) ?>
				<p><?= _x( 'Geolocate my address', 'location field', 'voxel' ) ?></p>
			</a>
		</div>
		<div class="ts-form-group switcher-label">
			<label>
				<div class="switch-slider">
					<div class="onoffswitch">
						<input v-model="field.value.map_picker" type="checkbox" class="onoffswitch-checkbox">
						<label class="onoffswitch-label" @click.prevent="field.value.map_picker = !field.value.map_picker"></label>
					</div>
				</div>
				<?= _x( 'Pick the location manually?', 'location field', 'voxel' ) ?>
			</label>

		</div>
		<div class="ts-form-group" v-show="field.value.map_picker">
			<label><?= _x( 'Pick on the map', 'location field', 'voxel' ) ?></label>
			<div class="location-field-map" ref="mapDiv"></div>
		</div>
		<template v-if="field.value.map_picker">

				<div class="ts-form-group vx-1-2">
					<label><?= _x( 'Latitude', 'location field', 'voxel' ) ?></label>
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_location_icon') ) ?: \Voxel\svg( 'marker.svg' ) ?>
						<input
							v-model="field.value.latitude" type="number" max="90" min="-90" class="ts-filter"
							placeholder="<?= esc_attr( _x( 'Latitude', 'location field', 'voxel' ) ) ?>"
						>
					</div>
				</div>
				<div class="ts-form-group vx-1-2">
					<label><?= _x( 'Longitude', 'location field', 'voxel' ) ?></label>
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_location_icon') ) ?: \Voxel\svg( 'marker.svg' ) ?>
						<input
							v-model="field.value.longitude" type="number" max="180" min="-180" class="ts-filter"
							placeholder="<?= esc_attr( _x( 'Longitude', 'location field', 'voxel' ) ) ?>"
						>
					</div>
				</div>

		</template>
		<div ref="marker" class="hidden">
			<div class="map-marker marker-type-icon mi-static">
				<?= \Voxel\svg( 'marker.svg' ) ?>
			</div>
		</div>
	</div>
</script>
