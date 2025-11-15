<?php

namespace Voxel\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Repository {

	private
		$post,
		$fields;

	public function __construct( \Voxel\Post $post ) {
		$this->post = $post;
	}

	public function get_fields() {
		if ( ! $this->post->post_type ) {
			return [];
		}

		if ( is_array( $this->fields ) ) {
			return $this->fields;
		}

		$this->fields = [];
		foreach ( $this->post->post_type->get_fields() as $field ) {
			$this->fields[ $field->get_key() ] = clone $field;
			$this->fields[ $field->get_key() ]->set_post( $this->post );
		}

		return $this->fields;
	}

	public function get_field( $field_key ) {
		if ( $field_key === 'voxel:promotion' ) {
			return $this->_get_promotion_field();
		} else {
			$field = apply_filters( 'voxel/post/get_field/'.$field_key, null, $this->post, $this->post->post_type );
			if ( $field instanceof \Voxel\Post_Types\Fields\Base_Post_Field ) {
				return $field;
			}

			$fields = $this->get_fields();
			return $fields[ $field_key ] ?? null;
		}
	}

	protected $_get_promotion_field = null;
	protected function _get_promotion_field() {
		if ( $this->_get_promotion_field === null ) {
			$field = clone ( $this->post->post_type->get_field( 'voxel:promotion' ) );
			$field->set_post( $this->post );

			$this->_get_promotion_field = $field;
		}

		return $this->_get_promotion_field;
	}

	public function get_review_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:review_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total'] ) ) {
			$stats = \Voxel\cache_post_review_stats( $this->post->get_id() );
		}

		return $stats;
	}

	public function get_timeline_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:timeline_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total'] ) ) {
			$stats = \Voxel\cache_post_timeline_stats( $this->post->get_id() );
		}

		return $stats;
	}

	public function get_wall_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:wall_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total'] ) ) {
			$stats = \Voxel\cache_post_wall_stats( $this->post->get_id() );
		}

		return $stats;
	}

	public function get_follow_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:follow_stats', true ), ARRAY_A );
		if ( ! isset( $stats['followed'] ) ) {
			$stats = \Voxel\cache_post_follow_stats( $this->post->get_id() );
		}

		return $stats;
	}

	public function get_review_reply_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:review_reply_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total'] ) ) {
			$stats = \Voxel\cache_post_review_reply_stats( $this->post->get_id() );
		}

		return $stats;
	}

	public function get_timeline_reply_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:timeline_reply_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total'] ) ) {
			$stats = \Voxel\cache_post_timeline_reply_stats( $this->post->get_id() );
		}

		return $stats;
	}

	public function get_wall_reply_stats() {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:wall_reply_stats', true ), ARRAY_A );
		if ( ! isset( $stats['total'] ) ) {
			$stats = \Voxel\cache_post_wall_reply_stats( $this->post->get_id() );
		}

		return $stats;
	}
}
