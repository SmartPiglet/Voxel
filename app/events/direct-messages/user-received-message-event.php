<?php

namespace Voxel\Events\Direct_Messages;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Received_Message_Event extends \Voxel\Events\Base_Event {

	public $message, $sender, $receiver, $user;

	public function prepare( $message_id ) {
		$message = \Voxel\Modules\Direct_Messages\Message::get( $message_id );
		if ( ! $message ) {
			throw new \Exception( 'Message not found.' );
		}

		$sender = $message->get_sender();
		$receiver = $message->get_receiver();
		if ( ! ( $sender && $receiver ) ) {
			throw new \Exception( 'Message not found.' );
		}

		$this->message = $message;
		$this->sender = $sender;
		$this->receiver = $receiver;
		$this->user = $receiver instanceof \Voxel\Post ? $receiver->get_author() : $receiver;
	}

	public function get_key(): string {
		return 'messages/user:received_message';
	}

	public function get_label(): string {
		return 'Messages: User received new message';
	}

	public function get_description(): ?string {
		return 'This event is throttled: it is triggered if at least 15 minutes have passed since the previous interaction. It does not run continuously while two users are chatting.';
	}

	public function get_category() {
		return 'messages';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->user;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'New message received from @message(sender.name)',
					'details' => function( $event ) {
						return [
							'message_id' => $event->message->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['message_id'] ?? null );
					},
					'links_to' => function( $event ) {
						return add_query_arg( 'chat', join( '', [
							$event->message->get_receiver_type() === 'post' ? $event->message->get_receiver_id() : '',
							$event->message->get_sender_type() === 'post' ? 'p' : 'u',
							$event->message->get_sender_id()
						] ), get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/') );
					},
				],
				'email' => [
					'enabled' => false,
					'subject' => 'New message received from @message(sender.name)',
					'message' => <<<HTML
					<strong>@message(receiver.name)</strong><br>
					You have received a new message from @message(sender.name):<br>
					<em>@message(content)</em>
					<a href="@message(sender.chat_link)">Open</a>
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
					'subject' => '@message(sender.name) sent a message to @message(receiver.name)',
					'details' => function( $event ) {
						return [
							'message_id' => $event->message->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['message_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->receiver->get_link(); },
					'image_id' => function( $event ) { return $event->receiver->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@message(sender.name) sent a message to @message(receiver.name)',
					'message' => <<<HTML
					@message(sender.name) sent a message to @message(receiver.name)
					<em>@message(content)</em>
					<a href="@message(sender.link)">View sender profile</a>
					<a href="@message(receiver.link)">View receiver profile</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->user = \Voxel\User::mock();
		$this->message = \Voxel\Modules\Direct_Messages\Message::mock();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ),
			'message' => \Voxel\Dynamic_Data\Group::Message( $this->message ),
		];
	}
}
