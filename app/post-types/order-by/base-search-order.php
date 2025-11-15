<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Search_Order {

	/**
	 * Post type object which this filter belongs to.
	 *
	 * @since 1.0
	 */
	protected $post_type;

	/**
	 * List of filter properties/configuration. Values below are available for
	 * all filter types, but there can be additional props for specific filter types.
	 *
	 * @since 1.0
	 */
	protected $props = [];

	public function __construct( $props = [] ) {
		$this->props = array_merge( [
			'type' => '',
		], $this->props );

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}
	}

	public static function preset( $props = [] ) {
		return ( new static( $props ) )->get_props();
	}

	public function get_models(): array {
		return [];
	}

	abstract public function get_label(): string;

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		//
	}

	public function index( \Voxel\Post $post ): array {
		return [];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args, array $clause_args ): void {
		//
	}

	/* Getters */
	public function get_type() {
		return $this->props['type'];
	}

	public function get_prop( $prop ) {
		if ( ! isset( $this->props[ $prop ] ) ) {
			return null;
		}

		return $this->props[ $prop ];
	}

	public function get_props() {
		return $this->props;
	}

	/* Setters */
	public function set_post_type( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	protected function get_source_model( $field_types ) {
		return function() use ( $field_types ) { ?>
			<div class="ts-form-group x-col-12">
				<label>Field:</label>
				<select v-model="clause.source">
					<option v-for="field in $root.getFieldsByType( <?= esc_attr( wp_json_encode( (array) $field_types ) ) ?> )" :value="field.key">
						{{ field.label }}
					</option>
				</select>
			</div>
		<?php };
	}

	protected function get_order_model() {
		return [
			'type' => \Voxel\Utils\Form_Models\Radio_Buttons_Model::class,
			'label' => 'Order',
			'classes' => 'x-col-12',
			'choices' => [
				'ASC' => __( 'Ascending', 'voxel-backend' ),
				'DESC' => __( 'Descending', 'voxel-backend' ),
			],
		];
	}
}
