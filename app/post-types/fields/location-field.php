<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Location_Field extends Base_Post_Field {

	protected $props = [
		'type' => 'location',
		'label' => 'Location',
		'placeholder' => '',
		'default' => [
			'address' => null,
			'point' => null,
		],
	];

	protected $supported_conditions = [
		'address' => [
			'label' => 'Address',
			'supported_conditions' => [ 'text' ],
		],
		'latitude' => [
			'label' => 'Latitude',
			'supported_conditions' => [ 'number' ],
		],
		'longitude' => [
			'label' => 'Longitude',
			'supported_conditions' => [ 'number' ],
		],
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default.address' => $this->get_default_value_model( [
				'v-model' => 'field.default.address',
				'placeholder' => 'Enter default address',
				'classes' => 'x-col-6',
				'infobox' => '',
			] ),
			'default.point' => $this->get_default_value_model( [
				'v-model' => 'field.default.point',
				'placeholder' => 'Enter coordinates (lat,lng) e.g. 42.5477,21.0991',
				'label' => '&nbsp;',
				'classes' => 'x-col-6',
			] ),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		$location = [
			'address' => $value['address'] ? sanitize_text_field( $value['address'] ) : null,
			'map_picker' => !! ( $value['map_picker'] ?? false ),
			'latitude' => $value['latitude'] ? round( floatval( $value['latitude'] ), 5 ) : null,
			'longitude' => $value['longitude'] ? round( floatval( $value['longitude'] ), 5 ) : null,
		];

		if ( is_null( $location['address'] ) || is_null( $location['latitude'] ) || is_null( $location['longitude'] ) ) {
			return null;
		}

		$location['latitude'] = \Voxel\clamp( $location['latitude'], -90, 90 );
		$location['longitude'] = \Voxel\clamp( $location['longitude'], -180, 180 );
		return $location;
	}

	public function update( $value ): void {
		if ( empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $value ) ) );
		}
	}

	public function get_value_from_post() {
		$value = (array) json_decode( get_post_meta(
			$this->post->get_id(), $this->get_key(), true
		), true );

		return [
			'address' => $value['address'] ?? null,
			'map_picker' => $value['map_picker'] ?? false,
			'latitude' => $value['latitude'] ?? null,
			'longitude' => $value['longitude'] ?? null,
		];
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$value = [
				'address' => $this->render_default_value( $this->props['default']['address'] ?? null ),
			];

			$default_point = $this->render_default_value( $this->props['default']['point'] ?? null );
			if ( is_string( $default_point ) && ! empty( $default_point ) ) {
				$point = explode( ',', $default_point );
				if ( is_numeric( $point[0] ?? null ) && is_numeric( $point[1] ?? null ) ) {
					$value['latitude'] = $point[0];
					$value['longitude'] = $point[1];
				}
			}
		} else {
			$value = $this->get_value();
			if ( ! is_array( $value ) ) {
				$value = [];
			}
		}

		return [
			'address' => $value['address'] ?? null,
			'map_picker' => $value['map_picker'] ?? false,
			'latitude' => is_numeric( $value['latitude'] ?? null ) ? (float) $value['latitude'] : null,
			'longitude' => is_numeric( $value['longitude'] ?? null ) ? (float) $value['longitude'] : null,
		];
	}

	protected function frontend_props() {
		\Voxel\enqueue_maps();
		return [
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
			'default_zoom' => \Voxel\get( 'settings.maps.default_location.zoom' ) ?: 10,
		];
	}

	protected function _get_distance_from_nearby_ref() {
		$value = $this->get_value();
		if ( ! ( $value['latitude'] && $value['longitude'] ) ) {
			return null;
		}

		global $_voxel_nearby_ref;
		if ( ! is_array( $_voxel_nearby_ref ) ) {
			return null;
		}

		$latFrom = $_voxel_nearby_ref['lat'];
		$lngFrom = $_voxel_nearby_ref['lng'];

		return round( \Voxel\st_distance_sphere(
			$_voxel_nearby_ref['lat'],
			$_voxel_nearby_ref['lng'],
			$value['latitude'],
			$value['longitude']
		) );
	}

	public function dynamic_data() {
		return Tag::Object( $this->get_label() )->properties( function() {
			return [
				'address' => Tag::String('Full address')->render( function() {
					return $this->get_value()['address'] ?? null;
				} ),
				'lat' => Tag::Number('Latitude')->render( function() {
					return $this->get_value()['latitude'] ?? null;
				} ),
				'lng' => Tag::Number('Longitude')->render( function() {
					return $this->get_value()['longitude'] ?? null;
				} ),
				'short_address' => Tag::String('Short address')->render( function() {
					$address = $this->get_value()['address'] ?? null;
					if ( ! $address ) {
						return null;
					}

					$parts = explode( ',', $address );
					return trim( $parts[0] );
				} ),
				'medium_address' => Tag::String('Medium address')->render( function() {
					$address = $this->get_value()['address'] ?? null;
					if ( ! $address ) {
						return null;
					}

					$parts = explode( ',', $address );
					return join( ', ', array_slice( $parts, 0, 2 ) );
				} ),
				'long_address' => Tag::String('Long address')->render( function() {
					$address = $this->get_value()['address'] ?? null;
					if ( ! $address ) {
						return null;
					}

					$parts = explode( ',', $address );
					return count( $parts ) >= 4 ? join( ', ', array_slice( $parts, 0, -1 ) ) : join( ', ', $parts );
				} ),
				'distance' => Tag::Object('Distance', 'To be used with Nearby order in post feeds.')->properties( function() {
					return [
						'meters' => Tag::Number('Meters')->render( function() {
							return $this->_get_distance_from_nearby_ref();
						} ),
						'kilometers' => Tag::Number('Kilometers')->render( function() {
							$distance = $this->_get_distance_from_nearby_ref();
							if ( ! is_numeric( $distance ) ) {
								return null;
							}

							return round( $distance / 1000, 3 );
						} ),
						'miles' => Tag::Number('Miles')->render( function() {
							$distance = $this->_get_distance_from_nearby_ref();
							if ( ! is_numeric( $distance ) ) {
								return null;
							}

							return round( $distance / 1609.344, 3 );
						} ),
					];
				} ),
			];
		} );
	}

	public function export_to_personal_data() {
		$location = $this->get_value();
		if ( is_null( $location['address'] ?? null ) || is_null( $location['latitude'] ?? null ) || is_null( $location['longitude'] ?? null ) ) {
			return null;
		}

		return sprintf( '%s (Lat: %s, Lng: %s)', $location['address'], $location['latitude'], $location['longitude'] );
	}
}
