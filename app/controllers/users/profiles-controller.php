<?php

namespace Voxel\Controllers\Users;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Profiles_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'init', '@register_post_type', -1 );
		$this->on( 'admin_head', '@show_profile_details' );
		$this->filter( 'manage_edit-profile_columns', '@profile_columns' );
		$this->filter( 'register_profile_post_type_args', '@manage_post_type', 100, 2 );
		$this->on( 'admin_menu', '@show_in_admin_menu', 50 );

		if ( is_admin() ) {
			$this->filter( 'request', '@profile_sort' );
		}
	}

	protected function register_post_type() {
		register_post_type( 'profile', [
			'labels' => [
				'name' => 'Profiles',
				'singular_name' => 'Profile',
			],
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'hierarchical'        => false,
			'query_var'           => true,
			'supports'            => [],
			'menu_position'       => 70,
			'delete_with_user'    => true,
			'_is_created_by_voxel' => false,
			'has_archive' => 'profile',
			'rewrite' => [
				'slug' => 'profile_preview',
				'with_front' => true,
			],
		] );

		remove_post_type_support( 'profile', 'author' );
		remove_post_type_support( 'profile', 'comments' );
		remove_post_type_support( 'profile', 'title' );
		remove_post_type_support( 'profile', 'editor' );
	}

	protected function show_profile_details() {
		$screen = get_current_screen();
		if ( ! ( ( $screen->post_type ?? null ) === 'profile' && ( $screen->id ?? null ) === 'edit-profile' ) ) {
			return;
		}

		add_filter( 'the_title', function( $title, $post_id ) {
			$post = \Voxel\Post::get( $post_id );
			if ( $post->post_type->get_key() !== 'profile' ) {
				return __( '(unknown)', 'voxel-backend' );
			}

			$author = $post->get_author();
			if ( ! ( $author && (int) $author->get_profile_id() === (int) $post->get_id() ) ) {
				return __( '(unknown)', 'voxel-backend' );
			}

			return sprintf( '#%d &mdash; %s', $author->get_id(), $author->get_display_name() );
		}, 10, 2 );

		add_filter( 'post_row_actions', function( $actions, $post ) {
			$post = \Voxel\Post::get( $post );
			if ( $post->post_type->get_key() !== 'profile' ) {
				return $actions;
			}

			$author = $post->get_author();
			if ( ! ( $author && (int) $author->get_profile_id() === (int) $post->get_id() ) ) {
				return $actions;
			}

			$actions['view'] = sprintf( '<a href="%s">%s</a>', esc_url( $author->get_link() ), __( 'View Profile', 'voxel-backend' ) );
			$actions['view_user'] = sprintf( '<a href="%s">%s</a>', esc_url( $author->get_edit_link() ), __( 'Edit User', 'voxel-backend' ) );
			return $actions;
		}, 10, 2 );
	}

	protected function profile_columns( $columns ) {
		$columns['title'] = __( 'Profile', 'voxel-backend' );
		// unset( $columns['author'] );
		unset( $columns['comments'] );
		unset( $columns['date'] );
		return $columns;
	}

	protected function profile_sort( $vars ) {
		$screen = get_current_screen();
		if ( ! ( $screen && $screen->id === 'edit-profile' ) ) {
			return $vars;
		}

		if ( empty( $vars['orderby'] ) ) {
			$vars['orderby'] = 'post_author';
			$vars['order'] = 'desc';
		}

		if ( $vars['orderby'] === 'title' ) {
			$vars['orderby'] = 'post_author';
		}

		if ( ! empty( $vars['s'] ) ) {
			add_filter( 'posts_join', function( $join, $wp_query ) {
			    global $wpdb;

			    if ( ! empty( $wp_query->query_vars['s'] ) && $wp_query->query_vars['post_type'] === 'profile' ) {
		        	$join .= " LEFT JOIN {$wpdb->users} ON {$wpdb->posts}.post_author = {$wpdb->users}.ID ";
			    }

			    return $join;
			}, 10, 2 );

			add_filter( 'posts_search', function( $search, $wp_query ) {
				global $wpdb;

				if ( ! empty( $wp_query->query_vars['s'] ) && $wp_query->query_vars['post_type'] === 'profile' ) {
					$search_term = $wp_query->query_vars['s'];
					$search = $wpdb->prepare( <<<SQL
						AND (
							{$wpdb->users}.display_name LIKE %s
							OR {$wpdb->users}.user_login LIKE %s
							OR {$wpdb->users}.user_email LIKE %s
							OR {$wpdb->posts}.post_title LIKE %s
							OR {$wpdb->posts}.post_content LIKE %s
						)
						SQL,
						'%'.$wpdb->esc_like( $search_term ).'%',
						'%'.$wpdb->esc_like( $search_term ).'%',
						'%'.$wpdb->esc_like( $search_term ).'%',
						'%'.$wpdb->esc_like( $search_term ).'%',
						'%'.$wpdb->esc_like( $search_term ).'%'
					);
				}

				return $search;
			}, 10, 2 );
		}

		return $vars;
	}

	protected function manage_post_type( $args, $post_type_key ) {
		$settings = \Voxel\get('post_types.profile.settings');

		$args['has_archive'] = 'profile';

		if ( $settings['permalinks']['custom'] ?? false ) {
			$args['has_archive'] = $settings['permalinks']['slug'] ?? 'profile';
			$args['rewrite'] = [
				'slug' => 'profile_preview',
				'with_front' => $settings['permalinks']['with_front'] ?? true,
			];

			global $wp_rewrite;
			$wp_rewrite->author_base = $settings['permalinks']['slug'] ?? $post_type_key;
			$permalink_front = \Voxel\get_permalink_front();
			if ( ! empty( $permalink_front ) && $permalink_front !== '/' && ! ( $settings['permalinks']['with_front'] ?? true ) ) {
				add_filter('author_rewrite_rules', function( $author_rewrite ) {
					global $wp_rewrite;

					$front = $wp_rewrite->front;
					$front = preg_replace( '|^/+|', '', $front );
					if ( empty( $front ) ) {
						return $author_rewrite;
					}

					$new_author_rewrite = [];
					foreach ( $author_rewrite as $key => $rewrite ) {
						$new_key = preg_replace('|^' . preg_quote( $front, '|' ) . '|', '', $key );
						$new_author_rewrite[ $new_key ] = $rewrite;
					}

					return $new_author_rewrite;
				} );

				add_filter( 'author_link', function( $link, $author_id ) {
					global $wp_rewrite;

					$front = $wp_rewrite->front;
					if ( ! empty( $front ) && str_starts_with( $front, '/' ) && str_ends_with( $front, '/' ) ) {
						$new_link = preg_replace( '/' . preg_quote( $front, '/' ) . '/u', '/', $link, 1 );
						return $new_link;
					}

					return $link;
				}, 20, 2 );
			}
		}

		if ( ( $settings['options']['archive']['has_archive'] ?? '' ) === 'enabled' ) {
			if ( ( $settings['options']['archive']['slug'] ?? '' ) === 'custom' ) {
				$args['has_archive'] = $settings['options']['archive']['custom_slug'] ?? 'profile';
			}
		} elseif ( ( $settings['options']['archive']['has_archive'] ?? '' ) === 'disabled' ) {
			$args['has_archive'] = false;
		}

		return $args;
	}

	protected function show_in_admin_menu() {
		add_users_page(
			__('Profiles (Voxel)', 'voxel-backend'),
			__('Profiles (Voxel)', 'voxel-backend'),
			'manage_options',
			'edit.php?post_type=profile'
		);
	}
}
