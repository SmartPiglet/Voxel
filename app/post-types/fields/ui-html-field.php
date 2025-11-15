<?php

namespace Voxel\Post_Types\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Ui_Html_Field extends Base_Post_Field {
	use Traits\Ui_Field;

	protected $props = [
		'type' => 'ui-html',
		'label' => 'UI HTML',
		'content' => '',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'content' => [
				'type' => \Voxel\Utils\Form_Models\Dtag_Model::class,
				'label' => 'Content',
				'classes' => 'x-col-12',
				':tag-groups' => '$root.uiHtmlDataGroups()',
			],
			'css_class' => $this->get_css_class_model(),
		];
	}

	protected function _render_dynamic_content() {
		if ( ! is_string( $this->props['content'] ) ) {
			return '';
		}

		if ( $this->is_new_post() ) {
			return \Voxel\render( $this->props['content'], [
				'post' => \Voxel\Dynamic_Data\Group::Noop(),
				'author' => \Voxel\Dynamic_Data\Group::User( \Voxel\get_current_user() ),
				'site' => \Voxel\Dynamic_Data\Group::Site(),
			] );
		} else {
			return \Voxel\render( $this->props['content'], [
				'post' => \Voxel\Dynamic_Data\Group::Post( $this->post ),
				'author' => \Voxel\Dynamic_Data\Group::User( $this->post->get_author() ),
				'site' => \Voxel\Dynamic_Data\Group::Site(),
			] );
		}
	}

	protected function frontend_props() {
		return [
			'content' => $this->_render_dynamic_content(),
		];
	}
}
