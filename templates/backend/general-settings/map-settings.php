<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Map provider</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'config.maps.provider',
			'label' => 'Select map provider',
			'classes' => 'x-col-12',
			'choices' => [
				'google_maps' => 'Google Maps',
				'mapbox' => 'Mapbox',
			],
		] ) ?>
	</div>
</div>
<template v-if="config.maps.provider === 'google_maps'">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>API key</h3>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Text_Model::render( [
				'v-model' => 'config.maps.google_maps.api_key',
				'label' => 'Google Maps API key',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Map options</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'config.maps.google_maps.map_type_id',
				'label' => 'Map type',
				'classes' => 'x-col-12',
				'choices' => [
					'roadmap' => 'Roadmap: Displays a normal street map',
					'satellite' => 'Satellite: Displays satellite images',
					'terrain' => 'Terrain: Displays maps with physical features such as terrain and vegetation',
					'hybrid' => 'Hybrid: Displays a transparent layer of major streets on satellite images',
				],
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.maps.google_maps.map_type_control',
				'label' => 'Show Map Type control',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.maps.google_maps.street_view_control',
				'label' => 'Show Street View control',
				'classes' => 'x-col-12',
			] ) ?>

			<div class="ts-form-group x-col-12">
				<label>Custom map skin</label>
				<textarea v-model="config.maps.google_maps.skin" placeholder="Paste the map skin JSON code here" style="height: 100px"></textarea>
				<p>
					You can create custom map styles through the
					<a href="https://console.cloud.google.com/google/maps-apis/studio/styles" target="_blank">Google Maps Cloud Console</a>.
					Leave empty to use default map skin.
				</p>
			</div>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Localization</h3>
		</div>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Language</label>
				<select v-model="config.maps.google_maps.language">
					<option value="">Default (browser detected)</option>
					<?php foreach ( \Voxel\Modules\Google_Maps\Supported_Languages::all() as $key => $label ): ?>
						<option value="<?= $key ?>"><?= $label ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="ts-form-group x-col-12">
				<label>Region</label>
				<select v-model="config.maps.google_maps.region">
					<option value="">All</option>
					<?php foreach ( \Voxel\Utils\Country_List::all() as $country ): ?>
						<option value="<?= $country['alpha-2'] ?>"><?= $country['name'] ?></option>
					<?php endforeach ?>
				</select>

			</div>
			<div class="ts-form-group x-col-12">
				<p>
					If you set the language of the map, it's important to consider setting the region too.
					This helps ensure that your application complies with local laws. If a region is set,
					address geocoding results will be biased towards that region too.
					<a href="https://developers.google.com/maps/documentation/javascript/localization" target="_blank">Read more</a>
				</p>
			</div>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Autocomplete: Search forms</h3>
		</div>

		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Search form: Autocomplete returns results for</label>
				<select v-model="config.maps.google_maps.autocomplete.feature_types">
					<option :value="null">All feature types</option>
					<option value="geocode">Geocoding results</option>
					<option value="address">Addresses</option>
					<option value="establishment">Establishments</option>
					<option value="(regions)">Regions</option>
					<option value="(cities)">Cities</option>
				</select>
			</div>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Autocomplete: Submission</h3>
		</div>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Post submission form: Autocomplete returns results for</label>
				<select v-model="config.maps.google_maps.autocomplete.feature_types_in_submission">
					<option :value="null">All feature types</option>
					<option value="geocode">Geocoding results</option>
					<option value="address">Addresses</option>
					<option value="establishment">Establishments</option>
					<option value="(regions)">Regions</option>
					<option value="(cities)">Cities</option>
				</select>

			</div>
			<div class="ts-form-group x-col-12">
				<p>
					Determine what kind of features should be searched by autocomplete.
					<a href="https://developers.google.com/maps/documentation/javascript/supported_types#table3" target="_blank">Read more</a> &middot;
					<a href="https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete" target="_blank">View demo</a>
				</p>
			</div>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Autocomplete: Countries</h3>
		</div>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Autocomplete returns results in</label>
				<select v-model="config.maps.google_maps.autocomplete.countries" multiple="multiple" style="height: 180px; padding-top: 15px; padding-bottom: 15px;" class="min-scroll">
					<?php foreach ( \Voxel\Utils\Country_List::all() as $country ): ?>
						<option value="<?= $country['alpha-2'] ?>"><?= $country['name'] ?></option>
					<?php endforeach ?>
				</select>

			</div>
			<div class="ts-form-group x-col-12">
				<p>Limit autocomplete results to one or more countries (max: 5).</p>
			</div>
		</div>
	</div>
