<?php

namespace Voxel\Dynamic_Data\Data_Groups\Post;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Timeline_Data {

	protected function get_timeline_data(): Base_Data_Type {
		return Tag::Object('Timeline posts')->properties( function() {
			return [
				'total' => Tag::Number('Total count')->render( function() {
					$stats = $this->post->repository->get_timeline_stats();
					return absint( $stats['total'] );
				} ),
				'latest' => Tag::Object('Latest post')->properties( function() {
					return [
						'id' => Tag::Number('ID')->render( function() {
							$stats = $this->post->repository->get_timeline_stats();
							return $stats['latest']['id'] ?? null;
						} ),
						'created_at' => Tag::Date('Date created')->render( function() {
							$stats = $this->post->repository->get_timeline_stats();
							return $stats['latest']['created_at'] ?? null;
						} ),
					];
				} ),
				'replies' => Tag::Object('Replies')->properties( function() {
					return [
						'total' => Tag::Number('Total count')->render( function() {
							$stats = $this->post->repository->get_timeline_reply_stats();
							return absint( $stats['total'] );
						} ),
						'latest' => Tag::Object('Latest reply')->properties( function() {
							return [
								'id' => Tag::Number('ID')->render( function() {
									$stats = $this->post->repository->get_timeline_reply_stats();
									return $stats['latest']['id'] ?? null;
								} ),
								'created_at' => Tag::Date('Date created')->render( function() {
									$stats = $this->post->repository->get_timeline_reply_stats();
									return $stats['latest']['created_at'] ?? null;
								} ),
								'author' => Tag::Object('Author')->properties( function() {
									return [
										'name' => Tag::String('Name')->render( function() {
											$stats = $this->post->repository->get_timeline_reply_stats();
											$user = \Voxel\User::get( $stats['latest']['user_id'] ?? null );
											$post = \Voxel\Post::get( $stats['latest']['published_as'] ?? null );
											return $user ? $user->get_display_name() : ( $post ? $post->get_title() : null );
										} ),
										'link' => Tag::URL('Link')->render( function() {
											$stats = $this->post->repository->get_timeline_reply_stats();
											$user = \Voxel\User::get( $stats['latest']['user_id'] ?? null );
											$post = \Voxel\Post::get( $stats['latest']['published_as'] ?? null );
											return $user ? $user->get_link() : ( $post ? $post->get_link() : null );
										} ),
										'avatar' => Tag::Number('Avatar')->render( function() {
											$stats = $this->post->repository->get_timeline_reply_stats();
											$user = \Voxel\User::get( $stats['latest']['user_id'] ?? null );
											$post = \Voxel\Post::get( $stats['latest']['published_as'] ?? null );
											return $user ? $user->get_avatar_id() : ( $post ? $post->get_logo_id() : null );
										} ),
									];
								} ),
							];
						} ),
					];
				} ),
			];
		} );
	}

}
