<?php

namespace Voxel\Controllers\Templates\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Base_Templates_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_pte.create_base_template', '@create_base_template' );
		$this->on( 'voxel_ajax_pte.update_base_template_id', '@update_base_template_id' );
		$this->on( 'voxel_ajax_pte.delete_base_template', '@delete_base_template' );
	}

	protected function create_base_template() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Could not create template', 'voxel-backend' ), 101 );
			}

			$template_key = $_GET['template_key'] ?? null;
			if ( $template_key === 'form' ) {
				$title = sprintf( 'Create %s', $post_type->get_singular_name() );
				$new_template_id = \Voxel\create_page(
					$title,
					sprintf( 'create-%s', $post_type->get_key() )
				);

				if ( is_wp_error( $new_template_id ) ) {
					throw new \Exception( __( 'Could not create template', 'voxel-backend' ), 103 );
				}

				$templates = $post_type->get_templates();
				$templates['form'] = $new_template_id;

				$post_type->repository->set_config( [
					'templates' => $templates,
				] );

				return wp_send_json( [
					'success' => true,
					'template_id' => $new_template_id,
				] );
			} else {
				throw new \Exception( __( 'Could not create template', 'voxel-backend' ), 102 );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function update_base_template_id() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Could not edit template', 'voxel-backend' ), 101 );
			}

			$template_key = $_GET['template_key'] ?? null;
			if ( ! in_array( $template_key, [ 'single', 'card', 'archive', 'form' ], true ) ) {
				throw new \Exception( __( 'Could not edit template', 'voxel-backend' ), 102 );
			}

			$template_type = $template_key === 'form' ? 'page' : 'template';

			$new_template_id = $_GET['new_template_id'] ?? null;
			if ( ! is_numeric( $new_template_id ) ) {
				throw new \Exception( __( 'Enter the ID of the new template.', 'voxel-backend' ), 103 );
			}

			$new_template_id = absint( $new_template_id );
			if ( $template_type === 'page' && ! \Voxel\page_exists( $new_template_id ) ) {
				throw new \Exception( __( 'Provided page template does not exist.', 'voxel-backend' ), 104 );
			} elseif ( $template_type === 'template' && ! \Voxel\template_exists( $new_template_id ) ) {
				throw new \Exception( __( 'Provided template does not exist.', 'voxel-backend' ), 105 );
			}

			$post_type_templates = $post_type->get_templates();
			$post_type_templates[ $template_key ] = $new_template_id;

			$post_type->repository->set_config( [
				'templates' => $post_type_templates,
			] );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function delete_base_template() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Could not delete template', 'voxel-backend' ), 101 );
			}

			$template_key = $_GET['template_key'] ?? null;
			if ( $template_key === 'form' ) {
				$templates = $post_type->get_templates();

				if ( is_numeric( $templates['form'] ) ) {
					wp_delete_post( $templates['form'] );
				}

				$templates['form'] = null;

				$post_type->repository->set_config( [
					'templates' => $templates,
				] );

				return wp_send_json( [
					'success' => true,
				] );
			} else {
				throw new \Exception( __( 'Could not delete template', 'voxel-backend' ), 102 );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