</template>

<template v-if="config.maps.provider === 'mapbox'">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>API Key</h3>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Text_Model::render( [
				'v-model' => 'config.maps.mapbox.api_key',
				'label' => 'Mapbox API key',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Skins</h3>
		</div>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Custom map skin</label>
				<input type="text" v-model="config.maps.mapbox.skin" placeholder="Paste the style URL here">

			</div>
			<div class="ts-form-group x-col-12">
				<p>You can create custom map styles through <a href="https://studio.mapbox.com/" target="_blank">Mapbox Studio</a>. Leave empty to use default map skin.</p>
			</div>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Localization</h3>
		</div>

		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Language</label>
				<select v-model="config.maps.mapbox.language">
					<option :value="null">Default (browser detected)</option>
					<optgroup label="Global coverage">
						<?php foreach ( \Voxel\Modules\Mapbox\Supported_Languages::global_coverage() as $key => $label ): ?>
							<option value="<?= $key ?>"><?= $label ?></option>
						<?php endforeach ?>
					</optgroup>
					<optgroup label="Local coverage">
						<?php foreach ( \Voxel\Modules\Mapbox\Supported_Languages::local_coverage() as $key => $label ): ?>
							<option value="<?= $key ?>"><?= $label ?></option>
						<?php endforeach ?>
					</optgroup>
					<optgroup label="Limited coverage">
						<?php foreach ( \Voxel\Modules\Mapbox\Supported_Languages::limited_coverage() as $key => $label ): ?>
							<option value="<?= $key ?>"><?= $label ?></option>
						<?php endforeach ?>
					</optgroup>
				</select>
			</div>
		</div>

	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Autocomplete: Search form</h3>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
				'v-model' => 'config.maps.mapbox.autocomplete.feature_types',
				'label' => 'Search form: Autocomplete returns results for',
				'classes' => 'x-col-12',
				'columns' => 'two',
				'choices' => [
					'country' => 'Countries',
					'region' => 'Regions',
					'postcode' => 'Postcodes',
					'district' => 'Districts',
					'place' => 'Places',
					'locality' => 'Localities',
					'neighborhood' => 'Neighborhoods',
					'address' => 'Addresses',
					'poi' => 'Points of interest',
				],
			] ) ?>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Autocomplete: Post submission</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
				'v-model' => 'config.maps.mapbox.autocomplete.feature_types_in_submission',
				'label' => 'Post submission form: Autocomplete returns results for',
				'classes' => 'x-col-12',
				'columns' => 'two',
				'choices' => [
					'country' => 'Countries',
					'region' => 'Regions',
					'postcode' => 'Postcodes',
					'district' => 'Districts',
					'place' => 'Places',
					'locality' => 'Localities',
					'neighborhood' => 'Neighborhoods',
					'address' => 'Addresses',
					'poi' => 'Points of interest',
				],
				'footnote' => <<<HTML
					<br>Determine what kind of features should be searched by autocomplete. If left empty, all available features will be used.
					<a href="https://docs.mapbox.com/api/search/geocoding/#data-types" target="_blank">Read more</a>
				HTML,
			] ) ?>
		</div>
	</div>
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Autocomplete: Countries</h3>
		</div>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<label>Autocomplete returns results in</label>
				<select v-model="config.maps.mapbox.autocomplete.countries" multiple="multiple" style="height: 180px; padding-top: 15px; padding-bottom: 15px;" class="min-scroll">
					<?php foreach ( \Voxel\Utils\Country_List::all() as $country ): ?>
						<option value="<?= $country['alpha-2'] ?>"><?= $country['name'] ?></option>
					<?php endforeach ?>
				</select>
				<br><br>
				<p>Limit autocomplete results to one or more countries.</p>
			</div>
		</div>
	</div>

</template>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Post submission: Default map picker location</h3>
	</div>
	<div class="x-row">
		<div class="ts-form-group x-col-12">
			<label>Latitude</label>
			<input v-model="config.maps.default_location.lat" type="number" min="-90" max="90" placeholder="42.5" step="any">
		</div>
		<div class="ts-form-group x-col-12">
			<label>Longitude</label>
			<input v-model="config.maps.default_location.lng" type="number" min="-180" max="180" placeholder="21.0" step="any">
		</div>
		<div class="ts-form-group x-col-12">
			<label>Zoom level</label>
			<input v-model="config.maps.default_location.zoom" type="number" min="0" max="30" placeholder="10">
		</div>
	</div>
</div>
