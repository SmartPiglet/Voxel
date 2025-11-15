<?php

namespace Voxel\Modules\Elementor\Controllers;

use Voxel\Modules\Elementor as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Elementor_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return class_exists( '\Elementor\Plugin' );
	}

	protected function dependencies() {
		new Container_Controller;
		new Document_Controller;
		new Loop_Controller;
		new Widget_Controller;
		new Visibility_Controller;
	}

	protected function hooks() {
		$this->on( 'elementor/widgets/register', '@register_widgets', 1000 );
		$this->on( 'elementor/controls/register', '@register_custom_controls', 1000 );
		$this->on( 'admin_footer', '@load_backend_icon_picker', 100 );
		$this->on( 'elementor/document/after_save', '@save_voxel_config', 100, 2 );
		$this->on( 'voxel_ajax_elementor.save_temporary_config', '@save_temporary_voxel_config', 100, 2 );
		$this->on( 'elementor/elements/categories_registered', '@register_widget_categories' );
		$this->on( 'admin_footer', '@enqueue_line_awesome_in_backend' );
		$this->on( 'elementor/editor/after_enqueue_scripts', '@enqueue_line_awesome_in_backend' );
		$this->on( 'elementor/init', '@add_custom_tabs' );
		$this->on( 'elementor/editor/footer', '@cache_values_of_vx_post_select' );
		$this->on( 'elementor/editor/after_enqueue_scripts', '@enqueue_elementor_scripts' );

		$this->on( 'elementor/editor/init', '@set_current_post_in_editor' );
		$this->on( 'elementor/ajax/register_actions', '@set_current_post_in_editor' );

		$this->on( 'elementor/editor/init', '@set_current_term_in_editor' );
		$this->on( 'elementor/ajax/register_actions', '@set_current_term_in_editor' );

		$this->on( 'wp_head', '@enqueue_global_styles', 0 );
		$this->on( 'wp_head', '@print_dynamic_styles' );

		$this->filter( 'elementor/widget/print_template', '@handle_tags_in_editor' );

		$this->filter( 'elementor/icons_manager/additional_tabs', '@register_line_awesome_pack' );

		$this->filter( 'elementor/editor/templates', '@load_custom_editor_templates' );

		$this->filter( 'elementor/element/is_dynamic_content', '__return_true', 1000 );

		$this->filter( 'elementor/editor/localize_settings', '@editor_config' );
		$this->filter( 'parse_query', '@hide_voxel_templates_from_library' );
		$this->filter( 'wp_get_attachment_image_src', '@fix_editor_image_preview', 100, 4 );

		// css should be rendered for all possible visibility states
		$this->on( 'elementor/element/before_parse_css', function() { \Voxel\set_rendering_css(true); } );
		$this->on( 'elementor/element/parse_css', function() { \Voxel\set_rendering_css(false); } );



		$this->on( 'elementor/element/image/section_style_image/before_section_end', '@custom_image_controls' );
		$this->on( 'elementor/element/heading/section_title/before_section_end', '@custom_heading_controls' );
		$this->on( 'elementor/element/text-editor/section_editor/before_section_end', '@custom_text_editor_controls' );
		$this->on( 'elementor/element/icon/section_style_icon/before_section_end', '@custom_icon_controls' );

		$this->on( 'elementor/theme/register_locations', '@register_locations' );

		$this->on( 'elementor/page_templates/header-footer/before_content', '\Voxel\print_header' );
		$this->on( 'elementor/page_templates/header-footer/after_content', '\Voxel\print_footer' );

		// print form-group and popup templates only in "Elementor Canvas"
		$this->on( 'elementor/page_templates/canvas/before_content', '@print_required_templates_in_canvas' );

		if ( apply_filters( 'voxel/custom-elementor-settings-parser', true ) !== false ) {
			$this->on( 'elementor/frontend/before_render', '@custom_settings_parser' );
		}

		// workaround for https://github.com/elementor/elementor/issues/13038
		if ( apply_filters( 'voxel/elementor-missing-globals-bugfix', true ) !== false ) {
			$this->on( 'rest_api_init', '@missing_globals_bugfix' );
		}

		if ( apply_filters( 'voxel/elementor-customize-preview-bugfix', true ) !== false ) {
			add_action( 'init', function() {
				if ( is_customize_preview() ) {
					add_filter( 'elementor/frontend/builder_content/before_print_css', '__return_false' );
				}
			} );
		}

		add_action( 'elementor/core/files/clear_cache', function() {
			wp_cache_flush();
		} );

		// if ( apply_filters( 'voxel/delete-elementor-css-on-save', true ) !== false ) {
		// 	add_action( 'elementor/document/after_save', function( $document ) {
		// 		delete_post_meta_by_key( '_elementor_css' );
		// 	} );
		// }

		$this->on( 'manage_elementor_library_posts_custom_column', '@modify_template_type_markup', 100, 2 );
		$this->on( 'voxel_ajax_backend.elementor.modify_template_type', '@modify_template_type_handler' );

		$this->on( 'elementor/frontend/before_render', '@render_dynamic_background_images_inline' );

		$this->on( 'init', '@elementor_3_15_0_additional_custom_breakpoints_bugfix' );

		// elementor-only optimizations (already included in voxel-elements)
		// if ( ! function_exists( '\vxe_async_regenerate_css' ) ) {
		// 	$this->filter( 'elementor/documents/ajax_save/return_data', '@regenerate_template_css_on_save', 100, 2 );
		// 	$this->on( 'wp_ajax_vxe_regenerate_css', '@async_regenerate_css_handler' );
		// 	add_action( 'elementor/core/files/clear_cache', function() {
		// 		$this->_async_regenerate_css( get_option( 'elementor_active_kit' ) );
		// 	} );
		// }

		foreach ( [
			'elementor_experiment-container',
			'elementor_experiment-container_grid',
			'elementor_experiment-nested-elements',
		] as $feature ) {
			add_filter( 'pre_option_'.$feature, function() {
				return 'active';
			} );
		}


		add_filter( 'pre_option_elementor_experiment-e_element_cache', function() {
			return 'inactive';
		} );

		add_filter( 'pre_option_elementor_element_cache_ttl', function() {
			return 'disable';
		} );

		add_filter( 'pre_option_elementor_experiment-e_optimized_markup', function() {
			return 'active';
		} );

		// Elementor Pro: Prevent logging of "Cannot redeclare control..." error when
		// calling `$frontend->get_builder_content_for_display( $template_id );`
		add_filter( 'doing_it_wrong_trigger_error', function( $trigger_error, $function_name, $message, $version ) {
			if ( $function_name === 'Elementor\Controls_Manager::add_control_to_stack' ) {
				if ( str_contains( $message, 'Cannot redeclare control' ) ) {
					return false;
				}
			}

			return $trigger_error;
		}, 1000, 4 );

		if ( class_exists( '\QM_Collector_Doing_It_Wrong' ) ) {
			add_action( 'init', function() {
				$filter = $GLOBALS['wp_filter']['doing_it_wrong_run'] ?? null;
				if ( $filter === null || empty( $filter->callbacks[10] ) ) {
					return;
				}

				foreach ( $filter->callbacks[10] as $key => $handler ) {
					if (
						is_array( $handler['function'] )
						&& ( $handler['function'][0] ?? null ) instanceof \QM_Collector_Doing_It_Wrong
						&& ( $handler['function'][1] ?? null ) === 'action_doing_it_wrong_run'
					) {
						$filter->callbacks[10][ $key ]['function'] = function( $function_name, $message, $version ) use ( $handler ) {
							if ( $function_name === 'Elementor\Controls_Manager::add_control_to_stack' ) {
								if ( str_contains( $message, 'Cannot redeclare control' ) ) {
									return;
								}
							}

							call_user_func_array( $handler['function'], func_get_args() );
						};
						break;
					}
				}
			} );
		}
	}

	protected function register_widgets() {
		$manager = \Elementor\Plugin::instance()->widgets_manager;
		foreach ( \Voxel\config('widgets') as $widget ) {
			if ( $widget !== null ) {
				$manager->register( new $widget );
			}
		}
	}

	/**
	 * Allows for rendering dynamic tags in Elementor editor while the user is editing.
	 *
	 * @since 1.0
	 */
	protected function handle_tags_in_editor( $template ) {
		if ( empty( $template ) ) {
			return $template;
		}

		return '<# var settings = voxel_handle_tags(settings) #>'.$template;
	}

	protected function register_custom_controls( $controls_manager ) {
		$controls_manager->register( new Module\Custom_Controls\Repeater_Control );
		$controls_manager->register( new Module\Custom_Controls\Media_Control );
		$controls_manager->register( new Module\Custom_Controls\Gallery_Control );
		$controls_manager->register( new Module\Custom_Controls\Icons_Control );
		$controls_manager->register( new Module\Custom_Controls\Select2_Control );
		$controls_manager->register( new Module\Custom_Controls\Url_Control );
		$controls_manager->register( new Module\Custom_Controls\Relation_Control );
		$controls_manager->register( new Module\Custom_Controls\Text_Control );
		$controls_manager->register( new Module\Custom_Controls\Textarea_Control );
		$controls_manager->register( new Module\Custom_Controls\Number_Control );
		$controls_manager->register( new Module\Custom_Controls\Wysiwyg_Control );
		$controls_manager->register( new Module\Custom_Controls\Color_Control );
		$controls_manager->register( new Module\Custom_Controls\Visibility_Control );
		$controls_manager->register( new Module\Custom_Controls\Date_Time_Control );
		$controls_manager->register( new Module\Custom_Controls\Code_Control );
		$controls_manager->register( new Module\Custom_Controls\Post_Select_Control );
		$controls_manager->register( new Module\Custom_Controls\Term_Select_Control );
		$controls_manager->register( new Module\Custom_Controls\Loop_Control );
		$controls_manager->register( new Module\Custom_Controls\Nested_Repeater_Control );
	}

	protected function load_backend_icon_picker() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$config = \Elementor\Icons_Manager::get_icon_manager_tabs();
			require locate_template( 'templates/backend/icon-picker.php' );
		}
	}

	protected function register_line_awesome_pack( $packs ) {
		if ( \Voxel\get('settings.icons.line_awesome.enabled') ) {
			$base_url = trailingslashit( get_template_directory_uri() ).'assets/icons/line-awesome/';

			// @todo: minify line-awesome.css and line-awesome.js on production build
			$packs['la-regular'] = [
				'name' => 'la-regular',
				'label' => __( 'Line Awesome - Regular', 'voxel-backend' ),
				'url' => $base_url.'line-awesome.css',
				'enqueue' => [ $base_url.'line-awesome.css' ],
				'prefix' => 'la-',
				'displayPrefix' => 'lar',
				'labelIcon' => 'fab fa-font-awesome-alt',
				'ver' => '1.3.0',
				'fetchJson' => $base_url.'line-awesome-regular.js',
				'native' => false,
			];

			$packs['la-solid'] = [
				'name' => 'la-solid',
				'label' => __( 'Line Awesome - Solid', 'voxel-backend' ),
				'url' => $base_url.'line-awesome.css',
				'enqueue' => [ $base_url.'line-awesome.css' ],
				'prefix' => 'la-',
				'displayPrefix' => 'las',
				'labelIcon' => 'fab fa-font-awesome-alt',
				'ver' => '1.3.0',
				'fetchJson' => $base_url.'line-awesome-solid.js',
				'native' => false,
			];

			$packs['la-brands'] = [
				'name' => 'la-brands',
				'label' => __( 'Line Awesome - Brands', 'voxel-backend' ),
				'url' => $base_url.'line-awesome.css',
				'enqueue' => [ $base_url.'line-awesome.css' ],
				'prefix' => 'la-',
				'displayPrefix' => 'lab',
				'labelIcon' => 'fab fa-font-awesome-alt',
				'ver' => '1.3.0',
				'fetchJson' => $base_url.'line-awesome-brands.js',
				'native' => false,
			];
		}

		if ( \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_font_icon_svg' ) ) {
			$packs += $this->get_elementor_native_icons();
		}

		return $packs;
	}

	protected function get_elementor_native_icons() {
		$get_fa_asset_url = function( $filename, $ext_type = 'css', $add_suffix = true ) {
			static $is_test_mode = null;
			if ( null === $is_test_mode ) {
				$is_test_mode = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'ELEMENTOR_TESTS' ) && ELEMENTOR_TESTS;
			}

			$url = ELEMENTOR_ASSETS_URL . 'lib/font-awesome/' . $ext_type . '/' . $filename;
			if ( ! $is_test_mode && $add_suffix ) {
				$url .= '.min';
			}

			return $url . '.' . $ext_type;
		};

		$initial_tabs = [
			'fa-regular' => [
				'name' => 'fa-regular',
				'label' => 'Font Awesome - Regular',
				'url' => $get_fa_asset_url( 'regular' ),
				'enqueue' => [ $get_fa_asset_url( 'fontawesome' ) ],
				'prefix' => 'fa-',
				'displayPrefix' => 'far',
				'labelIcon' => 'fab fa-font-awesome-alt',
				'ver' => '5.15.3',
				'fetchJson' => $get_fa_asset_url( 'regular', 'js', false ),
				'native' => true,
			],
			'fa-solid' => [
				'name' => 'fa-solid',
				'label' => 'Font Awesome - Solid',
				'url' => $get_fa_asset_url( 'solid' ),
				'enqueue' => [ $get_fa_asset_url( 'fontawesome' ) ],
				'prefix' => 'fa-',
				'displayPrefix' => 'fas',
				'labelIcon' => 'fab fa-font-awesome',
				'ver' => '5.15.3',
				'fetchJson' => $get_fa_asset_url( 'solid', 'js', false ),
				'native' => true,
			],
			'fa-brands' => [
				'name' => 'fa-brands',
				'label' => 'Font Awesome - Brands',
				'url' => $get_fa_asset_url( 'brands' ),
				'enqueue' => [ $get_fa_asset_url( 'fontawesome' ) ],
				'prefix' => 'fa-',
				'displayPrefix' => 'fab',
				'labelIcon' => 'fab fa-font-awesome-flag',
				'ver' => '5.15.3',
				'fetchJson' => $get_fa_asset_url( 'brands', 'js', false ),
				'native' => true,
			],
		];

		$initial_tabs = apply_filters( 'elementor/icons_manager/native', $initial_tabs );

		return $initial_tabs;
	}

	protected function editor_config( $config ) {
		$post_id = \Elementor\Plugin::$instance->editor->get_post_id();
		$settings = \Voxel\get_custom_page_settings( $post_id );

		$config['voxel'] = [
			'relations' => (object) ( $settings['relations'] ?? [] ),
		];

		return $config;
	}

	protected function save_voxel_config( $document, $data ) {
		$config = json_decode( stripslashes( $_REQUEST['voxel'] ?? '' ), ARRAY_A );
		$settings_to_save = [];

		if ( ! empty( $config['relations'] ) && is_array( $config['relations'] ) ) {
			$settings_to_save['relations'] = $config['relations'];
		}

		if ( ! empty( $config['template_tabs'] ) && is_array( $config['template_tabs'] ) ) {
			$settings_to_save['template_tabs'] = $config['template_tabs'];
		}

		if ( ! empty( $settings_to_save ) ) {
			update_post_meta( $document->get_id(), '_voxel_page_settings', wp_slash( wp_json_encode( $settings_to_save ) ) );
		} else {
			delete_post_meta( $document->get_id(), '_voxel_page_settings' );
		}

		// always delete temporary config (used while in the editor)
		delete_post_meta( $document->get_id(), '_voxel_page_settings_tmp' );
	}

	protected function save_temporary_voxel_config() {
		try {
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Permission denied.', 'voxel-backend' ) );
			}

			$document_id = absint( $_REQUEST['document_id'] ?? null );
			if ( ! $document_id ) {
				throw new \Exception( __( 'Invalid request', 'voxel-backend' ) );
			}

			$config = json_decode( stripslashes( $_REQUEST['voxel'] ?? '' ), ARRAY_A );
			$settings_to_save = [];

			if ( ! empty( $config['relations'] ) && is_array( $config['relations'] ) ) {
				$settings_to_save['relations'] = $config['relations'];
			}

			if ( ! empty( $config['template_tabs'] ) && is_array( $config['template_tabs'] ) ) {
				$settings_to_save['template_tabs'] = $config['template_tabs'];
			}

			if ( ! empty( $settings_to_save ) ) {
				update_post_meta( $document_id, '_voxel_page_settings_tmp', wp_slash( wp_json_encode( $settings_to_save ) ) );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function register_widget_categories( $elements_manager ) {
		$elements_manager->add_category( 'voxel', [
			'title' => __( 'Voxel', 'voxel-backend' ),
			'icon' => 'eicon-plus',
		] );
	}

	protected function hide_voxel_templates_from_library( $query ) {
		global $typenow;
		if ( ! is_admin() || $typenow !== 'elementor_library' || ( $_GET['tabs_group'] ?? '' ) !== 'library' ) {
			return $query;
		}

		if ( isset( $_GET['elementor_library_type'] ) && $_GET['elementor_library_type'] !== 'page' ) {
			return $query;
		}

		if ( ! isset( $query->query_vars['tax_query'] ) ) {
			$query->query_vars['tax_query'] = [];
		}

		$query->query_vars['tax_query'][] = [
			'taxonomy' => 'elementor_library_category',
			'field' => 'slug',
			'terms' => 'voxel-template',
			'operator' => 'NOT IN',
		];

		return $query;
	}

	protected function enqueue_line_awesome_in_backend() {
		$base_url = trailingslashit( get_template_directory_uri() ).'assets/icons/line-awesome/';
		wp_enqueue_style( 'line-awesome', $base_url.'line-awesome.css', [], '1.3.0' );

		if ( is_admin() && \Voxel\is_elementor_pro_active() ) {
			if ( $fapro_kit_id = get_option( 'elementor_font_awesome_pro_kit_id', false ) ) {
				wp_enqueue_script( 'font-awesome-pro', sprintf( 'https://kit.fontawesome.com/%s.js', $fapro_kit_id ), [], ELEMENTOR_PRO_VERSION );
			}
		}
	}

	protected function add_custom_tabs() {
		\Elementor\Controls_Manager::add_tab( 'tab_voxel', 'Voxel' );
		\Elementor\Controls_Manager::add_tab( 'tab_inline', 'Inline' );
		\Elementor\Controls_Manager::add_tab( 'tab_general', 'General' );
		\Elementor\Controls_Manager::add_tab( 'tab_fields', 'Field style' );
	}

	protected function load_custom_editor_templates( $templates ) {
		foreach ( $templates as $i => $template ) {
			if ( $template === 'navigator' ) {
				unset( $templates[ $i ] );
				ob_start();
				include ELEMENTOR_PATH . 'includes/editor-templates/navigator.php';
				$content = ob_get_clean();

				$content = str_replace( '<span class="elementor-navigator__element__title__text">{{{ title }}}</span>', <<<HTML
					<span class="elementor-navigator__element__title__text">
						{{{ title }}}
					</span>

					<span onclick="requestAnimationFrame( () => {
						jQuery('.elementor-tab-control-tab_voxel').click();
						requestAnimationFrame( () => jQuery('.elementor-control-_voxel_visibility_settings:not(.e-open)').click() );
					} )" class="elementor-navigator__element__vx_visibility {{ Array.isArray( obj.settings?._voxel_visibility_rules ) && obj.settings._voxel_visibility_rules.length ? '' : 'hidden' }} _vx-el-nav-visibility-{{ obj.id }}">
						<i class="icon-element eicon-flow"></i>
					</span>
					<span onclick="requestAnimationFrame( () => {
						jQuery('.elementor-tab-control-tab_voxel').click();
						requestAnimationFrame( () => jQuery('.elementor-control-_voxel_loop_settings:not(.e-open)').click() );
					} )" class="elementor-navigator__element__vx_loops {{ typeof obj.settings?._voxel_loop === 'string' && obj.settings._voxel_loop.length ? '' : 'hidden' }} _vx-el-nav-loops-{{ obj.id }}">
						<i class="icon-element eicon-sort-amount-desc"></i>
					</span>
				HTML, $content );

				\Elementor\Plugin::$instance->common->add_template( $content, 'text' );
			}
		}

		return $templates;
	}

	protected function set_current_post_in_editor() {
		$template_id = null;
		if ( \Voxel\is_elementor_ajax() ) {
			$template_id = absint( $_REQUEST['editor_post_id'] ?? '' );
			$current_post = \Voxel\get_post_for_preview( $template_id );
			\Voxel\set_current_post( $current_post );
		} elseif ( \Voxel\is_edit_mode() ) {
			$template_id = absint( $_REQUEST['post'] ?? '' );
			$current_post = \Voxel\get_post_for_preview( $template_id );
			\Voxel\set_current_post( $current_post );
		}

		if ( $template_id !== null ) {
			$context = $this->_get_editing_context( (int) $template_id );
			add_filter( 'voxel/js/elementor-editor-config', function( $config ) use ( $context ) {
				$config['editing_context'] = $context;

				if ( $context['context'] === 'term' ) {
					$config['editing_groups'] = [
						'term' => [
							'label' => 'Term',
							'type' => 'term',
						],
						'user' => [
							'label' => 'User',
							'type' => 'user',
						],
						'site' => [
							'label' => 'Site',
							'type' => 'site',
						],
					];
				} else if ( $context['context'] === 'post' && $context['post_type'] !== null ) {
					$exporter = \Voxel\Dynamic_Data\Exporter::get();
					$exporter->add_group_by_key( 'post', $context['post_type'] );
					$config['editing_groups'] = [
						'post' => [
							'label' => 'Post',
							'type' => 'post_type:'.$context['post_type'],
						],
						'author' => [
							'label' => 'Author',
							'type' => 'user',
						],
						'user' => [
							'label' => 'User',
							'type' => 'user',
						],
						'site' => [
							'label' => 'Site',
							'type' => 'site',
						],
					];
				} else if ( $context['context'] === 'archive' ) {
					$config['editing_groups'] = [
						'user' => [
							'label' => 'User',
							'type' => 'user',
						],
						'site' => [
							'label' => 'Site',
							'type' => 'site',
						],
					];
				}

				return $config;
			} );
		}
	}

	protected function _get_editing_context( int $template_id ): array {
		$term_template_ids = array_merge(
			array_column( \Voxel\get_custom_templates()['term_single'], 'id' ),
			array_column( \Voxel\get_custom_templates()['term_card'], 'id' )
		);

		if ( in_array( $template_id, $term_template_ids, true ) ) {
			return [
				'context' => 'term',
			];
		}

		foreach ( \Voxel\Post_Type::get_all() as $post_type ) {
			if ( $post_type->get_templates()['archive'] === $template_id ) {
				return [
					'context' => 'archive',
					'post_type' => $post_type->get_key(),
				];
			}
		}

		$post_type = \Voxel\get_post_type_for_preview( $template_id );
		if ( $post_type ) {
			return [
				'context' => 'post',
				'post_type' => $post_type->get_key(),
			];
		}

		$template = \Voxel\Post::get( $template_id );
		if ( $template && $template->post_type && $template->post_type->get_key() === 'page' ) {
			return [
				'context' => 'post',
				'post_type' => 'page',
			];
		}

		return [
			'context' => 'post',
			'post_type' => null,
		];
	}

	protected function set_current_term_in_editor() {
		if ( \Voxel\is_elementor_ajax() ) {
			$template_id = absint( $_REQUEST['editor_post_id'] ?? '' );
			$current_term = $this->_get_current_term_in_editor( $template_id );
			\Voxel\set_current_term( $current_term );
		} elseif ( \Voxel\is_edit_mode() ) {
			$template_id = absint( $_REQUEST['post'] ?? '' );
			$current_term = $this->_get_current_term_in_editor( $template_id );
			\Voxel\set_current_term( $current_term );
		}
	}

	private function _get_current_term_in_editor( $template_id ) {
		$page_settings = (array) get_post_meta( $template_id, '_elementor_page_settings', true );
		$term_id = $page_settings['voxel_preview_term'] ?? null;
		if ( is_numeric( $term_id ) && ( $term = \Voxel\Term::get( $term_id ) ) ) {
			return $term;
		}

		$term = get_terms( [
			'taxonomy' => array_keys( \Voxel\Taxonomy::get_voxel_taxonomies() ),
			'number' => 1,
			'hide_empty' => false,
		] );

		return ! is_wp_error( $term ) && isset( $term[0] ) ? \Voxel\Term::get( $term[0] ) : \Voxel\Term::dummy();
	}

	/**
	 * Fixes error: Elementor\Images_Manager retrieves previews in editor for
	 * all media controls. Those media controls that are using dynamic tags haven't
	 * been parsed yet and an invalid value is passed to `wp_get_attachment_image_src`.
	 *
	 * @since 1.0
	 */
	protected function fix_editor_image_preview( $image, $attachment_id, $size, $icon ) {
		if ( is_string( $attachment_id ) && strncmp( $attachment_id, '@tags()', 7 ) === 0 && \Voxel\is_elementor_ajax() ) {
			$attachment_id = \Voxel\render( $attachment_id );
			$src = wp_get_attachment_image_src( absint( $attachment_id ), $size, $icon );
			return $src ? $src : [''];
		}

		return $image;
	}

	protected function enqueue_global_styles() {
		add_action( 'wp_enqueue_scripts', [ \Elementor\Plugin::$instance->frontend, 'enqueue_styles' ] );
	}

	protected function print_dynamic_styles() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$mobile_end = \Elementor\Plugin::$instance->breakpoints->get_breakpoints('mobile')->get_value();
			$tablet_start = $mobile_end + 1;
			$tablet_end = \Elementor\Plugin::$instance->breakpoints->get_breakpoints('tablet')->get_value();
			$desktop_start = $tablet_end + 1;
			echo <<<HTML
			<style type="text/css">
				@media screen and (max-width: {$mobile_end}px) { .vx-hidden-mobile { display: none !important; } }
				@media screen and (min-width: {$tablet_start}px) and (max-width: {$tablet_end}px) { .vx-hidden-tablet { display: none !important; } }
				@media screen and (min-width: {$desktop_start}px) { .vx-hidden-desktop { display: none !important; } }
			</style>
			HTML;
		}
	}

	protected function custom_image_controls( $widget ) {
		$widget->add_responsive_control( 'vx_paragraph_gap', [
			'label' => __( 'Aspect ratio', 'voxel-backend' ),
			'description' => __( 'Set image aspect ratio e.g 16/9', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::TEXT,
			'selectors' => [
				'{{WRAPPER}} img' => 'aspect-ratio: {{VALUE}}; object-fit: cover;',
				':where({{WRAPPER}}):not(.elementor-absolute)' => 'width: inherit;',
			],
		] );
	}

	protected function custom_heading_controls( $widget ) {
		$widget->add_responsive_control( 'vx_heading_nowrap', [
			'label' => __( 'Disable wrapping', 'voxel-backend' ),
			'description' => __( 'Disable text wrap and enable ellipsis for overflowing text', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'nowrap',
			'selectors' => [
				'{{WRAPPER}} .elementor-heading-title' => 'white-space: nowrap;text-overflow: ellipsis; overflow: hidden;',
			],
		] );
	}

	protected function custom_text_editor_controls( $widget ) {


		$widget->add_control(
			'ts_clamp',
			[
				'label' => __( 'Line clamp?', 'voxel-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'voxel-elementor' ),
				'label_off' => __( 'No', 'voxel-elementor' ),

			]
		);

		$widget->add_responsive_control( 'vx_line_clamp', [
			'label' => __( 'Line clamp', 'voxel-backend' ),
			'description' => __( 'Truncate text at a specific number of lines. Please set columns to default to use Line clamp', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::NUMBER,
			'min' => 1,
			'max' => 100,
			'step' => 1,
			'condition' => [ 'ts_clamp' => 'yes' ],
			'selectors' => [
				'{{WRAPPER}}' => 'display: -webkit-box;-webkit-line-clamp: {{VALUE}};-webkit-box-orient: vertical;  overflow: hidden;',
			],
		] );
	}

	protected function custom_icon_controls( $widget ) {
		$widget->add_control( 'vx_icon_retain_colors', [
			'label' => __( 'Retain internal colors', 'voxel-backend' ),
			'description' => __( 'Retain the original colors of the SVG defined in the code', 'voxel-backend' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'revert-layer',
			'selectors' => [
				'{{WRAPPER}} path' => 'fill: {{VALUE}};',
			],
		] );
	}

	protected function register_locations( $manager ) {
		$manager->register_location('header');
		$manager->register_location('footer');
	}

	protected function custom_settings_parser( $element ) {
		$get_settings = static::class.'::_get_active_settings';
		( \Closure::bind( function( $element ) use ( $get_settings ) {
			if ( ! $element->parsed_active_settings ) {
				$settings = \Voxel\is_elementor_pro_active()
					? $element->get_parsed_dynamic_settings()
					: $element->get_settings();

				$element->parsed_active_settings = $get_settings( $element, $settings, $element->get_controls() );
			}
		}, null, \Elementor\Controls_Stack::class ) )( $element );
	}

	public static function _get_active_settings( $element, $settings, $controls ) {
		// \Voxel\measure_start( 'elementor/get_active_settings' );
		$is_first_request = ! $settings && ! $element->active_settings;

		if ( ! $settings ) {
			if ( $element->active_settings ) {
				return $element->active_settings;
			}

			$settings = $element->get_controls_settings();

			$controls = $element->get_controls();
		}

		$active_settings = [];

		foreach ( $settings as $setting_key => $setting ) {
			if ( ! isset( $controls[ $setting_key ] ) ) {
				$active_settings[ $setting_key ] = $setting;

				continue;
			}

			$control = $controls[ $setting_key ];

			if ( static::_is_control_visible( $element, $control, $settings ) ) {
				$control_obj = \Elementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

				if ( $control_obj instanceof \Elementor\Control_Repeater ) {
					foreach ( $setting as & $item ) {
						$item = static::_get_active_settings( $element, $item, $control['fields'] );
					}
				}

				$active_settings[ $setting_key ] = $setting;
			} else {
				$active_settings[ $setting_key ] = null;
			}
		}

		if ( $is_first_request ) {
			$element->active_settings = $active_settings;
		}

		// \Voxel\measure_end( 'elementor/get_active_settings' );
		return $active_settings;
	}

	public static function _is_control_visible( $element, $control, $values = null ) {
		if ( null === $values ) {
			$values = $element->get_settings();
		}

		if ( ! empty( $control['conditions'] ) && ! \Elementor\Conditions::check( $control['conditions'], $values ) ) {
			return false;
		}

		if ( empty( $control['condition'] ) ) {
			return true;
		}

		foreach ( $control['condition'] as $condition_key => $condition_value ) {
			$parts = explode( '!', $condition_key );
			$keys = explode( '[', $parts[0] );

			$pure_condition_key = $keys[0];
			$condition_sub_key = isset( $keys[1] ) ? substr( $keys[1], 0, -1 ) : null;
			$is_negative_condition = isset( $parts[1] );

			if ( ! isset( $values[ $pure_condition_key ] ) || null === $values[ $pure_condition_key ] ) {
				return false;
			}

			$instance_value = $values[ $pure_condition_key ];

			if ( $condition_sub_key && is_array( $instance_value ) ) {
				if ( ! isset( $instance_value[ $condition_sub_key ] ) ) {
					return false;
				}

				$instance_value = $instance_value[ $condition_sub_key ];
			}

			if ( is_array( $condition_value ) && ! empty( $condition_value ) ) {
				$is_contains = in_array( $instance_value, $condition_value, true );
			} elseif ( is_array( $instance_value ) && ! empty( $instance_value ) ) {
				$is_contains = in_array( $condition_value, $instance_value, true );
			} else {
				$is_contains = $instance_value === $condition_value;
			}

			if ( $is_negative_condition && $is_contains || ! $is_negative_condition && ! $is_contains ) {
				return false;
			}
		}

		return true;
	}

	protected function print_required_templates_in_canvas() {
		require locate_template( 'templates/components/popup.php' );
		require locate_template( 'templates/components/form-group.php' );
	}

	protected function missing_globals_bugfix() {
		$controller = \Elementor\Plugin::$instance->data_manager_v2->controllers['globals'] ?? null;
		if ( $controller ) {
			$controller->endpoints['globals/colors'] = new class( $controller ) extends \Elementor\Core\Editor\Data\Globals\Endpoints\Colors {
				public function get_item( $id, $request ) {
					$items = $this->get_kit_items();
					return $items[ $id ] ?? $items[ array_key_first( $items ) ];
				}
			};

			$controller->endpoints['globals/typography'] = new class( $controller ) extends \Elementor\Core\Editor\Data\Globals\Endpoints\Typography {
				public function get_item( $id, $request ) {
					$items = $this->get_kit_items();
					return $items[ $id ] ?? $items[ array_key_first( $items ) ];
				}
			};
		}
	}

	protected function cache_values_of_vx_post_select() {
		$cache = [];
		global $_vx_post_select_values;
		if ( is_array( $_vx_post_select_values ) ) {
			$ids = array_filter( array_map( 'absint', $_vx_post_select_values ) );
			$cache = [];
			if ( ! empty( $ids ) ) {
				global $wpdb;
				$id_in = join( ',', $ids );
				$results = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID IN ($id_in)" );
				foreach ( $results as $result ) {
					$cache[ $result->ID ] = $result->post_title;
				}
			}
		}

		printf( '<script type="text/javascript">window.VX_Post_Select_Cache = %s</script>', wp_json_encode( (object) $cache ) );

		$cache = [];
		global $_vx_term_select_values;
		if ( is_array( $_vx_term_select_values ) ) {
			$ids = array_filter( array_map( 'absint', $_vx_term_select_values ) );
			$cache = [];
			if ( ! empty( $ids ) ) {
				global $wpdb;
				$id_in = join( ',', $ids );
				$results = $wpdb->get_results( "SELECT term_id, name FROM {$wpdb->terms} WHERE term_id IN ($id_in)" );
				foreach ( $results as $result ) {
					$cache[ $result->term_id ] = $result->name;
				}
			}
		}

		printf( '<script type="text/javascript">window.VX_Term_Select_Cache = %s</script>', wp_json_encode( (object) $cache ) );
	}

	protected function modify_template_type_markup( $column_name, $post_id ) {
		if ( $column_name === 'elementor_library_type' ) {
			$document_types = array_filter( \Elementor\Plugin::$instance->documents->get_document_types(), function( $type ) {
				return $type::get_property( 'show_in_library' );
			} );
			?>
			<details class="vx-el-modify-type">
				<summary class="button button-small">Modify type</summary>
				<ul>
					<?php foreach ( $document_types as $key => $type ):
						$handler = add_query_arg( [
							'action' => 'backend.elementor.modify_template_type',
							'template_id' => $post_id,
							'new_template_type' => $key,
							'_wpnonce' => wp_create_nonce( 'vx-backend' ),
						], home_url( sprintf( '/?vx=1' ) ) )
						?>
						<li>
							<a vx-action href="<?= $handler ?>"><?= $type::get_title() ?></a>
						</li>
					<?php endforeach ?>
				</ul>
			</details>
			<?php
		}
	}

	protected function modify_template_type_handler() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx-backend' ) ) {
				throw new \Exception( 'Could not process request.' );
			}

			$template_id = absint( $_GET['template_id'] ?? 0 );
			$template_type = sanitize_text_field( $_GET['new_template_type'] );
			$allowed_types = \Elementor\Plugin::$instance->documents->get_document_types();
			if ( get_post_type( $template_id ) !== 'elementor_library' ) {
				throw new \Exception( 'Template not found.' );
			}

			if ( ! isset( $allowed_types[ $template_type ] ) ) {
				throw new \Exception( 'Cannot assign this type to template.' );
			}

			update_post_meta( $template_id, '_elementor_template_type', $template_type );
			wp_set_object_terms( $template_id, $template_type, 'elementor_library_type' );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	/**
	 * Adds support for dynamic background images by applying the styles inline.
	 *
	 * @since 1.2.6
	 */
	protected function render_dynamic_background_images_inline( $element ) {
		if ( $bg = $element->get_settings('background_image') ) {
			if ( ! empty( $bg['id'] ) && strncmp( $bg['id'], '@tags()', 7 ) === 0 ) {
				$bg['id'] = \Voxel\render( $bg['id'] );
				$bg['_dynamic'] = true;
			}

			if ( ! empty( $bg['id'] ) && ! empty( $bg['_dynamic'] ) ) {
				$url = wp_get_attachment_image_url( $bg['id'], $bg['size'] ?? 'full' );
				$element->add_render_attribute(
					'_wrapper',
					'style',
					sprintf( 'background-image: url(\'%s\')', esc_url( $url ) )
				);
			}
		}
	}

	/**
	 * Fix for Elementor 3.15.0 (additional_custom_breakpoints) compatibility
	 * issue with \Voxel\Widgets\Print_Template widget.
	 *
	 * @since 1.2.7
	 */
	protected function elementor_3_15_0_additional_custom_breakpoints_bugfix() {
		if ( ! is_admin() ) {
			return;
		}

		global $wp_filter;
		if ( ! isset( $wp_filter['elementor/css_file/parse_content'] ) ) {
			return;
		}

		foreach ( $wp_filter['elementor/css_file/parse_content']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $index => $callback ) {
				$fn = $callback['function'];
				if ( $fn instanceof \Closure ) {
					$reflectionClosure = new \ReflectionFunction($fn);
					if ( $reflectionClosure->getClosureThis() instanceof \Elementor\Core\Breakpoints\Manager ) {
						unset( $wp_filter['elementor/css_file/parse_content']->callbacks[ $priority ][ $index ] );
					}
				}
			}
		}
	}

	protected function regenerate_template_css_on_save( $data, $document ) {
		if ( $post = $document->get_post() ) {
			$this->_async_regenerate_css( $post->ID );
		}

		return $data;
	}

	protected function _async_regenerate_css( $template_id ) {
		wp_remote_post( add_query_arg( [
			'action' => 'vxe_regenerate_css',
			'template_id' => $template_id,
			'_wpnonce' => wp_create_nonce( 'vxe_regenerate_css' ),
		], admin_url('admin-ajax.php') ), [
			'timeout'   => 0.01,
			'blocking'  => false,
			'cookies'   => $_COOKIE,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		] );
	}

	protected function async_regenerate_css_handler() {
		session_write_close();
		check_ajax_referer( 'vxe_regenerate_css', '_wpnonce' );

		$post = get_post( absint( $_GET['template_id'] ?? null ) );
		if ( $post ) {
			$post_css = \Elementor\Core\Files\CSS\Post::create( $post->ID );
			$post_css->update();
		}

		die;
	}

	protected function enqueue_elementor_scripts() {
		wp_enqueue_script( 'vue' );
		wp_enqueue_script( 'sortable' );
		wp_enqueue_script( 'vue-draggable' );
		wp_enqueue_script( 'vx:backend.js' );
		wp_enqueue_script( 'vx:elementor.js' );
		wp_enqueue_script( 'vx:commons.js' );

		$config = [
			'header_id' => \Voxel\get( 'templates.header' ),
			'footer_id' => \Voxel\get( 'templates.footer' ),
			'editing_groups' => [
				'post' => [
					'label' => 'Post',
					'type' => 'simple-post',
				],
				'author' => [
					'label' => 'Author',
					'type' => 'user',
				],
				'user' => [
					'label' => 'User',
					'type' => 'user',
				],
				'site' => [
					'label' => 'Site',
					'type' => 'site',
				],
			],
		];

		printf(
			'<script type="text/javascript">var Voxel_Elementor_Config = %s;</script>',
			wp_json_encode( (object) apply_filters( 'voxel/js/elementor-editor-config', $config ) )
		);

		// styles
		wp_enqueue_style( 'vx:backend.css' );
		wp_enqueue_style( 'vx:elementor.css' );
		wp_enqueue_style( 'fonts:jetbrains-mono' );
		$this->enqueue_elementor_dark_mode();

		require locate_template( 'templates/backend/dynamic-data/dynamic-data.php' );
	}


	private function enqueue_elementor_dark_mode() {
		$assets = trailingslashit( get_template_directory_uri() ).'assets/';
		$dist = trailingslashit( $assets ).'dist/';
		$ui_theme = \Elementor\Core\Settings\Manager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );
		if ( 'light' !== $ui_theme ) {
			$ui_theme_media_queries = 'all';

			if ( 'auto' === $ui_theme ) {
				$ui_theme_media_queries = '(prefers-color-scheme: dark)';
			}

			wp_enqueue_style(
				'voxel-elementor-dark-mode',
				$dist.'elementor-dark-mode.css',
				[],
				\Voxel\get_assets_version(),
				$ui_theme_media_queries
			);
		}
	}
}
