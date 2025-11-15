<?php

namespace Voxel\Modules\Paid_Listings;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object, Data_Object_List};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Listing_Package {

	public readonly \Voxel\Order_Item $order_item;
	public readonly \Voxel\Order $order;

	protected function __construct( \Voxel\Order_Item $order_item ) {
		$this->order_item = $order_item;
		$this->order = $order_item->get_order();
	}

	public static function get( $order_item ): ?static {
		if ( is_int( $order_item ) ) {
			$order_item = \Voxel\Order_Item::get( $order_item );
		} elseif ( $order_item instanceof \Voxel\Order_Item ) {
			//
		} else {
			return null;
		}

		if ( ! $order_item ) {
			return null;
		}

		if ( $order_item->get_product_field_key() !== 'voxel:listing_plan' ) {
			return null;
		}

		if ( ! $order_item->get_order() ) {
			return null;
		}

		return new static( $order_item );
	}

	public function get_id(): int {
		return $this->order_item->get_id();
	}

	public function get_plan_key(): ?string {
		$plan_key = $this->order_item->get_details('voxel:listing_plan.plan');
		return is_string( $plan_key ) && ! empty( $plan_key ) ? $plan_key : null;
	}

	public function get_plan(): ?Module\Listing_Plan {
		return Module\Listing_Plan::get( $this->get_plan_key() );
	}

	public function get_limits(): array {
		$schema = static::get_limits_schema();
		$schema->set_value( $this->order_item->get_details('voxel:listing_plan.limits') );

		return $schema->export();
	}

	public function set_limits( array $limits ): void {
		$this->order_item->set_details( 'voxel:listing_plan.limits', $limits );
	}

	public function update_usage_meta(): void {
		$usage = [
			'can_post' => [],
		];

		foreach ( $this->get_limits() as $limit ) {
			if ( $limit['usage']['count'] < $limit['total'] ) {
				foreach ( $limit['post_types'] as $post_type_key ) {
					$usage['can_post'][ $post_type_key ] = true;
				}
			}
		}

		$this->order_item->update_meta('voxel:listing_plan_usage', $usage);
	}

	public function can_create_post( \Voxel\Post_Type|string $post_type ): bool {
		if ( ! in_array( $this->order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ], true ) ) {
			return false;
		}

		$post_type_key = is_string( $post_type ) ? $post_type : $post_type->get_key();
		foreach ( $this->get_limits() as $limit ) {
			if (
				in_array( $post_type_key, $limit['post_types'], true )
				&& $limit['usage']['count'] < $limit['total']
			) {
				return true;
			}
		}

