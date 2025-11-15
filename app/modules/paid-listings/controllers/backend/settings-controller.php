<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Backend;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_paid_listings.save_settings', '@save_settings' );
		$this->on( 'voxel_ajax_paid_listings.create_pricing_template', '@create_pricing_template' );
		$this->on( 'voxel_ajax_paid_listings.delete_pricing_template', '@delete_pricing_template' );
		$this->on( 'voxel_ajax_paid_listings.update_pricing_template', '@update_pricing_template' );
	}

	protected function save_settings() {
		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_plans' ) ) {
				throw new \Exception( 'Could not process request.', 70 );
			}

			if ( empty( $_POST['settings'] ) ) {
				throw new \Exception( 'Could not process request.', 80 );
			}

			$settings = json_decode( stripslashes( $_POST['settings'] ?? '' ), true );

			$schema = Module\get_settings_schema();
			$schema->set_value( $settings );

			$settings = $schema->export();

			\Voxel\set( 'paid_listings.settings', Schema::optimize_for_storage( $settings ) );

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

	protected function create_pricing_template() {
		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_plans' ) ) {
				throw new \Exception( 'Could not process request.' );
			}

			if ( is_numeric( \Voxel\get('paid_listings.settings.templates.pricing') ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$template_id = \Voxel\create_page(
				_x( 'Listing plans', 'paid listings pricing page title', 'voxel-backend' ),
				'listing-plans',
			);

			if ( is_wp_error( $template_id ) ) {
				throw new \Exception( __( 'Could not create page.', 'voxel-backend' ) );
			}

			\Voxel\set( 'paid_listings.settings.templates.pricing', $template_id );

			return wp_send_json( [
				'success' => true,
				'template_id'=> $template_id,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function delete_pricing_template() {
		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_plans' ) ) {
				throw new \Exception( 'Could not process request.' );
			}

			$template_id = \Voxel\get('paid_listings.settings.templates.pricing');
			if ( is_numeric( $template_id ) ) {
				wp_delete_post( $template_id );
			}

			\Voxel\set( 'paid_listings.settings.templates.pricing', null );

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

	protected function update_pricing_template() {
		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_plans' ) ) {
				throw new \Exception( 'Could not process request.' );
			}

			$new_template_id = absint( $_REQUEST['template_id'] ?? null );
			if ( ! \Voxel\page_exists( $new_template_id ) ) {
				throw new \Exception( __( 'Provided page template does not exist.', 'voxel-backend' ) );
			}

			\Voxel\set( 'paid_listings.settings.templates.pricing', $new_template_id );

			return wp_send_json( [
				'success' => true,
				'template_id' => $new_template_id,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
