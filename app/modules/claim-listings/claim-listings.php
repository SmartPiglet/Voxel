<?php

namespace Voxel\Modules\Claim_Listings;

use \Voxel\Modules\Claim_Listings as Module;
use \Voxel\Modules\Paid_Listings as Paid_Listings;

if ( ! defined('ABSPATH') ) {
	exit;
}

new Controllers\Claim_Listings_Controller;

function is_claimable( \Voxel\Post $post ): bool {
	if ( ! \Voxel\get( 'paid_listings.settings.claims.enabled' ) ) {
		return false;
	}

	if ( $post->is_verified() ) {
		return false;
	}

	if ( $post->get_status() !== 'publish' ) {
		return false;
	}

	if ( ! (
		$post->post_type
		&& Paid_Listings\has_plans_for_post_type( $post->post_type )
	) ) {
		return false;
	}

	return true;
}

function get_proof_of_ownership_field() {
	return new \Voxel\Utils\Object_Fields\File_Field( [
		'label' => 'Proof of ownership',
		'key' => 'proof_of_ownership',
		'allowed-types' => apply_filters( 'voxel/claim_requests/proof_of_ownership/allowed_file_types', [
			'image/png',
			'image/jpeg',
			'image/webp',
			'application/pdf',
			'application/msword', // .doc
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
		] ),
		'max-size' => apply_filters( 'voxel/claim_requests/proof_of_ownership/max_file_size', 2000 ),
		'max-count' => apply_filters( 'voxel/claim_requests/proof_of_ownership/max_file_count', 1 ),
		'private_upload' => true,
	] );
}

function get_product(): \Voxel\Post {
	$product = \Voxel\Post::find( [
		'post_type' => '_vx_catalog',
		'post_status' => 'publish',
		'meta_query' => [
			[
				'key' => '_vx_catalog_category',
				'value' => 'claim_request',
			],
		],
	] );

	if ( ! $product ) {
		$product_id = wp_insert_post( [
			'post_type' => '_vx_catalog',
			'post_status' => 'publish',
			'post_title' => 'Claim request',
			'post_author' => \Voxel\get_main_admin()?->get_id(),
			'meta_input' => [
				'_vx_catalog_category' => 'claim_request',
			],
		] );

		$product = \Voxel\Post::get( $product_id );
	}

	if ( ! $product->get_author() && \Voxel\get_main_admin() ) {
		wp_update_post( [
			'ID' => $product->get_id(),
			'post_author' => \Voxel\get_main_admin()?->get_id(),
		] );

		$product = \Voxel\Post::force_get( $product->get_id() );
	}

	return $product;
}