		return false;
	}

	public function assign_to_post( \Voxel\Post $post, bool $consume_slot = true ) {
		if ( ! in_array( $this->order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ], true ) ) {
			throw new \Exception( _x( 'Plan not available', 'paid listings', 'voxel' ), 91 );
		}

		$limits = $this->get_limits();
		foreach ( $limits as &$limit ) {
			if (
				$limit['usage']['count'] < $limit['total']
				&& in_array( $post->post_type->get_key(), $limit['post_types'], true )
			) {
				$time = time();
				if ( $consume_slot ) {
					$limit['usage']['count']++;

					array_unshift( $limit['usage']['posts'], [
						'id' => $post->get_id(),
						'type' => $post->post_type->get_key(),
						'time' => $time,
					] );
				}

				$this->set_limits( $limits );
				$this->update_usage_meta();
				$this->save();

				update_post_meta( $post->get_id(), 'voxel:listing_plan', wp_slash( wp_json_encode( [
					'plan' => $this->get_plan_key(),
					'package' => $this->get_id(),
					'time' => $time,
				] ) ) );

				$post->set_verified( !! $limit['mark_verified'] );

				if ( $limit['priority']['enabled'] && is_int( $limit['priority']['value'] ) ) {
					update_post_meta( $post->get_id(), 'voxel:priority', $limit['priority']['value'] );
				} else {
					delete_post_meta( $post->get_id(), 'voxel:priority' );
				}

				if ( $limit['expiration']['mode'] === 'fixed_days' && is_int( $limit['expiration']['fixed_days'] ) ) {
					$expires_at = strtotime( sprintf( '+%d days', $limit['expiration']['fixed_days'] ), \Voxel\now()->getTimestamp() );
					update_post_meta( $post->get_id(), 'voxel:listing_plan_expiry', date( 'Y-m-d H:i:s', $expires_at ) );
				} else {
					delete_post_meta( $post->get_id(), 'voxel:listing_plan_expiry' );
				}

				return;
			}
		}

		throw new \Exception( _x( 'Plan limits exhausted', 'paid listings', 'voxel' ), 92 );
	}

	public function remove_from_post( \Voxel\Post $post, bool $restore_slot = true ) {
		$assigned_package = Module\get_assigned_package( $post );
		if ( $assigned_package['details']['package'] !== $this->get_id() ) {
			return null;
		}

		$limits = $this->get_limits();
		foreach ( $limits as &$limit ) {
			if ( in_array( $post->post_type->get_key(), $limit['post_types'], true ) ) {
				if ( $restore_slot ) {
					foreach ( $limit['usage']['posts'] as $used_post_index => $used_post ) {
						if (
							$used_post['id'] === $post->get_id()
							&& $assigned_package['details']['time'] === $used_post['time']
						) {
							unset( $limit['usage']['posts'][ $used_post_index ] );
							$limit['usage']['posts'] = array_values( $limit['usage']['posts'] );

							if ( $limit['usage']['count'] > 0 ) {
								$limit['usage']['count']--;
							}
							break;
						}
					}
				}

				$this->set_limits( $limits );
				$this->update_usage_meta();
				$this->save();

				delete_post_meta( $post->get_id(), 'voxel:listing_plan' );

				$post->set_verified( false );
				delete_post_meta( $post->get_id(), 'voxel:priority' );
				delete_post_meta( $post->get_id(), 'voxel:listing_plan_expiry' );

				return;
			}
		}
	}

	public function save(): void {
		$this->order_item->save();
	}

	public function get_label_for_limit( array $limit ): string {
		$post_types = array_map( function( $key ) {
			$post_type = \Voxel\Post_Type::get( $key );
			return $post_type ? $post_type->get_plural_name() : null;
		}, $limit['post_types'] );

		return join( ', ', array_filter( $post_types ) );
	}

	public function get_recent_posts_for_limit( array $limit, int $cursor = 0, int $per_page = 5 ): array {
		$all_posts = (array) ( $limit['usage']['posts'] ?? [] );
		$recent_posts = array_slice( $all_posts, $cursor, $per_page );
		$recent_ids = array_filter( array_unique( array_column( $recent_posts, 'id' ) ) );

		$response = [
			'list' => [],
			'has_more' => false,
		];

		if ( ! empty( $recent_ids ) ) {
			_prime_post_caches( $recent_ids, false, false );

			$response['has_more'] = count( $all_posts ) > ( $cursor + $per_page );
			$response['list'] = array_map( function( $p ) {
				global $wp_post_statuses;

				$post = \Voxel\Post::get( $p['id'] ?? null );
				$post_type = \Voxel\Post_Type::get( $p['type'] ?? null );
				$title = $post ? $post->get_display_name() : _x( '(deleted)', 'listing plan details', 'voxel' );
				$link = $post ? $post->get_link() : null;

				$description = [];
				if ( $post_type ) {
					$description[] = $post_type->get_singular_name();
				}

				if ( $post && ! empty( $wp_post_statuses[ $post->get_status() ]->label ?? null ) ) {
					$description[] = $wp_post_statuses[ $post->get_status() ]->label;
				}

				if ( $timestamp = ( $p['time'] ?? null ) ) {
					$description[] = \Voxel\datetime_format( $timestamp );
				}

				return [
					'id' => $p['id'] ?? null,
					'title' => $title,
					'description' => join( ' · ', $description ),
					'link' => $link,
				];
			}, $recent_posts );
		}

		return $response;
	}

	public function get_backend_edit_link(): string {
		return add_query_arg( 'package', $this->get_id(), admin_url( 'admin.php?page=voxel-paid-listings' ) );
	}

	public static function get_limits_schema(): Data_Object_List {
		return Schema::Object_List( [
			'total' => Schema::Int()->min(0)->default(0),
			'post_types' => Schema::List()
				->validator( fn( $item ) => is_string( $item ) && ! empty( $item ) )
				->unique()
				->default([]),
			'mark_verified' => Schema::Bool()->default(false),
			'priority' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'value' => Schema::Int(),
			] ),
			'expiration' => Schema::Object( [
				'mode' => Schema::Enum( [ 'fixed_days', 'auto' ] )->default('auto'),
				'fixed_days' => Schema::Int(),
			] ),
			'usage' => Schema::Object( [
				'count' => Schema::Int()->min(0)->default(0),
				'posts' => Schema::Object_List( [
					'id' => Schema::Int(), // post_id
					'type' => Schema::String(), // post_type
					'time' => Schema::Int(), // timestamp
					'action' => Schema::Enum( [ 'publish', 'relist', 'claim', 'switch' ] ),
				] )->default([]),
			] ),
		] )->default([]);
	}
}
