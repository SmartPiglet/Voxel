<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Relations_Filter extends Base_Filter {

	protected $props = [
		'type' => 'relations',
		'label' => 'Post relation',
		'source' => 'post-relation',
		'manual_relation_key' => '',
		'manual_relation_type' => 'has_one',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),

			'source' => function() { ?>
				<div class="ts-form-group x-col-12">
					<label>Data source:</label>
					<select v-model="filter.source">
						<option v-for="field in $root.getFieldsByType('post-relation')" :value="field.key">
							{{ field.label }}
						</option>
						<option value="(manual)">Set manually</option>
					</select>
				</div>
			<?php },
			'manual_relation_key' => [
				'v-if' => 'filter.source === "(manual)"',
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Relation key',
				'classes' => 'x-col-6',
			],
			'manual_relation_type' => [
				'v-if' => 'filter.source === "(manual)"',
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Relation type',
				'classes' => 'x-col-6',
				'choices' => [
					'has_one' => 'Has one',
					'has_many' => 'Has many',
					'belongs_to_one' => 'Belongs to one',
					'belongs_to_many' => 'Belongs to many',
				],
			],
			'icon' => $this->get_icon_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		if ( $this->props['source'] === '(manual)' ) {
			$relation_key = esc_sql( $this->props['manual_relation_key'] );
			$column_key = in_array( $this->props['manual_relation_type'], [ 'has_one', 'has_many' ], true ) ? 'parent_id' : 'child_id';
		} else {
			$field = $this->post_type->get_field( $this->props['source'] );
			if ( ! ( $field && $field->get_type() === 'post-relation' ) ) {
				return;
			}

			$relation_key = esc_sql( $field->get_relation_key() );
			$column_key = in_array( $field->get_prop('relation_type'), [ 'has_one', 'has_many' ], true ) ? 'parent_id' : 'child_id';
		}


		global $wpdb;
		$join_key = esc_sql( $this->db_key() );
		$select_key = $column_key === 'child_id' ? 'parent_id' : 'child_id';

		$query->join( <<<SQL
			INNER JOIN {$wpdb->prefix}voxel_relations AS `{$join_key}` ON (
				`{$join_key}`.`{$select_key}` = {$value}
				AND `{$query->table->get_escaped_name()}`.post_id = `{$join_key}`.`{$column_key}`
				AND `{$join_key}`.relation_key = '{$relation_key}'
			)
		SQL );
	}

	public function parse_value( $value ) {
		if ( empty( $value ) || ! is_numeric( $value ) ) {
			return null;
		}

		return absint( $value );
	}

	public function get_related_post() {
		$value = $this->parse_value( $this->get_value() );
		if ( ! $value ) {
			return null;
		}

		$post = \Voxel\Post::get( $value );
		if ( ! ( $post && $post->post_type ) ) {
			return null;
		}

		if ( $this->props['source'] === '(manual)' ) {
			if ( ! $post->is_managed_by_voxel() ) {
				return null;
			}
		} else {
			$field = $this->post_type->get_field( $this->props['source'] );
			if ( ! ( $field && $field->get_type() === 'post-relation' ) ) {
				return null;
			}

			if ( ! in_array( $post->post_type->get_key(), $field->get_prop('post_types'), true ) ) {
				return null;
			}
		}

		return $post;
	}

	public function frontend_props() {
		$postdata = [];
		if ( $post = $this->get_related_post() ) {
			$postdata = [
				'title' => $post->get_display_name(),
				'logo' => $post->get_avatar_markup(),
			];
		}

		return [
			'post' => $postdata,
		];
	}

	public function get_elementor_controls(): array {
		return [
			'value' => [
				'label' => _x( 'Default value', 'relations filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
			],
		];
	}
}
