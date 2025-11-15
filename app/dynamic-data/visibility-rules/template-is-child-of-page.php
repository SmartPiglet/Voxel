<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Template_Is_Child_Of_Page extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'template:is_child_of_page';
	}

	public function get_label(): string {
		return _x( 'Is child of page', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		global $wpdb;

		$results = $wpdb->get_results( <<<SQL
			SELECT ID, post_title FROM {$wpdb->posts}
			WHERE post_type = 'page' AND post_status = 'publish'
		SQL, OBJECT_K );

		$pages = array_map( function( $row ) {
			return sprintf( '%s (#%d)', $row->post_title, $row->ID );
		}, $results );

		$this->define_arg( 'page_id', [
			'type'    => 'select',
			'label'   => _x( 'Select parent page', 'visibility rules', 'voxel-backend' ),
			'choices' => $pages,
		] );
	}

	public function evaluate(): bool {
		if ( ! is_page() ) {
			return false;
		}

		global $post;

		$selected_parent_id = $this->get_arg('page_id');

		$ancestors = get_post_ancestors( $post );

		return in_array( $selected_parent_id, $ancestors );
	}
}
