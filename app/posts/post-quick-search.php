<?php

namespace Voxel\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Quick_Search {

	protected $post;

	public function __construct( \Voxel\Post $post ) {
		$this->post = $post;
	}

	public function get_text() {
		if ( ! $this->post->post_type ) {
			return $this->post->get_display_name();
		}

		$config = $this->post->post_type->config('settings.quick_search.text');
		if ( $config['type'] === 'dynamic' ) {
			return \Voxel\render( $config['dynamic']['content'], [
				'author' => \Voxel\Dynamic_Data\Group::User( $this->post->get_author() ),
				'post' => \Voxel\Dynamic_Data\Group::Post( $this->post ),
				'site' => \Voxel\Dynamic_Data\Group::Site(),
			] );
		} else {
			return $this->post->get_display_name();
		}
	}

	public function get_thumbnail() {
		if ( ! $this->post->post_type ) {
			return $this->post->get_avatar_markup();
		}

		$config = $this->post->post_type->config('settings.quick_search.thumbnail');

		$field = $this->post->get_field( $config['source'] );
		if ( ! ( $field && in_array( $field->get_type(), [ 'image', 'profile-avatar' ], true ) ) ) {
			return null;
		}

		$value = $field->get_value();
		if ( ! empty( $value[0] ) ) {
			$image = wp_get_attachment_image( $value[0], 'thumbnail', false, [
				'class' => 'ts-status-avatar',
			] );

			if ( ! empty( $image ) ) {
				return $image;
			}
		}

		$default = $field->get_prop('default');
		if ( ! empty( $default ) ) {
			$image = wp_get_attachment_image( $default, 'thumbnail', false, [
				'class' => 'ts-status-avatar',
			] );

			if ( ! empty( $image ) ) {
				return $image;
			}
		}

		return null;
	}

	public function get_default_icon() {
		return $this->post->post_type->config('settings.quick_search.thumbnail.default_icon');
	}
}
