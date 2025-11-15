<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Reviews_Status_Created_Event extends \Voxel\Events\Base_Event {

	public $post_type;

	public $review, $post, $author;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function prepare( $review_id ) {
		$review = \Voxel\Timeline\Status::get( $review_id );
		if ( ! ( $review && $review->get_post() && $review->get_user() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->review = $review;
		$this->post = $review->get_post();
		$this->author = $review->get_user();
	}

	public function get_key(): string {
		return sprintf( 'post-types/%s/review:created', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: Review submitted', $this->post_type->get_label() );
	}

	public function get_category() {
		return sprintf( 'post-type:%s', $this->post_type->get_key() );
	}

	public static function notifications(): array {
		return [
			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					$post = $event->review->get_post();
					return $post ? $post->get_author() : null;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@author(display_name) submitted a review on @post(title)',
					'details' => function( $event ) {
						return [
							'review_id' => $event->review->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['review_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->review->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) submitted a review on @post(title)',
					'message' => <<<HTML
					A new review has been submitted on <strong>@post(title)</strong>
					by <strong>@author(display_name)</strong>.
					<a href="@post(permalink)">Open</a>
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
					'subject' => '@author(display_name) submitted a review on @post(title)',
					'details' => function( $event ) {
						return [
							'review_id' => $event->review->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['review_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->post->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) submitted a review on @post(title)',
					'message' => <<<HTML
					A new review has been submitted on <strong>@post(title)</strong>
					by <strong>@author(display_name)</strong>.
					<a href="@post(permalink)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->review = \Voxel\Timeline\Status::mock();
	}

	public function dynamic_tags(): array {
		return [
			'review' => \Voxel\Dynamic_Data\Group::Timeline_Review( $this->review ),
			'author' => \Voxel\Dynamic_Data\Group::User( $this->review && $this->review->get_user() ? $this->review->get_user() : \Voxel\User::mock() ),
			'post' => \Voxel\Dynamic_Data\Group::Post( $this->review && $this->review->get_post() ? $this->review->get_post() : \Voxel\Post::mock( [ 'post_type' => $this->post_type->get_key() ] ) ),
		];
	}
}
