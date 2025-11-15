<?php

namespace Voxel\Dynamic_Data\Data_Groups\Post\Relations;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Relation_Request_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'posts/relation-request';
	}

	public $relation_ids;
	public function __construct( array $relation_ids ) {
		$this->relation_ids = $relation_ids;
	}

	protected function properties(): array {
		return [
			'posts' => Tag::Object_List('Posts')->items( function() {
				$relation_ids = $this->relation_ids;
				if ( count( $relation_ids ) > 10 ) {
					$relation_ids = array_slice( $relation_ids, 0, 10 );
				}

				_prime_post_caches( $relation_ids );
				return array_map( function( $post_id ) {
					return \Voxel\Post::get( $post_id );
				}, $relation_ids );
			} )->properties( function( $index, $post ) {
				return [
					'id' => Tag::Number('ID')->render( function() use ( $post ) {
						return $post ? $post->get_id() : null;
					} ),
					'title' => Tag::String('Title')->render( function() use ( $post ) {
						return $post ? $post->get_display_name() : _x( '(deleted)', 'deleted item', 'voxel' );
					} ),
					'permalink' => Tag::URL('Permalink')->render( function() use ( $post ) {
						return $post ? $post->get_link() : null;
					} ),
				];
			} ),
			'count' => Tag::Number('Relation count')->render( function() {
				return count( $this->relation_ids );
			} ),
			'title' => Tag::String('Post title')->render( function() {
				$post = \Voxel\Post::get( $this->relation_ids[0] ?? null );
				return $post ? $post->get_display_name() : _x( '(deleted)', 'deleted item', 'voxel' );
			} )->hidden(),
		];
	}

	public static function mock(): self {
		return new static( [] );
	}
}
