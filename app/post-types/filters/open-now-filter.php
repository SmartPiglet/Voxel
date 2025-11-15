<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Open_Now_Filter extends Base_Filter {

	protected $props = [
		'type' => 'open-now',
		'label' => 'Open Now',
		'placeholder' => '',
		'source' => 'work_hours',
		'convert_tz' => 'post',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'placeholder' => $this->get_placeholder_model(),
			'source' => $this->get_source_model( 'work-hours' ),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'convert_tz' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Match based on:',
				'classes' => 'x-col-12',
				'choices' => [
					'post' => 'Local time: The current time of each post based on the individual post timezone',
					'site' => 'Site time: The current time based on the site timezone',
				],
			],
			'description' => $this->get_description_model(),
			'icon' => $this->get_icon_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		if ( $this->props['convert_tz'] === 'site' ) {
			//
		} else {
			$table->add_column( '`timezone` VARCHAR(64) NOT NULL' );
			$table->add_key( 'KEY(`timezone`)' );
		}
	}

	public function index( \Voxel\Post $post ): array {
		if ( $this->props['convert_tz'] === 'site' ) {
			return [];
		} else {
			return [
				'timezone' => sprintf( '\'%s\'', esc_sql( $post->get_timezone()->getName() ) ),
			];
		}
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		global $wpdb;

		$join_key = esc_sql( $this->db_key() );
		$post_type_key = esc_sql( $this->post_type->get_key() );
		$field_key = esc_sql( $this->props['source'] );

		if ( $this->props['convert_tz'] === 'site' ) {
			$minute_of_week = \Voxel\get_minute_of_week( new \DateTime( 'now', wp_timezone() ) );
			$start_offset = "( `{$join_key}`.`start` )";
			$end_offset = "( `{$join_key}`.`end` )";
		} else {
			$minute_of_week = \Voxel\get_minute_of_week( \Voxel\utc() );
			$start_offset = "( `{$join_key}`.`start` - TIMESTAMPDIFF( MINUTE, UTC_TIMESTAMP(),
				CONVERT_TZ( UTC_TIMESTAMP(), \"UTC\", `{$query->table->get_escaped_name()}`.timezone )
			) )";

			$end_offset = "( `{$join_key}`.`end` - TIMESTAMPDIFF( MINUTE, UTC_TIMESTAMP(),
				CONVERT_TZ( UTC_TIMESTAMP(), \"UTC\", `{$query->table->get_escaped_name()}`.timezone )
			) )";
		}

		$query->join( <<<SQL
			INNER JOIN {$wpdb->prefix}voxel_work_hours AS `{$join_key}` ON (
				`{$query->table->get_escaped_name()}`.post_id = `{$join_key}`.post_id
				AND `{$join_key}`.post_type = '{$post_type_key}'
				AND `{$join_key}`.field_key = '{$field_key}'
				AND {$minute_of_week} BETWEEN {$start_offset} AND {$end_offset}
			)
		SQL );

		$query->groupby( "`{$query->table->get_escaped_name()}`.post_id" );
	}

	public function parse_value( $value ) {
		return absint( $value ) === 1 ? 1 : null;
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'open now filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'checked' => _x( 'Checked', 'open now filter', 'voxel-backend' ),
					'unchecked' => _x( 'Unchecked', 'open now filter', 'voxel-backend' ),
				],
			],
			'open_in_popup' => [
				'label' => _x( 'Display as button', 'open now filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'conditional' => false,
			],
		];
	}

	public function get_default_value_from_elementor( $controls ) {
		return ( $controls['value'] ?? null ) === 'checked' ? 1 : null;
	}

	public function frontend_props() {
		return [
			'openInPopup' => ( $this->elementor_config['open_in_popup'] ?? null ) === 'yes',
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
		];
	}
}
