<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Terms_Filter extends Base_Filter {

	protected $supported_conditions = ['taxonomy'];

	protected $props = [
		'type' => 'terms',
		'label' => 'Terms',
		'placeholder' => '',
		'source' => '',
		'orderby' => 'default',
		'multiple' => true,
		'operator' => 'or',
		'adaptive' => false,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),

			'source' => $this->get_source_model( 'taxonomy' ),
			'orderby' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Term order',
				'classes' => 'x-col-12',
				'choices' => [
					'default' => 'Default',
					'name' => 'Alphabetical',
				],
			],
			'multiple' => [
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Allow selection of multiple terms?',
				'classes' => 'x-col-12',
			],
			'operator' => [
				'v-if' => 'filter.multiple',
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Search behavior',
				'classes' => 'x-col-12',
				'choices' => [
					'or' => 'Match posts containing ANY of the selected terms',
					'and' => 'Match posts containing ALL of the selected terms',
				],
			],
			'adaptive' => [
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Enable adaptive display?',
				'infobox' => 'Updates terms after each search, hiding ones with no results and showing how many results each remaining term returns.',
				'classes' => 'x-col-12',
			],
			'icon' => $this->get_icon_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		global $wpdb;

		$term_slugs = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $term_slugs === null ) {
			return;
		}

		$taxonomy = \Voxel\Taxonomy::get( $this->_get_taxonomy() );
		if ( ! $taxonomy ) {
			return;
		}

		$_term_slugs = array_map( function( $term_slug ) {
			return '\''.esc_sql( sanitize_title( $term_slug ) ).'\'';
		}, $term_slugs );

		$_joined_terms = join( ',', $_term_slugs );
		$term_taxonomy_ids = $wpdb->get_col( $wpdb->prepare( <<<SQL
			SELECT tt.term_taxonomy_id FROM {$wpdb->terms} AS t
				LEFT JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				WHERE tt.taxonomy = %s AND t.slug IN ({$_joined_terms})
		SQL, $taxonomy->get_key() ) );

		$term_taxonomy_ids = array_filter( array_map( 'absint', $term_taxonomy_ids ) );
		$ids = array_unique( $term_taxonomy_ids );

		if ( empty( $ids ) ) {
			return;
		}

		$join_key = esc_sql( $this->db_key() );
		$ids_string = join( ',', $ids );

		if ( $this->get_prop('operator') === 'and' || ! $this->props['multiple'] ) {
			foreach ( $ids as $term_taxonomy_id ) {
				$query->where( $wpdb->prepare( <<<SQL
					EXISTS (
						SELECT 1 FROM {$wpdb->term_relationships}
						WHERE {$wpdb->term_relationships}.term_taxonomy_id = %d
						AND {$wpdb->term_relationships}.object_id = `{$query->table->get_escaped_name()}`.post_id
					)
				SQL, $term_taxonomy_id ) );
			}
		} else {
			$query->join( <<<SQL
				LEFT JOIN {$wpdb->term_relationships} AS `{$join_key}_tr`
					ON ( `{$query->table->get_escaped_name()}`.post_id = `{$join_key}_tr`.object_id )
			SQL );

			$query->where( "`{$join_key}_tr`.term_taxonomy_id IN ({$ids_string})" );
		}
	}

	protected function _get_taxonomy() {
		$field = $this->post_type->get_field( $this->props['source'] );
		return $field ? $field->get_prop('taxonomy') : '';
	}

	public function get_taxonomy(): ?\Voxel\Taxonomy {
		$field = $this->post_type->get_field( $this->props['source'] );
		if ( ! ( $field && $field->get_type() === 'taxonomy' ) ) {
			return null;
		}

		$taxonomy = \Voxel\Taxonomy::get( $field->get_prop('taxonomy') );
		if ( ! $taxonomy ) {
			return null;
		}

		return $taxonomy;
	}

	public function frontend_props() {
		$taxonomy = \Voxel\Taxonomy::get( $this->_get_taxonomy() );
		if ( ! $taxonomy ) {
			return [
				'terms' => [],
			];
		}

		$args = [
			'orderby' => $this->get_prop( 'orderby' ) === 'name' ? 'name' : 'default',
			'fields' => [ 'order', 'icon_raw' ],
		];

		if ( $this->elementor_config['hide_empty_terms'] === 'yes' || $this->is_adaptive() ) {
			$args['hide_empty'] = [ $this->post_type->get_key() ];
		}

		$transient_key = sprintf( 'filter:%s.%s.%s', $this->post_type->get_key(), $this->get_key(), 'v5' );
		$t = get_transient( $transient_key );

		$terms = ( is_array( $t ) && isset( $t['terms'] ) ) ? $t['terms'] : [];
		$time = ( is_array( $t ) && isset( $t['time'] ) ) ? $t['time'] : 0;
		$hash = ( is_array( $t ) && isset( $t['hash'] ) ) ? $t['hash'] : false;
		$new_hash = md5( wp_json_encode( $args ) );

		if ( ! $t || ( $time < $taxonomy->get_version() ) || $hash !== $new_hash ) {
			$terms = \Voxel\get_terms( $this->_get_taxonomy(), $args );
			set_transient( $transient_key, [
				'terms' => $terms,
				'time' => time(),
				'hash' => $new_hash,
			], 14 * DAY_IN_SECONDS );
			// \Voxel\log('from query');
		} else {
			// \Voxel\log('from cache');
		}

		$render_icons = function( &$terms ) use ( &$render_icons ) {
			foreach ( $terms as &$term ) {
				if ( ! empty( $term['icon_raw'] ) ) {
					$term['icon'] = \Voxel\get_icon_markup( $term['icon_raw'] );
				}

				if ( ! empty( $term['children'] ) ) {
					$render_icons( $term['children'] );
				}
			}
		};

		$render_icons( $terms );

		return [
			'terms' => $terms,
			'per_page' => apply_filters( 'voxel/terms-filter/per-page', 20, $this ),
			'selected' => $this->_get_selected_terms() ?: ((object) []),
			'taxonomy' => [
				'label' => $taxonomy->get_label(),
				'key' => $taxonomy->get_key(),
			],
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
			'multiple' => (bool) $this->props['multiple'],
			'display_as' => $this->elementor_config['display_as'] ?? 'popup',
		];
	}

	public function is_adaptive(): bool {
		return !! $this->props['adaptive'];
	}

	protected function _get_selected_terms() {
		if ( array_key_exists( 'selected_terms', $this->cache ) ) {
			return $this->cache['selected_terms'];
		}

		$value = $this->parse_value( $this->get_value() ) ?: [];
		if ( empty( $value ) ) {
			return null;
		}

		$terms = \Voxel\get_terms( $this->_get_taxonomy(), [
			'orderby' => 'name',
			'slug__in' => $value,
		] );

		$selected = [];
		foreach ( $terms as $term ) {
			$selected[ $term['slug'] ] = $term;
		}

		$this->cache['selected_terms'] = ! empty( $selected ) ? $selected : null;
		return $this->cache['selected_terms'];
	}

	public function parse_value( $value ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}

		$terms = explode( ',', trim( $value ) );
		$terms = array_filter( array_map( 'sanitize_title', $terms ) );

		if ( ! $this->props['multiple'] ) {
			$terms = isset( $terms[0] ) ? [ $terms[0] ] : [];
		}

		return ! empty( $terms ) ? $terms : null;
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'terms filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => 'Enter a comma-delimited list of terms to be selected by default',
			],
			'display_as' => [
				'label' => _x( 'Display as', 'terms_filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'popup' => _x( 'Popup', 'terms_filter', 'voxel-backend' ),
					'inline' => _x( 'Inline', 'terms_filter', 'voxel-backend' ),
					'buttons' => _x( 'Buttons', 'terms_filter', 'voxel-backend' ),
				],
				'conditional' => false,
			],
			'hide_empty_terms' => [
				'label' => _x( 'Hide empty terms?', 'terms_filter', 'voxel-backend' ),
				'description' => __( 'If checked, terms not assigned to any published post in this post type will not be listed', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'conditional' => $this->is_adaptive(),
			],
			'inl_term_columns' => [
				'label' => __( 'Additional settings', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'conditional' => false,
			],
			'inl_custom_menu_cols' => [
				'full_key' => $this->get_key().'__inl_custom_menu_cols',
				'label' => __( 'Multi column list', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'your-plugin' ),
				'label_off' => __( 'Hide', 'your-plugin' ),
				'return_value' => 'yes',
				'conditional' => false,
			],
			'inl_set_menu_cols' => [
				'label' => __( 'Menu columns', 'voxel-backend' ),
				'description' => __( 'We recommend increasing popup min width before if you plan to display the menu in multiple columns', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 6,
				'step' => 1,
				'default' => 1,
				'selectors' => [
					'{{CURRENT_ITEM}} .ts-term-dropdown-list' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); display: grid;',
				],
				'condition' => [ $this->get_key().'__inl_custom_menu_cols' => 'yes' ],
				'conditional' => false,
				'responsive' => true,
			],

			'inl_cols_cgap' => [
				'label' => __( 'Column gap', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{CURRENT_ITEM}} .ts-term-dropdown-list' => 'column-gap: {{SIZE}}{{UNIT}}!important;',
				],
				'condition' => [ $this->get_key().'__inl_custom_menu_cols' => 'yes' ],
				'conditional' => false,
				'responsive' => true,
			],


		];
	}
}
