<?php

namespace Voxel\Controllers\Frontend\Statistics;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Statistics_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'template_include', '@render_statistics_template', 100 );
		$this->filter( '_voxel/editor/get_post_for_preview', '@set_post_in_preview', 10, 2 );
		$this->filter( 'rank_math/filter_metadata', '@rank_math_permalink_bugfix', 10, 2 );
	}

	protected function render_statistics_template( $template ) {
		$page_id = (int) \Voxel\get( 'templates.post_stats' );
		if ( $page_id && is_page( $page_id ) ) {
			return locate_template( 'templates/frontend/statistics.php' );
		}

		return $template;
	}

	protected function set_post_in_preview( $post, $template_id ) {
		if ( (int) $template_id === (int) \Voxel\get( 'templates.post_stats' ) ) {
			$page_settings = (array) get_post_meta( $template_id, '_elementor_page_settings', true );
			$post_id = $page_settings['voxel_preview_post'] ?? null;
			if ( is_numeric( $post_id ) && ( $_post = \Voxel\Post::get( $post_id ) ) ) {
				$post = $_post;
			} else {
				$_post = \Voxel\Post::find( [
					'post_type' => array_merge( ['__none__'], (array) \Voxel\get( 'settings.stats.enabled_post_types' ) ),
					'post_status' => 'publish',
				] );

				if ( $_post ) {
					$post = $_post;
				}
			}

			add_filter( 'voxel/js/elementor-editor-config', function( $config ) use ( $post ) {
				if ( isset( $config['editing_groups'] ) && $post && $post->post_type ) {
					$config['editing_groups']['post'] = [
						'label' => 'Post',
						'type' => 'post_type:'.$post->post_type->get_key(),
					];

					$exporter = \Voxel\Dynamic_Data\Exporter::get();
					$exporter->add_group_by_key( 'post', $post->post_type->get_key() );
				}

				return $config;
			}, 100 );
		}

		return $post;
	}

	/**
	 * Fixes an obscure bug where the Statistics page slug gets set to the
	 * previewed post's slug when editing the template through Elementor.
	 *
	 * @since 1.5.5
	 */
	protected function rank_math_permalink_bugfix( $meta, $request ) {
		$stats_page_id = \Voxel\get( 'templates.post_stats' );
		$object_id = $request->get_param( 'objectID' );
		$object_type = $request->get_param( 'objectType' );

		if ( $object_type === 'post' && is_numeric( $stats_page_id ) && is_numeric( $object_id ) && (int) $stats_page_id === (int) $object_id ) {
			unset( $meta['permalink'] );
		}

		return $meta;
	}
}
