<?php

namespace Voxel\Modules\Elementor\Controllers;

use Voxel\Modules\Elementor as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Loop_Controller extends \Voxel\Controllers\Base_Controller {

	protected $looped_elements = [];

	protected function hooks() {
		$this->on( 'elementor/element/common/_section_style/after_section_end', '@register_loop_settings', 100 );
		$this->on( 'elementor/element/section/section_advanced/after_section_end', '@register_loop_settings', 100 );
		$this->on( 'elementor/element/column/section_advanced/after_section_end', '@register_loop_settings', 100 );
		$this->on( 'elementor/element/container/section_layout/after_section_end', '@register_loop_settings', 100 );

		$this->on( 'elementor/controls/register', '@add_repeater_loop_setting', 1010 );
		$this->on( 'elementor/controls/register', '@add_nested_repeater_loop_setting', 1020 );

		foreach ( [ 'widget', 'column', 'section', 'container' ] as $element_type ) {
			$this->on( sprintf( 'elementor/frontend/%s/before_render', $element_type ), '@run_loops', 100 );
			$this->on( sprintf( 'elementor/frontend/%s/should_render', $element_type ), '@should_render', 100, 2 );
		}
	}

	protected function register_loop_settings( $element ) {
		$element->start_controls_section( '_voxel_loop_settings', [
			'label' => __( 'Loop element', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );

		$element->add_control( '_voxel_loop', [
			'label' => __( 'Loop this element based on', 'voxel-backend' ),
			'label_block' => true,
			'type' => 'voxel-loop',
			'default' => '',
		] );

		$element->add_control( '_voxel_loop_limit', [
			'label' => __( 'Loop limit', 'voxel-backend' ),
			'description' => __( 'If a hard limit is set, the loop will stop there even if there are additional items left', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'min' => 0,
			'default' => '',
			'classes' => 'hide-dtag-button',
			'condition' => [ '_voxel_loop!' => '' ],
		] );

		$element->add_control( '_voxel_loop_offset', [
			'label' => __( 'Loop offset', 'voxel-backend' ),
			'description' => __( 'Skip a set amount of items from the start of the loop', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'min' => 0,
			'default' => '',
			'classes' => 'hide-dtag-button',
			'condition' => [ '_voxel_loop!' => '' ],
		] );

		$element->end_controls_section();
	}

	protected function add_repeater_loop_setting( $controls_manager ) {
		$repeater = $controls_manager->get_control('repeater');
		$this->_add_repeater_loop_settings( $repeater );
	}

	protected function add_nested_repeater_loop_setting( $controls_manager ) {
		$nested_elements_repeater = $controls_manager->get_control('nested-elements-repeater');
		if ( $nested_elements_repeater ) {
			$this->_add_repeater_loop_settings( $nested_elements_repeater );
		}
	}

	private function _add_repeater_loop_settings( $repeater ) {
		$fields = $repeater->get_settings('fields');
		$fields[ '_voxel_loop' ] = [
			'name' => '_voxel_loop',
			'type' => 'voxel-loop',
			'label' => __( 'Loop repeater row', 'voxel-backend' ),
			'default' => '',
		];
		$fields[ '_voxel_loop_limit' ] = [
			'name' => '_voxel_loop_limit',
			'type' => 'number',
			'label' => __( 'Loop limit', 'voxel-backend' ),
			'description' => __( 'If a hard limit is set, the loop will stop there even if there are additional items left', 'voxel-backend' ),
			'default' => '',
			'min' => 0,
			'classes' => 'hide-dtag-button',
			'condition' => [ '_voxel_loop!' => '' ],
		];
		$fields[ '_voxel_loop_offset' ] = [
			'name' => '_voxel_loop_offset',
			'type' => 'number',
			'label' => __( 'Loop offset', 'voxel-backend' ),
			'description' => __( 'Skip a set amount of items from the start of the loop', 'voxel-backend' ),
			'default' => '',
			'min' => 0,
			'classes' => 'hide-dtag-button',
			'condition' => [ '_voxel_loop!' => '' ],
		];

		$repeater->set_settings( 'fields', $fields );
	}

	public function run_loops( $element ) {
		$loopable = $element->get_settings('_voxel_loop');
		if ( empty( $loopable ) ) {
			return;
		}

		if ( \Voxel\is_importing_elementor_template() ) {
			return;
		}

		if ( \Voxel\Dynamic_Data\Looper::is_running( $loopable ) ) {
			unset( $this->looped_elements[ $element->get_id() ] );
			return;
		}

		( \Closure::bind( function( $element ) {
			$element->children = [];
		}, null, \Elementor\Element_Base::class ) )( $element );

		\Voxel\Dynamic_Data\Looper::run( $loopable, [
			'limit' => $element->get_settings('_voxel_loop_limit'),
			'offset' => $element->get_settings('_voxel_loop_offset'),
			'callback' => function() use ( $element ) {
				$classname = get_class( $element );
				$loop_element = new $classname( $element->get_data(), [] );
				$loop_element->print_element();
			},
		] );

		$this->looped_elements[ $element->get_id() ] = true;
	}

	protected function should_render( $should_render, $element ) {
		if ( isset( $this->looped_elements[ $element->get_id() ] ) ) {
			return false;
		}

		return $should_render;
	}
}
