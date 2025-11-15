<?php

namespace Voxel\Dynamic_Data\Data_Groups\Timeline;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Reply_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'timeline/reply';
	}

	protected static $instances = [];
	public static function get( \Voxel\Timeline\Reply $reply ): self {
		if ( ! array_key_exists( $reply->get_id(), static::$instances ) ) {
			static::$instances[ $reply->get_id() ] = new static( $reply );
		}

		return static::$instances[ $reply->get_id() ];
	}

	public $reply;
	protected function __construct( \Voxel\Timeline\Reply $reply ) {
		$this->reply = $reply;
	}

	protected function properties(): array {
		return [
			'id' => Tag::Number('ID')->render( function() {
				return $this->reply->get_id();
			} ),
			'content' => Tag::String('Content')->render( function() {
				return $this->reply->get_content_for_display();
			} ),
			'created_at' => Tag::Date('Created at')->render( function() {
				return $this->reply->get_created_at();
			} ),
			'link' => Tag::URL('Link')->render( function() {
				return $this->reply->get_link();
			} ),
		];
	}

	protected function aliases(): array {
		return [
			':id' => 'id',
			':content' => 'content',
			':created_at' => 'created_at',
			':link' => 'link',
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Timeline\Reply::mock() );
	}
}
