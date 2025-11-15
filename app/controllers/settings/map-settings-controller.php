<?php

namespace Voxel\Controllers\Settings;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Map_Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/global-settings/register', '@register_settings' );
	}

	protected function register_settings( $settings ) {
		$settings['maps'] = Schema::Object( [
			'provider' => Schema::Enum( [
				'google_maps',
				'mapbox',
			] )->default('google_maps'),
			'default_location' => Schema::Object( [
				'lat' => Schema::Float()->min(-90)->max(90),
				'lng' => Schema::Float()->min(-180)->max(180),
				'zoom' => Schema::Int()->min(0)->max(30),
			] ),
			'google_maps' => Schema::Object( [
				'api_key' => Schema::String(),
				'skin' => Schema::String(),
				'language' => Schema::String()->default(''),
				'region' => Schema::String()->default(''),
				'autocomplete' => Schema::Object( [
					'feature_types' => Schema::Enum( [
						'geocode',
						'address',
						'establishment',
						'(regions)',
						'(cities)',
						null,
					] ),
					'feature_types_in_submission' => Schema::Enum( [
						'geocode',
						'address',
						'establishment',
						'(regions)',
						'(cities)',
						null,
					] ),
					'countries' => Schema::List()
						->validator('is_string')
						->default([]),
				] ),
				'map_type_id' => Schema::Enum( [
					'roadmap',
					'satellite',
					'terrain',
					'hybrid',
				] )->default('roadmap'),
				'map_type_control' => Schema::Bool()->default(false),
				'street_view_control' => Schema::Bool()->default(false),
			] ),
			'mapbox' => Schema::Object( [
				'api_key' => Schema::String(),
				'skin' => Schema::String(),
				'language' => Schema::String(),
				'autocomplete' => Schema::Object( [
					'feature_types' => Schema::List()
						->allowed_values( [
							'country',
							'region',
							'postcode',
							'district',
							'place',
							'locality',
							'neighborhood',
							'address',
							'poi',
						] )->default([]),
					'feature_types_in_submission' => Schema::List()
						->allowed_values( [
							'country',
							'region',
							'postcode',
							'district',
							'place',
							'locality',
							'neighborhood',
							'address',
							'poi',
						] )->default([]),
					'countries' => Schema::List()
						->validator('is_string')
						->default([]),
				] ),
			] ),
		] );

		return $settings;
	}
}
