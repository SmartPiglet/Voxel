<?php

namespace Voxel\Dynamic_Data\Data_Groups\Messages;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Message_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'message';
	}

	public $message;
	public function __construct( \Voxel\Modules\Direct_Messages\Message $message ) {
		$this->message = $message;
	}

	protected function properties(): array {
		return [
			'sender' => Tag::Object('Sender')->properties( function() {
				return [
					'name' => Tag::String('Name')->render( function() {
						return $this->message->get_sender_name();
					} ),
					'link' => Tag::URL('Permalink')->render( function() {
						return $this->message->get_sender_link();
					} ),
					'avatar' => Tag::Number('Avatar ID')->render( function() {
						return $this->message->get_sender()->get_avatar_id();
					} ),
					'chat_link' => Tag::URL('Chat link')->render( function() {
						return add_query_arg( 'chat', join( '', [
							$this->message->get_receiver_type() === 'post' ? $this->message->get_receiver_id() : '',
							$this->message->get_sender_type() === 'post' ? 'p' : 'u',
							$this->message->get_sender_id(),
						] ), get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/') );
					} ),
				];
			} ),
			'receiver' => Tag::Object('Receiver')->properties( function() {
				return [
					'name' => Tag::String('Name')->render( function() {
						return $this->message->get_receiver_name();
					} ),
					'link' => Tag::URL('Permalink')->render( function() {
						return $this->message->get_receiver_link();
					} ),
					'avatar' => Tag::Number('Avatar ID')->render( function() {
						return $this->message->get_receiver()->get_avatar_id();
					} ),
					'chat_link' => Tag::URL('Chat link')->render( function() {
						return add_query_arg( 'chat', join( '', [
							$this->message->get_receiver_type() === 'post' ? $this->message->get_receiver_id() : '',
							$this->message->get_sender_type() === 'post' ? 'p' : 'u',
							$this->message->get_sender_id(),
						] ), get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/') );
					} ),
				];
			} ),
			'content' => Tag::String('Content')->render( function() {
				return $this->message->get_content_for_display();
			} ),
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Modules\Direct_Messages\Message::mock() );
	}

}
