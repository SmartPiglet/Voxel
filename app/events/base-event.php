<?php

namespace Voxel\Events;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Event {

	private static $all;

	public $recipient;

	abstract public function get_key(): string;

	abstract public function get_label(): string;

	public function get_description(): ?string {
		return null;
	}

	public $_inapp_sent_cache = [];

	public function dynamic_tags(): array {
		return [];
	}

	public static function notifications(): array {
		return [];
	}

	public function dispatch() {
		try {
			$this->prepare( ...func_get_args() );
			$this->send_notifications();
		} catch ( \Exception $e ) {
			\Voxel\log($e);
		}
	}

	public function mock_props() {
		$this->recipient = \Voxel\User::mock();
		$this->set_mock_props();
	}

	public function set_mock_props() {
		//
	}

	public function get_dynamic_tags(): array {
		$tags = [];

		$tags['recipient'] = \Voxel\Dynamic_Data\Group::User( $this->recipient );

		$tags += $this->dynamic_tags();

		$tags['admin'] = \Voxel\Dynamic_Data\Group::User( \Voxel\get_main_admin() ?? \Voxel\User::mock() );
		$tags['site'] = \Voxel\Dynamic_Data\Group::Site();

		return $tags;
	}

	public function get_notifications(): array {
		$notifications = static::notifications();
		$events = (array) \Voxel\get( 'events', [] );

		foreach ( $notifications as $destination => $notification ) {
			$notification['inapp']['default_subject'] = $notification['inapp']['subject'];
			$notification['email']['default_subject'] = $notification['email']['subject'];
			$notification['email']['default_message'] = $notification['email']['message'];

			$notification['inapp']['subject'] = null;
			$notification['email']['subject'] = null;
			$notification['email']['message'] = null;

			$config = (array) ( $events[ $this->get_key() ]['notifications'] ?? [] );
			if ( isset( $config[ $destination ] ) ) {
				$notification['inapp']['enabled'] = $config[ $destination ]['inapp']['enabled'];
				$notification['inapp']['subject'] = $config[ $destination ]['inapp']['subject'];
				$notification['email']['enabled'] = $config[ $destination ]['email']['enabled'];
				$notification['email']['subject'] = $config[ $destination ]['email']['subject'];
				$notification['email']['message'] = $config[ $destination ]['email']['message'];
			}

			$notifications[ $destination ] = $notification;
		}

		return $notifications;
	}

	public function get_editor_config(): array {
		return [
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'category' => $this->get_category(),
			'notifications' => array_map( function( $n ) {
				return [
					'label' => $n['label'],
					'inapp' => [
						'enabled' => $n['inapp']['enabled'],
						'subject' => $n['inapp']['subject'],
						'default_subject' => $n['inapp']['default_subject'],
					],
					'email' => [
						'enabled' => $n['email']['enabled'],
						'subject' => $n['email']['subject'],
						'default_subject' => $n['email']['default_subject'],
						'message' => $n['email']['message'],
						'default_message' => $n['email']['default_message'],
					],
				];
			}, $this->get_notifications() ),
		];
	}

	public function send_notifications(): void {
		$emails = [];

		foreach ( $this->get_notifications() as $destination => $notification ) {
			$recipient = $notification['recipient']( $this );
			if ( ! $recipient instanceof \Voxel\User ) {
				continue;
			}

			$this->recipient = $recipient;

			if ( $notification['inapp']['enabled'] ) {
				$this->_inapp_sent_cache[ $destination ] = \Voxel\Notification::create( [
					'user_id' => $recipient->get_id(),
					'type' => $this->get_key(),
					'details' => array_merge(
						$notification['inapp']['details']( $this ),
						[ 'destination' => $destination ]
					),
				] );

				$recipient->update_notification_count();
			}

			if ( $notification['email']['enabled'] ) {
				$subject = \Voxel\render(
					$notification['email']['subject'] ?: $notification['email']['default_subject'],
					$this->get_dynamic_tags()
				);
				$message = \Voxel\render(
					$notification['email']['message'] ?: $notification['email']['default_message'],
					$this->get_dynamic_tags()
				);

				$emails[] = [
					'recipient' => $recipient->get_email(),
					'subject' => $subject,
					'message' => $message,
					'headers' => [
						'Content-type: text/html;',
					],
				];
			}
		}

		if ( ! empty( $emails ) ) {
			\Voxel\Utils\Async_Requests\Async_Email::instance()->data( [ 'emails' => $emails ] )->dispatch();
		}

		do_action( sprintf( 'voxel/app-events/%s', $this->get_key() ), $this );
	}

