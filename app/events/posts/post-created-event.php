<?php

namespace Voxel\Events\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Created_Event extends \Voxel\Events\Base_Event {

	public $post_type;

	public $post, $author;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function prepare( $post_id ) {
		$post = \Voxel\Post::force_get( $post_id );
		if ( ! ( $post && $post->get_author() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->post = $post;
		$this->author = $post->get_author();
	}

	public function get_key(): string {
		return sprintf( 'post-types/%s/post:created', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: User created a new post', $this->post_type->get_label() );
	}

	public function get_description(): ?string {
		return 'This event is triggered whenever a new post is created through the frontend submission form, regardless of its status (published, pending, draft).';
	}

	public function get_category() {
		return sprintf( 'post-type:%s', $this->post_type->get_key() );
	}

	public static function notifications(): array {
		return [
			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					return $event->author;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'Your post has been created.',
					'details' => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['post_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->post->get_link(); },
					'image_id' => function( $event ) { return $event->post->get_logo_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your post has been created.',
					'message' => <<<HTML
					Your post <strong>@post(title)</strong> has been created.
					<a href="@post(url)">Open</a>
					HTML,
				],
			],

			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@author(display_name) created a new post.',
					'details' => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['post_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->post->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) created a new post.',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> have created a new post
					<strong>@post(title)</strong>.
					<a href="@post(url)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->author = \Voxel\User::mock();
	}

	public function dynamic_tags(): array {
		return [
			'author' => \Voxel\Dynamic_Data\Group::User( $this->author ),
			'post' => \Voxel\Dynamic_Data\Group::Post( $this->post ?: \Voxel\Post::mock( [ 'post_type' => $this->post_type->get_key() ] ) ),
		];
	}
}
