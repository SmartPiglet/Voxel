<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

function create_template( $title, $template_type = 'page' ) {
	$template_id = wp_insert_post( [
		'post_type' => 'elementor_library',
		'post_status' => 'publish',
		'post_title' => $title,
		'meta_input' => [
			'_elementor_edit_mode' => 'builder',
			'_elementor_template_type' => $template_type,
		],
	] );

	if ( ! is_wp_error( $template_id ) ) {
		if ( ! term_exists( 'voxel-template', 'elementor_library_category' ) ) {
			wp_insert_term( 'Voxel Template', 'elementor_library_category', [
				'slug' => 'voxel-template',
			] );
		}

		wp_set_object_terms( $template_id, 'voxel-template', 'elementor_library_category' );
		wp_set_object_terms( $template_id, $template_type, 'elementor_library_type' );
	}

	return $template_id;
}

function template_exists( $template_id ) {
	return is_int( $template_id ) && get_post_type( $template_id ) === 'elementor_library' && get_post_status( $template_id ) !== 'trash';
}

function create_page( $title, $slug = '' ) {
	return wp_insert_post( [
		'post_type' => 'page',
		'post_status' => 'publish',
		'post_title' => $title,
		'post_name' => $slug,
		'meta_input' => [
			'_elementor_edit_mode' => 'builder',
		],
	] );
}

function page_exists( $page_id ) {
	return is_int( $page_id ) && get_post_type( $page_id ) === 'page' && get_post_status( $page_id ) !== 'trash';
}

function print_template( $template_id ) {
	if ( ! \Voxel\is_elementor_active() ) {
		return;
	}

	if ( ! \Voxel\is_preview_mode() ) {
		\Voxel\enqueue_template_css( $template_id );
		wp_print_styles( 'elementor-post-'.$template_id );

		if ( did_action( 'wp_enqueue_scripts' ) ) {
			$template_assets = get_post_meta( $template_id, '_elementor_page_assets', true );
			if ( is_array( $template_assets['styles'] ?? null ) && version_compare( ELEMENTOR_VERSION, '3.24.0', '>=' ) ) {
				wp_print_styles( $template_assets['styles'] );
			}
		}
	}

	// fix incorrect rendering of templates in the editor
	if ( \Voxel\is_edit_mode() ) {
		wp_styles()->do_item( 'elementor-post-'.$template_id );
		add_filter( 'elementor/frontend/builder_content/before_print_css', '__return_false', 1150 );

		$frontend = \Elementor\Plugin::$instance->frontend;
		echo $frontend->get_builder_content_for_display( $template_id );

		remove_filter( 'elementor/frontend/builder_content/before_print_css', '__return_false', 1150 );
	} else {
		$frontend = \Elementor\Plugin::$instance->frontend;
		echo $frontend->get_builder_content_for_display( $template_id );
	}
}

function print_template_css( $template_id ) {
	if ( ! \Voxel\is_elementor_active() ) {
		return;
	}

	static $printed = [];
	if ( isset( $printed[ $template_id ] ) ) {
		return;
	}

	$printed[ $template_id ] = true;
	$css_file = \Elementor\Core\Files\CSS\Post::create( $template_id );
	$css_file->print_css();

	// elementor automatically enqueues the CSS file even if the CSS has already been
	// printed inline; to get around it, we dequeue the template css file at a late hook
	add_action( 'wp_footer', function() use ( $template_id ) {
		wp_dequeue_style( sprintf( 'elementor-post-%d', $template_id ) );
	} );
}

function enqueue_template_css( $template_id ) {
	if ( ! \Voxel\is_elementor_active() ) {
		return;
	}

	$template_assets = get_post_meta( $template_id, '_elementor_page_assets', true );
	if ( ! empty( $template_assets ) && version_compare( ELEMENTOR_VERSION, '3.24.0', '>=' ) ) {
		\Elementor\Plugin::$instance->assets_loader->enable_assets( $template_assets );
	}

	$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
	$css_file->enqueue();
}

function get_page_setting( $setting_key, $post_id = null ) {
	if ( ! \Voxel\is_elementor_active() ) {
		return;
	}

	$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );
	$page_settings_model = $page_settings_manager->get_model( $post_id ?? get_the_ID() );
	return $page_settings_model->get_settings( $setting_key );
}