	public static function get_categories(): array {
		$categories = [];
		$categories['orders'] = [
			'key' => 'orders',
			'label' => 'Orders',
		];

		$categories['promotions'] = [
			'key' => 'promotions',
			'label' => '— Promoted posts',
		];

		foreach ( \Voxel\Product_Type::all() as $product_type ) {
			$categories[ sprintf( 'product-type:%s', $product_type->get_key() ) ] = [
				'key' => sprintf( 'product-type:%s', $product_type->get_key() ),
				'label' => sprintf( '— %s', $product_type->get_label() ),
			];
		}

		$categories['membership'] = [
			'key' => 'membership',
			'label' => 'Membership',
		];

		$categories['timeline'] = [
			'key' => 'timeline',
			'label' => 'Timeline',
		];

		$categories['messages'] = [
			'key' => 'messages',
			'label' => 'Direct Messages',
		];

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$categories[ sprintf( 'post-type:%s', $post_type->get_key() ) ] = [
				'key' => sprintf( 'post-type:%s', $post_type->get_key() ),
				'label' => $post_type->get_label(),
			];
		}

		$categories = apply_filters( 'voxel/app-events/categories', $categories );

		return $categories;
	}

	public static function get_all(): array {
		if ( ! is_null( static::$all ) ) {
			return static::$all;
		}

		$events = [
			// timeline events
			'timeline/followers/user-followed-event' => new \Voxel\Events\Timeline\Followers\User_Followed_Event,
			'timeline/followers/post-followed-event' => new \Voxel\Events\Timeline\Followers\Post_Followed_Event,
			'users/timeline/status:created' => new \Voxel\Events\Timeline\Statuses\User_Timeline_Status_Created_Event,
			'users/timeline/post-liked' => new \Voxel\Events\Timeline\Statuses\User_Liked_Event,
			'users/timeline/post-reposted' => new \Voxel\Events\Timeline\Statuses\User_Reposted_Event,
			'users/timeline/post-quoted' => new \Voxel\Events\Timeline\Statuses\User_Quoted_Event,
			'timeline/comment:submitted' => new \Voxel\Events\Timeline\Comments\Comment_Submitted_Event,
			'timeline/comment:approved' => new \Voxel\Events\Timeline\Comments\Comment_Approved_Event,
			'timeline/comment-reply:submitted' => new \Voxel\Events\Timeline\Comments\Comment_Reply_Submitted_Event,
			'timeline/comment-reply:approved' => new \Voxel\Events\Timeline\Comments\Comment_Reply_Approved_Event,
			'users/timeline/comment-liked' => new \Voxel\Events\Timeline\Comments\Comment_Liked_Event,
			'users/timeline/mentioned-in-post' => new \Voxel\Events\Timeline\Mentions\User_Mentioned_In_Post_Event,
			'users/timeline/mentioned-in-comment' => new \Voxel\Events\Timeline\Mentions\User_Mentioned_In_Comment_Event,

			// order general events
			'products/orders/customer:order_placed' => new \Voxel\Events\Products\Orders\Customer_Placed_Order_Event,
			'products/orders/customer:order_canceled' => new \Voxel\Events\Products\Orders\Customer_Canceled_Order_Event,
			'products/orders/vendor:order_approved' => new \Voxel\Events\Products\Orders\Vendor_Approved_Order_Event,
			'products/orders/vendor:order_declined' => new \Voxel\Events\Products\Orders\Vendor_Declined_Order_Event,

			// order shipping events
			'products/orders/shipping/customer:marked_delivered' => new \Voxel\Events\Products\Orders\Shipping\Customer_Marked_Delivered_Event,
			'products/orders/shipping/vendor:marked_delivered' => new \Voxel\Events\Products\Orders\Shipping\Vendor_Marked_Delivered_Event,
			'products/orders/shipping/vendor:marked_shipped' => new \Voxel\Events\Products\Orders\Shipping\Vendor_Marked_Shipped_Event,
			'products/orders/shipping/vendor:shared_tracking' => new \Voxel\Events\Products\Orders\Shipping\Vendor_Shared_Tracking_Event,

			// membership events
			'membership/user:registered' => new \Voxel\Events\Membership\User_Registered_Event,
			'membership/user:data-export-requested' => new \Voxel\Events\Membership\User_Data_Export_Requested_Event,

			// direct message events
			'messages/user:received_message' => new \Voxel\Events\Direct_Messages\User_Received_Message_Event,
			'messages/user:received_message_unthrottled' => new \Voxel\Events\Direct_Messages\User_Received_Message_Unthrottled_Event,

			// voxpoints events
			'voxpoints/points:changed' => new \Voxel\Events\VoxPoints\Points_Changed_Event,
		];

		if ( !! ( \Voxel\get('settings.timeline.moderation.user_timeline.posts.require_approval') ) ) {
			$events['users/timeline/status:approved'] = new \Voxel\Events\Timeline\Statuses\User_Timeline_Status_Approved_Event;
		}

		if ( \Voxel\get( 'paid_listings.settings.promotions.enabled' ) ) {
			$event = new \Voxel\Events\Promotions\Promotion_Activated_Event;
			$events[ $event->get_key() ] = $event;

			$event = new \Voxel\Events\Promotions\Promotion_Canceled_Event;
			$events[ $event->get_key() ] = $event;
		}

		foreach ( \Voxel\Product_Type::all() as $product_type ) {
			if ( $product_type->get_product_mode() === 'booking' ) {
				foreach ( [
					\Voxel\Events\Bookings\Booking_Placed_Event::class,
					\Voxel\Events\Bookings\Booking_Confirmed_Event::class,
					\Voxel\Events\Bookings\Booking_Canceled_By_Customer_Event::class,
					\Voxel\Events\Bookings\Booking_Canceled_By_Vendor_Event::class,
					\Voxel\Events\Bookings\Booking_Rescheduled_By_Customer_Event::class,
					\Voxel\Events\Bookings\Booking_Rescheduled_By_Vendor_Event::class,
				] as $event_class ) {
					$event = new $event_class( $product_type );
					$events[ $event->get_key() ] = $event;
				}
			}

			if (
				$product_type->config('modules.deliverables.enabled')
				&& $product_type->config('modules.deliverables.delivery_methods.manual')
			) {
				$event = new \Voxel\Events\Products\Orders\Downloads\Vendor_Shared_File_Event( $product_type );
				$events[ $event->get_key() ] = $event;
			}
		}

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			if ( $post_type->get_setting( 'submissions.enabled' ) || $post_type->get_setting( 'submissions.update_status' ) !== 'disabled' ) {
				$event = new \Voxel\Events\Posts\Post_Created_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				$event = new \Voxel\Events\Posts\Post_Submitted_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				$event = new \Voxel\Events\Posts\Post_Updated_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				$event = new \Voxel\Events\Posts\Post_Approved_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				$event = new \Voxel\Events\Posts\Post_Rejected_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				$event = new \Voxel\Events\Posts\Post_Expired_Event( $post_type );
				$events[ $event->get_key() ] = $event;
			}

			// Post_Reviews_Status_Created_Event
			if ( $post_type->get_setting( 'timeline.reviews' ) !== 'disabled' ) {
				$event = new \Voxel\Events\Timeline\Statuses\Post_Reviews_Status_Created_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				if ( $post_type->timeline->reviews_require_approval() ) {
					$event = new \Voxel\Events\Timeline\Statuses\Post_Reviews_Status_Approved_Event( $post_type );
					$events[ $event->get_key() ] = $event;
				}
			}

			// Post_Wall_Status_Created_Event
			if ( $post_type->get_setting( 'timeline.wall' ) !== 'disabled' ) {
				$event = new \Voxel\Events\Timeline\Statuses\Post_Wall_Status_Created_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				if ( $post_type->timeline->wall_posts_require_approval() ) {
					$event = new \Voxel\Events\Timeline\Statuses\Post_Wall_Status_Approved_Event( $post_type );
					$events[ $event->get_key() ] = $event;
				}
			}

			// Post_Timeline_Status_Created_Event
			if ( $post_type->get_setting( 'timeline.enabled' ) ) {
				$event = new \Voxel\Events\Timeline\Statuses\Post_Timeline_Status_Created_Event( $post_type );
				$events[ $event->get_key() ] = $event;

				if ( $post_type->timeline->timeline_posts_require_approval() ) {
					$event = new \Voxel\Events\Timeline\Statuses\Post_Timeline_Status_Approved_Event( $post_type );
					$events[ $event->get_key() ] = $event;
				}
			}

			foreach ( $post_type->get_fields() as $field ) {
				if (
					$field->get_type() === 'post-relation'
					&& $field->get_prop('allowed_authors') === 'any'
					&& $field->get_prop('require_author_approval') === 'always'
				) {
					$event = new \Voxel\Events\Post_Relations\Relation_Requested_Event( $field );
					$events[ $event->get_key() ] = $event;

					$event = new \Voxel\Events\Post_Relations\Relation_Approved_Event( $field );
					$events[ $event->get_key() ] = $event;

					$event = new \Voxel\Events\Post_Relations\Relation_Declined_Event( $field );
					$events[ $event->get_key() ] = $event;
				}
			}
		}

		$events = apply_filters( 'voxel/app-events/register', $events );

		static::$all = $events;
		return static::$all;
	}
}
