<?php

namespace Voxel\Post_Types\Fields\Profile;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Profile_Name_Field extends \Voxel\Post_Types\Fields\Text_Field {

	public function before_props_assigned(): void {
		$this->props['label'] = 'Profile name';
		$this->props['type'] = 'profile-name';
		$this->props['key'] = 'voxel:name';
	}

	public function update( $value ): void {
		$author_id = $this->post->get_author_id();
		if ( ! $author_id ) {
			return;
		}

		wp_update_user( [
			'ID' => $author_id,
			'display_name' => $value,
		] );
	}

	public function get_value() {
		$author = $this->post->get_author();
		if ( ! $author ) {
			return null;
		}

		$wp_user = $author->get_wp_user_object();
		return $wp_user->display_name;
	}

	public static function is_singular(): bool {
		return true;
	}
}
