<?php

namespace Voxel\Events\Post_Relations;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Relation_Requested_Event extends \Voxel\Events\Base_Event {

	public $field, $post_type;

	public $post, $author, $relation_ids;

	public function __construct( $field ) {
		$this->field = $field;
		$this->post_type = $field->get_post_type();
	}

	public function prepare( $requesting_post_id, $responding_author_id, $relation_ids ) {
		$this->post = \Voxel\Post::get( $requesting_post_id );
		$this->author = \Voxel\User::get( $responding_author_id );
		$this->relation_ids = array_map( 'absint', (array) $relation_ids );
		if ( ! ( $this->post && $this->post->get_author() && $this->author && ! empty( $this->relation_ids ) ) ) {
			throw new \Exception( 'Missing data.' );
		}

		$this->field->set_post( $this->post );
	}

	public function get_key(): string {
		return sprintf(
			'post-types/%s/post-relations/%s:requested',
			$this->post_type->get_key(),
			$this->field->get_relation_key()
		);
	}

	public function get_label(): string {
		return sprintf( '%s: Post relation requested', $this->field->get_label() );
	}

	public function get_category() {
		return sprintf( 'post-type:%s', $this->post_type->get_key() );
	}

	public static function notifications(): array {
		return [
			'author' => [
				'label' => 'Notify author',
				'recipient' => function( $event ) {
					return $event->author;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@requesting_post(title) has requested to add @request(count).is_equal_to(1).then(one).else(@value()) of your items to their post',
					'details' => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
							'author_id' => $event->author->get_id(),
							'relation_ids' => $event->relation_ids,
						];
					},
					'apply_details' => function( $event, $details, $notification ) {
						$event->prepare(
							$details['post_id'] ?? null,
							$details['author_id'] ?? null,
							$details['relation_ids'] ?? null
						);

						if ( $event->is_request_expired( $notification ) ) {
							throw new \Exception( 'Request has expired.' );
						}
					},
					'links_to' => function( $event ) {
						return '(details)';
					},
					'image_id' => function( $event ) {
						return $event->post->get_avatar_id();
					},
					'actions_page_count' => function( $event, $notification ) {
						try {
							return ceil( count( $event->relation_ids ) / \Voxel\Notification::ACTIONS_PER_PAGE );
						} catch ( \Exception $e ) {
							return 1;
						}
					},
					'actions' => function( $page, $event, $details, $notification ) {
						if ( ! is_array( $details['response'] ?? null ) ) {
							$details['response'] = [];
						}

						$actions = [];

						// @todo approve all/decline all
						if ( $page === 1 && count( $details['response'] ) < count( $event->relation_ids ) ) {
							// code...
						}

						$per_page = \Voxel\Notification::ACTIONS_PER_PAGE;
						$offset = ( $page - 1 ) * $per_page;
						$relevant_post_ids = array_slice( $event->relation_ids, $offset, $per_page );

						_prime_post_caches( $relevant_post_ids );

						foreach ( $relevant_post_ids as $post_id ) {
							$post = \Voxel\Post::get( $post_id );
							if ( $post && $post->is_managed_by_voxel() ) {
								$actions[] = [
									'type' => 'list-item',
									'subject' => $post->get_display_name(),
									'links_to' => $post->get_link(),
									'image_markup' => $post->get_avatar_markup(),
									'actions' => function() use ( $post, $event, $details, $notification ) {
										$response = $details['response'][ $post->get_id() ] ?? null;
										if ( $response === true ) {
											return [
												[
													'type' => 'plain',
													'label' => _x( 'Approved', 'relation request', 'voxel' ),
												]
											];
										} elseif ( $response === false ) {
											return [
												[
													'type' => 'plain',
													'label' => _x( 'Declined', 'relation request', 'voxel' ),
												]
											];
										}

										return [
											[
												'type' => 'button',
												'key' => sprintf( 'post-%d-approve', $post->get_id() ),
												'label' => _x( 'Approve', 'relation request', 'voxel' ),
												'handler' => function() use ( $post, $event, $details, $notification ) {
													$event->field->approve_relations_from_author(
														absint( $details['author_id'] ?? 0 ),
														[ $post->get_id() ]
													);

													$details['response'][ $post->get_id() ] = true;
													$notification->update( 'details', $details );

													$approved_event = new \Voxel\Events\Post_Relations\Relation_Approved_Event( $event->field );
													$approved_event->dispatch( $event->post->get_id(), $post->get_id() );
												},
											],
											[
												'type' => 'button',
												'key' => sprintf( 'post-%d-decline', $post->get_id() ),
												'label' => _x( 'Decline', 'relation request', 'voxel' ),
												'handler' => function() use ( $post, $event, $details, $notification ) {
													$event->field->decline_relations_from_author(
														absint( $details['author_id'] ?? 0 ),
														[ $post->get_id() ]
													);

													$details['response'][ $post->get_id() ] = false;
													$notification->update( 'details', $details );

													$declined_event = new \Voxel\Events\Post_Relations\Relation_Declined_Event( $event->field );
													$declined_event->dispatch( $event->post->get_id(), $post->get_id() );
												},
											],
										];
									}
								];
							}
						}

						return $actions;
					},
				],
				'email' => [
					'enabled' => false,
					'subject' => '@requesting_post(title) has requested to add @request(count).is_equal_to(1).then(one).else(@value()) of your items to their post',
					'message' => <<<HTML
					<strong>@requesting_post(title)</strong> has requested to add the following items to their post:<br>
					<ol>
					@request(posts.title[]).list(,,<li>,</li>)
					</ol>
					<a href="@requesting_post(permalink)">Review</a>
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
					'subject' => '@requesting_post(title) has requested to add @request(count).is_equal_to(1).then(an item).else(@value() items) to their post',
					'details' => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
							'author_id' => $event->author->get_id(),
							'relation_ids' => $event->relation_ids,
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare(
							$details['post_id'] ?? null,
							$details['author_id'] ?? null,
							$details['relation_ids'] ?? null
						);
					},
					'links_to' => function( $event ) {
						return $event->post->get_link();
					},
					'image_id' => function( $event ) {
						return $event->post->get_avatar_id();
					},
				],
				'email' => [
					'enabled' => false,
					'subject' => '@requesting_post(title) has requested to add @request(count).is_equal_to(1).then(an item).else(@value() items) to their post',
					'message' => <<<HTML
					<strong>@requesting_post(title)</strong> has requested to add the following items to their post:<br>
					<ol>
					@request(posts.title[]).list(,,<li>,</li>)
					</ol>
					<a href="@requesting_post(permalink)">View post</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->relation_ids = [];
		$this->author = \Voxel\User::mock();
	}

	public function dynamic_tags(): array {
		return [
			'request' => \Voxel\Dynamic_Data\Group::Post_Relation_Request( $this->relation_ids ),
			'requesting_post' => \Voxel\Dynamic_Data\Group::Post( $this->post ?: \Voxel\Post::mock( [ 'post_type' => $this->post_type->get_key() ] ) ),
			'requesting_author' => \Voxel\Dynamic_Data\Group::User( $this->post ? $this->post->get_author() : \Voxel\User::mock() ),
			'responding_author' => \Voxel\Dynamic_Data\Group::User( $this->author ),
		];
	}

	public function is_request_expired( \Voxel\Notification $notification ): bool {
		$pending_meta = $this->field->get_pending_meta();
		$n_id = $pending_meta[ $this->author->get_id() ]['n'] ?? null;

		$details = $notification->get_details();
		$response = (array) ( $details['response'] ?? [] );

		// keep notification visible after all relation requests have been responded to
		if ( count( $response ) === count( $this->relation_ids ) ) {
			return false;
		}

		return ! ( $n_id && absint( $n_id ) === $notification->get_id() );
	}
}
