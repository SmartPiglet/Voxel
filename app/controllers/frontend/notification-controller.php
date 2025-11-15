<?php

namespace Voxel\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Notification_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_notifications.list', '@list_notifications' );
		$this->on( 'voxel_ajax_notifications.open', '@open_notification' );
		$this->on( 'voxel_ajax_notifications.get_actions', '@get_actions' );
		$this->on( 'voxel_ajax_notifications.run_action', '@run_action' );
		$this->on( 'voxel_ajax_notifications.clear_all', '@clear_all_notifications' );
	}

	protected function list_notifications() {
		try {
			$page = absint( $_GET['pg'] ?? 1 );
			$per_page = 10;
			$last_checked = \Voxel\current_user()->get_notification_count();
			$last_checked_time = strtotime( $last_checked['since'] );

			if ( $page === 1 ) {
				\Voxel\current_user()->reset_notification_count();
			}

			$notifications = \Voxel\Notification::query( [
				'user_id' => get_current_user_id(),
				'limit' => $per_page + 1,
				'offset' => ( $page - 1 ) * $per_page,
			] );

			$has_more = count( $notifications ) > $per_page;
			if ( $has_more ) {
				array_pop( $notifications );
			}

			$list = [];
			foreach ( $notifications as $notification ) {
				if ( $notification->is_valid() ) {
					$list[] = [
						'id' => $notification->get_id(),
						'subject' => $notification->get_subject(),
						'links_to' => $notification->get_links_to(),
						'image_url' => $notification->get_image_url(),
						'seen' => $notification->is_seen(),
						'time' => $notification->get_time_for_display(),
						'is_new' => strtotime( $notification->get_created_at() ) > $last_checked_time,
						'total_pages' => $notification->get_actions_page_count(),
					];
				}
			}

			return wp_send_json( [
				'success' => true,
				'has_more' => $has_more,
				'list' => $list,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function open_notification() {
		try {
			$notification = \Voxel\Notification::find( [
				'id' => $_GET['item_id'] ?? 0,
				'user_id' => get_current_user_id(),
			] );

			if ( ! $notification ) {
				throw new \Exception( _x( 'Notification not found.', 'notifications', 'voxel' ) );
			}

			if ( ! $notification->is_seen() ) {
				$notification->update( 'seen', 1 );
			}

			return wp_send_json( [
				'success' => true,
				'redirect_to' => $notification->get_links_to(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function clear_all_notifications() {
		try {
			global $wpdb;
			$user_id = get_current_user_id();
			$wpdb->query( "DELETE FROM {$wpdb->prefix}voxel_notifications WHERE user_id = {$user_id}" );

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

	protected function get_actions() {
		try {
			$notification = \Voxel\Notification::find( [
				'id' => $_GET['item_id'] ?? 0,
				'user_id' => get_current_user_id(),
			] );

			if ( ! $notification ) {
				throw new \Exception( _x( 'Notification not found.', 'notifications', 'voxel' ) );
			}

			$page = \Voxel\clamp( absint( $_GET['pg'] ?? 1 ), 1, 1000 );

			$actions = $notification->get_actions( $page );

			if ( ! $notification->is_seen() ) {
				$notification->update( 'seen', 1 );
			}

			return wp_send_json( [
				'success' => true,
				'actions' => $actions,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function run_action() {
		try {
			$notification = \Voxel\Notification::find( [
				'id' => $_GET['item_id'] ?? 0,
				'user_id' => get_current_user_id(),
			] );

			$action_key = sanitize_text_field( $_GET['action_key'] ?? '' );
			$page = \Voxel\clamp( absint( $_GET['pg'] ?? 1 ), 1, 1000 );

			if ( ! $notification ) {
				throw new \Exception( _x( 'Notification not found.', 'notifications', 'voxel' ) );
			}

			$actions = $notification->get_actions( $page );
			$action_index = null;

			foreach ( $actions as $i => $action ) {
				if ( ( $action['key'] ?? null ) === $action_key ) {
					if ( is_callable( $action['handler'] ?? null ) ) {
						$action['handler']();
					}

					$action_index = $i;
					break;
				}

				if ( ! empty( $action['actions'] ) ) {
					foreach ( (array) $action['actions'] as $subaction ) {
						if ( ( $subaction['key'] ?? null ) === $action_key ) {
							if ( is_callable( $subaction['handler'] ?? null ) ) {
								$subaction['handler']();
							}

							$action_index = $i;
							break(2);
						}
					}
				}
			}

			if ( $action_index === null ) {
				throw new \Exception( _x( 'Action not found.', 'notifications', 'voxel' ) );
			}

			$notification = \Voxel\Notification::force_get( $notification->get_id() );

			return wp_send_json( [
				'success' => true,
				'updated_action' => $notification->get_actions( $page )[ $action_index ] ?? null,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
