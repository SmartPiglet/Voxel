<?php

namespace Voxel\Controllers\Templates\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Custom_Templates_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_pte.create_custom_template', '@create_custom_template' );
		$this->on( 'voxel_ajax_pte.update_custom_template_details', '@update_custom_template_details' );
		$this->on( 'voxel_ajax_pte.update_custom_template_rules', '@update_custom_template_rules' );
		$this->on( 'voxel_ajax_pte.update_custom_template_order', '@update_custom_template_order' );
		$this->on( 'voxel_ajax_pte.delete_custom_template', '@delete_custom_template' );
	}

	protected function create_custom_template() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Could not create template', 'voxel-backend' ), 101 );
			}

			$templates = $post_type->templates->get_custom_templates();
			$group_key = $_GET['group'] ?? null;

			if ( ! isset( $templates[ $group_key ] ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			$label = sanitize_text_field( $_GET['label'] ?? '' );
			if ( empty( $label ) ) {
				throw new \Exception( __( 'Template label is required.', 'voxel-backend' ), 103 );
			}

			$template_id = \Voxel\create_template(
				sprintf( 'Post type: %s | Template: %s (%s)', $post_type->get_key(), $group_key, $label )
			);

			if ( is_wp_error( $template_id ) ) {
				throw new \Exception( __( 'Could not create template', 'voxel-backend' ), 104 );
			}

			$template_config = [
				'label' => $label,
				'id' => absint( $template_id ),
				'unique_key' => strtolower( \Voxel\random_string(8) ),
			];

			if ( in_array( $group_key, [ 'single_post' ], true ) ) {
				$template_config['visibility_rules'] = [];
			}

			$templates[ $group_key ][] = $template_config;

			$templates = array_map( 'array_values', $templates );
			$post_type->repository->set_config( [
				'custom_templates' => $templates,
			] );

			return wp_send_json( [
				'success' => true,
				'templates' => $templates,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function update_custom_template_details() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Could not update template', 'voxel-backend' ), 101 );
			}

			$templates = $post_type->templates->get_custom_templates();
			$unique_key = $_GET['unique_key'] ?? null;
			$group_key = $_GET['group'] ?? null;

			if ( ! isset( $templates[ $group_key ] ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			foreach ( $templates[ $group_key ] as $i => $template ) {
				if ( $template['unique_key'] === $unique_key ) {
					$new_template_id = $_GET['new_template_id'] ?? null;
					if ( ! is_numeric( $new_template_id ) ) {
						throw new \Exception( __( 'Template ID cannot be empty', 'voxel-backend' ), 102 );
					}

					$new_template_id = absint( $new_template_id );
					if ( ! \Voxel\template_exists( $new_template_id ) ) {
						throw new \Exception( __( 'Provided template does not exist', 'voxel-backend' ), 103 );
					}

					$new_template_label = sanitize_text_field( $_GET['new_template_label'] ?? '' );
					if ( empty( $new_template_label ) ) {
						throw new \Exception( __( 'Template label cannot be empty', 'voxel-backend' ), 104 );
					}

					$templates[ $group_key ][ $i ]['id'] = $new_template_id;
					$templates[ $group_key ][ $i ]['label'] = $new_template_label;

					// make sure templates are stored as indexed arrays
					$templates = array_map( 'array_values', $templates );
					$post_type->repository->set_config( [
						'custom_templates' => $templates,
					] );

					return wp_send_json( [
						'success' => true,
					] );
				}
			}

			throw new \Exception( __( 'Could not update template.', 'voxel-backend' ), 105 );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function update_custom_template_rules() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Could not update template', 'voxel-backend' ), 101 );
			}

			$templates = $post_type->templates->get_custom_templates();
			$unique_key = $_GET['unique_key'] ?? null;
			$group_key = $_GET['group'] ?? null;

			if ( ! isset( $templates[ $group_key ] ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			foreach ( $templates[ $group_key ] as $i => $template ) {
				if ( $template['unique_key'] === $unique_key ) {
					$rules = (array) json_decode( wp_unslash( $_POST['visibility_rules'] ?? '' ), true );
					$templates[ $group_key ][ $i ]['visibility_rules'] = is_array( $rules ) ? $rules : [];

					// make sure templates are stored as indexed arrays
					$templates = array_map( 'array_values', $templates );
					$post_type->repository->set_config( [
						'custom_templates' => $templates,
					] );

					return wp_send_json( [
						'success' => true,
					] );
				}
			}

			throw new \Exception( __( 'Could not update template.', 'voxel-backend' ), 105 );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function update_custom_template_order() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$post_type = \Voxel\Post_Type::get( $_REQUEST['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 101 );
			}

			$custom_templates = json_decode( stripslashes( $_REQUEST['custom_templates'] ), true );

			if ( ! is_array( $custom_templates ) || empty( $custom_templates ) ) {
				throw new \Exception( 'Invalid request.', 102 );
			}

			$templates = array_map( 'array_values', $custom_templates );
			$post_type->repository->set_config( [
				'custom_templates' => $custom_templates,
			] );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function delete_custom_template() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$post_type = \Voxel\Post_Type::get( $_REQUEST['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 101 );
			}

			$templates = $post_type->templates->get_custom_templates();
			$unique_key = $_REQUEST['unique_key'] ?? null;
			$group_key = $_REQUEST['group'] ?? null;

			if ( ! isset( $templates[ $group_key ] ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			foreach ( $templates[ $group_key ] as $i => $template ) {
				if ( $template['unique_key'] === $unique_key ) {
					if ( is_numeric( $template['id'] ) ) {
						wp_delete_post( $template['id'] );
					}

					unset( $templates[ $group_key ][ $i ] );

					// make sure templates are stored as indexed arrays
					$templates = array_map( 'array_values', $templates );
					$post_type->repository->set_config( [
						'custom_templates' => $templates,
					] );

					return wp_send_json( [
						'success' => true,
						'templates' => $templates,
					] );
				}
			}

			throw new \Exception( __( 'Could not delete template.', 'voxel-backend' ), 105 );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
