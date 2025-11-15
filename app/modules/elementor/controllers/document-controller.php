<?php

namespace Voxel\Modules\Elementor\Controllers;

use Voxel\Modules\Elementor as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Document_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'elementor/documents/register_controls', '@register_document_settings', 100 );
		$this->filter( 'elementor/frontend/builder_content_data', '@restrict_content', 100, 2 );
	}

	protected function register_document_settings( $document ) {
		$document->start_controls_section( 'voxel_document_settings', [
			'label' => __( 'Voxel Settings', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );

		if ( $post_type = \Voxel\get_post_type_for_preview( $document->get_main_id() ) ) {
			$document->add_control( 'voxel_preview_post', [
				'label' => __( 'Post to use in preview', 'voxel-elementor' ),
				'type' => 'voxel-post-select',
				'post_type' => [ $post_type->get_key() ],
			] );

			$document->add_control( 'voxel_preview_post_apply', [
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<a href="#" onclick="voxel_reload_editor(); return false;" class="elementor-button">Apply changes</a>',
			] );
		} elseif ( (int) $document->get_main_id() === (int) \Voxel\get( 'templates.post_stats' ) ) {
			$document->add_control( 'voxel_preview_post', [
				'label' => __( 'Post to use in preview', 'voxel-elementor' ),
				'type' => 'voxel-post-select',
				'post_type' => (array) \Voxel\get( 'settings.stats.enabled_post_types' ),
			] );

			$document->add_control( 'voxel_preview_post_apply', [
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<a href="#" onclick="voxel_reload_editor(); return false;" class="elementor-button">Apply changes</a>',
			] );
		} elseif ( in_array( (int) $document->get_main_id(), array_merge(
			array_column( \Voxel\get_custom_templates()['term_single'], 'id' ),
			array_column( \Voxel\get_custom_templates()['term_card'], 'id' )
		), true ) ) {
			$document->add_control( 'voxel_preview_term', [
				'label' => __( 'Term to use in preview', 'voxel-elementor' ),
				'type' => 'voxel-term-select',
				'taxonomy' => array_keys( \Voxel\Taxonomy::get_voxel_taxonomies() ),
			] );

			$document->add_control( 'voxel_preview_post_apply', [
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<a href="#" onclick="voxel_reload_editor(); return false;" class="elementor-button">Apply changes</a>',
			] );
		}

		$document->add_control( 'voxel_hide_header', [
			'label' => __( 'Hide header on this page', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
			'label_on' => __( 'Hide', 'voxel-backend' ),
			'label_off' => __( 'Show', 'voxel-backend' ),
		] );

		$document->add_control( 'voxel_hide_footer', [
			'label' => __( 'Hide footer on this page', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
			'label_on' => __( 'Hide', 'voxel-backend' ),
			'label_off' => __( 'Show', 'voxel-backend' ),
		] );

		

		$document->end_controls_section();

		$document->start_controls_section( '_voxel_visibility_settings', [
			'label' => __( 'Visibility', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );

		$document->add_control( '_voxel_visibility_behavior', [
			'label' => __( 'Document visibility', 'voxel-backend' ),
			'label_block' => true,
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'show',
			'options' => [
				'show' => 'Show this document if',
				'hide' => 'Hide this document if',
			],
		] );

		$document->add_control( '_voxel_visibility_rules', [
			'type' => 'voxel-visibility',
		] );

		$document->add_control( '_voxel_visibility_hidden', [
			'label' => __( 'When document is restricted, display:', 'voxel-backend' ),
			'label_block' => true,
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'none',
			'options' => [
				'none' => 'Blank',
				'auth' => 'Auth template',
				'restricted' => 'Restricted page template',
				'404' => '404 page template',
				'custom' => 'Custom template',
			],
		] );

		$document->add_control( '_voxel_visibility_hidden_custom', [
			'label' => __( 'Template ID', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'condition' => [ '_voxel_visibility_hidden' => 'custom' ],
		] );

		$document->end_controls_section();

		$document->start_controls_section( '_voxel_sticky_settings', [
			'label' => __( 'Sticky position', 'voxel-backend' ),
			'tab' => 'tab_voxel',
		] );

			if ( ! ( method_exists( $document, '_is_runtime' ) && $document->_is_runtime() ) ) {
				$template_selector = '.elementor.elementor-'.$document->get_id();

				$document->add_control(
					'sticky_container_desktop',
					[
						'label' => __( 'Sticky on desktop', 'voxel-backend' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'sticky'  => __( 'Enable', 'voxel-backend' ),
							'initial' => __( 'Disable', 'voxel-backend' ),
						],

						'selectors' => [
							'(desktop)'.$template_selector => 'position: {{VALUE}}',
						],
					]
				);

				$document->add_control(
					'sticky_container_tablet',
					[
						'label' => __( 'Sticky on tablet', 'voxel-backend' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'sticky'  => __( 'Enable', 'voxel-backend' ),
							'initial' => __( 'Disable', 'voxel-backend' ),
						],

						'selectors' => [
							'(tablet)'.$template_selector => 'position: {{VALUE}}',
						],
					]
				);

				$document->add_control(
					'sticky_container_mobile',
					[
						'label' => __( 'Sticky on mobile', 'voxel-backend' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'sticky'  => __( 'Enable', 'voxel-backend' ),
							'initial' => __( 'Disable', 'voxel-backend' ),
						],

						'selectors' => [
							'(mobile)'.$template_selector => 'position: {{VALUE}}',
						],
					]
				);


				$document->add_responsive_control( 'sticky_top_value', [
					'label' => __( 'Top', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'vh'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						$template_selector => 'top: {{SIZE}}{{UNIT}};',
					],
				] );

				$document->add_responsive_control( 'sticky_left_value', [
					'label' => __( 'Left', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'vh'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						$template_selector => 'left: {{SIZE}}{{UNIT}};',
					],
				] );

				$document->add_responsive_control( 'sticky_right_value', [
					'label' => __( 'Right', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'vh'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						$template_selector => 'right: {{SIZE}}{{UNIT}};',
					],
				] );

				$document->add_responsive_control( 'sticky_bottom_value', [
					'label' => __( 'Bottom', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'vh'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						$template_selector => 'bottom: {{SIZE}}{{UNIT}};',
					],
				] );

				$document->add_control( 'sticky_z_index', [
					'label' => __( 'Z-index', 'voxel-backend' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 500,
							'step' => 1,
						],
					],
					'selectors' => [
						$template_selector => 'z-index: {{SIZE}}',
					],
				] );
			}

		$document->end_controls_section();
	}

	protected function restrict_content( $data, $post_id ) {
		$template_type = get_post_meta( $post_id, '_elementor_template_type', true );

		// incompatible with elementor-pro popups
		if ( $template_type === 'popup' ) {
			return $data;
		}

		$behavior = \Voxel\get_page_setting( '_voxel_visibility_behavior', $post_id );
		$rules = \Voxel\get_page_setting( '_voxel_visibility_rules', $post_id );
		$on_hidden = \Voxel\get_page_setting( '_voxel_visibility_hidden', $post_id );

		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return $data;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $rules );
		if ( $behavior === 'hide' ) {
			$should_render = $rules_passed ? false : true;
		} else {
			$should_render = $rules_passed ? true : false;
		}

		if ( $should_render ) {
			return $data;
		}

		add_filter( 'elementor/frontend/the_content', [ $this, 'show_restricted_template' ], 100 );

		return [ [ 'elType' => 'voxel-empty-element' ] ];
	}

	public function show_restricted_template( $content ) {
		remove_filter( 'elementor/frontend/the_content', [ $this, 'show_restricted_template' ], 100 );

		$post_id = \Elementor\Plugin::$instance->documents->get_current()->get_post()->ID;
		$on_hidden = \Voxel\get_page_setting( '_voxel_visibility_hidden', $post_id );
		$custom_template_id = \Voxel\get_page_setting( '_voxel_visibility_hidden_custom', $post_id );

		$getTemplate = function( $template_id ) {
			ob_start();
			\Voxel\print_template( $template_id );
			return ob_get_clean();
		};

		if ( $on_hidden === 'none' ) {
			return '';
		} elseif ( $on_hidden === 'auth' ) {
			// make sure user is redirected to the same page after login
			$_REQUEST['redirect_to'] = \Voxel\get_current_url();
			return $getTemplate( \Voxel\get('templates.auth') );
		} elseif ( $on_hidden === 'restricted' ) {
			return $getTemplate( \Voxel\get('templates.restricted') );
		} elseif ( $on_hidden === '404' ) {
			return $getTemplate( \Voxel\get('templates.404') );
		} else {
			if ( absint( $custom_template_id ) === absint( $post_id ) ) {
				return '';
			}

			return $getTemplate( $custom_template_id );
		}
	}
}
