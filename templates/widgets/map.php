<?php
/**
 * Map widget template.
 *
 * @since 1.0
 */
?>

<?php if ( $source === 'current-post' ): ?>
	<div class="ts-map ts-map-autoload" data-config="<?= esc_attr( wp_json_encode( [
		'center' => [ 'lat' => $address['latitude'], 'lng' => $address['longitude'] ],
		'zoom' => $default_zoom,
		'minZoom' => $this->get_settings_for_display( 'ts_min_zoom' ),
		'maxZoom' => $this->get_settings_for_display( 'ts_max_zoom' ),
		'markers' => [ [
			'lat' => $address['latitude'],
			'lng' => $address['longitude'],
			'template' => rawurlencode( \Voxel\_post_get_marker( $post ) ),
			'uriencoded' => true,
		] ],
	] ) ) ?>"></div>
<?php else: ?>
	<?php if ( $this->get_settings_for_display('ts_drag_search') === 'yes' ): ?>
		<div class="ts-map-drag">
			<?php if ( $this->get_settings_for_display('ts_drag_search_mode') === 'automatic' ): ?>
				<a href="#" class="ts-map-btn ts-drag-toggle <?= $this->get_settings_for_display( 'ts_drag_search_default' ) === 'checked' ? 'active' : '' ?>">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkmark_icon') ) ?: \Voxel\svg( 'checkmark-circle.svg' ) ?>
					<?= _x( 'Search as I move the map', 'map', 'voxel' ) ?>
				</a>
			<?php else: ?>
				<a href="#" class="ts-search-area hidden ts-map-btn">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<?= _x( 'Search this area', 'map', 'voxel' ) ?>
				</a>
			<?php endif ?>
		</div>
	<?php endif ?>

	<div class="ts-map" data-config="<?= esc_attr( wp_json_encode( [
		'center' => [
			'lat' => $this->get_settings_for_display( 'ts_default_lat' ),
			'lng' => $this->get_settings_for_display( 'ts_default_lng' ),
		],
		'zoom' => $default_zoom,
		'minZoom' => $this->get_settings_for_display( 'ts_min_zoom' ),
		'maxZoom' => $this->get_settings_for_display( 'ts_max_zoom' ),
	] ) ) ?>"></div>
	
	<a href="#" rel="nofollow" role="button" class="vx-geolocate-me hidden" aria-label="Share your location">
		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_mylocation_icon') ) ?: \Voxel\svg( 'current-location-icon.svg' ) ?>
	</a>

	<div style="display: none;">
		<svg id="ts-symbol-marker" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
			<path d="M3 10.9696C3 6.01585 7.02944 2 12 2C16.9706 2 21 6.01585 21 10.9696C21 16.3296 15.929 20.2049 12.7799 21.8117C12.2877 22.0628 11.7123 22.0628 11.2201 21.8117C8.07101 20.2049 3 16.3296 3 10.9696ZM12 14.6136C13.933 14.6136 15.5 13.0323 15.5 11.0818C15.5 9.13121 13.933 7.54997 12 7.54997C10.067 7.54997 8.5 9.13121 8.5 11.0818C8.5 13.0323 10.067 14.6136 12 14.6136Z"/>
		</svg>
	</div>
<?php endif ?>
