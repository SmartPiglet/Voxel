<?php

namespace Voxel\Controllers\Timeline;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Timeline_Controller extends \Voxel\Controllers\Base_Controller {

	protected function dependencies() {
		new \Voxel\Controllers\Timeline\Timeline_Actions_Controller;
		new \Voxel\Controllers\Frontend\Timeline\Timeline_Controller;
	}

	protected function hooks() {
		$this->filter( 'voxel/global-settings/register', '@register_settings' );

		if ( \Voxel\get('settings.timeline.enabled', true) ) {
			$this->on( 'admin_menu', '@add_menu_pages' );
			$this->on( 'user_register', '@handle_autofollow' );
			$this->on( 'voxel_ajax_backend.timeline.purge_cache', '@purge_cache' );
		}
	}

	protected function register_settings( $settings ) {
		$settings['timeline'] = Schema::Object( [
			'enabled' => Schema::Bool()->default(true),
			'user_timeline' => Schema::Object( [
				'visibility' => Schema::Enum( [ 'public', 'logged_in', 'followers_only', 'customers_only', 'private' ] )->default('public'),
			] ),
			'posts' => Schema::Object( [
				'maxlength' => Schema::Int()->min(0)->default(5000),
				'images' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'max_count' => Schema::Int()->min(0)->default(3),
					'max_size' => Schema::Int()->min(0)->default(2000),
					'allowed_formats' => Schema::List()->default( [
						'image/jpeg',
						// 'image/gif',
						'image/png',
						'image/webp',
					] )->validator( function( $item ) {
						return in_array( $item, [
							'image/jpeg',
							'image/gif',
							'image/png',
							'image/webp',
						], true );
					} ),
				] ),
				'editable' => Schema::Bool()->default(true),
				'rate_limit' => Schema::Object( [
					'time_between' => Schema::Int()->min(0)->default(20),
					'hourly_limit' => Schema::Int()->min(0)->default(20),
					'daily_limit' => Schema::Int()->min(0)->default(100),
				] ),
				'truncate_at' => Schema::Int()->min(0)->default(280),
				'quotes' => Schema::Object( [
					'truncate_at' => Schema::Int()->min(0)->default(160),
				] ),
				'per_page' => Schema::Int()->min(1)->default(10),
			] ),
			'replies' => Schema::Object( [
				'maxlength' => Schema::Int()->min(0)->default(2000),
				'images' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'max_count' => Schema::Int()->min(0)->default(3),
					'max_size' => Schema::Int()->min(0)->default(2000),
					'allowed_formats' => Schema::List()->default( [
						'image/jpeg',
						// 'image/gif',
						'image/png',
						'image/webp',
					] )->validator( function( $item ) {
						return in_array( $item, [
							'image/jpeg',
							'image/gif',
							'image/png',
							'image/webp',
						], true );
					} ),
				] ),
				'editable' => Schema::Bool()->default(true),
				'max_nest_level' => Schema::Int()->min(0)->default(1),
				'truncate_at' => Schema::Int()->min(0)->default(280),
				'rate_limit' => Schema::Object( [
					'time_between' => Schema::Int()->min(0)->default(5),
					'hourly_limit' => Schema::Int()->min(0)->default(100),
					'daily_limit' => Schema::Int()->min(0)->default(1000),
				] ),
				'per_page' => Schema::Int()->min(1)->default(10),
			] ),
			'moderation' => $this->register_moderation_settings(),
			'reposts' => Schema::Object( [
				'enabled' => Schema::Bool()->default(true),
			] ),
			'followers' => Schema::Object( [
				'autofollow' => Schema::Object( [
					'users' => Schema::String()->default(''),
					'posts' => Schema::String()->default(''),
				] ),
			] ),
			'author' => Schema::Object( [
				'show_username' => Schema::Bool()->default(true),
			] ),
		] );

		return $settings;
	}

	protected function register_moderation_settings() {
		$post_types = [];

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$settings = [];

			if ( $post_type->get_setting('timeline.enabled') ) {
				$settings['post_timeline'] = Schema::Object( [
					'posts' => Schema::Object( [
						'require_approval' => Schema::Bool()->default(false),
					] ),
					'comments' => Schema::Object( [
						'require_approval' => Schema::Bool()->default(false),
					] ),
				] );
			}

			if ( $post_type->get_setting('timeline.wall') !== 'disabled' ) {
				$settings['post_wall'] = Schema::Object( [
					'posts' => Schema::Object( [
						'require_approval' => Schema::Bool()->default(false),
					] ),
					'comments' => Schema::Object( [
						'require_approval' => Schema::Bool()->default(false),
					] ),
					'moderators' => Schema::Object( [
						'post_author' => Schema::Bool()->default(true),
					] ),
				] );
			}

			if ( $post_type->get_setting('timeline.reviews') !== 'disabled' ) {
				$settings['post_reviews'] = Schema::Object( [
					'posts' => Schema::Object( [
						'require_approval' => Schema::Bool()->default(false),
					] ),
					'comments' => Schema::Object( [
						'require_approval' => Schema::Bool()->default(false),
					] ),
					'moderators' => Schema::Object( [
						'post_author' => Schema::Bool()->default(true),
					] ),
				] );
			}

			if ( ! empty( $settings ) ) {
				$post_types[ $post_type->get_key() ] = Schema::Object( $settings );
			}
		}

		return Schema::Object( [
			'user_timeline' => Schema::Object( [
				'posts' => Schema::Object( [
					'require_approval' => Schema::Bool()->default(false),
				] ),
				'comments' => Schema::Object( [
					'require_approval' => Schema::Bool()->default(false),
				] ),
			] ),
			'post_types' => Schema::Object( $post_types ),
		] );
	}

	protected function add_menu_pages() {
		add_menu_page(
			__( 'Timeline', 'voxel-backend' ),
			__( 'Timeline', 'voxel-backend' ),
			'edit_others_posts',
			'voxel-timeline',
			function() {
				$table = new \Voxel\Timeline\Backend\Timeline_Status_Table;
				$table->prepare_items();
				require locate_template( 'templates/backend/timeline/status-table.php' );
			},
			sprintf( 'data:image/svg+xml;base64,%s', base64_encode( \Voxel\paint_svg(
				file_get_contents( locate_template( 'assets/images/svgs/comments-alt-1.svg' ) ),
				'#a7aaad'
			) ) ),
			'0.285'
		);

		add_submenu_page(
			'voxel-timeline',
			__( 'Replies', 'voxel-backend' ),
			__( 'Replies', 'voxel-backend' ),
			'edit_others_posts',
			'voxel-timeline-replies',
			function() {
				$table = new \Voxel\Timeline\Backend\Timeline_Reply_Table;
				$table->prepare_items();
				require locate_template( 'templates/backend/timeline/reply-table.php' );
			},
			'90.0'
		);

		add_submenu_page(
			'voxel-timeline',
			__( 'Settings', 'voxel-backend' ),
			__( 'Settings', 'voxel-backend' ),
			'manage_options',
			'voxel-timeline-settings',
			function() {
				$schema = \Voxel\Controllers\Settings\Settings_Controller::get_settings_schema();
				$schema->set_value( \Voxel\get( 'settings', [] ) );
				$config = $schema->export();

				$config['tab'] = $_GET['tab'] ?? 'timeline';

				require locate_template( 'templates/backend/timeline/settings/settings.php' );
			},
			'100.0'
		);
	}

	protected function handle_autofollow( $user_id ) {
		$user = \Voxel\User::get( $user_id );
		if ( $user === null ) {
			return null;
		}

		$autofollow_users = (string) \Voxel\get( 'settings.timeline.followers.autofollow.users' );
		$user_ids = explode( ',', $autofollow_users );
		$user_ids = array_filter( array_map( 'absint', $user_ids ) );
		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				if ( $user_to_follow = \Voxel\User::get( $user_id ) ) {
					$user->set_follow_status( 'user', $user_to_follow->get_id(), \Voxel\FOLLOW_ACCEPTED );

					if ( $profile_to_follow = $user_to_follow->get_or_create_profile() ) {
						$user->set_follow_status( 'post', $profile_to_follow->get_id(), \Voxel\FOLLOW_ACCEPTED );
					}
				}
			}
		}

		$autofollow_posts = (string) \Voxel\get( 'settings.timeline.followers.autofollow.posts' );
		$post_ids = explode( ',', $autofollow_posts );
		$post_ids = array_filter( array_map( 'absint', $post_ids ) );
		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				if ( $post_to_follow = \Voxel\Post::get( $post_id ) ) {
					$user->set_follow_status( 'post', $post_to_follow->get_id(), \Voxel\FOLLOW_ACCEPTED );
				}
			}
		}

		// \Voxel\log($user_ids, $post_ids);
	}

	protected function purge_cache() {
		try {
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('voxel:timeline_stats', 'voxel:follow_stats')" );
			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('voxel:timeline_stats', 'voxel:review_stats', 'voxel:wall_stats', 'voxel:timeline_reply_stats', 'voxel:review_reply_stats', 'voxel:wall_reply_stats', 'voxel:follow_stats')" );

			return wp_send_json( [
				'success' => true,
				'message' => 'All stats cache has been purged',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
