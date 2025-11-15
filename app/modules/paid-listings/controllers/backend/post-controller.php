<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Backend;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'add_meta_boxes', '@add_plan_metabox', 83 );
		$this->on( 'voxel_ajax_paid_listings.backend.load_user_packages', '@load_user_packages' );
		$this->on( 'voxel_ajax_paid_listings.backend.posts.assign_package', '@assign_package' );
		$this->on( 'voxel_ajax_paid_listings.backend.posts.switch_package', '@switch_package' );
		$this->on( 'voxel_ajax_paid_listings.backend.posts.remove_package', '@remove_package' );
		$this->on( 'voxel_ajax_paid_listings.backend.posts.update_expiry', '@update_expiry' );
	}

	protected function add_plan_metabox() {
		$post = \Voxel\Post::get( get_post() );
		if ( ! ( $post && $post->is_managed_by_voxel() && $post->get_author() ) ) {
			return;
		}

		add_meta_box(
			'vx_listing_plan',
			_x( 'Listing Plan', 'listing plan metabox', 'voxel-backend' ),
			function() use ( $post ) {
				$config = $this->_get_metabox_config( $post );
				require locate_template('app/modules/paid-listings/templates/backend/listing-plan-metabox.php');
			},
			null,
			'side',
			'low',
		);
	}

	protected function _get_metabox_config( $post ) {
		$assigned_package = Module\get_assigned_package( $post );
		$package = $assigned_package['package'];
		$plan = $assigned_package['plan'];
		$author = $post->get_author();

		$config = [
			'post' => [
				'id' => $post->get_id(),
				'backend_edit_link' => $post->get_backend_edit_link(),
			],
			'post_type' => [
				'key' => $post->post_type->get_key(),
			],
			'author' => [
				'id' => $author->get_id(),
			],
			'plan' => [
				'key' => $assigned_package['details']['plan'],
				'label' => $plan ? $plan->get_label() : null,
				'is_deleted' => $plan === null,
			],
			'package' => [
				'exists' => false,
			],
			'_wpnonce' => wp_create_nonce('paid_listings.backend.posts.manage_plan'),
		];

		if ( $package ) {
			$total = 0;
			$used = 0;
			$is_slot_restorable = false;
			foreach ( $package->get_limits() as $limit ) {
				$total += $limit['total'];
				$used += $limit['usage']['count'];

				if ( in_array( $post->post_type->get_key(), $limit['post_types'], true ) ) {
					foreach ( $limit['usage']['posts'] as $used_post_index => $used_post ) {
						if (
							$used_post['id'] === $post->get_id()
							&& $assigned_package['details']['time'] === $used_post['time']
						) {
							$is_slot_restorable = true;
							break;
						}
					}
				}
			}

			$used = min( $total, $used );

			$config['package'] = [
				'exists' => true,
				'id' => $package ? $package->get_id() : null,
				'edit_link' => $package ? $package->get_backend_edit_link() : null,
				'total' => $total,
				'used' => $used,
				'is_slot_restorable' => $is_slot_restorable,
				'expires_at' => null,
				'_expires_at' => null,
			];

			$expiry_timestamp = strtotime( (string) get_post_meta( $post->get_id(), 'voxel:listing_plan_expiry', true ) );
			if ( $expiry_timestamp ) {
				$config['package']['expires_at'] = date( 'Y-m-d H:i:s', $expiry_timestamp );
				$config['package']['_expires_at'] = \Voxel\date_format( $expiry_timestamp );
			}
		}

		return $config;
	}

	protected function assign_package() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_listings.backend.posts.manage_plan' );

			$schema = Schema::Object( [
				'user_id' => Schema::Int(),
				'post_id' => Schema::Int(),
				'new_package_id' => Schema::Int(),
				'consume_new_plan_slot' => Schema::Bool(),
			] );

			$schema->set_value( (array) json_decode( wp_unslash( $_REQUEST['config'] ?? [] ) ) );
			$config = $schema->export();

			$user = \Voxel\User::get( $config['user_id'] );
			$post = \Voxel\Post::get( $config['post_id'] );
			if ( ! ( $user && $post ) ) {
				throw new \Exception( 'Could not assign plan', 105 );
			}

			$package = Module\Listing_Package::get( $config['new_package_id'] );
			if ( ! $package ) {
				throw new \Exception( 'You must select a plan to proceed', 106 );
			}

			if ( ! $user->is_customer_of( $package->order->get_id() ) ) {
				throw new \Exception( 'Could not assign plan', 107 );
			}

			$package->assign_to_post( $post, !! $config['consume_new_plan_slot'] );

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

	protected function switch_package() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_listings.backend.posts.manage_plan' );

			$schema = Schema::Object( [
				'user_id' => Schema::Int(),
				'post_id' => Schema::Int(),
				'old_package_id' => Schema::Int(),
				'new_package_id' => Schema::Int(),
				'consume_new_plan_slot' => Schema::Bool(),
				'restore_old_plan_slot' => Schema::Bool(),
			] );

			$schema->set_value( (array) json_decode( wp_unslash( $_REQUEST['config'] ?? [] ) ) );
			$config = $schema->export();

			$user = \Voxel\User::get( $config['user_id'] );
			$post = \Voxel\Post::get( $config['post_id'] );
			if ( ! ( $user && $post ) ) {
				throw new \Exception( 'Could not assign plan', 105 );
			}

			$new_package = Module\Listing_Package::get( $config['new_package_id'] );
			if ( ! $new_package ) {
				throw new \Exception( 'You must select a plan to proceed', 106 );
			}

			if ( ! $user->is_customer_of( $new_package->order->get_id() ) ) {
				throw new \Exception( 'Could not assign plan', 107 );
			}

			$old_package = Module\Listing_Package::get( $config['old_package_id'] );
			if ( $old_package ) {
				$old_package->remove_from_post( $post, !! $config['restore_old_plan_slot'] );
			}

			$new_package->assign_to_post( $post, !! $config['consume_new_plan_slot'] );

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

	protected function remove_package() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_listings.backend.posts.manage_plan' );

			$schema = Schema::Object( [
				'user_id' => Schema::Int(),
				'post_id' => Schema::Int(),
				'old_package_id' => Schema::Int(),
				'restore_old_plan_slot' => Schema::Bool(),
			] );

			$schema->set_value( (array) json_decode( wp_unslash( $_REQUEST['config'] ?? [] ) ) );
			$config = $schema->export();

			$user = \Voxel\User::get( $config['user_id'] );
			$post = \Voxel\Post::get( $config['post_id'] );
			if ( ! ( $user && $post ) ) {
				throw new \Exception( 'Could not remove plan', 105 );
			}

			$old_package = Module\Listing_Package::get( $config['old_package_id'] );
			$old_package->remove_from_post( $post, !! $config['restore_old_plan_slot'] );

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

	protected function update_expiry() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_listings.backend.posts.manage_plan' );

			$schema = Schema::Object( [
				'post_id' => Schema::Int(),
				'new_expiry_date' => Schema::String()->default(''),
			] );

			$schema->set_value( (array) json_decode( wp_unslash( $_REQUEST['config'] ?? [] ) ) );
			$config = $schema->export();

			$post = \Voxel\Post::get( $config['post_id'] );
			if ( ! $post ) {
				throw new \Exception( 'Could not update plan', 105 );
			}

			$timestamp = strtotime( $config['new_expiry_date'] );
			if ( $timestamp !== false ) {
				update_post_meta( $post->get_id(), 'voxel:listing_plan_expiry', date( 'Y-m-d H:i:s', $timestamp ) );

				return wp_send_json( [
					'success' => true,
					'expires_at' => date( 'Y-m-d H:i:s', $timestamp ),
					'_expires_at' => \Voxel\date_format( $timestamp ),
				] );
			} else {
				delete_post_meta( $post->get_id(), 'voxel:listing_plan_expiry' );

				return wp_send_json( [
					'success' => true,
					'expires_at' => null,
					'_expires_at' => null,
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function load_user_packages() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_listings.backend.posts.manage_plan' );

			$user = \Voxel\User::get( $_REQUEST['user_id'] ?? null );
			$post_type = \Voxel\Post_Type::get( $_REQUEST['post_type'] ?? null );

			if ( ! ( $user && $post_type ) ) {
				throw new \Exception( 'Could not retrieve plans.' );
			}

			$packages = Module\get_available_packages( $user, $post_type );

			return wp_send_json( [
				'success' => true,
				'list' => array_map( function( $package ) {
					$total = 0;
					$used = 0;
					foreach ( $package->get_limits() as $limit ) {
						$total += $limit['total'];
						$used += $limit['usage']['count'];
					}

					$used = min( $total, $used );
					$plan = $package->get_plan();

					return [
						'id' => $package->get_id(),
						'total' => $total,
						'used' => $used,
						'plan' => [
							'label' => $plan ? $plan->get_label() : null,
						],
					];
				}, $packages ),
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