function get_template_link( $template, $fallback = null ) {
	if ( empty( $fallback ) ) {
		$fallback = home_url('/');
	}

	return get_permalink( \Voxel\get( 'templates.'.$template ) ) ?: $fallback;
}

function print_header() {
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		return;
	}

	$template_id = \Voxel\resolve_template_for_location( 'header' );
	if ( \Voxel\template_exists( $template_id ) ) {
		\Voxel\print_template( $template_id );
	}
}

function print_footer() {
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		return;
	}

	$template_id = \Voxel\resolve_template_for_location( 'footer' );
	if ( \Voxel\template_exists( $template_id ) ) {
		\Voxel\print_template( $template_id );
	}
}

function resolve_template_for_location( string $type ) {
	$templates = \Voxel\get( 'custom_templates' );
	if ( empty( $templates[ $type ] ) ) {
		return \Voxel\get( sprintf( 'templates.%s', $type ) );
	}

	foreach ( $templates[ $type ] as $index => $template ) {
		if ( empty( $template['visibility_rules'] ) ) {
			continue;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $template['visibility_rules'] );
		if ( $rules_passed ) {
			return $template['id'];
		}
	}

	return \Voxel\get( sprintf( 'templates.%s', $type ) );
}

function get_custom_page_settings( $post_id ) {
	return (array) json_decode( get_post_meta( $post_id, '_voxel_page_settings', true ), ARRAY_A );
}

function get_temporary_custom_page_settings( $post_id ) {
	$settings = (array) json_decode( get_post_meta( $post_id, '_voxel_page_settings_tmp', true ), ARRAY_A );
	return ! empty( $settings ) ? $settings : \Voxel\get_custom_page_settings( $post_id );
}

function get_related_widget( \Elementor\Widget_Base $widget, $document_id, $relation_key, $relation_side ) {
	$page_settings = \Voxel\is_elementor_ajax()
		? \Voxel\get_temporary_custom_page_settings( $document_id )
		: \Voxel\get_custom_page_settings( $document_id );
	$relations = $page_settings['relations'] ?? [];
	$relation_group = $relations[ $relation_key ] ?? [];
	$other_side = $relation_side === 'left' ? 'right' : 'left';
	$path_key = $other_side === 'right' ? 'rightPath' : 'leftPath';

	foreach ( $relation_group as $relation ) {
		if ( $relation[ $relation_side ] === $widget->get_id() ) {
			$data = \Elementor\Plugin::$instance->documents->get_current()->get_elements_data();
			$path = explode( '.', $relation[ $path_key ] ?? '' );

			while ( ! empty( $path ) ) {
				$index = array_shift( $path );
				if ( ! isset( $data[ $index ] ) ) {
					break;
				}

				if ( empty( $path ) && $data[ $index ]['elType'] === 'widget' ) {
					return $data[ $index ];
				}

				$data = $data[ $index ]['elements'];
			}
		}
	}

	return null;
}

function get_post_for_preview( $template_id ) {
	$post_type = \Voxel\get_post_type_for_preview( $template_id );

	$post = apply_filters( '_voxel/editor/get_post_for_preview', null, $template_id );
	if ( $post !== null ) {
		return $post;
	}

	if ( $post_type ) {
		$page_settings = (array) get_post_meta( $template_id, '_elementor_page_settings', true );
		$post_id = $page_settings['voxel_preview_post'] ?? null;
		if ( is_numeric( $post_id ) && ( $_post = get_post( $post_id ) ) ) {
			$post = $_post;
		} else {
			$post = current( get_posts( [
				'number' => 1,
				'status' => 'publish',
				'post_type' => $post_type->get_key(),
				'orderby' => 'date',
				'order' => 'ASC',
			] ) );
		}

		// if we're editing the preview card for a post type, pass that information to the
		// editor frontend so that we can adjust the editing layout
		$custom_card_templates = array_column( $post_type->templates->get_custom_templates()['card'], 'id' );
		if ( (int) $post_type->get_templates()['card'] === (int) $template_id || in_array( $template_id, $custom_card_templates ) ) {
			add_filter( 'voxel/js/elementor-editor-config', function( $config ) {
				$config['is_preview_card'] = true;
				return $config;
			} );
		}

		return \Voxel\Post::get( $post ) ?? \Voxel\Post::dummy( [ 'post_type' => $post_type->get_key() ] );
	} else {
		$custom_term_card_templates = array_column( \Voxel\get_custom_templates()['term_card'], 'id' );
		if ( in_array( $template_id, $custom_term_card_templates ) ) {
			add_filter( 'voxel/js/elementor-editor-config', function( $config ) {
				$config['is_preview_card'] = true;
				return $config;
			} );
		}

		return \Voxel\Post::get( $template_id );
	}
}

