<?php

namespace Voxel\Post_Types\Fields\Post_Relation_Field;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Groups\Post\Post_Data_Group as Post_Data_Group;
use \Voxel\Dynamic_Data\Data_Groups\Post\Simple_Post_Data_Group as Simple_Post_Data_Group;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Exports {

	public function dynamic_data() {
		if ( in_array( $this->props['relation_type'], [ 'has_one', 'belongs_to_one' ], true ) ) {
			if ( count( $this->props['post_types'] ) === 1 && ( $post_type = \Voxel\Post_Type::get( $this->props['post_types'][0] ?? null ) ) ) {
				return Tag::Object( $this->get_label() )->properties( function() use ( $post_type ) {
					if ( isset( $GLOBALS['vx_preview_card_current_ids'] ) ) {
						_prime_post_caches( \Voxel\prime_relations_cache( $GLOBALS['vx_preview_card_current_ids'], $this ) );
					}

					$value = (array) $this->get_value();
					$post = \Voxel\Post::get( $value[0] ?? null );

					\Voxel\Dynamic_Data\Exporter::get()->add_group_by_key( 'post', $post_type->get_key() );

					if ( ! ( $post !== null && $post->post_type !== null && $post->post_type->get_key() === $post_type->get_key() ) ) {
						return Post_Data_Group::mock( $post_type->get_key() );
					}

					return \Voxel\Dynamic_Data\Group::Post( $post );
				} );
			} else {
				return Tag::Object( $this->get_label() )->properties( function() {
					if ( isset( $GLOBALS['vx_preview_card_current_ids'] ) ) {
						_prime_post_caches( \Voxel\prime_relations_cache( $GLOBALS['vx_preview_card_current_ids'], $this ) );
					}

					$value = (array) $this->get_value();
					$post = \Voxel\Post::get( $value[0] ?? null );
					if ( $post === null ) {
						return Simple_Post_Data_Group::mock();
					}

					return \Voxel\Dynamic_Data\Group::Simple_Post( $post );
				} );
			}
		} else {
			if ( count( $this->props['post_types'] ) === 1 && ( $post_type = \Voxel\Post_Type::get( $this->props['post_types'][0] ?? null ) ) ) {
				return Tag::Object_List( $this->get_label() )->items( function() {
					if ( isset( $GLOBALS['vx_preview_card_current_ids'] ) ) {
						_prime_post_caches( \Voxel\prime_relations_cache( $GLOBALS['vx_preview_card_current_ids'], $this ) );
					}

					$post_ids = (array) $this->get_value();
					_prime_post_caches( $post_ids );

					return $post_ids;
				} )->properties( function( $index, $post_id ) use ( $post_type ) {
					\Voxel\Dynamic_Data\Exporter::get()->add_group_by_key( 'post', $post_type->get_key() );

					$post = \Voxel\Post::get( $post_id );
					if ( ! ( $post !== null && $post->post_type !== null && $post->post_type->get_key() === $post_type->get_key() ) ) {
						return Post_Data_Group::mock( $post_type->get_key() );
					}

					return \Voxel\Dynamic_Data\Group::Post( $post );
				} );
			} else {
				return Tag::Object_List( $this->get_label() )->items( function() {
					if ( isset( $GLOBALS['vx_preview_card_current_ids'] ) ) {
						_prime_post_caches( \Voxel\prime_relations_cache( $GLOBALS['vx_preview_card_current_ids'], $this ) );
					}

					$post_ids = (array) $this->get_value();
					_prime_post_caches( $post_ids );

					return $post_ids;
				} )->properties( function( $index, $post_id ) {
					$post = \Voxel\Post::get( $post_id );
					if ( $post === null ) {
						return Simple_Post_Data_Group::mock();
					}

					return \Voxel\Dynamic_Data\Group::Simple_Post( $post );
				} );
			}
		}
	}
}
