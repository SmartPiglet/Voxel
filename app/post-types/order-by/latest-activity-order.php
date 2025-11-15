<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Latest_Activity_Order extends Base_Search_Order {

	protected $props = [
		'type' => 'latest-activity',
		'activity' => 'wall',
		'include_replies' => false,
		'order' => 'DESC',
	];

	public function get_label(): string {
		return 'Latest activity';
	}

	public function get_models(): array {
		return [
			'activity' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Activity type',
				'classes' => 'x-col-12',
				'choices' => [
					'wall' => 'Wall posts',
					'reviews' => 'Reviews',
					'timeline' => 'Post timeline',
				],
			],
			'include_replies' => [
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Should status replies be considered as activity?',
				'classes' => 'x-col-12',
			],
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$table->add_column( sprintf( '`%s` DATETIME', esc_sql( $this->_get_column_key() ) ) );
		$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->_get_column_key() ) ) );
	}

	public function index( \Voxel\Post $post ): array {
		$default = strtotime( $post->get_date() );
		if ( $this->props['activity'] === 'timeline' ) {
			$stats = $post->repository->get_timeline_stats();
			$reply_stats = $post->repository->get_timeline_reply_stats();
		} elseif ( $this->props['activity'] === 'reviews' ) {
			$stats = $post->repository->get_review_stats();
			$reply_stats = $post->repository->get_review_reply_stats();
		} else {
			$stats = $post->repository->get_wall_stats();
			$reply_stats = $post->repository->get_wall_reply_stats();
		}

		$timestamp = strtotime( $stats['latest']['created_at'] ?? null );

		if ( $this->props['include_replies'] ) {
			$reply_timestamp = strtotime( $reply_stats['latest']['created_at'] ?? null );
			if ( $reply_timestamp !== null && ( $timestamp === null || $reply_timestamp > $timestamp ) ) {
				$timestamp = $reply_timestamp;
			}
		}

		return [
			$this->_get_column_key() => sprintf( '\'%s\'', esc_sql( date( 'Y-m-d H:i:s', $timestamp ?: $default ) ) ),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		$query->orderby( sprintf(
			'`%s` %s',
			$this->_get_column_key(),
			$this->props['order'] === 'ASC' ? 'ASC' : 'DESC'
		) );
	}

	private function _get_column_key() {
		return sprintf( 'activity_%s', $this->props['activity'] );
	}
}
