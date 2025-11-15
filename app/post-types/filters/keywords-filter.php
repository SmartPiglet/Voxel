<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Keywords_Filter extends Base_Filter {

	protected $supported_conditions = ['text'];

	protected $supported_field_types = [
		'title',
		'description',
		'text',
		'texteditor',
		'location',
		'taxonomy',
		'profile-name',
	];

	protected $props = [
		'type' => 'keywords',
		'label' => 'Keywords',
		'placeholder' => '',

		// by default search for matches in title and description
		'sources' => [
			'title',
			'description',
		],
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-12' ]),
			'placeholder' => $this->get_model( 'placeholder', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),

			'sources' => function() { ?>
				<div class="ts-form-group x-col-12 ts-checkbox">
					<label>Look for matches in:</label>
					<div class="ts-checkbox-container">
						<label v-for="field in $root.getFieldsByType( <?= esc_attr( wp_json_encode( $this->supported_field_types ) ) ?> )"
							class="container-checkbox">
							{{ field.label }}
							<input type="checkbox" :value="field.key" v-model="filter.sources">
							<span class="checkmark"></span>
						</label>

						<template v-for="repeater in $root.getFieldsByType('repeater')">
							<keywords-source-repeater
								:keywords-filter="filter"
								:repeater="repeater"
								:key-base="repeater.key"
								:label-base="repeater.label"
								:field-types="<?= esc_attr( wp_json_encode( $this->supported_field_types ) ) ?>"
							></keywords-source-repeater>
						</template>
					</div>
				</div>
			<?php },
			'icon' => $this->get_model( 'icon', [ 'classes' => 'x-col-12' ]),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		$table->add_column( sprintf( '`%s` TEXT NOT NULL', esc_sql( $this->db_key() ) ) );
		$table->add_key( sprintf( 'FULLTEXT(`%s`)', esc_sql( $this->db_key() ) ) );
	}

	public function index( \Voxel\Post $post ): array {
		$values = [];
		foreach ( $this->props['sources'] as $field_key ) {
			$parts = explode( '->', $field_key );
			$original_parts = $parts;
			$field = $post->get_field( $parts[0] );
			if ( ! $field ) {
				continue;
			}

			if ( $field->get_type() === 'repeater' ) {
				$repeater_value = $field->get_value();
				if ( ! is_array( $repeater_value ) ) {
					continue;
				}

				array_shift( $parts );
				do {
					$field = $field->get_fields()[ $parts[0] ] ?? null;
					array_shift( $parts );
				} while ( $field && $field->get_type() === 'repeater' && count( $parts ) );

				if ( $field ) {
					$extracted_values = [];
					$this->_extract_values_from_repeater( $repeater_value, array_slice( $original_parts, 1 ), $extracted_values );

					if ( $extracted_values !== null ) {
						if ( $field->get_type() === 'taxonomy' ) {
							foreach ( $extracted_values as $field_value ) {
								$terms = \Voxel\Term::query( [
									'taxonomy' => $field->get_prop('taxonomy'),
									'hide_empty' => false,
									'orderby' => 'slug__in',
									'slug' => ! empty( $field_value ) ? $field_value : [''],
								] );

								$values[] = join( ' ', array_map( function( $term ) {
									return $term->get_label();
								}, $terms ) );
							}
						} elseif ( $field->get_type() === 'location' ) {
							foreach ( $extracted_values as $field_value ) {
								$values[] = $field_value['address'] ?? null;
							}
						} else {
							foreach ( $extracted_values as $field_value ) {
								$values[] = $field_value;
							}
						}
					}
				}
			} elseif ( $field->get_type() === 'taxonomy' ) {
				$values[] = join( ' ', array_map( function( $term ) {
					return $term->get_label();
				}, $field->get_value() ) );
			} elseif ( $field->get_type() === 'location' ) {
				$values[] = $field->get_value()['address'] ?? null;
			} else {
				$values[] = $field->get_value();
			}
		}

		$data = join( ' ', array_filter( $values, '\is_string' ) );
		$data = wp_strip_all_tags( $data );
		$data = $this->prepare_keywords_for_indexing( $data );

		return [
			$this->db_key() => sprintf( '\'%s\'', esc_sql( $data ) ),
		];
	}

	protected function prepare_keywords_for_indexing( string $str ): string {
		$keywords = preg_split( '/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY );
		$normalized_keywords = [];
		foreach ( $keywords as $keyword ) {
			// trim leading punctuation characters
			$normalized = preg_replace( '/^[[:punct:]]+/u', '', $keyword );

			// remove specified punct within the keyword
			$normalized = str_replace( [ '-', '.', '\'', '’', '‘', ',' ], '', $normalized );

			// transform other punct to indexable/queryable text
			$normalized = join( '', array_map( function( $char ) {
				if ( ctype_punct( $char ) && $char !== '_' ) {
					return sprintf( 'U_%X', mb_ord( $char, 'UTF-8' ) );
				}

				return $char;
			}, \Voxel\mb_str_split( $normalized ) ) );

			if ( ! empty( $normalized ) ) {
				$normalized_keywords[ $normalized ] = true;
			}
		}

		return join( ' ', array_keys( $normalized_keywords ) );
	}

	protected function prepare_keywords_for_querying( string $str ): string {
		$stopwords = \Voxel\get_stopwords();
		$min_word_length = \Voxel\get_keyword_minlength();
		$str = mb_substr( $str, 0, apply_filters( 'voxel/keyword-search/max-query-length', 128 ) );
		$keywords = preg_split( '/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY );

		$normalized_keywords = [];
		foreach ( $keywords as $keyword ) {
			// trim leading punctuation characters
			$normalized = preg_replace( '/^[[:punct:]]+/u', '', $keyword );

			// remove specified punct within the keyword
			$normalized = str_replace( [ '-', '.', '\'', '’', '‘', ',' ], '', $normalized );

			if ( mb_strlen( $normalized ) < $min_word_length ) {
				continue;
			}

			if ( isset( $stopwords[ strtolower( $normalized ) ] ) ) {
				continue;
			}

			// transform other punct to indexable/queryable text
			$normalized = join( '', array_map( function( $char ) {
				if ( ctype_punct( $char ) && $char !== '_' ) {
					return sprintf( 'U_%X', mb_ord( $char, 'UTF-8' ) );
				}

				return $char;
			}, \Voxel\mb_str_split( $normalized ) ) );

			if ( ! empty( $normalized ) ) {
				$normalized_keywords[ sprintf( '+%s*', $normalized ) ] = true;
			}
		}

		return join( ' ', array_keys( $normalized_keywords ) );
	}

	protected function _extract_values_from_repeater( $rows, $parts, &$extracted_values ) {
		$parts_count = count( $parts );
		if ( is_array( $rows ) && $parts_count >= 1 ) {
			foreach ( $rows as $row ) {
				if ( isset( $row[ $parts[0] ] ) ) {
					if ( $parts_count > 1 && is_array( $row[ $parts[0] ] ) ) {
						$this->_extract_values_from_repeater( $row[ $parts[0] ], array_slice( $parts, 1 ), $extracted_values );
					} elseif ( $parts_count === 1 ) {
						$extracted_values[] = $row[ $parts[0] ];
					}
				}
			}
		}
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$keywords = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $keywords === null ) {
			return;
		}

		$keywords = $this->prepare_keywords_for_querying( $keywords );
		if ( empty( $keywords ) ) {
			return;
		}

		$query->where( sprintf(
			'MATCH (`%s`) AGAINST (\'%s\' IN BOOLEAN MODE)',
			esc_sql( $this->db_key() ),
			esc_sql( $keywords )
		) );
	}

	public function orderby_relevance( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$keywords = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $keywords === null ) {
			return;
		}

		$orderby_key = $this->db_key().'_relevance';

		$query->select( sprintf(
			'MATCH (`%s`) AGAINST (\'%s\' IN BOOLEAN MODE) AS `%s`',
			esc_sql( $this->db_key() ),
			esc_sql( $keywords ),
			esc_sql( $orderby_key )
		) );

		$query->orderby( sprintf( '`%s` DESC', esc_sql( $orderby_key ) ) );
	}

	public function parse_value( $value ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}

		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			return null;
		}

		return $value;
	}

	public function frontend_props() {
		return [
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
			'display_as' => $this->elementor_config['display_as'] ?? 'popup',
		];
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'date filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::TEXT,
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