function get_post_type_for_preview( $template_id ) {
	return current( array_filter( \Voxel\Post_Type::get_all(), function( $post_type ) use ( $template_id ) {
		$templates = $post_type->get_templates();
		$custom_card_templates = array_column( $post_type->templates->get_custom_templates()['card'], 'id' );
		$custom_single_templates = array_column( $post_type->templates->get_custom_templates()['single'], 'id' );
		$custom_single_post_templates = array_column( $post_type->templates->get_custom_templates()['single_post'], 'id' );
		return (
			in_array( $template_id, [ $templates['single'], $templates['card'] ] )
			|| in_array( $template_id, $custom_card_templates )
			|| in_array( $template_id, $custom_single_templates )
			|| in_array( $template_id, $custom_single_post_templates )
		);
	} ) );
}

function get_base_templates(): array {
	return [
		/* General */
		[
			'category' => 'header',
			'label' => __( 'Default Header', 'voxel-backend' ),
			'key' => 'templates.header',
			'id' => \Voxel\get( 'templates.header' ),
			'image' => \Voxel\get_image('post-types/header.png'),
			'type' => 'template',
		],
		[
			'category' => 'footer',
			'label' => __( 'Default Footer', 'voxel-backend' ),
			'key' => 'templates.footer',
			'id' => \Voxel\get( 'templates.footer' ),
			'image' => \Voxel\get_image('post-types/footer.png'),
			'type' => 'template',
		],
		[
			'category' => 'social',
			'label' => __( 'Newsfeed', 'voxel-backend' ),
			'key' => 'templates.timeline',
			'id' => \Voxel\get( 'templates.timeline' ),
			'image' => \Voxel\get_image('post-types/timeline.png'),
			'type' => 'page',
		],
		[
			'category' => 'social',
			'label' => __( 'Inbox', 'voxel-backend' ),
			'key' => 'templates.inbox',
			'id' => \Voxel\get( 'templates.inbox' ),
			'image' => \Voxel\get_image('post-types/timeline.png'),
			'type' => 'page',
		],
		[
			'category' => 'general',
			'label' => __( 'Post statistics', 'voxel-backend' ),
			'key' => 'templates.post_stats',
			'id' => \Voxel\get( 'templates.post_stats' ),
			'image' => \Voxel\get_image('post-types/prvc.png'),
			'type' => 'page',
		],
		[
			'category' => 'general',
			'label' => __( 'Privacy Policy', 'voxel-backend' ),
			'key' => 'templates.privacy_policy',
			'id' => \Voxel\get( 'templates.privacy_policy' ),
			'image' => \Voxel\get_image('post-types/prvc.png'),
			'type' => 'page',
		],
		[
			'category' => 'general',
			'label' => __( 'Terms & Conditions', 'voxel-backend' ),
			'key' => 'templates.terms',
			'id' => \Voxel\get( 'templates.terms' ),
			'image' => \Voxel\get_image('post-types/prvc.png'),
			'type' => 'page',
		],
		[
			'category' => 'general',
			'label' => __( '404 Not Found', 'voxel-backend' ),
			'key' => 'templates.404',
			'id' => \Voxel\get( 'templates.404' ),
			'image' => \Voxel\get_image('post-types/404.png'),
			'type' => 'template',
		],
		[
			'category' => 'general',
			'label' => __( 'Restricted content', 'voxel-backend' ),
			'key' => 'templates.restricted',
			'id' => \Voxel\get( 'templates.restricted' ),
			'image' => \Voxel\get_image('post-types/restricted.png'),
			'type' => 'template',
		],

		/* Membership */
		[
			'category' => 'membership',
			'label' => __( 'Login & registration', 'voxel-backend' ),
			'key' => 'templates.auth',
			'id' => \Voxel\get( 'templates.auth' ),
			'image' => \Voxel\get_image('post-types/login.png'),
			'type' => 'page',
		],
		[
			'category' => 'membership',
			'label' => __( 'Current plan', 'voxel-backend' ),
			'key' => 'templates.current_plan',
			'id' => \Voxel\get( 'templates.current_plan' ),
			'image' => \Voxel\get_image('post-types/plans.png'),
			'type' => 'page',
		],

		/* Orders */
		[
			'category' => 'orders',
			'label' => __( 'Orders page', 'voxel-backend' ),
			'key' => 'templates.orders',
			'id' => \Voxel\get( 'templates.orders' ),
			'image' => \Voxel\get_image('post-types/orders.png'),
			'type' => 'page',
		],
		// [
		// 	'category' => 'orders',
		// 	'label' => __( 'Reservations page', 'voxel-backend' ),
		// 	'key' => 'templates.reservations',
		// 	'id' => \Voxel\get( 'templates.reservations' ),
		// 	'image' => \Voxel\get_image('post-types/orders.png'),
		// 	'type' => 'page',
		// ],
		[
			'category' => 'orders',
			'label' => __( 'Cart summary', 'voxel-backend' ),
			'key' => 'templates.checkout',
			'id' => \Voxel\get( 'templates.checkout' ),
			'image' => \Voxel\get_image('post-types/orders.png'),
			'type' => 'page',
		],
		[
			'category' => 'orders',
			'label' => __( 'Stripe Connect account', 'voxel-backend' ),
			'key' => 'templates.stripe_account',
			'id' => \Voxel\get( 'templates.stripe_account' ),
			'image' => \Voxel\get_image('post-types/orders.png'),
			'type' => 'page',
		],
		// [
		// 	'category' => 'orders',
		// 	'label' => __( 'Order tags: QR code handler', 'voxel-backend' ),
		// 	'key' => 'templates.qr_tags',
		// 	'id' => \Voxel\get( 'templates.qr_tags' ),
		// 	'image' => \Voxel\get_image('post-types/orders.png'),
		// 	'type' => 'page',
		// ],

		/* Style kits */
		[
			'category' => 'style_kits',
			'label' => __( 'Popup styles', 'voxel-backend' ),
			'key' => 'templates.kit_popups',
			'id' => \Voxel\get( 'templates.kit_popups' ),
			'image' => \Voxel\get_image('post-types/orders.png'),
			'type' => 'template',
		],
		[
			'category' => 'style_kits',
			'label' => __( 'Timeline styles', 'voxel-backend' ),
			'key' => 'templates.kit_timeline',
			'id' => \Voxel\get( 'templates.kit_timeline' ),
			'image' => \Voxel\get_image('post-types/orders.png'),
			'type' => 'template',
		],
	];
}

