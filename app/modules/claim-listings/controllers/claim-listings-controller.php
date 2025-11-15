<?php

namespace Voxel\Modules\Claim_Listings\Controllers;

use \Voxel\Modules\Claim_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Listings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return !! (
			\Voxel\get('settings.addons.paid_listings.enabled')
			&& \Voxel\get('paid_listings.settings.claims.enabled')
		);
	}

	protected function dependencies() {
		new Frontend\Claim_Checkout_Controller;
		new Frontend\Claim_Order_Controller;
	}

	protected function hooks() {
		$this->filter( 'voxel/advanced-list/actions', '@register_claim_action' );
		$this->on( 'voxel/advanced-list/action:claim_post', '@render_claim_action', 10, 2 );
		$this->filter( 'voxel/post/get_field/voxel:claim_request', '@register_claim_request_product_field', 10, 3 );
		$this->filter( 'voxel/app-events/categories', '@register_app_event_categories' );
		$this->filter( 'voxel/app-events/register', '@register_app_events' );
		$this->register_product_type();
	}

	protected function register_claim_action( $actions ) {
		$actions['claim_post'] = __( 'Claim post', 'voxel-elementor' );
		return $actions;
	}

	protected function render_claim_action( $widget, $action ) {
		require locate_template( 'app/modules/claim-listings/templates/frontend/claim-post-action.php' );
	}

	protected function register_product_type() {
		$approval = \Voxel\get( 'paid_listings.settings.claims.approval', 'manual' );
		return \Voxel\Product_Type::register_virtual( [
			'settings' => [
				'key' => 'voxel:claim_request',
				'label' => 'Claim Request',
				'product_mode' => 'regular',
				'payments' => [
					'mode' => 'offline',
					'mode_offline' => [
						'order_approval' => $approval === 'automatic' ? 'automatic' : 'manual',
					],
				],
				'supports_marketplace' => false,
			],
			'modules' => [
				'base_price' => [
					'enabled' => false,
				],
				'cart' => [
					'enabled' => false,
				],
			],
		] );
	}

	protected function register_claim_request_product_field( $field, $post, $post_type ) {
		if ( $post_type->get_key() !== '_vx_catalog' ) {
			return null;
		}

		$catalog_category = get_post_meta( $post->get_id(), '_vx_catalog_category', true );
		if ( $catalog_category !== 'claim_request' ) {
			return null;
		}

		$field = new \Voxel\Post_Types\Fields\Product_Field( [
			'label' => 'Claim request',
			'key' => 'voxel:claim_request',
			'product-types' => [
				'voxel:claim_request',
			],
		] );

		$field->set_post( $post );

		$field->_set_value( [
			'product_type' => 'voxel:claim_request',
			'enabled' => true,
		] );

		return $field;
	}

	protected function register_app_event_categories( array $categories ): array {
		$categories['claim_listings'] = [
			'key' => 'claim_listings',
			'label' => 'Claim listings',
		];

		return $categories;
	}

	protected function register_app_events( array $events ): array {
		foreach ( [
			Module\App_Events\Claim_Submitted_Event::class,
			Module\App_Events\Claim_Approved_Event::class,
			Module\App_Events\Claim_Declined_Event::class,
		] as $event_class ) {
			$event = new $event_class;
			$events[ $event->get_key() ] = $event;
		}

		return $events;
	}
}
