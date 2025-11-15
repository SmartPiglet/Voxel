<?php

namespace Voxel\Dynamic_Data\Data_Groups\Timeline;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Review_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'timeline/review';
	}

	protected static $instances = [];
	public static function get( \Voxel\Timeline\Status $review ): self {
		if ( ! array_key_exists( $review->get_id(), static::$instances ) ) {
			static::$instances[ $review->get_id() ] = new static( $review );
		}

		return static::$instances[ $review->get_id() ];
	}

	public $review;
	protected function __construct( \Voxel\Timeline\Status $review ) {
		$this->review = $review;
	}

	protected function properties(): array {
		return [
			'id' => Tag::Number('ID')->render( function() {
				return $this->review->get_id();
			} ),
			'content' => Tag::String('Content')->render( function() {
				return $this->review->get_content_for_display();
			} ),
			'created_at' => Tag::Date('Created at')->render( function() {
				return $this->review->get_created_at();
			} ),
			'link' => Tag::URL('Link')->render( function() {
				return $this->review->get_link();
			} ),
			'score' => Tag::Number('Score (1-5)')->render( function() {
				$score = $this->review->get_review_score();
				return $score === null ? null : ( $score + 3 );
			} ),
			'likes' => Tag::Object('Likes')->properties( function() {
				return [
					'count' => Tag::Number('Count')->render( function() {
						return $this->review->get_like_count();
					} ),
				];
			} ),
			'replies' => Tag::Object('Replies')->properties( function() {
				return [
					'count' => Tag::Number('Count')->render( function() {
						return $this->review->get_reply_count();
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
			':score' => 'score',
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Timeline\Status::mock() );
	}
}
