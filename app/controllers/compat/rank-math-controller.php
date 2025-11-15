<?php

namespace Voxel\Controllers\Compat;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Rank_Math_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return class_exists( '\RankMath' );
	}

	protected function hooks() {
		$this->on( 'rank_math/vars/register_extra_replacements', '@register_replacements' );
		$this->filter( 'rank_math/replacements', '@render_replacements' );
	}

	/**
	 * Display "Dynamic Tag (VX)" in the list of available replacements
	 * in the back-end.
	 *
	 * @since 1.2.8
	 */
	protected function register_replacements() {
		rank_math_register_var_replacement( 'vx', [
			'name'        => _x( 'Dynamic Tag (VX)', 'rank math', 'voxel-backend' ),
			'description' => _x( 'Output the value of a dynamic tag', 'rank math', 'voxel-backend' ),
			'variable'    => 'vx(@post(title))',
		], '__return_null' );
	}

	/**
	 * Custom rendering handler for "Dynamic Tag (VX)" replacement.
	 *
	 * @since 1.2.8
	 */
	protected function render_replacements( $replacements ) {
		foreach ( debug_backtrace(0, 10) as $frame ) {
			if ( ( $frame['class'] ?? '' ) === 'RankMath\Replace_Variables\Replacer' && ( $frame['function'] ?? '' ) === 'replace' ) {
				$content = is_string( $frame['args'][0] ?? null ) ? $frame['args'][0] : '';

				$tokenizer = new \Voxel\Dynamic_Data\VoxelScript\Tokenizer;
				$token_list = $tokenizer->tokenize( $content );

				foreach ( $token_list->get_tokens() as $token ) {
					if ( $token instanceof \Voxel\Dynamic_Data\VoxelScript\Tokens\Dynamic_Tag ) {
						$tag = $token->to_script();
						$replacements[ '%vx('.$tag.')%' ] = \Voxel\render( $tag );
					}
				}

				break;
			}
		}

		return $replacements;
	}
}
