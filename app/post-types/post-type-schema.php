<?php

namespace Voxel\Post_Types;

use Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Schema {

	protected $post_type, $repository;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function get_schema(): Data_Object {
		return Schema::Object( [
			'settings' => Schema::Object( [
				'map' => Schema::Object( [
					'markers' => Schema::Object( [
						'type' => Schema::Enum( [ 'icon', 'image', 'text' ] )->default('icon'),
						'type_icon' => Schema::Object( [
							'source' => Schema::String(),
							'default' => Schema::String(),
						] ),
						'type_image' => Schema::Object( [
							'image_source' => Schema::String(),
							'default_image' => Schema::String(),
							'icon_source' => Schema::String(),
						] ),
						'type_text' => Schema::Object( [
							'text' => Schema::String(),
						] ),
					] ),
				] )->transformer( function( $value ) {
					// compatibility with pre-1.4.2 settings structure
					if ( isset( $value['marker_type'] ) ) {
						$value['markers'] = [
							'marker_type' => $value['marker_type'] ?? null,
							'type_icon' => [
								'default' => $value['marker_icon'] ?? null,
							],
							'type_image' => [
								'image_source' => $value['marker_image'] ?? null,
							],
							'type_text' => [
								'text' => $value['marker_text'] ?? null,
							],
						];
					}

					return $value;
				} ),
				'quick_search' => Schema::Object( [
					'text' => Schema::Object( [
						'type' => Schema::Enum( [ 'title', 'dynamic' ] )->default('title'),
						'dynamic' => Schema::Object( [
							'content' => Schema::String()->default_cb( function() {
								return $this->post_type->get_key() === 'profile' ? '@author(:display_name)' : '@post(:title)';
							} ),
						] ),
					] ),
					'thumbnail' => Schema::Object( [
						'source' => Schema::String()->default_cb( function() {
							return $this->post_type->get_key() === 'profile' ? 'voxel:avatar' : 'logo';
						} ),
						'default_icon' => Schema::String(),
					] ),
				] ),
				'options' => Schema::Object( [
					'gutenberg' => Schema::Enum( [ 'auto', 'enabled' ] )->default('auto'),
					'excerpt' => Schema::Enum( [ 'auto', 'enabled' ] )->default('auto'),
					'author' => Schema::Enum( [ 'auto', 'enabled' ] )->default('auto'),
					'export_to_personal_data' => Schema::Bool()->default(false),
					'delete_with_user' => Schema::Enum( [ 'auto', 'enabled', 'disabled' ] )->default('auto'),
					'default_archive_query' => Schema::Enum( [ 'enabled', 'disabled' ] )->default('disabled'),
					'hierarchical' => Schema::Enum( [ 'auto', 'enabled', 'disabled' ] )->default('auto'),
					'publicly_queryable' => Schema::Enum( [ 'auto', 'enabled', 'disabled' ] )->default('auto'),
					'supports' => Schema::Object( [
						'page_attributes' => Schema::Enum( [ 'auto', 'enabled' ] )->default('auto'),
					] ),
					'archive' => Schema::Object( [
						'has_archive' => Schema::Enum( [ 'auto', 'enabled', 'disabled' ] )->default('auto'),
						'slug' => Schema::Enum( [ 'default', 'custom' ] )->default('default'),
						'custom_slug' => Schema::String()->default( $this->post_type->get_key() ),
					] ),
				] ),
				'permalinks' => Schema::Object( [
					'custom' => Schema::Bool()->default(false),
					'slug' => Schema::String()->default( $this->post_type->get_key() ),
					'with_front' => Schema::Bool()->default(true),
				] ),
				'messages' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),
		] );
	}

	protected $config_schema_cache;
	public function config( $option, $default = null ) {
		if ( $this->config_schema_cache === null ) {
			$this->config_schema_cache = $this->get_schema();
		}

		$path = explode( '.', $option );

		$schema_item = $this->config_schema_cache;
		foreach ( $path as $item_key ) {
			if ( ! $schema_item instanceof \Voxel\Utils\Config_Schema\Data_Object ) {
				return $default;
			}

			$schema_item = $schema_item->get_prop( $item_key );
		}

		if ( $schema_item === null ) {
			return $default;
		}

		if ( $schema_item->get_meta('exported') === true ) {
			return $schema_item->get_meta('exported_value') ?? $default;
		}

		$config = $this->post_type->repository->config;
		foreach ( $path as $item_key ) {
			if ( ! isset( $config[ $item_key ] ) ) {
				$config = $default;
				break;
			}

			$config = $config[ $item_key ];
		}

		$schema_item->set_value( $config );
		$value = $schema_item->export();
		$schema_item->set_meta('exported', true);
		$schema_item->set_meta('exported_value', $value);

		return $value;
	}
}
