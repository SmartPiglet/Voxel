<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Timeline {

	protected
		$post_type,
		$moderation_settings;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function get_moderation_settings(): array {
		if ( $this->moderation_settings === null ) {
			$settings = \Voxel\get( 'settings.timeline.moderation.post_types' );

			if ( is_array( $settings[ $this->post_type->get_key() ] ?? null ) ) {
				$this->moderation_settings = $settings[ $this->post_type->get_key() ];
			} else {
				$this->moderation_settings = [];
			}
		}

		return $this->moderation_settings;
	}

	public function timeline_posts_require_approval(): bool {
		$settings = $this->get_moderation_settings();
		return !! ( $settings['post_timeline']['posts']['require_approval'] ?? false );
	}

	public function timeline_comments_require_approval(): bool {
		$settings = $this->get_moderation_settings();
		return !! ( $settings['post_timeline']['comments']['require_approval'] ?? false );
	}

	public function wall_posts_require_approval(): bool {
		$settings = $this->get_moderation_settings();
		return !! ( $settings['post_wall']['posts']['require_approval'] ?? false );
	}

	public function wall_comments_require_approval(): bool {
		$settings = $this->get_moderation_settings();
		return !! ( $settings['post_wall']['comments']['require_approval'] ?? false );
	}

	public function reviews_require_approval(): bool {
		$settings = $this->get_moderation_settings();
		return !! ( $settings['post_reviews']['posts']['require_approval'] ?? false );
	}

	public function review_comments_require_approval(): bool {
		$settings = $this->get_moderation_settings();
		return !! ( $settings['post_reviews']['comments']['require_approval'] ?? false );
	}
}
