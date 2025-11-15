<?php

namespace Voxel\Post_Types\Fields\Singular;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Timezone_Field extends \Voxel\Post_Types\Fields\Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'label' => 'Timezone',
		'type' => 'timezone',
		'key' => 'timezone',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
		];
	}

	public function sanitize( $value ) {
		return in_array( $value, timezone_identifiers_list(), true ) ? $value : null;
	}

	public function update( $value ): void {
		if ( $this->is_empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( $value ) );
		}
	}

	public function get_value_from_post() {
		return get_post_meta( $this->post->get_id(), $this->get_key(), true );
	}

	public function get_timezone() {
		try {
			return new \DateTimeZone( $this->get_value() );
		} catch( \Exception $e ) {
			return wp_timezone();
		}
	}

	protected function frontend_props() {
		return [
			'list' => timezone_identifiers_list(),
			'default' => sprintf(
				'%s (%s)',
				wp_timezone_string(),
				_x( 'site default', 'timezones', 'voxel' )
			),
		];
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}

	public static function is_singular(): bool {
		return true;
	}

	public function dynamic_data() {
		return Tag::String( $this->get_label() )->render( function() {
			return $this->get_value();
		} );
	}
}
