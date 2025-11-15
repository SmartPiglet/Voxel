<?php

namespace Voxel\Modules\Paid_Listings;

use \Voxel\Modules\Paid_Listings as Module;
use Voxel\Utils\Config_Schema\{Schema, Data_Object, Data_Object_List};

if ( ! defined('ABSPATH') ) {
	exit;
}

new Controllers\Paid_Listings_Controller;

function get_settings_schema(): Data_Object {
	return Schema::Object( [
		'templates' => Schema::Object( [
			'pricing' => Schema::Int(),
		] ),
		'claims' => Schema::Object( [
			'enabled' => Schema::Bool()->default(false),
			'proof_of_ownership' => Schema::Enum( [ 'required', 'optional', 'disabled' ] )->default('optional'),
			'approval' => Schema::Enum( [ 'automatic', 'manual' ] )->default('manual'),
		] ),
		'promotions' => Schema::Object( [
			'enabled' => Schema::Bool()->default(false),
			'packages' => Schema::Object_List( [
				'key' => Schema::String(),
				'post_types' => Schema::List()->default([]),
				'duration' => Schema::Object( [
					'type' => Schema::Enum( ['days'] ),
					'amount' => Schema::Int()->min(1)->default(7),
				] ),
				'priority' => Schema::Int()->min(1)->default(2),
				'price' => Schema::Object( [
					'amount' => Schema::Float()->min(0),
				] ),
				'ui' => Schema::Object( [
					'label' => Schema::String(),
					'description' => Schema::String(),
					'icon' => Schema::String(),
					'color' => Schema::String(),
				] ),
			] )->default( [] ),
			'payments' => Schema::Object( [
				'mode' => Schema::Enum( [ 'payment', 'offline' ] )->default('payment'),
			] ),
			'order_approval' => Schema::enum( [ 'automatic', 'manual' ] )->default('automatic'),
		] ),
	] );
}

function has_plans_for_post_type( \Voxel\Post_Type|string $post_type ): bool {
	$post_type_key = is_string( $post_type ) ? $post_type : $post_type->get_key();

	foreach ( Module\Listing_Plan::all() as $plan ) {
		if ( $plan->supports_post_type( $post_type_key ) ) {
			return true;
		}
	}

	return false;
}

function get_plans_for_post_type( \Voxel\Post_Type|string $post_type ): array {
	$post_type_key = is_string( $post_type ) ? $post_type : $post_type->get_key();

	$plans = [];
	foreach ( Module\Listing_Plan::all() as $plan ) {
		if ( $plan->supports_post_type( $post_type_key ) ) {
			$plans[ $plan->get_key() ] = $plan;
		}
	}

	return $plans;
}

/**
 * Get all packages a customer can use to submit a
 * post of a given post type.
 *
 * @since 1.7
 */
function get_available_packages(
	\Voxel\User $user,
	\Voxel\Post_Type $post_type
): array {
	global $wpdb;

	$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
	$sql = sprintf( <<<SQL
		SELECT items.id FROM {$wpdb->prefix}vx_order_items
			AS items
		LEFT JOIN {$wpdb->prefix}vx_orders
			AS orders ON ( items.order_id = orders.id )
		WHERE orders.customer_id = %d
			AND orders.status IN ('completed','sub_active','sub_trialing')
			AND items.field_key = 'voxel:listing_plan'
			AND JSON_VALID( items.details )
			AND JSON_UNQUOTE( JSON_EXTRACT(
				items.details,
				'$.meta."voxel:listing_plan_usage".can_post."%s"'
			) ) = 'true'
			AND orders.testmode IS {$testmode}
		ORDER BY orders.id DESC
	SQL, absint( $user->get_id() ), esc_sql( $post_type->get_key() ) );

	$ids = $wpdb->get_col($sql);
	if ( empty( $ids ) ) {
		return [];
	}

	$order_items = \Voxel\Order_Item::query( [
		'id' => $ids,
		'limit' => null,
	] );

	$packages = [];
	foreach ( $order_items as $order_item ) {
		if ( $package = Module\Listing_Package::get( $order_item ) ) {
			$packages[] = $package;
		}
	}

	return $packages;
}

/**
 * Get the currently assigned plan, package, and meta
 * for a given post.
 *
 * @since 1.7
 */
function get_assigned_package( \Voxel\Post $post ): array {
	$schema = Schema::Object( [
		'plan' => Schema::String(),
		'package' => Schema::Int()->min(0),
		'use_slot_on_publish' => Schema::Bool(),
		'time' => Schema::Int(),
	] );

	$schema->set_value( (array) json_decode(
		get_post_meta( $post->get_id(), 'voxel:listing_plan', true ),
		true
	) );

	$details = $schema->export();

	$plan = Module\Listing_Plan::get( $details['plan'] );
	$package = Module\Listing_Package::get( $details['package'] );
	$use_slot_on_publish = !! $details['use_slot_on_publish'];

	return [
		'plan' => $plan,
		'package' => $package,
		'use_slot_on_publish' => $use_slot_on_publish,
		'details' => $details,
	];
}

