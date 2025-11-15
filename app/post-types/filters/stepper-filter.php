<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stepper_Filter extends Base_Filter {
	use Traits\Numeric_Filter_Helpers;

	protected $supported_conditions = ['number'];

	protected $props = [
		'type' => 'stepper',
		'label' => 'Stepper',
		'placeholder' => '',
		'source' => '',
		'step_size' => 1,
		'range_start' => 0,
		'range_end' => 1000,
		'compare' => 'equals',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'source' => $this->_get_source_model(),
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
			'compare' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Comparison',
				'classes' => 'x-col-12',
				'choices' => [
					'equals' => 'Equals selected value',
					'greater_than' => 'Greater than or equal to selected value',
					'less_than' => 'Less than or equal to selected value',
				],
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

		if ( $this->props['compare'] === 'greater_than' ) {
			$operator = '>=';
		} elseif ( $this->props['compare'] === 'less_than' ) {
			$operator = '<=';
		} else {
			$operator = '=';
		}

		$parts = explode( '->', $this->props['source'] );
		$field = $this->post_type->get_field( $parts[0] );
		if ( $field ) {
			if ( $field->get_type() === 'number' ) {
				$value = $this->_prepare_value( $value );

				$query->where( sprintf(
					"`%s` {$operator} %d",
					esc_sql( $this->db_key() ),
					$value
				) );
			} elseif ( $field->get_type() === 'product' && ! empty( $field->get_supported_product_types() ) ) {
				if ( ( $parts[1] ?? null ) === ':minimum_price' ) {
					$query->join_price_index( $args );
					$query->where( sprintf(
						"%s {$operator} %s",
						$query->get_minimum_price_query( $args ),
						esc_sql( $value * 100 )
					) );
				} elseif ( ( $parts[1] ?? null ) === 'addons' ) {
					$product_type = \Voxel\Product_Type::get( $parts[2] ?? null );
					if ( $product_type && $product_type->config('modules.addons.enabled') ) {
						$addons = $product_type->config( 'modules.addons.items' );
						foreach ( $addons as $addon ) {
							if ( $addon['type'] === 'numeric' && $addon['key'] === ( $parts[3] ?? null ) ) {
								$query->join_price_index( $args );

								$db_key__enabled = sprintf( '%s_addon_%s_enabled', $product_type->get_key(), $addon['key'] );
								$query->where( sprintf( 'pricing.`%s` = 1', esc_sql( $db_key__enabled ) ) );

								$db_key__max = sprintf( '%s_addon_%s_max', $product_type->get_key(), $addon['key'] );
								$query->where( sprintf(
									"pricing.`%s` {$operator} %s",
									esc_sql( $db_key__max ),
									esc_sql( $value )
								) );

								if ( ! isset( $GLOBALS['_addon_filters'] ) ) {
									$GLOBALS['_addon_filters'] = [];
								}

								$GLOBALS['_addon_filters'][ $addon['key'] ] = [
									'min' => $value,
								];
								break;
							}
						}
					}
				}
			}
		} else {
			if ( $parts[0] === ':post' ) {
				if ( ( $parts[1] ?? null ) === 'priority' ) {
					$value = $this->_prepare_value( $value );

					$query->where( sprintf(
						"`priority` {$operator} %d",
						$value
					) );
				}
			}
		}
	}

	public function frontend_props() {
		$step = abs( (float) $this->props['step_size'] );
		$precision = absint( strlen( substr( strrchr( $step, '.' ), 1 ) ) );
		$value = $this->parse_value( $this->get_value() );

		return [
			'value' => $value,
			'step_size' => $step,
			'precision' => $precision,
			'range_start' => (float) $this->props['range_start'],
			'range_end' => (float) $this->props['range_end'],
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
			'display_as' => $this->elementor_config['display_as'] ?? 'popup',
		];
	}

	public function parse_value( $value ) {
		if ( ! is_numeric( $value ) ) {
			return null;
		}

		return (float) $value;
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'range filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
			],
			'display_as' => [
				'label' => _x( 'Display as', 'keywords_filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'popup' => _x( 'Popup', 'keywords_filter', 'voxel-backend' ),
					'inline' => _x( 'Inline', 'keywords_filter', 'voxel-backend' ),
				],
				'conditional' => false,
			],
		];
	}
}
