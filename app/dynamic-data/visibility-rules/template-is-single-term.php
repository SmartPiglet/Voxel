<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Template_Is_Single_Term extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'template:is_single_term';
	}

	public function get_label(): string {
		return _x( 'Is single term', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$taxonomies = array_filter( \Voxel\Taxonomy::get_all(), function( $taxonomy ) {
			return $taxonomy->is_public();
		} );

		$this->define_arg( 'taxonomy', [
			'type' => 'select',
			'label' => _x( 'Taxonomy', 'visibility rules', 'voxel-backend' ),
			'choices' => array_map( function( $taxonomy ) {
				return sprintf( '%s (%s)', $taxonomy->get_label(), $taxonomy->get_key() );
			}, $taxonomies ),
		] );

		$this->define_arg( 'term_id', [
			'type' => 'text',
			'label' => _x( 'Enter term ID or slug. Leave empty to match all terms in selected taxonomy.', 'visibility rules', 'voxel-backend' ),
		] );
	}

	public function evaluate(): bool {
		if ( $this->get_arg('taxonomy') === 'category' ) {
			return is_category( $this->get_arg('term_id') );
		} elseif ( $this->get_arg('taxonomy') === 'post_tag' ) {
			return is_tag( $this->get_arg('term_id') );
		} else {
			return is_tax( $this->get_arg('taxonomy'), $this->get_arg('term_id') );
		}
	}
}
