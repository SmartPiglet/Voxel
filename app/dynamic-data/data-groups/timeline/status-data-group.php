<?php

namespace Voxel\Dynamic_Data\Data_Groups\Timeline;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Status_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'timeline/status';
	}

	protected static $instances = [];
	public static function get( \Voxel\Timeline\Status $status ): self {
		if ( ! array_key_exists( $status->get_id(), static::$instances ) ) {
			static::$instances[ $status->get_id() ] = new static( $status );
		}

		return static::$instances[ $status->get_id() ];
	}

	public $status;
	protected function __construct( \Voxel\Timeline\Status $status ) {
		$this->status = $status;
	}

	protected function properties(): array {
		return [
			'id' => Tag::Number('ID')->render( function() {
				return $this->status->get_id();
			} ),
			'content' => Tag::String('Content')->render( function() {
				return $this->status->get_content_for_display();
			} ),
			'created_at' => Tag::Date('Created at')->render( function() {
				return $this->status->get_created_at();
			} ),
			'link' => Tag::URL('Link')->render( function() {
				return $this->status->get_link();
			} ),
			'likes' => Tag::Object('Likes')->properties( function() {
				return [
					'count' => Tag::Number('Count')->render( function() {
						return $this->status->get_like_count();
					} ),
				];
			} ),
			'replies' => Tag::Object('Replies')->properties( function() {
				return [
					'count' => Tag::Number('Count')->render( function() {
						return $this->status->get_reply_count();
					} ),
				];
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
		return new static( \Voxel\Timeline\Status::mock() );
	}
}
