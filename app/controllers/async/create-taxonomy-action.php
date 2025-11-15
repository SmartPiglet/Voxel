<?php

namespace Voxel\Controllers\Async;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Create_Taxonomy_Action extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_create_taxonomy', '@create_taxonomy' );
	}

	protected function create_taxonomy() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		$post_type = \Voxel\Post_Type::get( $_POST['post_type'] ?? null );
		$label = sanitize_text_field( $_POST['label'] ?? '' );
		$key = sanitize_key( $_POST['key'] ?? '' );

		try {
			if ( ! ( $post_type && $label && $key ) ) {
				throw new \Exception( __( 'Taxonomy label and key are required.', 'voxel-backend' ) );
			}

			if ( taxonomy_exists( $key ) ) {
				throw new \Exception( __( 'A taxonomy with this key already exists.', 'voxel-backend' ) );
			}

			$taxonomies = \Voxel\get('taxonomies');
			$taxonomies[ $key ] = [
				'settings' => [
					'key' => $key,
					'singular' => $label,
					'plural' => $label,
					'post_type' => [ $post_type->get_key() ],
				],
			];

			\Voxel\set( 'taxonomies', $taxonomies );
			flush_rewrite_rules();

			return wp_send_json( [
				'success' => true,
				'message' => __( 'Taxonomy created.', 'voxel-backend' ),
				'taxonomy' => [
					'key' => $key,
					'label' => $label,
				],
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
