<?php

namespace Voxel\Post_Types\Fields\Post_Relation_Field;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Models {

	/**
	 * Form models for the Post Relation field configuration screen.
	 *
	 * @since 1.0
	 */
	public function get_models(): array {
		$post_types = [];
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$post_types[ $post_type->get_key() ] = $post_type->get_label();
		}

		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'relation_type' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Relation type',
				':class' => '{
					"vx-disabled": $root.config.settings.key === "collection" && field.key === "items",
					"x-col-8": ["has_many","belongs_to_many"].includes(field.relation_type),
					"x-col-12": !["has_many","belongs_to_many"].includes(field.relation_type),
				}',
				'choices' => [
					'has_one' => 'Has one',
					'has_many' => 'Has many',
					'belongs_to_one' => 'Belongs to one',
					'belongs_to_many' => 'Belongs to many',
				],
			],

			'max_count' => [
				'type' => \Voxel\Utils\Form_Models\Number_Model::class,
				'label' => 'Max relation count',
				'classes' => 'x-col-4',
				'v-if' => '["has_many","belongs_to_many"].includes(field.relation_type)',
			],

			'post_types' => [
				'type' => \Voxel\Utils\Form_Models\Checkboxes_Model::class,
				'label' => 'Related to',
				'choices' => $post_types,
				'classes' => 'x-col-12',
				'columns' => 'three',
			],

			'allowed_authors' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Limit post selections by author',
				'classes' => 'x-col-12',
				':class' => '{"vx-disabled": ($root.config.settings.key === "collection" && field.key === "items")}',
				'choices' => [
					'current_author' => 'Current author: User can pick from their posts only',
					'any' => 'Any: User can pick posts from any author',
				],
			],

			'require_author_approval' => [
				'type' => \Voxel\Utils\Form_Models\Select_Model::class,
				'label' => 'Require approval for posts that belong to a different author',
				'classes' => 'x-col-12',
				':class' => '{"vx-disabled": ($root.config.settings.key === "collection" && field.key === "items")}',
				'v-if' => '!repeater && field.allowed_authors === "any"',
				'choices' => [
					'never' => 'Never: Post relation will be saved right away',
					'always' => 'Always: Post relation will be saved after the author has manually approved it',
				],
			],

			'allowed_statuses' => [
				'type' => \Voxel\Utils\Form_Models\Checkboxes_Model::class,
				'label' => 'Enable non-published posts for selection',
				'description' => 'Only non-published posts that belong to the current author will be listed, regardless of the value set in "Limit post selections by author".',
				'classes' => 'x-col-12',
				'choices' => [
					'draft' => 'Draft',
					'pending' => 'Pending',
					'expired' => 'Expired',
					'unpublished' => 'Unpublished',
					'rejected' => 'Rejected',
					'future' => 'Scheduled',
				],
			],

			'use_custom_key' => [
				'v-if' => '!repeater',
				'type' => \Voxel\Utils\Form_Models\Switcher_Model::class,
				'label' => 'Use custom relation key',
				':class' => '{"vx-disabled": ($root.config.settings.key === "collection" && field.key === "items")}',
				'description' => 'By default, the field key will be used as the relation key. Enable this setting to use a custom relation key instead.',
				'classes' => 'x-col-12',
			],

			'custom_key' => [
				'v-if' => '!repeater && field.use_custom_key',
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Relation key',
				'classes' => 'x-col-12',
			],
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model( [
				'placeholder' => 'Enter related post ID(s). Separate multiple values with commas e.g. 171,250,265...',
			] ),
			'hidden' => $this->get_hidden_model(),
		];
	}
}
