<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Controller extends Base_Controller {

	protected function hooks() {
		$this->on( 'add_meta_boxes', '@display_fields' );
		$this->on( 'save_post', '@save_post', 1000, 2 );
		$this->on( 'add_meta_boxes', '@add_author_metabox', 80 );
		$this->on( 'add_meta_boxes', '@add_verification_metabox', 85 );
		$this->on( 'add_meta_boxes', '@add_priority_metabox', 90 );
		$this->on( 'add_meta_boxes', '@add_expiry_metabox', 100 );
		$this->on( 'voxel/admin/save_post', '@save_verification_status' );
		$this->on( 'voxel/admin/save_post', '@save_author' );
		$this->on( 'voxel/admin/save_post', '@save_expiration_date' );
		$this->on( 'voxel/admin/save_post', '@save_priority' );
		$this->on( 'voxel/admin/save_post', '@send_notifications', 1500 );
		$this->on( 'trashed_post', '@unindex_on_trash' );

		$this->on( 'wp_insert_post', '@update_post_index', 1000 );
		$this->on( 'after_delete_post', '@update_post_index', 1000 );

		$this->on( 'voxel/post-type-archive', '@print_archive_template' );

		$this->on( 'voxel/post/wall-stats-updated', '@wall_stats_updated' );
		$this->on( 'voxel/post/review-stats-updated', '@review_stats_updated' );
		$this->on( 'voxel/post/timeline-stats-updated', '@timeline_stats_updated' );

		$this->on( 'voxel/post/wall-reply-stats-updated', '@wall_reply_stats_updated' );
		$this->on( 'voxel/post/review-reply-stats-updated', '@review_reply_stats_updated' );
		$this->on( 'voxel/post/timeline-reply-stats-updated', '@timeline_reply_stats_updated' );

		$this->on( 'voxel_ajax_admin.get_fields_form', '@get_fields_form' );
		$this->on( 'add_meta_boxes', '@remove_taxonomy_metaboxes' );

		$this->filter( 'wp_revisions_to_keep', '@limit_revision_count', 10, 2 );

		$this->filter( 'posts_results', '@enable_preview_for_nonpublished_posts', 100, 2 );

		$this->filter( 'transition_post_status', '@on_transition_post_status', 10, 3 );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'responsive-embeds' );
	}

	protected function display_fields() {
		$post = \Voxel\Post::get( get_post() );
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		add_meta_box(
			'voxel_post_fields',
			__( 'Fields', 'voxel-backend' ).sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $post->get_edit_link() ), __( 'Edit in frontend form', 'voxel-backend' ) ),
			function() use ( $post ) {
				require locate_template( 'templates/backend/edit-post-metabox.php' );
			},
			$post->post_type->get_key(),
			'normal',
			'high'
		);
	}

	protected function get_fields_form() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_post' );

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Post type not provided.', 'voxel-backend' ) );
			}

			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( $post && ! $post->is_editable_by_current_user() ) {
				throw new \Exception( __( 'You cannot edit this post.', 'voxel-backend' ) );
			}

			if ( $post && $post->post_type && $post->post_type->get_key() !== $post_type->get_key() ) {
				throw new \Exception( __( 'You cannot edit this post.', 'voxel-backend' ) );
			}

			if ( ! $post && ! current_user_can( $post_type->wp_post_type->cap->create_posts ) ) {
				throw new \Exception( __( 'You cannot edit this post.', 'voxel-backend' ) );
			}

			$widget = new \Voxel\Widgets\Create_Post( [
				'id' => '7e8fe52',
				'elType' => 'widget',
				'widgetType' => 'ts-create-post',
				'elements' => [],
				'settings' => [
					'ts_post_type' => $post_type->get_key(),
					'_ts_admin_mode' => true,
					'_ts_admin_mode_nonce' => wp_create_nonce( 'vx_create_post_admin_mode' ),
				],
			], [] );

			require locate_template( 'templates/backend/edit-post-fields.php' );
		} catch ( \Exception $e ) {
			printf( '<p title="%s" style="display: none;">%s</p>', $e->getMessage(), __( 'An error occurred.', 'voxel-backend' ) );
			exit;
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function print_archive_template( $post_type ) {
		if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
			return;
		}

		$template_id = $post_type->get_templates()['archive'] ?? null;

		if ( post_password_required( $template_id ) ) {
			return;
		}

		if ( ! \Voxel\is_elementor_active() ) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $template_id );
		if ( ! ( $document && $document->is_built_with_elementor() ) ) {
			return;
		}

		$frontend = \Elementor\Plugin::$instance->frontend;
		add_action( 'wp_enqueue_scripts', function() use ( $frontend, $template_id ) {
			$frontend->enqueue_styles();
			\Voxel\enqueue_template_css( $template_id );
		} );

		get_header();
		if ( \Voxel\get_page_setting( 'voxel_hide_header', $template_id ) !== 'yes' ) {
			\Voxel\print_header();
		}

		echo $frontend->get_builder_content_for_display( $template_id );

		if ( \Voxel\get_page_setting( 'voxel_hide_footer', $template_id ) !== 'yes' ) {
			\Voxel\print_footer();
		}
		get_footer();
	}

	protected function save_post( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['vx_admin_save_post_nonce'] ?? '', 'vx_admin_save_post_nonce' )  ) {
			return;
		}

		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		do_action( 'voxel/admin/save_post', \Voxel\Post::get( $post ) );
	}

	protected function add_verification_metabox() {
		$post = \Voxel\Post::get( get_post() );
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		add_meta_box(
			'vx_verification',
			_x( 'Verification Status', 'Post verification status metabox title', 'voxel' ),
			function() use ( $post ) {
				wp_nonce_field( 'vx_admin_save_post_nonce', 'vx_admin_save_post_nonce' );
				?>
				<select name="vx_verification_status" style=" width: 100%;max-width: 100%;min-width: 0; box-sizing: border-box;">
					<option value="verified" <?php selected( $post->is_verified() ) ?>><?= __( 'Verified', 'voxel-backend' ) ?></option>
					<option value="unverified" <?php selected( ! $post->is_verified() ) ?>><?= __( 'Unverified', 'voxel-backend' ) ?></option>
				</select>
			<?php },
			null,
			'side',
			'low',
		);
	}

	protected function add_author_metabox() {
		$post = \Voxel\Post::get( get_post() );
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		add_meta_box(
			'vx_author',
			_x( 'Author', 'author metabox', 'voxel-backend' ),
			function() use ( $post ) {
				$author = $post->get_author();

				$config = [];
				if ( $author ) {
					$config = [
						'author' => [
							'id' => $author->get_id(),
							'avatar' => $author->get_avatar_markup(),
							'display_name' => $author->get_display_name(),
							'roles' => array_values( $author->get_role_keys() ),
							'edit_link' => $author->get_edit_link(),
						],
					];
				}

				wp_nonce_field( 'vx_admin_save_post_nonce', 'vx_admin_save_post_nonce' );
				require locate_template( 'templates/backend/author-metabox.php' );
			},
			null,
			'side',
			'low',
		);
	}

	protected function add_expiry_metabox() {
		$post = \Voxel\Post::get( get_post() );
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		if ( in_array( $post->post_type->get_key(), [ 'profile' ], true ) ) {
			return;
		}

		add_meta_box(
			'vx_expiry',
			_x( 'Expiration date', 'expiry metabox', 'voxel-backend' ),
			function() use ( $post ) {
				$rules = $post->post_type->repository->get_expiration_rules();
				$rule_expirations = \Voxel\resolve_expiration_rules( $post );

				$custom_expiry = (string) get_post_meta( $post->get_id(), 'voxel:expiry_date', true );
				$has_custom_expiry = !! strtotime( $custom_expiry );

				$config = [
					'expiry_mode' => $has_custom_expiry
						? ( $custom_expiry === '9999-01-01 00:00:00' ? 'never' : 'custom' )
						: 'follow_rules',
					'custom_expiry' => ( $has_custom_expiry && $custom_expiry !== '9999-01-01 00:00:00' )
						? $custom_expiry
						: '',
				];

				require locate_template( 'templates/backend/expiry-metabox.php' );
			},
			null,
			'side',
			'low',
		);
	}

	protected function add_priority_metabox() {
		$post = \Voxel\Post::get( get_post() );
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		$active_package = $post->promotions->get_active_package();
		if ( $active_package !== null ) {
			add_meta_box(
				'vx_promotion_package',
				_x( 'Promotion package', 'promotion metabox', 'voxel-backend' ),
				function() use ( $post, $active_package ) {
					$priority = $active_package['priority'] ?? 0;
					$package_title = null;
					$order_id = null;
					$order_link = null;
					$start_date = null;
					$end_date = null;

					if ( is_numeric( $active_package['order_id'] ?? null ) ) {
						$order_id = intval( $active_package['order_id'] );
						$order_link = add_query_arg( 'order_id', intval( $active_package['order_id'] ), admin_url( 'admin.php?page=voxel-orders' ) );
					}

					if ( $start_stamp = strtotime( $active_package['start_date'] ?? '' ) ) {
						$start_date = \Voxel\date_format( $start_stamp );
					}

					if ( $end_stamp = strtotime( $active_package['end_date'] ?? '' ) ) {
						$end_date = \Voxel\date_format( $end_stamp );
					}

					if ( $package = \Voxel\Product_Types\Promotions\Promotion_Package::get( $active_package['package_key'] ?? null ) ) {
						$package_title = $package->get_label();
					}

					require locate_template( 'templates/backend/promotion-package-metabox.php' );
				},
				null,
				'side',
				'low',
			);
		}

		add_meta_box(
			'vx_priority',
			_x( 'Priority', 'priority metabox', 'voxel-backend' ),
			function() use ( $post ) {
				$priority = (int) get_post_meta( $post->get_id(), 'voxel:priority', true );
				$priority = is_numeric( $priority ) ? intval( $priority ) : 0;

				require locate_template( 'templates/backend/priority-metabox.php' );
			},
			null,
			'side',
			'low',
		);
	}

	protected function save_verification_status( $post ) {
		if ( ! array_key_exists( 'vx_verification_status', $_POST ) ) {
			return;
		}

		$current_status = $post->is_verified();
		$new_status = ( $_POST['vx_verification_status'] ?? null ) === 'verified';
		if ( $current_status !== $new_status ) {
			$post->set_verified( $new_status );
		}
	}

	protected function save_author( $post ) {
		$new_author_id = is_numeric( $_POST['vx_author'] ?? null ) ? absint( $_POST['vx_author'] ) : null;
		if ( $new_author_id !== null && $new_author_id !== absint( $post->get_author_id() ) && ( $new_author = \Voxel\User::get( $new_author_id ) ) ) {
			global $wpdb;
			$wpdb->update( $wpdb->posts, [
				'post_author' => $new_author->get_id(),
			], $where = [ 'ID' => $post->get_id() ] );

			if ( $post && $post->post_type && $post->post_type->is_managed_by_voxel() ) {
				\Voxel\queue_user_post_stats_for_caching( $new_author->get_id() );
			}
		}

		if ( $post->post_type->get_key() === 'profile' ) {
			\Voxel\update_site_specific_user_meta( isset( $new_author ) ? $new_author->get_id() : $post->get_author_id(), 'voxel:profile_id', $post->get_id() );
		}
	}

	protected function save_expiration_date( $post ) {
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		if ( ! array_key_exists( 'vx_expiry_mode', $_POST ) ) {
			return;
		}

		$expiry_mode = $_POST['vx_expiry_mode'] ?? null;
		if ( $expiry_mode === 'follow_rules' ) {
			delete_post_meta( $post->get_id(), 'voxel:expiry_date' );
		} elseif ( $expiry_mode === 'custom' ) {
			$expiry_date = strtotime( (string) ( $_POST['vx_expiry_custom'] ?? '' ) );
			if ( $expiry_date ) {
				update_post_meta( $post->get_id(), 'voxel:expiry_date', date( 'Y-m-d H:i:s', $expiry_date ) );
			}
		} elseif ( $expiry_mode === 'never' ) {
			update_post_meta( $post->get_id(), 'voxel:expiry_date', '9999-01-01 00:00:00' );
		}
	}

	protected function save_priority( $post ) {
		if ( ! ( $post && $post->is_managed_by_voxel() ) ) {
			return;
		}

		if ( ! array_key_exists( 'vx_post_priority', $_POST ) ) {
			return;
		}

		$priority_level = $_REQUEST['vx_post_priority'] ?? 'medium';
		if ( $priority_level === 'low' ) {
			update_post_meta( $post->get_id(), 'voxel:priority', -1 );
		} elseif ( $priority_level === 'high' ) {
			update_post_meta( $post->get_id(), 'voxel:priority', 1 );
		} elseif ( $priority_level === 'custom' ) {
			$custom_priority = intval( $_REQUEST['vx_post_priority_custom'] ?? 0 );
			$custom_priority = \Voxel\clamp( $custom_priority, -128, 127 );

			if ( $custom_priority !== 0 ) {
				update_post_meta( $post->get_id(), 'voxel:priority', $custom_priority );
			} else {
				delete_post_meta( $post->get_id(), 'voxel:priority' );
			}
		} else {
			delete_post_meta( $post->get_id(), 'voxel:priority' );
		}
	}

	protected function send_notifications( $post ) {
		if ( ! $post->is_managed_by_voxel() ) {
			return;
		}

		$current_status = $_POST['post_status'] ?? null;
		$previous_status = $_POST['original_post_status'] ?? null;

		if ( $current_status === 'publish' && in_array( $previous_status, [ 'pending', 'rejected' ], true ) ) {
			( new \Voxel\Events\Posts\Post_Approved_Event( $post->post_type ) )->dispatch( $post->get_id() );
		}

		if ( $current_status === 'rejected' && $previous_status !== 'rejected' ) {
			( new \Voxel\Events\Posts\Post_Rejected_Event( $post->post_type ) )->dispatch( $post->get_id() );
		}
	}

	protected function unindex_on_trash( $post_id ) {
		$post = \Voxel\Post::get( $post_id );
		if ( $post && $post->is_managed_by_voxel() ) {
			$post->unindex();
		}
	}

	protected function wall_stats_updated( $post_id ) {
		$this->_maybe_reindex_activiy_column( 'wall', $post_id );
	}

	protected function review_stats_updated( $post_id ) {
		$this->_maybe_reindex_activiy_column( 'reviews', $post_id );
		$this->_maybe_reindex_rating_column( $post_id );
	}

	protected function timeline_stats_updated( $post_id ) {
		$this->_maybe_reindex_activiy_column( 'timeline', $post_id );
	}

	protected function _maybe_reindex_activiy_column( $activity_type, $post_id, $is_reply = false ) {
		$post = \Voxel\Post::get( $post_id );

		if ( ! ( $post && $post->should_index() ) ) {
			return;
		}

		$orderbys = $post->post_type->get_search_orders();
		foreach ( $orderbys as $orderby_group ) {
			foreach ( (array) $orderby_group->get_prop('clauses') as $clause ) {
				if (
					$clause['type'] === 'latest-activity'
					&& $clause['activity'] === $activity_type
					&& ( ! $is_reply || ( $is_reply && $clause['include_replies'] ) )
				) {
					$post->index();
					return;
				}
			}
		}
	}

	protected function wall_reply_stats_updated( $post_id ) {
		$this->_maybe_reindex_activiy_column( 'wall', $post_id, true );
	}

	protected function review_reply_stats_updated( $post_id ) {
		$this->_maybe_reindex_activiy_column( 'reviews', $post_id, true );
	}

	protected function timeline_reply_stats_updated( $post_id ) {
		$this->_maybe_reindex_activiy_column( 'timeline', $post_id, true );
	}

	protected function _maybe_reindex_rating_column( $post_id ) {
		$post = \Voxel\Post::get( $post_id );
		if ( ! ( $post && $post->should_index() ) ) {
			return;
		}

		$orderbys = $post->post_type->get_search_orders();
		foreach ( $orderbys as $orderby_group ) {
			foreach ( (array) $orderby_group->get_prop('clauses') as $clause ) {
				if ( $clause['type'] === 'rating' ) {
					$post->index();
					return;
				}
			}
		}
	}

	protected function remove_taxonomy_metaboxes() {
		$screen = get_current_screen();
		if ( ! ( $screen && $screen->post_type ) ) {
			return;
		}

		$post_type = \Voxel\Post_Type::get( $screen->post_type );
		if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
			return;
		}

		foreach ( $post_type->get_fields() as $field ) {
			if ( $field->get_type() === 'taxonomy' && ( $taxonomy_key = $field->get_prop('taxonomy') ) && $field->get_prop('backend_edit_mode') !== 'native_metabox' ) {
				remove_meta_box( $taxonomy_key.'div', $post_type->get_key(), 'side' );
				remove_meta_box( 'tagsdiv-'.$taxonomy_key, $post_type->get_key(), 'side' );
			}
		}
	}

	protected function limit_revision_count( $num, $post ) {
		if ( ! ( $post instanceof \WP_Post && $post->post_type && post_type_supports( $post->post_type, 'revisions' ) ) ) {
			return 0;
		}

		$max = \Voxel\get( 'settings.db.max_revisions', 5 );
		return absint( $max );
	}

	protected function update_post_index( $post_id ) {
		static $hooked = false;

		if ( ! isset( $GLOBALS['_vx_post_reindex_ids'] ) ) {
			$GLOBALS['_vx_post_reindex_ids'] = [];
		}

		$GLOBALS['_vx_post_reindex_ids'][ $post_id ] = true;

		if ( ! $hooked ) {
			$hooked = true;
			add_action( 'shutdown', function() {
				foreach ( $GLOBALS['_vx_post_reindex_ids'] as $post_id => $true ) {
					$post = \Voxel\Post::get( $post_id );
					if ( $post && $post->post_type && $post->post_type->is_managed_by_voxel() ) {
						$post = \Voxel\Post::force_get( $post->get_id() );
						$post->should_index() ? $post->index() : $post->unindex();
						// \Voxel\log( 'Updating index for post '.$post_id );
					}
				}
			} );
		}
	}

	/**
	 * Fix: Post statuses marked as protected don't allow authors to preview their own posts.
	 *
	 * @since 1.2.6
	 */
	protected function enable_preview_for_nonpublished_posts( $posts, $wp_query ) {
		$previewable_statuses = apply_filters( 'voxel/previewable_statuses', [
			'draft' => true,
			'pending' => true,
			'expired' => true,
			'unpublished' => true,
			'rejected' => true,
		] );

		if ( ! empty( $posts ) && ( $wp_query->is_single || $wp_query->is_page ) ) {
			$post = \Voxel\Post::get( $posts[0] );
			if ( $post->is_managed_by_voxel() && $post->get_author_id() === get_current_user_id() ) {
				$status = get_post_status_object( $post->get_status() );

				if ( $status && ! $status->public && isset( $previewable_statuses[ $status->name ] ) ) {
					$handler = function( $posts, $wp_query ) use ( $post, &$handler ) {
						remove_filter( 'the_posts', $handler, 100, 2 );
						$posts[] = $post->get_wp_post_object();
						return $posts;
					};

					add_filter( 'the_posts', $handler, 100, 2 );
				}
			}
		}

		return $posts;
	}

	protected function on_transition_post_status( $new_status, $old_status, $post ) {
		if ( $new_status === 'expired' && $old_status !== 'expired' ) {
			$post = \Voxel\Post::get( $post->ID );
			if ( $post && $post->post_type && $post->is_managed_by_voxel() ) {
				( new \Voxel\Events\Posts\Post_Expired_Event( $post->post_type ) )->dispatch( $post->get_id() );
			}
		}
	}
}
