<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Filter extends Base_Filter {

	protected $props = [
		'type' => 'user',
		'label' => 'Author',
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

		$join_key = esc_sql( $this->db_key() );
		$value = absint( $value );

		$query->join( <<<SQL
			INNER JOIN {$wpdb->posts} AS `{$join_key}` ON (
				`{$query->table->get_escaped_name()}`.post_id = `{$join_key}`.ID
				AND `{$join_key}`.post_author = {$value}
			)
		SQL );
	}

	public function parse_value( $value ) {
		if ( empty( $value ) || ! is_numeric( $value ) ) {
			return null;
		}

		return absint( $value );
	}

	public function frontend_props() {
		$value = $this->parse_value( $this->get_value() );
		$userdata = [];
		if ( $user = \Voxel\User::get( $value ) ) {
			$userdata = [
				'name' => $user->get_display_name(),
				'avatar' => $user->get_avatar_markup(),
			];
		}

		return [
			'user' => $userdata,
		];
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'author filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
			],
		];
	}
}