/**
 * Create (or reuse) a draft post after selecting
 * a pricing plan.
 *
 * @since 1.7
 */
function get_or_create_draft(
	Module\Listing_Package $package,
	\Voxel\Post_Type $post_type,
	\Voxel\User $author
): ?\Voxel\Post {
	$listing_plan_meta = [
		'plan' => $package->get_plan_key(),
		'package' => $package->get_id(),
		'use_slot_on_publish' => true,
	];

	$existing_draft = \Voxel\Post::find( [
		'author' => $author->get_id(),
		'post_type' => $post_type->get_key(),
		'post_status' => 'draft',
		'meta_query' => [
			'relation' => 'AND',
			[
				'key' => 'voxel:listing_plan',
				'value' => wp_json_encode( $listing_plan_meta ),
			],
			[
				'key' => '_is_blank_draft',
				'value' => '1',
			],
		],
	] );

	if ( $existing_draft !== null ) {
		return $existing_draft;
	}

	// prevent "empty_content" wp_error during post insert
	add_filter( 'wp_insert_post_empty_content', '__return_false', 1e5 );

	$draft_id = wp_insert_post( [
		'post_type' => $post_type->get_key(),
		'post_title' => '',
		'post_status' => 'draft',
		'post_author' => $author->get_id(),
		'meta_input' => [
			'voxel:listing_plan' => wp_slash( wp_json_encode( $listing_plan_meta ) ),
			'_is_blank_draft' => '1',
		],
	], true );

	if ( is_wp_error( $draft_id ) ) {
		return null;
	}

	return \Voxel\Post::get( $draft_id );
}

/**
 * Check if the given user has at least one completed/active
 * order of the given plan.
 *
 * @since 1.7
 */
function user_has_bought_plan( \Voxel\User $user, Module\Listing_Plan $plan ): bool {
	global $wpdb;

	$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
	$sql = $wpdb->prepare( <<<SQL
		SELECT 1 FROM {$wpdb->prefix}vx_order_items AS items
		LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( items.order_id = orders.id )
		WHERE orders.customer_id = %d
			AND orders.status IN ('completed','sub_active','sub_trialing')
			AND items.field_key = 'voxel:listing_plan'
			AND JSON_VALID( items.details )
			AND JSON_UNQUOTE( JSON_EXTRACT(
				items.details,
				'$."voxel:listing_plan".plan'
			) ) = %s
			AND orders.testmode IS {$testmode}
		LIMIT 1
	SQL, $user->get_id(), $plan->get_key() );

	return !! $wpdb->get_var( $sql );
}

/**
 * Plan usage summary.
 *
 * @since 1.7
 */
function get_usage_summary_for_user( \Voxel\User $user ): array {
	static $cache = [];
	if ( isset( $cache[ $user->get_id() ] ) ) {
		return $cache[ $user->get_id() ];
	}

	$order_items = \Voxel\Order_Item::query( [
		'customer_id' => $user->get_id(),
		'status' => ['completed', 'sub_active', 'sub_trialing'],
		'field_key' => 'voxel:listing_plan',
		'limit' => null,
	] );

	$limits = [
		'post_types' => [],
		'plans' => [],
		'all' => [
			'total' => 0,
			'used' => 0,
		],
	];

	$packages = [];
	foreach ( $order_items as $order_item ) {
		$package = Module\Listing_Package::get( $order_item );
		if ( ! $package ) {
			continue;
		}

		foreach ( $package->get_limits() as $limit ) {
			$total = $limit['total'];
			$used = min( $limit['usage']['count'], $limit['total'] );

			foreach ( $limit['post_types'] as $post_type_key ) {
				$post_type = \Voxel\Post_Type::get( $post_type_key );
				if ( ! $post_type ) {
					continue;
				}

				if ( ! isset( $limits['post_types'][ $post_type->get_key() ] ) ) {
					$limits['post_types'][ $post_type->get_key() ] = [
						'total' => 0,
						'used' => 0,
					];
				}

				$limits['post_types'][ $post_type->get_key() ]['total'] += $total;
				$limits['post_types'][ $post_type->get_key() ]['used'] += $used;
			}

			if ( $plan = $package->get_plan() ) {
				if ( ! isset( $limits['plans'][ $plan->get_key() ] ) ) {
					$limits['plans'][ $plan->get_key() ] = [
						'total' => 0,
						'used' => 0,
					];
				}

				$limits['plans'][ $plan->get_key() ]['total'] += $total;
				$limits['plans'][ $plan->get_key() ]['used'] += $used;
			}

			$limits['all']['total'] += $total;
			$limits['all']['used'] += $used;
		}
	}

	$cache[ $user->get_id() ] = $limits;
	return $limits;
}
