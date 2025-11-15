<?php

namespace Voxel\Controllers\Compat;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Yoast_Seo_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return defined( 'WPSEO_VERSION' );
	}

	protected function hooks() {
		$this->filter( 'wpseo_replacements', '@render_replacements', 100, 2 );
	}

	/**
	 * Render dynamic tags added in the format `vx(<dtag>)`, e.g.
	 * `vx(@post(:title)) %%sep%% %%sitename%%`
	 *
	 * @since 1.2.8
	 */
	protected function render_replacements( $replacements, $args ) {
		foreach ( debug_backtrace(0, 8) as $frame ) {
			if ( ( $frame['class'] ?? '' ) === 'WPSEO_Replace_Vars' && ( $frame['function'] ?? '' ) === 'replace' ) {
				$content = is_string( $frame['args'][0] ?? null ) ? $frame['args'][0] : '';

				$tokenizer = new \Voxel\Dynamic_Data\VoxelScript\Tokenizer;
				$token_list = $tokenizer->tokenize( $content );

				if ( is_author() && ( $current_author = \Voxel\User::get( get_the_author_meta('ID') ) ) ) {
					\Voxel\set_current_post( $current_author->get_or_create_profile() );
				}

				foreach ( $token_list->get_tokens() as $token ) {
					if ( $token instanceof \Voxel\Dynamic_Data\VoxelScript\Tokens\Dynamic_Tag ) {
						$tag = $token->to_script();
						$replacements[ 'vx('.$tag.')' ] = \Voxel\render( $tag );
					}
				}

				break;
			}
		}

		return $replacements;
	}
}
