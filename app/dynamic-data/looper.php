<?php

namespace Voxel\Dynamic_Data;

use \Voxel\Dynamic_Data\VoxelScript\Tokenizer as Tokenizer;
use \Voxel\Dynamic_Data\VoxelScript\Tokens\Dynamic_Tag as Dynamic_Tag;
use \Voxel\Dynamic_Data\Data_Types\Data_Object_List as Data_Object_List;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Looper {

	protected static $loops = [];

	public static function is_running( $loopable ): bool {
		return isset( static::$loops[ $loopable ] );
	}

	public static function run( $loopable, array $options ) {
		$cb = is_callable( $options['callback'] ?? null ) ? $options['callback'] : function() {};
		$limit = is_numeric( $options['limit'] ?? null ) ? absint( $options['limit'] ) : null;
		$offset = is_numeric( $options['offset'] ?? null ) ? absint( $options['offset'] ) : null;
		$groups = is_array( $options['groups'] ?? null ) ? $options['groups'] : \Voxel\_get_default_render_groups();

		$property = static::parse_loopable( (string) $loopable, $groups );
		if ( $property === null ) {
			return null;
		}

		static::$loops[ $loopable ] = [
			'active' => true,
		];

		$total_processed = 0;
		$original_index = $property->get_current_index();
		foreach ( $property->get_items() as $index => $item ) {
			if ( $offset !== null && $index < $offset ) {
				continue;
			}

			if ( $limit !== null && $total_processed >= $limit ) {
				break;
			}

			$property->set_current_index( $index );
			$cb( $index );
			$total_processed++;
		}

		$property->set_current_index( $original_index );

		unset( static::$loops[ $loopable ] );
	}

	public static function run_at_index( $loopable, array $options ) {
		$cb = is_callable( $options['callback'] ?? null ) ? $options['callback'] : function() {};
		$index = is_numeric( $options['index'] ?? null ) ? absint( $options['index'] ) : null;
		$groups = is_array( $options['groups'] ?? null ) ? $options['groups'] : \Voxel\_get_default_render_groups();

		if ( $index === null ) {
			return null;
		}

		$property = static::parse_loopable( (string) $loopable, $groups );
		if ( $property === null ) {
			return null;
		}

		$original_index = $property->get_current_index();

		$property->set_current_index( $index );
		$cb( $index );

		$property->set_current_index( $original_index );
	}

	public static function parse_loopable( string $loopable, array $groups ): ?Data_Object_List {
		$tokenizer = new Tokenizer;
		$token_list = $tokenizer->tokenize( $loopable );
		$tokens = $token_list->get_tokens();

		if ( ! isset( $tokens[0] ) ) {
			return null;
		}

		$token = $tokens[0];
		if ( ! $token instanceof Dynamic_Tag ) {
			return null;
		}

		if ( ! isset( $groups[ $token->get_group_key() ] ) ) {
			return null;
		}

		$group = $groups[ $token->get_group_key() ];
		$property = $group->get_property( $token->get_property_path() );
		if ( ! ( $property instanceof Data_Object_List ) ) {
			return null;
		}

		return $property;
	}
}
