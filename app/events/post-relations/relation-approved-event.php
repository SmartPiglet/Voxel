<?php

namespace Voxel\Events\Post_Relations;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Relation_Approved_Event extends \Voxel\Events\Base_Event {

	public $field, $post_type;

	public
		$requesting_post,
		$requesting_author,
		$responding_post,
		$responding_author;

	public function __construct( $field ) {
		$this->field = $field;
		$this->post_type = $field->get_post_type();
	}

	public function prepare( $requesting_post_id, $responding_post_id ) {
		$this->requesting_post = \Voxel\Post::get( $requesting_post_id );
		$this->responding_post = \Voxel\Post::get( $responding_post_id );
		if ( ! ( $this->requesting_post && $this->responding_post ) ) {
			throw new \Exception( 'Missing data.' );
		}

		$this->requesting_author = $this->requesting_post->get_author();
		$this->responding_author = $this->responding_post->get_author();
		if ( ! ( $this->requesting_author && $this->responding_author ) ) {
			throw new \Exception( 'Missing data.' );
		}

		$this->field->set_post( $this->requesting_post );
	}

	public function get_key(): string {
		return sprintf(
			'post-types/%s/post-relations/%s:approved',
			$this->post_type->get_key(),
			$this->field->get_relation_key()
		);
	}

	public function get_label(): string {
		return sprintf( '%s: Post relation approved', $this->field->get_label() );
	}

	public function get_category() {
		return sprintf( 'post-type:%s', $this->post_type->get_key() );
	}

	public static function notifications(): array {
		return [
			'author' => [
				'label' => 'Notify author',
				'recipient' => function( $event ) {
					return $event->requesting_author;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your request to add @responding_post(:title) to @requesting_post(:title) has been approved',
					'details' => function( $event ) {
						return [
							'requester' => $event->requesting_post->get_id(),
							'responder' => $event->responding_post->get_id(),
						];
					},
					'apply_details' => function( $event, $details, $notification ) {
						$event->prepare( $details['requester'] ?? null, $details['responder'] ?? null );
					},
					'links_to' => function( $event ) {
						return $event->responding_post->get_link();
					},
					'image_id' => function( $event ) {
						return $event->responding_post->get_avatar_id();
					},
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your request to add @responding_post(:title) to @requesting_post(:title) has been approved',
					'message' => <<<HTML
					Your request to add <strong>@responding_post(:title)</strong> to
					<strong>@requesting_post(:title)</strong> has been approved.
					<a href="@responding_post(:url)">Open @responding_post(:title)</a>
					<a href="@requesting_post(:url)">Open @requesting_post(:title)</a>
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
					'subject' => '@responding_post(:title) has approved the relation request from @requesting_post(:title)',
					'details' => function( $event ) {
						return [
							'requester' => $event->requesting_post->get_id(),
							'responder' => $event->responding_post->get_id(),
						];
					},
					'apply_details' => function( $event, $details, $notification ) {
						$event->prepare( $details['requester'] ?? null, $details['responder'] ?? null );
					},
					'links_to' => function( $event ) {
						return $event->responding_post->get_link();
					},
					'image_id' => function( $event ) {
						return $event->responding_post->get_avatar_id();
					},
				],
				'email' => [
					'enabled' => false,
					'subject' => '@responding_post(:title) has approved the relation request from @requesting_post(:title)',
					'message' => <<<HTML
					<strong>@responding_post(:title)</strong> has approved the relation
					request from <strong>@requesting_post(:title)</strong>
					<a href="@responding_post(:url)">Open @responding_post(:title)</a>
					<a href="@requesting_post(:url)">Open @requesting_post(:title)</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->responding_author = \Voxel\User::mock();
		$this->requesting_author = \Voxel\User::mock();
	}

	public function dynamic_tags(): array {
		return [
			'responding_post' => \Voxel\Dynamic_Data\Group::Post( $this->responding_post ?: \Voxel\Post::mock( [ 'post_type' => $this->post_type->get_key() ] ) ),
			'responding_author' => \Voxel\Dynamic_Data\Group::User( $this->responding_author ),
			'requesting_post' => \Voxel\Dynamic_Data\Group::Post( $this->requesting_post ?: \Voxel\Post::mock( [ 'post_type' => $this->post_type->get_key() ] ) ),
			'requesting_author' => \Voxel\Dynamic_Data\Group::User( $this->requesting_author ),
		];
	}
}
