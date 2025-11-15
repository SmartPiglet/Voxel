<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Following_Post_Filter extends Base_Filter {

	protected $props = [
		'type' => 'following-post',
		'label' => 'Following post',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'icon' => $this->get_icon_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		global $wpdb;

		$posts_join_key = esc_sql( $this->db_key().'_p' );
		$followers_join_key = esc_sql( $this->db_key().'_f' );
		$post_id = absint( $value );

		$query->join( <<<SQL
			INNER JOIN {$wpdb->posts} AS `{$posts_join_key}` ON (
				`{$query->table->get_escaped_name()}`.post_id = `{$posts_join_key}`.ID
			)
		SQL );

		$query->join( $wpdb->prepare( <<<SQL
			INNER JOIN {$wpdb->prefix}voxel_followers AS `{$followers_join_key}` ON (
				`{$followers_join_key}`.follower_type = 'user'
				AND `{$followers_join_key}`.follower_id = `{$posts_join_key}`.post_author
				AND `{$followers_join_key}`.status = 1
				AND`{$followers_join_key}`.object_type = 'post'
				AND `{$followers_join_key}`.object_id = %d
			)
		SQL, $post_id ) );
	}

	public function parse_value( $value ) {
		if ( empty( $value ) || ! is_numeric( $value ) ) {
			return null;
		}

		return absint( $value );
	}

	public function frontend_props() {
		$value = $this->parse_value( $this->get_value() );

		return [
			'post_id' => $value,
		];
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'following filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
			],
		];
	}
}
