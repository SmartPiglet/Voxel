<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Privacy_Controller extends Base_Controller {

	protected function hooks() {
		$this->filter( 'wp_privacy_personal_data_exporters', '@register_exporters' );
		$this->filter( 'wp_kses_allowed_html', '@allowed_html_in_export_file', 10, 2 );
	}

	protected function register_exporters( $exporters ) {
		$post_types = \Voxel\Post_Type::get_voxel_types();
		foreach ( $post_types as $post_type ) {
			if ( ! $post_type->get_setting( 'options.export_to_personal_data' ) ) {
				continue;
			}

			$exporters['vx-post-type:'.$post_type->get_key()] = [
				'exporter_friendly_name' => $post_type->get_label(),
				'callback' => function( $email_address, $page = 1 ) use ( $post_type ) {
					$user = \Voxel\User::get( get_user_by( 'email', $email_address ) );
					if ( ! $user ) {
						return [ 'data' => [], 'done' => true ];
					}

					return $this->export_posts( $post_type, $user, $page );
				},
			];
		}

		$exporters['vx:timeline'] = [
			'exporter_friendly_name' => 'Timeline posts & reviews',
			'callback' => function( $email_address, $page = 1 ) {
				$user = \Voxel\User::get( get_user_by( 'email', $email_address ) );
				if ( ! $user ) {
					return [ 'data' => [], 'done' => true ];
				}

				return $this->export_timeline( $user, $page );
			},
		];

		$exporters['vx:timeline-replies'] = [
			'exporter_friendly_name' => 'Timeline replies',
			'callback' => function( $email_address, $page = 1 ) {
				$user = \Voxel\User::get( get_user_by( 'email', $email_address ) );
				if ( ! $user ) {
					return [ 'data' => [], 'done' => true ];
				}

				return $this->export_timeline_replies( $user, $page );
			},
		];

		return $exporters;
	}

	protected function export_posts( $post_type, $user, $page ) {
		$data = [];
		$per_page = 100;

		$posts = \Voxel\Post::query( [
			'post_type' => $post_type->get_key(),
			'posts_per_page' => $per_page + 1,
			'author' => $user->get_id(),
			'offset' => ( $page - 1 ) * $per_page,
			'post_status' => [ 'publish', 'pending' ],
		] );

		$has_more = count( $posts ) > $per_page;
		if ( $has_more ) {
			array_pop( $posts );
		}

		foreach ( $posts as $post ) {
			$export_data = array_filter( array_map( function( $field ) {
				$value = $field->get_value_for_personal_data_exporter();
				if ( empty( $value ) ) {
					return null;
				}

				if ( ! ( is_string( $value ) || is_numeric( $value ) ) ) {
					$value = wp_json_encode( $value );
				}

				return [
					'name' => $field->get_label(),
					'value' => $value,
				];
			}, $post->get_fields() ) );

			array_unshift( $export_data, [
				'name' => 'Post ID',
				'value' => $post->get_id(),
			] );

			$data[] = [
				'group_id' => sprintf( 'vx:post_type:%s', $post_type->get_key() ),
				'group_label' => $post_type->get_label(),
				'item_id' => sprintf( 'vx:post_type:%s-post:%d', $post_type->get_key(), $post->get_id() ),
				'data' => $export_data,
			];
		}

		return [
			'data' => $data,
			'done' => ! $has_more,
		];
	}

	protected function export_timeline( $user, $page ) {
		$data = [];
		$per_page = 100;

		$query = \Voxel\Timeline\Status::query( [
			'user_id' => $user->get_id(),
			'limit' => $per_page + 1,
			'offset' => ( $page - 1 ) * $per_page,
		] );

		$statuses = $query['items'];
		$has_more = $query['count'] > $per_page;
		if ( $has_more && $query['count'] === count( $query['items'] ) ) {
			array_pop( $statuses );
		}

		foreach ( $statuses as $status ) {
			$data[] = [
				'group_id' => 'vx:timeline',
				'group_label' => 'Timeline',
				'item_id' => sprintf( 'vx:timeline:%d', $status->get_id() ),
				'data' => [
					[ 'name' => 'ID', 'value' => $status->get_id() ],
					[ 'name' => 'Link', 'value' => $status->get_link() ],
					[ 'name' => 'Created at', 'value' => $status->get_time_for_display() ],
					[ 'name' => 'Content', 'value' => $status->get_content_for_display() ],
				],
			];
		}

		return [
			'data' => $data,
			'done' => ! $has_more,
		];
	}

	protected function export_timeline_replies( $user, $page ) {
		$data = [];
		$per_page = 100;

		$query = \Voxel\Timeline\Reply::query( [
			'user_id' => $user->get_id(),
			'limit' => $per_page + 1,
			'offset' => ( $page - 1 ) * $per_page,
		] );

		$replies = $query['items'];
		$has_more = $query['count'] > $per_page;
		if ( $has_more && $query['count'] === count( $query['items'] ) ) {
			array_pop( $replies );
		}

		foreach ( $replies as $reply ) {
			$data[] = [
				'group_id' => 'vx:timeline-replies',
				'group_label' => 'Timeline replies',
				'item_id' => sprintf( 'vx:timeline-replies:%d', $reply->get_id() ),
				'data' => [
					[ 'name' => 'ID', 'value' => $reply->get_id() ],
					[ 'name' => 'Link', 'value' => $reply->get_link() ],
					[ 'name' => 'Created at', 'value' => $reply->get_time_for_display() ],
					[ 'name' => 'Content', 'value' => $reply->get_content_for_display() ],
				],
			];
		}

		return [
			'data' => $data,
			'done' => ! $has_more,
		];
	}

	protected function allowed_html_in_export_file( $allowed_tags, $context ) {
		if ( $context === 'personal_data_export' ) {
			$allowed_tags['br'] = [];
			$allowed_tags['hr'] = [];
			$allowed_tags['details'] = [];
			$allowed_tags['summary'] = [];
		}

		return $allowed_tags;
	}
}
