<?php

namespace Voxel\Modules\Elementor\Controllers;

use Voxel\Modules\Elementor as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Visibility_Controller extends \Voxel\Controllers\Base_Controller {

	protected $hidden_elements = [];

	protected function hooks() {
		$this->on( 'elementor/element/common/_section_style/after_section_end', '@register_settings', 90 );
		$this->on( 'elementor/element/section/section_advanced/after_section_end', '@register_settings', 90 );
		$this->on( 'elementor/element/column/section_advanced/after_section_end', '@register_settings', 90 );
		$this->on( 'elementor/element/container/section_layout/after_section_end', '@register_settings', 90 );
		$this->on( 'elementor/controls/register', '@register_settings_in_repeater', 1010 );
		$this->on( 'elementor/controls/register', '@register_settings_in_nested_repeater', 1020 );
		$this->on( 'elementor/widget/before_render_content', '@apply_widget_visibility_settings', 1000 );

		foreach ( [ 'container', 'section', 'column' ] as $element_type ) {
			$this->on( sprintf( 'elementor/frontend/%s/before_render', $element_type ), '@evaluate_visibility_rules', 100 );
			$this->filter( sprintf( 'elementor/frontend/%s/should_render', $element_type ), '@apply_visibility_settings', 1000, 2 );
		}
	}

	protected function register_settings( $element ) {
		$element->start_controls_section( '_voxel_visibility_settings', [
			'label' => __( 'Visibility', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );

		$element->add_control( '_voxel_visibility_behavior', [
			'label' => __( 'Element visibility', 'voxel-backend' ),
			'label_block' => true,
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'show',
			'options' => [
				'show' => __( 'Show this element if', 'voxel-backend' ),
				'hide' => __( 'Hide this element if', 'voxel-backend' ),
			],
		] );

		$element->add_control( '_voxel_visibility_rules', [
			'type' => 'voxel-visibility',
			'condition' => [ '_voxel_visibility_behavior!' => '' ],
		] );

		$element->end_controls_section();
	}

	protected function evaluate_visibility_rules( $element ) {
		$behavior = $element->get_settings( '_voxel_visibility_behavior' );
		$rules = $element->get_settings( '_voxel_visibility_rules' );
		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $rules );
		if ( $behavior === 'hide' ) {
			$should_render = $rules_passed ? false : true;
		} else {
			$should_render = $rules_passed ? true : false;
		}

		if ( ! $should_render ) {
			( \Closure::bind( function( $element ) {
				$element->children = [];
			}, null, \Elementor\Element_Base::class ) )( $element );
			$this->hidden_elements[ $element->get_id() ] = true;
		}
	}

	protected function apply_visibility_settings( $should_render, $element ) {
		if ( isset( $this->hidden_elements[ $element->get_id() ] ) ) {
			unset( $this->hidden_elements[ $element->get_id() ] );
			return false;
		}

		return $should_render;
	}

	protected function apply_widget_visibility_settings( $widget ) {
		$behavior = $widget->get_settings( '_voxel_visibility_behavior' );
		$rules = $widget->get_settings( '_voxel_visibility_rules' );

		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return;
		}

		if ( \Voxel\is_edit_mode() && current_user_can('administrator') ) {
			return;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $rules );
		if ( $behavior === 'hide' ) {
			$should_render = $rules_passed ? false : true;
		} else {
			$should_render = $rules_passed ? true : false;
		}

		if ( ! $should_render ) {
			$skin = new \Voxel\Widgets\Empty_Skin( $widget );
			$widget->add_skin( $skin );
			$widget->set_settings( '_skin', $skin->get_id() );
		}
	}

	protected function register_settings_in_repeater( $controls_manager ) {
		$repeater = $controls_manager->get_control('repeater');
		$this->_register_settings_in_repeater( $repeater );
	}

	protected function register_settings_in_nested_repeater( $controls_manager ) {
		$nested_elements_repeater = $controls_manager->get_control('nested-elements-repeater');
		if ( $nested_elements_repeater ) {
			$this->_register_settings_in_repeater( $nested_elements_repeater );
		}
	}

	protected function _register_settings_in_repeater( $repeater ) {
		$fields = $repeater->get_settings('fields');
		$fields['_voxel_visibility_behavior'] = [
			'name' => '_voxel_visibility_behavior',
			'type' => 'select',
			'label' => __( 'Row visibility', 'voxel-backend' ),
			'default' => 'show',
			'options' => [
				'show' => __( 'Show this row if', 'voxel-backend' ),
				'hide' => __( 'Hide this row if', 'voxel-backend' ),
			],
		];

		$fields['_voxel_visibility_rules'] = [
			'name' => '_voxel_visibility_rules',
			'type' => 'voxel-visibility',
			'condition' => [ '_voxel_visibility_behavior!' => '' ],
		];

		$repeater->set_settings( 'fields', $fields );
	}
}
