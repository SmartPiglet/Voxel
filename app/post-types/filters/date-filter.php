<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Filter extends Base_Filter {
	use Traits\Date_Filter_Helpers;

	protected $props = [
		'type' => 'date',
		'label' => 'Date',
		'source' => 'date',
		'input_mode' => 'date-range',
		'compare' => 'greater_than',
		'l10n_from' => 'From',
		'l10n_to' => 'To',
		'l10n_pickdate' => 'Choose date',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'description' => $this->get_model( 'description', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'source' => $this->_get_source_model(),
			'input_mode' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Input mode',
				'classes' => 'x-col-12',
				'choices' => [
					'date-range' => 'Date range',
					'single-date' => 'Single date',
				],
			],
			'compare' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'v-if' => 'filter.input_mode === \'single-date\'',
				'label' => 'Comparison',
				'classes' => 'x-col-12',
				'choices' => [
					'equals' => 'Equals selected date',
					'greater_than' => 'Greater than selected date',
					'less_than' => 'Less than selected date',
				],
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
			'icon' => $this->get_icon_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		if ( $this->props['source'] === ':post->date' ) {
			$datatype = 'DATETIME';
		} else {
			$field = $this->post_type->get_field( $this->props['source'] );
			$datatype = $field && $field->get_prop( 'enable_timepicker' ) ? 'DATETIME' : 'DATE';
		}

		$table->add_column( sprintf( '`%s` %s', esc_sql( $this->db_key() ), $datatype ) );
		$table->add_key( sprintf( 'KEY(`%s`)', esc_sql( $this->db_key() ) ) );
	}

	public function index( \Voxel\Post $post ): array {
		$field = $post->get_field( $this->props['source'] );
		if ( $field && $field->get_type() === 'date' ) {
			$timestamp = strtotime( $field->get_value() );
			$format = $field->get_prop( 'enable_timepicker' ) ? 'Y-m-d H:i:s' : 'Y-m-d';
			if ( $timestamp ) {
				$value = date( $format, $timestamp );
			}
		} else {
			if ( $this->props['source'] === ':post->date' ) {
				if ( $timestamp = strtotime( $post->get_date() ) ) {
					$value = date( 'Y-m-d H:i:s', $timestamp );
				}
			}
		}

		return [
			$this->db_key() => isset( $value ) ? sprintf( '\'%s\'', esc_sql( $value ) ) : 'NULL',
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
			if ( ! ( strtotime( $value['start'] ?? null ) && strtotime( $value['end'] ?? null ) ) ) {
				return;
			}
		}

		if ( $this->props['input_mode'] === 'single-date' ) {
			if ( $this->props['compare'] === 'greater_than' ) {
				$operator = '>=';
			} elseif ( $this->props['compare'] === 'less_than' ) {
				$operator = '<=';
			} else {
				$operator = '=';
			}

			$query->where( sprintf(
				"`%s` {$operator} '%s'",
				esc_sql( $this->db_key() ),
				esc_sql( $value['start'] )
			) );
		} else {
			$query->where( sprintf(
				"`%s` BETWEEN '%s' AND '%s'",
				esc_sql( $this->db_key() ),
				esc_sql( $value['start'] ),
				esc_sql( $value['end'] )
			) );
		}
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}

	public function _get_source_model() {
		return function() { ?>
			<div class="ts-form-group x-col-6">
				<label>Data source:</label>
				<select v-model="filter.source">
					<option v-for="field in $root.getFieldsByType('date')" :value="field.key">
						{{ field.label }}
					</option>
					<optgroup label="Post data">
						<option value=":post->date">Date published</option>
					</optgroup>
				</select>
			</div>
		<?php };
	}
}
