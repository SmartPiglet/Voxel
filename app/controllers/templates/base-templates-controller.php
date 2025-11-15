<?php

namespace Voxel\Controllers\Templates;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Base_Templates_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.create_base_template', '@create_base_template' );
		$this->on( 'voxel_ajax_backend.update_base_template_id', '@update_base_template_id' );
		$this->on( 'voxel_ajax_backend.delete_base_template', '@delete_base_template' );
	}

	protected function create_base_template() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$templates = \Voxel\get_base_templates();
			$template_key = $_GET['template_key'] ?? null;

			if ( empty( $template_key ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 101 );
			}

			$template = null;
			foreach ( $templates as $tpl ) {
				if ( $tpl['key'] === $template_key ) {
					$template = $tpl;
					break;
				}
			}

			if ( $template === null ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			if ( ! empty( $template['id'] ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 103 );
			}

			if ( $template['type'] === 'page' ) {
				$template_id = \Voxel\create_page( $template['label'], sanitize_title( $template['label'] ) );

				if ( is_wp_error( $template_id ) ) {
					throw new \Exception( 'Could not create template.', 104 );
				}

				\Voxel\set( $template['key'], $template_id );
			} elseif ( $template['type'] == 'template' ) {
				$template_id = \Voxel\create_template( $template['label'] );

				if ( is_wp_error( $template_id ) ) {
					throw new \Exception( 'Could not create template.', 105 );
				}

				\Voxel\set( $template['key'], $template_id );
			} else {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 106 );
			}

			return wp_send_json( [
				'success' => true,
				'template_id'=> $template_id,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function update_base_template_id() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_templates' );
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			$templates = \Voxel\get_base_templates();
			$template_key = $_GET['template_key'] ?? null;

			if ( empty( $template_key ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 101 );
			}

			$template = null;
			foreach ( $templates as $tpl ) {
				if ( $tpl['key'] === $template_key ) {
					$template = $tpl;
					break;
				}
			}

			if ( $template === null ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			$new_template_id = $_GET['new_template_id'] ?? null;
			if ( ! is_numeric( $new_template_id ) ) {
				throw new \Exception( __( 'Enter the ID of the new template.', 'voxel-backend' ), 103 );
			}

			$new_template_id = absint( $new_template_id );
			if ( $template['type'] === 'page' && ! \Voxel\page_exists( $new_template_id ) ) {
				throw new \Exception( __( 'Provided page template does not exist.', 'voxel-backend' ), 104 );
			} elseif ( $template['type'] === 'template' && ! \Voxel\template_exists( $new_template_id ) ) {
				throw new \Exception( __( 'Provided template does not exist.', 'voxel-backend' ), 105 );
			}

			\Voxel\set( $template['key'], $new_template_id );

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

			$templates = \Voxel\get_base_templates();
			$template_key = $_GET['template_key'] ?? null;

			if ( empty( $template_key ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 101 );
			}

			$template = null;
			foreach ( $templates as $tpl ) {
				if ( $tpl['key'] === $template_key ) {
					$template = $tpl;
					break;
				}
			}

			if ( $template === null ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			if ( is_numeric( $template['id'] ) ) {
				wp_delete_post( $template['id'] );
			}

			\Voxel\set( $template['key'], null );

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
}
