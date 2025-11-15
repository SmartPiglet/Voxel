<?php

namespace Voxel\Controllers\Frontend\Timeline;

use \Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Feed_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_timeline/v2/comments/get_feed', '@get_feed' );
		$this->on( 'voxel_ajax_nopriv_timeline/v2/comments/get_feed', '@get_feed' );
	}

	protected function get_feed() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'GET' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$status = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! ( $status && $status->is_viewable_by_current_user() ) ) {
				throw new \Exception( _x( 'Could not load comments.', 'timeline', 'voxel' ) );
			}

			$mode = $_REQUEST['mode'] ?? null;
			$parent_id = is_numeric( $_REQUEST['parent_id'] ?? null ) ? absint( $_REQUEST['parent_id'] ?? null ) : 0;
			$reply_id = is_numeric( $_REQUEST['reply_id'] ?? null ) ? absint( $_REQUEST['reply_id'] ?? null ) : 0;
			$page = absint( $_REQUEST['page'] ?? 1 );
			$per_page = absint( \Voxel\get( 'settings.timeline.replies.per_page', 10 ) );

			$args = [
				'status_id' => $status->get_id(),
				'parent_id' => $parent_id,
				'limit' => $per_page + 1,
				'with_user_like_status' => true,
				'order_by' => 'created_at',
				'order' => 'asc',
				'moderation' => 1,
			];

			if ( $page > 1 ) {
				$args['offset'] = ( $page - 1 ) * $per_page;
			}

			if ( $mode === 'single_comment' && $reply_id !== null ) {
				$args['id'] = $reply_id;
				$args['parent_id'] = null;
				$args['limit'] = 1;
				$args['offset'] = null;
			}

			$query = \Voxel\Timeline\Reply::query( $args );
			$comments = $query['items'];
			$has_more = $query['count'] > $per_page;
			if ( $has_more && $query['count'] === count( $query['items'] ) ) {
				array_pop( $comments );
			}

			$data = array_map( function( $comment ) {
				return $comment->get_frontend_config();
			}, $comments );

			/*if ( \Voxel\is_dev_mode() ) {
				$dev = [
					'args' => $args,
					'query' => \Voxel\Timeline\Reply::_generate_search_query( $args ),
				];
			}*/

			return wp_send_json( [
				'success' => true,
				'data' => $data,
				'has_more' => $has_more,
				'dev' => $dev ?? null,
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
