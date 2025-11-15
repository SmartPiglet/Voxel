<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Taxonomy_Select_Model extends Base_Form_Model {

	protected $args = [
		'post_type' => null,
	];

	private $choices;

	protected function init() {
		$taxonomies = array_filter( \Voxel\Taxonomy::get_all(), function( $taxonomy ) {
			return in_array( $this->args['post_type'], $taxonomy->get_post_types(), true );
		} );

		$this->choices = array_map( function( $taxonomy ) {
			return [
				'label' => $taxonomy->get_label(),
				'key' => $taxonomy->get_key(),
			];
		}, $taxonomies );
	}

	protected function template() { ?>
		<taxonomy-select
			<?= $this->attributes('v-model') ?>
			:taxonomies="<?= esc_attr( wp_json_encode( $this->choices ) ) ?>"
		></taxonomy-select>
	<?php }

}