function get_custom_templates(): array {
	$groups = [
		'header' => [],
		'footer' => [],
		'term_single' => [],
		'term_card' => [],
	];

	$needs_resaving = false;

	foreach ( (array) ( \Voxel\get( 'custom_templates' ) ?? [] ) as $group => $templates ) {
		if ( ! isset( $groups[ $group ] ) ) {
			continue;
		}

		foreach ( (array) $templates as $template ) {
			if ( isset( $template['id'], $template['label'] ) && is_numeric( $template['id'] ) ) {
				$template_config = [
					'label' => $template['label'],
					'id' => absint( $template['id'] ),
					'unique_key' => $template['unique_key'] ?? null,
				];

				if ( in_array( $group, [ 'header', 'footer', 'term_single' ], true ) ) {
					$template_config['visibility_rules'] = is_array( $template['visibility_rules'] ?? null ) ? $template['visibility_rules'] : [];
				}

				if ( empty( $template_config['unique_key'] ) ) {
					$template_config['unique_key'] = strtolower( \Voxel\random_string(8) );
					$needs_resaving = true;
				}

				$groups[ $group ][] = $template_config;
			}
		}
	}

	if ( $needs_resaving ) {
		\Voxel\set( 'custom_templates', $groups );
	}

	return $groups;
}
