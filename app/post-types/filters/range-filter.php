<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Range_Filter extends Base_Filter {
	use Traits\Numeric_Filter_Helpers;

	protected $props = [
		'type' => 'range',
		'label' => 'Range',
		'placeholder' => '',
		'handles' => 'single',
		'compare' => 'in_range',
		'source' => '',
		'step_size' => 1,
		'range_start' => 0,
		'range_end' => 1000,
		'format_numeric' => true,
		'format_prefix' => '',
		'format_suffix' => '',
		'adaptive' => false,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'source' => $this->_get_source_model(),
			'handles' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Handles',
				'classes' => 'x-col-6',
				'choices' => [
					'single' => 'Single handle',
					'double' => 'Double handles',
				],
			],
			'compare' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Comparison',
				'classes' => 'x-col-6',
				'choices' => [
					'in_range' => 'Inside selected range',
					'outside_range' => 'Outside selected range',
				],
			],
			'range_start' => [
				'type' => \Voxel\Utils\Form_Models\Number_Model::class,
				'label' => 'Range start',
				'classes' => 'x-col-4',
				'step' => 'any',
			],
			'range_end' => [
				'type' => \Voxel\Utils\Form_Models\Number_Model::class,
				'label' => 'Range end',
				'classes' => 'x-col-4',
				'step' => 'any',
			],
			'step_size' => [
				'type' => \Voxel\Utils\Form_Models\Number_Model::class,
				'label' => 'Step size',
				'classes' => 'x-col-4',
				'min' => 0,
				'step' => 'any',
			],

			'format_numeric' => [
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Format displayed value',
				'classes' => 'x-col-12',
			],
			'format_prefix' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Prefix',
				'classes' => 'x-col-6',
			],
			'format_suffix' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Suffix',
				'classes' => 'x-col-6',
			],
			'adaptive' => [
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Enable adaptive display?',
				'infobox' => 'Retrieves min/max values automatically; slider boundaries are updated after each search',
				'classes' => 'x-col-12',
			],
			'description' => $this->get_description_model(),
			'icon' => $this->get_icon_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		if ( $this->props['handles'] === 'single' ) {
			$this->_query_single_handle( $query, $args );
		} else {
			$this->_query_double_handles( $query, $args );
		}
	}

	protected function _query_single_handle( \Voxel\Post_Types\Index_Query $query, array $args ) {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		$value = array_shift( $value );
		$operator = $this->props['compare'] === 'outside_range' ? '>=' : '<=';

		if ( $this->props['source'] === 'product->:minimum_price' ) {
			$query->join_price_index( $args );
			$query->where( sprintf(
				"%s {$operator} %d",
				$query->get_minimum_price_query( $args ),
				esc_sql( $value * 100 )
			) );
		} else {
			$value = $this->_prepare_value( $value );
			$db_key = $this->props['source'] === ':post->priority' ? 'priority' : $this->db_key();

			$query->where( sprintf(
				"`%s` {$operator} %d",
				esc_sql( $db_key ),
				$value
			) );
		}
	}

	protected function _query_double_handles( \Voxel\Post_Types\Index_Query $query, array $args ) {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		[ $start, $end ] = $value;

		if ( $this->props['source'] === 'product->:minimum_price' ) {
			$query->join_price_index( $args );
			$clauses = [];
			if ( is_numeric( $start ) ) {
				$operator = $this->props['compare'] === 'outside_range' ? '<=' : '>=';
				$clauses[] = sprintf(
					"%s {$operator} %d",
					$query->get_minimum_price_query( $args ),
					esc_sql( $start * 100 )
				);
			}

			if ( is_numeric( $end ) ) {
				if ( ! empty( $clauses ) ) {
					$clauses[] = $this->props['compare'] === 'outside_range' ? 'OR' : 'AND';
				}

				$operator = $this->props['compare'] === 'outside_range' ? '>=' : '<=';
				$clauses[] = sprintf(
					"%s {$operator} %d",
					$query->get_minimum_price_query( $args ),
					esc_sql( $end * 100 )
				);
			}

			if ( ! empty( $clauses ) ) {
				$query->where( sprintf(
					'( %s )',
					join( ' ', $clauses )
				) );
			}
		} else {
			$clauses = [];
			$db_key = $this->props['source'] === ':post->priority' ? 'priority' : $this->db_key();

			if ( is_numeric( $start ) ) {
				$operator = $this->props['compare'] === 'outside_range' ? '<=' : '>=';
				$clauses[] = sprintf(
					"`%s` {$operator} %d",
					esc_sql( $db_key ),
					$this->_prepare_value( $start )
				);
			}

			if ( is_numeric( $end ) ) {
				if ( ! empty( $clauses ) ) {
					$clauses[] = $this->props['compare'] === 'outside_range' ? 'OR' : 'AND';
				}

				$operator = $this->props['compare'] === 'outside_range' ? '>=' : '<=';
				$clauses[] = sprintf(
					"`%s` {$operator} %d",
					esc_sql( $db_key ),
					$this->_prepare_value( $end )
				);
			}

			if ( ! empty( $clauses ) ) {
				$query->where( sprintf(
					'( %s )',
					join( ' ', $clauses )
				) );
			}
		}
	}

	public function get_required_scripts(): array {
		return [ 'nouislider' ];
	}

	public function is_adaptive(): bool {
		return !! $this->props['adaptive'];
	}

	public function frontend_props() {
		if ( ! is_admin() ) {
			wp_print_styles( 'nouislider' );
		}

		$value = $this->parse_value( $this->get_value() );
		$display_as = $this->elementor_config['display_as'] ?? 'popup';
		if ( $display_as === 'minmax' && $this->props['handles'] !== 'double' ) {
			$display_as = 'popup';
		}

		return [
			'handles' => $this->props['handles'],
			'compare' => $this->props['compare'],
			'step_size' => abs( (float) $this->props['step_size'] ),
			'range_start' => (float) $this->props['range_start'],
			'range_end' => (float) $this->props['range_end'],
			'value' => $value !== null ? $value : [],
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
			'display_as' => $display_as,
			'format' => [
				'numeric' => $this->props['format_numeric'],
				'prefix' => $this->props['format_prefix'],
				'suffix' => $this->props['format_suffix'],
			],
		];
	}

	public function parse_value( $value ) {
		if ( $this->props['handles'] === 'single' ) {
			return is_numeric( $value ) ? [ (float) $value ] : null;
		} else {
			if ( empty( $value ) || strpos( $value, '..' ) === false ) {
				return null;
			}

			$values = explode( '..', $value );
			$start = (float) $values[0];
			$end = (float) $values[1];

			return [ $start, $end ];
		}
	}

	public function get_elementor_controls(): array {
		if ( $this->props['handles'] === 'single' ) {
			return [
				'value' => [
					'label' => _x( 'Default value', 'range filter', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
				],
				'display_as' => [
					'label' => _x( 'Display as', 'keywords_filter', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'popup' => _x( 'Slider Popup', 'keywords_filter', 'voxel-backend' ),
						'inline' => _x( 'Slider Inline', 'keywords_filter', 'voxel-backend' ),
					],
					'conditional' => false,
				],
			];
		}

		return [
			'start' => [
				'label' => _x( 'Default start value', 'range filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'classes' => 'ts-half-width',
			],
			'end' => [
				'label' => _x( 'Default end value', 'range filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'classes' => 'ts-half-width',
			],
			'display_as' => [
				'label' => _x( 'Display as', 'keywords_filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'popup' => _x( 'Popup', 'keywords_filter', 'voxel-backend' ),
					'inline' => _x( 'Inline', 'keywords_filter', 'voxel-backend' ),
					'minmax' => _x( 'Min/Max Inputs', 'keywords_filter', 'voxel-backend' ),
				],
				'conditional' => false,
			],
		];
	}

	public function get_default_value_from_elementor( $controls ) {
		if ( $this->props['handles'] === 'single' ) {
			return is_numeric( $controls['value'] ?? null ) ? $controls['value'] : null;
		}

		$start = $controls['start'] ?? null;
		$end = $controls['end'] ?? null;
		return ( is_numeric( $start ) && is_numeric( $end ) ) ? sprintf( '%s..%s', $start, $end ) : null;
	}
}
