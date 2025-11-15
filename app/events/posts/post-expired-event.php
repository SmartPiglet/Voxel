<?php

namespace Voxel\Events\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Expired_Event extends \Voxel\Events\Base_Event {

	public $post_type;

	public $post, $author;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function prepare( $post_id ) {
		$post = \Voxel\Post::get( $post_id );
		if ( ! ( $post && $post->get_author() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->post = $post;
		$this->author = $post->get_author();
	}

	public function get_key(): string {
		return sprintf( 'post-types/%s/post:expired', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: Post expired', $this->post_type->get_label() );
	}

	public function get_category() {
		return sprintf( 'post-type:%s', $this->post_type->get_key() );
	}

	public static function notifications(): array {
		return [
			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => fn($event) => $event->author,
				'inapp' => [
					'enabled' => true,
					'subject' => '"@post(title)" has expired',
					'details' => fn($event) => [
						'post_id' => $event->post->get_id(),
					],
					'apply_details' => fn($event, $details) => $event->prepare($details['post_id'] ?? null),
					'links_to' => fn($event) => $event->post->get_link(),
					'image_id' => fn($event) => $event->post->get_logo_id(),
				],
				'email' => [
					'enabled' => true,
					'subject' => '"@post(title)" has expired',
					'message' => <<<HTML
					Your post <b>@post(title)</b> has expired.
					<a href="@post(permalink)">Manage post</a>
					HTML,
				],
			],

			'admin' => [
				'label' => 'Notify admin',
				'recipient' => fn($event) => \Voxel\get_main_admin(),
				'inapp' => [
					'enabled' => false,
					'subject' => '"@post(title)" has expired',
					'details' => fn($event) => [
						'post_id' => $event->post->get_id(),
					],
					'apply_details' => fn($event, $details) => $event->prepare($details['post_id'] ?? null),
					'links_to' => fn($event) => $event->post->get_link(),
					'image_id' => fn($event) => $event->author->get_avatar_id(),
				],
				'email' => [
					'enabled' => false,
					'subject' => '"@post(title)" has expired',
					'message' => <<<HTML
					<b>@post(title)</b> by <b>@author(display_name)</b>
					has expired.
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
