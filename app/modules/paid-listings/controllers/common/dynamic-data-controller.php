<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Common;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Modules\Paid_Listings\Dynamic_Data\Visibility_Rules as Visibility_Rules;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Dynamic_Data_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/dynamic-data/visibility-rules', '@register_visibility_rules' );
		$this->filter( 'voxel/dynamic-data/groups/user/properties', '@register_user_data', 10, 2 );
		$this->filter( 'voxel/dynamic-data/groups/post/properties', '@register_post_data', 10, 2 );
	}

	protected function register_visibility_rules( $rules ): array {
		$rules['listing:plan'] = Visibility_Rules\Listing_Plan_Is::class;
		$rules['user:has_listing_plan'] = Visibility_Rules\User_Has_Listing_Plan::class;
		$rules['author:has_listing_plan'] = Visibility_Rules\Author_Has_Listing_Plan::class;

		return $rules;
	}

	protected function register_user_data( $properties, $group ) {
		$properties['paid_listings'] = Tag::Object('Listing limits')->properties( function() use ( $group ) {
			return [
				'post_types' => Tag::Object('By post type')->properties( function() use ( $group ) {
					$properties = [];

					foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
						if ( ! Module\has_plans_for_post_type( $post_type ) ) {
							continue;
						}

						$properties[ $post_type->get_key() ] = Tag::Object( $post_type->get_label() )
							->properties( function() use ( $group, $post_type ) {
								return [
									'total' => Tag::Number('Total')->render( function() use ( $group, $post_type ) {
										$summary = Module\get_usage_summary_for_user( $group->user );
										return $summary['post_types'][ $post_type->get_key() ]['total'] ?? 0;
									} ),
									'used' => Tag::Number('Used')->render( function() use ( $group, $post_type ) {
										$summary = Module\get_usage_summary_for_user( $group->user );
										return $summary['post_types'][ $post_type->get_key() ]['used'] ?? 0;
									} ),
									'remaining' => Tag::Number('Remaining')->render( function() use ( $group, $post_type ) {
										$summary = Module\get_usage_summary_for_user( $group->user );
										$total = $summary['post_types'][ $post_type->get_key() ]['total'] ?? 0;
										$used = $summary['post_types'][ $post_type->get_key() ]['used'] ?? 0;

										return $total - $used;
									} ),
								];
							} );
					}

					return $properties;
				} ),
				'plans' => Tag::Object('By plan')->properties( function() use ( $group ) {
					$properties = [];

					foreach ( Module\Listing_Plan::all() as $plan ) {
						$properties[ $plan->get_key() ] = Tag::Object( $plan->get_label() )
							->properties( function() use ( $group, $plan ) {
								return [
									'total' => Tag::Number('Total')->render( function() use ( $group, $plan ) {
										$summary = Module\get_usage_summary_for_user( $group->user );
										return $summary['plans'][ $plan->get_key() ]['total'] ?? 0;
									} ),
									'used' => Tag::Number('Used')->render( function() use ( $group, $plan ) {
										$summary = Module\get_usage_summary_for_user( $group->user );
										return $summary['plans'][ $plan->get_key() ]['used'] ?? 0;
									} ),
									'remaining' => Tag::Number('Remaining')->render( function() use ( $group, $plan ) {
										$summary = Module\get_usage_summary_for_user( $group->user );
										$total = $summary['plans'][ $plan->get_key() ]['total'] ?? 0;
										$used = $summary['plans'][ $plan->get_key() ]['used'] ?? 0;

										return $total - $used;
									} ),
								];
							} );
					}

					return $properties;
				} ),
				'all' => Tag::Object('All')->properties( function() use ( $group ) {
					return [
						'total' => Tag::Number('Total')->render( function() use ( $group ) {
							$summary = Module\get_usage_summary_for_user( $group->user );
							return $summary['all']['total'] ?? 0;
						} ),
						'used' => Tag::Number('Used')->render( function() use ( $group ) {
							$summary = Module\get_usage_summary_for_user( $group->user );
							return $summary['all']['used'] ?? 0;
						} ),
						'remaining' => Tag::Number('Remaining')->render( function() use ( $group ) {
							$summary = Module\get_usage_summary_for_user( $group->user );
							$total = $summary['all']['total'] ?? 0;
							$used = $summary['all']['used'] ?? 0;

							return $total - $used;
						} ),
					];
				} ),
			];
		} );

		return $properties;
	}

	protected function register_post_data( $properties, $group ) {
		if ( Module\has_plans_for_post_type( $group->post_type ) ) {
			$properties['listing_plan'] = Tag::Object('Listing plan')->properties( function() use ( $group ) {
				return [
					'plan_key' => Tag::String('Plan key')->render( function() use ( $group ) {
						$assigned_package = Module\get_assigned_package( $group->post );
						$plan = $assigned_package['plan'];

						return $plan?->get_key();
					} ),
					'plan_label' => Tag::String('Plan label')->render( function() use ( $group ) {
						$assigned_package = Module\get_assigned_package( $group->post );
						$plan = $assigned_package['plan'];

						return $plan?->get_label();
					} ),
					'expiry_date' => Tag::String('Expiration date')->render( function() use ( $group ) {
						$timestamp = strtotime(
							(string) get_post_meta( $group->post->get_id(), 'voxel:listing_plan_expiry', true )
						);

						return $timestamp ? date( 'Y-m-d H:i:s', $timestamp ) : '';
					} ),
					'order_id' => Tag::String('Order ID')->render( function() use ( $group ) {
						$assigned_package = Module\get_assigned_package( $group->post );
						$package = $assigned_package['package'];

						return $package?->order->get_id();
					} ),
					'order_item_id' => Tag::String('Order item ID')->render( function() use ( $group ) {
						$assigned_package = Module\get_assigned_package( $group->post );
						$package = $assigned_package['package'];

						return $package?->get_id();
					} ),
					'order_link' => Tag::String('Order link')->render( function() use ( $group ) {
						$assigned_package = Module\get_assigned_package( $group->post );
						$package = $assigned_package['package'];

						return $package?->order->get_link();
					} ),
				];
			} );
		}

		return $properties;
	}
}
