<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Timeline extends Base_Widget {

	public function get_name() {
		return 'ts-timeline';
	}

	public function get_title() {
		return __( 'Timeline (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'ts_timeline_settings',
			[
				'label' => __( 'Timeline settings', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control( 'ts_mode', [
				'label' => __( 'Display mode', 'voxel-elementor' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'user_feed',
				'options' => [
					'post_reviews' => 'Current post reviews',
					'post_wall' => 'Current post wall',
					'post_timeline' => 'Current post timeline',
					'author_timeline' => 'Current author timeline',
					'user_feed' => 'Logged-in user newsfeed',
					'global_feed' => 'Sitewide activity',
				],
			] );

			$repeater = new \Elementor\Repeater;

			$repeater->add_control( 'ts_order', [
				'label' => __( 'Order', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => [
					'latest' => __( 'Latest', 'voxel-elementor' ),
					'earliest' => __( 'Earliest', 'voxel-elementor' ),
					'most_liked' => __( 'Most liked', 'voxel-elementor' ),
					'most_discussed' => __( 'Most discussed', 'voxel-elementor' ),
					'most_popular' => __( 'Most popular (likes+comments)', 'voxel-elementor' ),
					'best_rated' => __( 'Best rated (reviews only)', 'voxel-elementor' ),
					'worst_rated' => __( 'Worst rated (reviews only)', 'voxel-elementor' ),
				],
			] );

			$repeater->add_control( 'ts_time', [
				'label' => __( 'Timeframe', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all_time',
				'options' => [
					'today' => __( 'Today', 'voxel-elementor' ),
					'this_week' => __( 'This week', 'voxel-elementor' ),
					'this_month' => __( 'This month', 'voxel-elementor' ),
					'this_year' => __( 'This year', 'voxel-elementor' ),
					'all_time' => __( 'All time', 'voxel-elementor' ),
					'custom' => __( 'Custom', 'voxel-elementor' ),
				],
			] );

			$repeater->add_control( 'ts_time_custom', [
				'label' => __( 'Show items from the past number of days', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 7,
				'condition' => [ 'ts_time' => 'custom' ],
			] );

			$repeater->add_control( 'ts_label', [
				'label' => __( 'Label', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Latest',
			] );

			$this->add_control( 'ts_ordering_options', [
				'label' => __( 'Ordering options', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'_disable_loop' => true,
				'title_field' => '{{{ ts_label }}}',
			] );

			$this->add_control(
				'no_status_text',
				[
					'label' => __( 'No posts text', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'No posts available', 'voxel-elementor' ),
					'placeholder' => __( 'Type your text', 'voxel-elementor' ),
				]
			);

			$this->add_control(
				'ts_search_enabled',
				[
					'label' => __( 'Enable search input', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);
	

			$this->add_control(
				'ts_search_value',
				[
					'label' => __( 'Default search value', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '@tags()@site().query_var(q)',
			'condition' => [ 'ts_time' => 'custom' ],
					'condition' => [ 'ts_search_enabled' => 'yes' ],
				]
			);
		$this->end_controls_section();

		$this->start_controls_section(
			'ts_timeline_icons',
			[
				'label' => __( 'Timeline icons', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'ts_verified_icon',
				[
					'label' => __( 'Verified', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_repost_icon',
				[
					'label' => __( 'Repost', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_more_icon',
				[
					'label' => __( 'More', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_like_icon',
				[
					'label' => __( 'Like', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_liked_icon',
				[
					'label' => __( 'Liked', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_comment_icon',
				[
					'label' => __( 'Comment', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_reply_icon',
				[
					'label' => __( 'Reply', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_gallery_icon',
				[
					'label' => __( 'Gallery', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_upload_icon',
				[
					'label' => __( 'Upload', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_emoji_icon',
				[
					'label' => __( 'Emoji', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_search_ico',
				[
					'label' => __( 'Search', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'trash_icon',
				[
					'label' => __( 'Delete', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_external_icon',
				[
					'label' => __( 'External link', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_timeline_load_icon',
				[
					'label' => __( 'Load more', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

			$this->add_control(
				'ts_no_posts',
				[
					'label' => __( 'No posts', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,

				]
			);

		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$current_user = \Voxel\get_current_user();
		$current_post = \Voxel\get_current_post();
		$current_author = $current_post ? $current_post->get_author() : null;
		$mode = $this->get_settings( 'ts_mode' );

		// visibility
		if ( in_array( $mode, [ 'post_reviews', 'post_wall', 'post_timeline' ], true ) ) {
			if ( ! ( $current_post && $current_post->post_type ) ) {
				return;
			}

			$visibility_key = ( $mode === 'post_wall' ? 'wall_visibility' : ( $mode === 'post_reviews' ? 'review_visibility' : 'visibility' ) );
			$visibility = $current_post->post_type->get_setting( 'timeline.'.$visibility_key );
			if ( $visibility === 'logged_in' && ! is_user_logged_in() ) {
				return;
			} elseif ( $visibility === 'followers_only' && ! ( is_user_logged_in() && ( \Voxel\current_user()->follows_post( $current_post->get_id() ) || $current_post->get_author_id() === get_current_user_id() ) ) ) {
				return;
			} elseif ( $visibility === 'customers_only' && ! ( is_user_logged_in() && ( \Voxel\current_user()->has_bought_product( $current_post->get_id() ) || $current_post->get_author_id() === get_current_user_id() ) ) ) {
				return;
			} elseif ( $visibility === 'private' && ! $current_post->is_editable_by_current_user() ) {
				return;
			}
		} elseif ( $mode === 'author_timeline' ) {
			if ( ! $current_author ) {
				return;
			}

			$visibility = \Voxel\get( 'settings.timeline.user_timeline.visibility', 'public' );
			if ( $visibility === 'logged_in' ) {
				if ( ! is_user_logged_in() ) {
					return;
				}
			} elseif ( $visibility === 'followers_only' ) {
				if ( ! is_user_logged_in() ) {
					return;
				}

				if ( ! ( $current_user->get_id() === $current_author->get_id() || $current_user->follows_user( $current_author->get_id() ) ) ) {
					return;
				}
			} elseif ( $visibility === 'customers_only' ) {
				if ( ! is_user_logged_in() ) {
					return;
				}

				if ( ! (
					$current_user->get_id() === $current_author->get_id()
					|| (
						$current_author->has_cap('administrator')
						&& apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true
						&& $current_user->has_bought_product_from_platform()
					) || (
						$current_user->has_bought_product_from_vendor( $current_author->get_id() )
					)
				) ) {
					return;
				}
			} elseif ( $visibility === 'private' ) {
				if ( ! ( is_user_logged_in() && $current_user->get_id() === $current_author->get_id() ) ) {
					return;
				}
			} else /* $visibility === 'public' */ {
				//
			}
		} elseif ( $mode === 'user_feed' ) {
			if ( ! is_user_logged_in() ) {
				return;
			}
		} elseif ( $mode === 'global_feed' ) {
			//
		} elseif ( $mode === 'single_status' ) {
			//
		} else {
			return;
		}

		$ordering_options = [];
		foreach ( (array) $this->get_settings( 'ts_ordering_options' ) as $ordering_option ) {
			$ordering_options[] = [
				'_id' => $ordering_option['_id'],
				'label' => $ordering_option['ts_label'],
				'order' => $ordering_option['ts_order'],
				'time' => $ordering_option['ts_time'],
				'time_custom' => $ordering_option['ts_time_custom'],
			];
		}

		$search_enabled = $this->get_settings_for_display('ts_search_enabled') === 'yes';
		$default_query = '';

		if ( $search_enabled ) {
			$default_query = \Voxel\mb_trim( (string) $this->get_settings_for_display('ts_search_value') );
			if ( ! empty( $_GET['q'] ) ) {
				$_submitted_query = \Voxel\mb_trim( wp_unslash( (string) $_GET['q'] ) );
				if ( ! empty( $_submitted_query ) ) {
					$default_query = $_submitted_query;
				}
			}
		}

		$cfg = [
			'timeline' => [
				'mode' => $mode,
			],
			'current_user' => [
				'exists' => !! $current_user,
				'id' => $current_user ? $current_user->get_id() : null,
				'username' => $current_user ? $current_user->get_username() : null,
				'display_name' => $current_user ? $current_user->get_display_name() : null,
				'avatar_url' => $current_user ? $current_user->get_avatar_url() : null,
				'link' => $current_user ? $current_user->get_link() : null,
			],
			'current_post' => [
				'exists' => !! $current_post,
				'id' => $current_post ? $current_post->get_id() : null,
				'display_name' => $current_post ? $current_post->get_display_name() : null,
				'avatar_url' => $current_post ? $current_post->get_avatar_url() : null,
				'link' => $current_post ? $current_post->get_link() : null,
			],
			'current_author' => [
				'exists' => $current_post && $current_post->get_author_id(),
				'id' => $current_post ? $current_post->get_author_id() : null,
			],
			'settings' => [
				'posts' => [
					'editable' => !! \Voxel\get( 'settings.timeline.posts.editable', true ),
					'content_maxlength' => absint( \Voxel\get( 'settings.timeline.posts.maxlength', 5000 ) ),
					'truncate_at' => \Voxel\get( 'settings.timeline.posts.truncate_at', 280 ),
					'gallery_enabled' => !! \Voxel\get( 'settings.timeline.posts.images.enabled', true ),
					'gallery_max_uploads' => absint( \Voxel\get( 'settings.timeline.posts.images.max_count', 3 ) ),
					'gallery_allowed_formats' => (array) \Voxel\get( 'settings.timeline.posts.images.allowed_formats', [
						'image/jpeg',
						'image/gif',
						'image/png',
						'image/webp',
					] ),
				],
				'replies' => [
					'editable' => !! \Voxel\get( 'settings.timeline.replies.editable', true ),
					'content_maxlength' => absint( \Voxel\get( 'settings.timeline.replies.maxlength', 2000 ) ),
					'truncate_at' => \Voxel\get( 'settings.timeline.replies.truncate_at', 280 ),
					'max_nest_level' => \Voxel\get( 'settings.timeline.replies.max_nest_level', 1 ),
					'gallery_enabled' => !! \Voxel\get( 'settings.timeline.replies.images.enabled', true ),
					'gallery_max_uploads' => absint( \Voxel\get( 'settings.timeline.replies.images.max_count', 1 ) ),
					'gallery_allowed_formats' => (array) \Voxel\get( 'settings.timeline.replies.images.allowed_formats', [
						'image/jpeg',
						'image/gif',
						'image/png',
						'image/webp',
					] ),
				],
				'reposts' => [
					'enabled' => !! \Voxel\get( 'settings.timeline.reposts.enabled', true ),
				],
				'quotes' => [
					'truncate_at' => \Voxel\get( 'settings.timeline.posts.quotes.truncate_at', 160 ),
					'placeholder' => _x( 'What\'s on your mind?', 'timeline', 'voxel' ),
				],
				'emojis' => [
					'url' => trailingslashit( get_template_directory_uri() ) . 'assets/vendor/emoji-list/emoji-list.json',
				],
				'link_preview' => [
					'default_image' => \Voxel\get_image('link-preview.webp'),
				],
				'mentions' => [
					'url' => home_url('/?vx=1&action=user.profile'),
				],
				'hashtags' => [
					'url' => get_permalink( \Voxel\get( 'templates.timeline' ) ),
				],
				'search' => [
					'enabled' => $search_enabled,
					'maxlength' => apply_filters( 'voxel/keyword-search/max-query-length', 128 ),
					'default_query' => $default_query,
				],
				'ordering_options' => $ordering_options,
				'filtering_options' => [
					'all' => _x( 'All', 'timeline filters', 'voxel' ),
				],
			],
			'l10n' => [
				'emoji_groups' => [
					'Smileys & Emotion' => _x( 'Smileys & Emotion', 'emoji popup', 'voxel' ),
					'People & Body' => _x( 'People & Body', 'emoji popup', 'voxel' ),
					'Animals & Nature' => _x( 'Animals & Nature', 'emoji popup', 'voxel' ),
					'Food & Drink' => _x( 'Food & Drink', 'emoji popup', 'voxel' ),
					'Travel & Places' => _x( 'Travel & Places', 'emoji popup', 'voxel' ),
					'Activities' => _x( 'Activities', 'emoji popup', 'voxel' ),
					'Objects' => _x( 'Objects', 'emoji popup', 'voxel' ),
				],
				'no_activity' => $this->get_settings_for_display('no_status_text'),
				'editedOn' => _x( 'Edited on @date', 'timeline', 'voxel' ),
				'oneLike' => _x( '1 like', 'timeline', 'voxel' ),
				'countLikes' => _x( '@count likes', 'timeline', 'voxel' ),
				'oneReply' => _x( '1 reply', 'timeline', 'voxel' ),
				'countReplies' => _x( '@count replies', 'timeline', 'voxel' ),
				'cancelEdit' => _x( 'Your changes will be lost. Do you wish to proceed?', 'timeline', 'voxel' ),
			],
			'reviews' => [
				'' => null, // cast to object
			],
			'async' => [
				'composer' => \Voxel\get_esm_src('timeline-composer.js'),
				'comments' => \Voxel\get_esm_src('timeline-comments.js'),
			],
			'nonce' => wp_create_nonce('vx_timeline'),
			'single_status_id' => null,
			'single_reply_id' => null,
		];

		if ( ! empty( $_GET['status_id'] ) && is_numeric( $_GET['status_id'] ) ) {
			$cfg['single_status_id'] = (int) $_GET['status_id'];
		}

		if ( ! empty( $_GET['reply_id'] ) && is_numeric( $_GET['reply_id'] ) ) {
			$cfg['single_reply_id'] = (int) $_GET['reply_id'];
		}

		if ( $current_user ) {
			$cfg['settings']['filtering_options']['liked'] = _x( 'Liked', 'timeline filters', 'voxel' );
		}

		if ( $current_user && $current_user->can_moderate_timeline_feed( (string) $mode, [ 'post' => $current_post ] ) ) {
			$cfg['settings']['filtering_options']['pending'] = _x( 'Pending', 'timeline filters', 'voxel' );
		}

		$icons = [
			'verified' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_verified_icon') ) ?: \Voxel\get_svg( 'verified.svg' ),
			'repost' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_repost_icon') ) ?: \Voxel\get_svg( 'repost.svg' ),
			'more' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_more_icon') ) ?: \Voxel\get_svg( 'menu-meatball.svg' ),
			'liked' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_liked_icon') ) ?: \Voxel\get_svg( 'heart-filled.svg' ),
			'like' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_like_icon') ) ?: \Voxel\get_svg( 'heart.svg' ),
			'comment' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_comment_icon') ) ?: \Voxel\get_svg( 'comment-bubble.svg' ),
			'reply' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_reply_icon') ) ?: \Voxel\get_svg( 'reply.svg' ),
			'gallery' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_gallery_icon') ) ?: \Voxel\get_svg( 'gallery.svg' ),
			'upload' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_icon') ) ?: \Voxel\get_svg( 'upload-cloud.svg' ),
			'emoji' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_emoji_icon') ) ?: \Voxel\get_svg( 'emo.svg' ),
			'search' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_ico') ) ?: \Voxel\get_svg( 'search.svg' ),
			'trash' => \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\get_svg( 'trash-can.svg' ),
			'external-link' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_external_icon') ) ?: \Voxel\get_svg( 'external.svg' ),
			'loading' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\get_svg( 'reload.svg' ),
			'no-post' => \Voxel\get_icon_markup( $this->get_settings_for_display('ts_no_posts') ) ?: \Voxel\get_svg( 'keyword-research' ),
		];

		if ( $mode === 'post_reviews' ) {
			$cfg['composer'] = [
				'feed' => 'post_reviews',
				'can_post' => false,
			];

			if ( $current_user && $current_post && $current_user->can_review_post( $current_post->get_id() ) ) {
				$cfg['composer']['can_post'] = true;
				$cfg['composer']['post_as'] = 'current_user';
				$cfg['composer']['placeholder'] = sprintf( _x( 'Review %s', 'timeline', 'voxel' ), $current_post->get_display_name() );
				$cfg['composer']['reviews_post_type'] = $current_post->post_type->get_key();
				$cfg['reviews'][ $current_post->post_type->get_key() ] = $current_post->post_type->reviews->get_timeline_config();
			}
		} elseif ( $mode === 'post_wall' ) {
			$cfg['composer'] = [
				'feed' => 'post_wall',
				'can_post' => false,
			];

			if ( $current_user && $current_post && $current_user->can_post_to_wall( $current_post->get_id() ) ) {
				$cfg['composer']['can_post'] = true;
				$cfg['composer']['post_as'] = 'current_user';
				$cfg['composer']['placeholder'] = sprintf( _x( 'What\'s on your mind, %s?', 'timeline', 'voxel' ), $current_user->get_display_name() );
				// $cfg['composer']['can_switch'] = true;
			}
		} elseif ( $mode === 'post_timeline' ) {
			$cfg['composer'] = [
				'feed' => 'post_timeline',
				'can_post' => false,
			];

			if ( $current_user && $current_post && $current_post->is_editable_by_user( $current_user ) ) {
				$cfg['composer']['can_post'] = true;
				$cfg['composer']['post_as'] = 'current_post';
				$cfg['composer']['placeholder'] = _x( 'What\'s on your mind?', 'timeline', 'voxel' );
			}
		} elseif ( $mode === 'author_timeline' ) {
			$cfg['composer'] = [
				'feed' => 'user_timeline',
				'can_post' => false,
			];

			if ( $current_user && $current_post && $current_post->get_author_id() === $current_user->get_id() ) {
				$cfg['composer']['can_post'] = true;
				$cfg['composer']['post_as'] = 'current_user';
				$cfg['composer']['placeholder'] = sprintf( _x( 'What\'s on your mind, %s?', 'timeline', 'voxel' ), $current_user->get_display_name() );
			}
		} elseif ( $mode === 'user_feed' ) {
			$cfg['composer'] = [
				'feed' => 'user_timeline',
				'can_post' => false,
			];

			if ( $current_user ) {
				$cfg['composer']['can_post'] = true;
				$cfg['composer']['post_as'] = 'current_user';
				$cfg['composer']['placeholder'] = sprintf( _x( 'What\'s on your mind, %s?', 'timeline', 'voxel' ), $current_user->get_display_name() );
			}
		} elseif ( $mode === 'global_feed' ) {
			$cfg['composer'] = [
				'feed' => 'user_timeline',
				'can_post' => false,
			];

			if ( $current_user ) {
				$cfg['composer']['can_post'] = true;
				$cfg['composer']['post_as'] = 'current_user';
				$cfg['composer']['placeholder'] = sprintf( _x( 'What\'s on your mind, %s?', 'timeline', 'voxel' ), $current_user->get_display_name() );
			}
		} else {
			return;
		}

		wp_print_styles( $this->get_style_depends() );

		if ( $timeline_kit = \Voxel\get( 'templates.kit_timeline', null ) ) {
			if ( \Voxel\is_edit_mode() ) {
				\Voxel\print_template_css( $timeline_kit );
			} else {
				\Voxel\enqueue_template_css( $timeline_kit );
				wp_print_styles( 'elementor-post-'.$timeline_kit );
			}
		}

		require locate_template( 'templates/widgets/timeline.php' );

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_timeline_v2();' );
		}
	}

	public function get_script_depends() {
		return [
			'vx:timeline-main.js',
			'swiper',
		];
	}

	public function get_style_depends() {
		return [
			'vx:forms.css',
			'vx:social-feed.css',
			'e-swiper',
		];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
