<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Recurring_Date_Filter extends Base_Filter {
	use Traits\Date_Filter_Helpers;

	protected $props = [
		'type' => 'recurring-date',
		'label' => 'Recurring Date',
		'source' => 'recurring-date',
		'input_mode' => 'date-range',
		'match_ongoing' => true,
		'l10n_from' => 'From',
		'l10n_to' => 'To',
		'l10n_pickdate' => 'Pick date',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'source' => $this->get_source_model( 'recurring-date' ),
			'input_mode' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Input mode',
				'classes' => 'x-col-6',
				'choices' => [
					'date-range' => 'Date range',
					'single-date' => 'Single date',
				],
			],
			'match_ongoing' => [
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Match ongoing dates',
				'classes' => 'x-col-12',
				'description' => 'Set whether to match dates that have already begun but haven\'t ended yet.',
			],

			'l10n_from' => [
				'v-if' => 'filter.input_mode === \'date-range\'',
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'From label',
				'classes' => 'x-col-6',
			],
			'l10n_to' => [
				'v-if' => 'filter.input_mode === \'date-range\'',
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'To label',
				'classes' => 'x-col-6',
			],
			
			'l10n_pickdate' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Placeholder',
				'classes' => 'x-col-12',
			],
			'description' => $this->get_description_model(),
			'icon' => $this->get_icon_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		// preset
		if ( is_string( $value ) ) {
			$preset = \Voxel\get_range_presets( $value );
			if ( ! $preset ) {
				return;
			}

			$value = $preset['callback']( \Voxel\now() );
			if ( ! ( strtotime( $value['start'] ?? '' ) && strtotime( $value['end'] ?? '' ) ) ) {
				return;
			}
		}

		// recurring dates are always stored in UTC format, so convert queried range from site tz to UTC
		$reference_date = new \DateTime( '2020-01-01 00:00:00', wp_timezone() );
		$timezone = new \DateTimeZone( $reference_date->format('P') );
		try {
			if ( ! str_starts_with( $value['start'], '1000-01-01' ) ) {
				$start_date = new \DateTime( $value['start'], $timezone );
				$start_date->setTimezone( new \DateTimeZone('UTC') );
				$value['start'] = $start_date->format( 'Y-m-d H:i:s' );
			}

			if ( ! str_starts_with( $value['end'], '9999-12-31' ) ) {
				$end_date = new \DateTime( $value['end'], $timezone );
				$end_date->setTimezone( new \DateTimeZone('UTC') );
				$value['end'] = $end_date->format( 'Y-m-d H:i:s' );
			}
		} catch ( \Exception $e ) {}

		global $wpdb;

		$range_start = esc_sql( $value['start'] );
		$range_end = esc_sql( $value['end'] );
		$join_key = esc_sql( $this->db_key() );
		$post_type_key = esc_sql( $this->post_type->get_key() );
		$field_key = esc_sql( $this->props['source'] );

		// querying all ranges
		if ( $value['start'] === '1000-01-01' ) {
			$where_clause = '';
			$current_start = \Voxel\Utils\Recurring_Date\get_current_start_query(
				\Voxel\utc()->format( 'Y-m-d' ),
				$value['end'],
				$this->props['match_ongoing']
			);
		} else {
			$where_clause = 'AND ('.\Voxel\Utils\Recurring_Date\get_where_clause(
				$value['start'],
				$value['end'],
				$this->props['input_mode'],
				$this->props['match_ongoing']
			).')';

			$current_start = \Voxel\Utils\Recurring_Date\get_current_start_query( $value['start'], $value['end'], $this->props['match_ongoing'] );
		}

		$query->join( <<<SQL
			INNER JOIN (
				SELECT post_id, {$current_start} FROM {$wpdb->prefix}voxel_recurring_dates
				WHERE `post_type` = '{$post_type_key}' AND `field_key` = '{$field_key}' {$where_clause}
			) AS `{$join_key}` ON `{$query->table->get_escaped_name()}`.post_id = `{$join_key}`.post_id
		SQL );

		$query->groupby( "`{$query->table->get_escaped_name()}`.post_id" );
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}
}
