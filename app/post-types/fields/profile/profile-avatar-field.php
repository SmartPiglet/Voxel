<?php

namespace Voxel\Post_Types\Fields\Profile;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Profile_Avatar_Field extends \Voxel\Post_Types\Fields\File_Field {

	protected $props = [
		'type' => 'profile-avatar',
		'key' => 'voxel:avatar',
		'label' => 'Profile picture',
		'max-count' => 1,
		'max-size' => 2000,
		'allowed-types' => [],
		'default' => null,
	];

	public function get_models(): array {
		$models = parent::get_models();
		unset( $models['allowed-types'] );
		unset( $models['max-count'] );
		$models['default'] = [
			'type' => \Voxel\Utils\Form_Models\Media_Model::class,
			'label' => 'Default avatar',
			'width' => '1/1',
			'multiple' => false,
		];

		return $models;
	}

	public function update( $value ): void {
		$author_id = $this->post->get_author_id();
		if ( ! $author_id ) {
			return;
		}

		$file_ids = $this->_prepare_ids_from_sanitized_input( $value, [
			'post_parent' => $this->post->get_id(),
		] );

		if ( empty( $file_ids ) ) {
			delete_user_meta( $author_id, $this->get_key() );
		} else {
			update_user_meta( $author_id, $this->get_key(), absint( $file_ids[0] ) );
		}
	}

	public function get_value() {
		$author_id = $this->post->get_author_id();
		if ( ! $author_id ) {
			return [];
		}

		$meta_value = get_user_meta( $author_id, $this->get_key(), true );
		$ids = explode( ',', $meta_value );
		$ids = array_filter( array_map( 'absint', $ids ) );
		return $ids;
	}

	protected function get_allowed_types() {
		return [
			'image/jpeg',
			'image/png',
			'image/webp',
		];
	}

	public static function is_singular(): bool {
		return true;
	}
}
