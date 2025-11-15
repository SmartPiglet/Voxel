<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Backend;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Table_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'current_screen', '@register_table_columns' );
	}

	protected function register_table_columns( $screen ) {
		if ( ! ( $screen && $screen->post_type && $screen->base === 'edit' ) ) {
			return;
		}

		$post_type = \Voxel\Post_Type::get( $screen->post_type );
		if ( ! ( $post_type && Module\has_plans_for_post_type( $post_type ) ) ) {
			return;
		}

		add_filter( sprintf( 'manage_edit-%s_columns', $post_type->get_key() ), function( $columns ) {
			$new = [];
			foreach ( $columns as $key => $label ) {
				if ( $key === 'date' ) {
					$new['vx_listing_plan'] = _x( 'Plan', 'posts table column', 'voxel-backend' );
				}

				$new[ $key ] = $label;
			}

			if ( ! isset( $new['vx_listing_plan'] ) ) {
				$new['vx_listing_plan'] = _x( 'Plan', 'posts table column', 'voxel-backend' );
			}

			return $new;
		} );

		add_filter( sprintf( 'manage_%s_posts_custom_column', $post_type->get_key() ), function( $column, $post_id ) {
			$post = \Voxel\Post::get( $post_id );
			$assigned_package = Module\get_assigned_package( $post );
			$package = $assigned_package['package'];
			$plan = $assigned_package['plan'];

			if ( $plan && $package ) { ?>
				<a href="<?= esc_url( $package->get_backend_edit_link() ) ?>">
					<?= esc_html( $plan->get_label() ) ?>
				</a>
			<?php } else { ?>
				&mdash;
			<?php }
		}, 10, 2 );

		add_action( 'admin_head', function() {
			echo '<style>.column-vx_listing_plan { width: 10%; }</style>';
		} );

		add_action( 'restrict_manage_posts', function( $post_type_key, $which ) {
			if ( $which !== 'top' ) {
				return;
			}

			$selected = sanitize_text_field( $_GET['vx_listing_plan'] ?? '' );
			?>
			<label for="vx_listing_plan" class="screen-reader-text">Filter by listing plan</label>
			<select name="vx_listing_plan" id="vx_listing_plan">
				<option value="">All plans</option>
				<?php foreach ( Module\get_plans_for_post_type( $post_type_key ) as $plan ): ?>
					<option value="<?= esc_attr( $plan->get_key() ) ?>" <?= selected( $selected, $plan->get_key(), false ) ?>>
						<?= esc_html( $plan->get_label() ) ?>
					</option>
				<?php endforeach ?>
			</select>
			<?php
		}, 10, 2 );

		add_filter( 'the_posts', function( $posts, $q ) {
			if ( ! ( is_admin() && $q->is_main_query() ) ) {
				return $posts;
			}

			$packages = [];
			foreach ( $posts as $post ) {
				$meta = get_post_meta( $post->ID, 'voxel:listing_plan', true );
				$meta = (array) json_decode( (string) $meta, true );
				$package_id = $meta['package'] ?? null;
				if ( is_numeric( $package_id ) ) {
					$packages[ $package_id ] = true;
				}
			}

			if ( ! empty( $packages ) ) {
				$order_items = \Voxel\Order_Item::query( [
					'id' => array_keys( $packages ),
					'limit' => null,
				] );
			}

			return $posts;
		}, 10, 2 );

		add_action( 'pre_get_posts', function( $query ) {
			if ( ! ( is_admin() && $query->is_main_query() ) ) {
				return;
			}

			$target_hash = spl_object_hash($query);
			$plan_key = sanitize_text_field( $_GET['vx_listing_plan'] ?? '' );
			if ( empty( $plan_key ) ) {
				return;
			}

			add_filter( 'posts_clauses', function( $clauses, $q ) use ( $target_hash, $plan_key ) {
				if ( spl_object_hash($q) !== $target_hash ) {
					return $clauses;
				}

				global $wpdb;

				$clauses['join'] .= <<<SQL
					INNER JOIN {$wpdb->postmeta} AS vxplanmeta ON (
						vxplanmeta.post_id = {$wpdb->posts}.ID
						AND vxplanmeta.meta_key = 'voxel:listing_plan'
					)
				SQL;

				$clauses['where'] .= $wpdb->prepare( <<<SQL
					AND (
						JSON_VALID( vxplanmeta.meta_value )
						AND JSON_UNQUOTE(
							JSON_EXTRACT( vxplanmeta.meta_value, "$.plan" )
						) = %s
					)
				SQL, $plan_key );

				if ( empty( $clauses['distinct'] ) ) {
					$clauses['distinct'] = 'DISTINCT';
				}

				return $clauses;
			}, 10, 2);
		} );
	}
}
