<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Reviews_Status_Approved_Event extends Post_Reviews_Status_Created_Event {

	public function get_key(): string {
		return sprintf( 'post-types/%s/review:approved', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: Review approved', $this->post_type->get_label() );
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->review->get_author() ?? null;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your review has been approved',
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
					'subject' => 'Your review has been approved',
					'message' => <<<HTML
					Your review on @post(title) has been approved and published.
					<a href="@review(link)">Open</a>
					HTML,
				],
			],

			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					$post = $event->review->get_post();
					return $post ? $post->get_author() : null;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'New review published on @post(title)',
					'details' => function( $event ) {
						return [
							'review_id' => $event->review->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['review_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->review->get_link(); },
					'image_id' => function( $event ) { return $event->post->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) published a review on @post(title)',
					'message' => <<<HTML
					A new review has been published on <strong>@post(title)</strong>
					by <strong>@author(display_name)</strong>.
					<a href="@review(link)">Open</a>
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
					'subject' => 'New review published on @post(title)',
					'details' => function( $event ) {
						return [
							'review_id' => $event->review->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['review_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->review->get_link(); },
					'image_id' => function( $event ) { return $event->post->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) published a review on @post(title)',
					'message' => <<<HTML
					A new review has been published on <strong>@post(title)</strong>
					by <strong>@author(display_name)</strong>.
					<a href="@review(link)">Open</a>
					HTML,
				],
			],
		];
	}
}
