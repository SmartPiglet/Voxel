<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Revisions {

	private $post_type, $repository;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
		$this->repository = $post_type->repository;
	}

	public function get_revisions() {
		return array_values( (array) json_decode( get_option( $this->revision_key() ), ARRAY_A ) );
	}

	public function get_revision( $timestamp ) {
		foreach ( $this->get_revisions() as $revision ) {
			if ( (string) ( $revision['time'] ?? null ) === (string) $timestamp ) {
				return $revision;
			}
		}
	}

	public function save_revision() {
		$revisions = $this->get_revisions();
		if ( count( $revisions ) >= $this->max_revision_count() ) {
			array_shift( $revisions );
		}

		$revisions[] = [
			'time' => time(),
			'author' => get_current_user_id(),
			'config' => $this->repository->config,
		];

		update_option( $this->revision_key(), wp_json_encode( $revisions ), false );
	}

	public function delete_revision( $timestamp ) {
		$revisions = $this->get_revisions();
		foreach ( $revisions as $index => $revision ) {
			if ( empty( $revision['time'] ) || empty( $revision['author'] ) || empty( $revision['config'] ) ) {
				unset( $revisions[ $index ] );
			}

			if ( (string) $revision['time'] === (string) $timestamp ) {
				unset( $revisions[ $index ] );
			}
		}

		update_option( $this->revision_key(), wp_json_encode( array_values( $revisions ) ), false );
	}

	public function max_revision_count() {
		return apply_filters( 'voxel/post-types/max-revision-count', 15 );
	}

	public function revision_key() {
		return sprintf( 'voxel:post-type-%s:revisions', $this->post_type->get_key() );
	}

}
