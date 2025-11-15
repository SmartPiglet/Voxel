<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Template_Tabs extends Base_Widget {

	public function get_name() {
		return 'ts-template-tabs';
	}

	public function get_title() {
		return __( 'Template tabs (deprecated)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 'ts_general', [
			'label' => __( 'General', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new \Elementor\Repeater;

		$repeater->add_control( 'template_id', [
			'label' => __( 'Template', 'voxel-elementor' ),
			'description' => 'Select the template that will be used to render the contents of this tab',
			'type' => 'voxel-post-select',
			'post_type' => [ 'page', 'elementor_library' ],
		] );

		$repeater->add_control( 'url_key', [
			'label' => __( 'URL key', 'voxel-elementor' ),
			'description' => 'Enter a unique key for this tab, which can be used to directly open this tab through the URL.',
			'type' => \Elementor\Controls_Manager::TEXT,
		] );

		$repeater->add_control( 'render_method', [
			'label' => __( 'Render method', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'client',
			'options' => [
				'server' => 'Server-side: Render template on page load.',
				'client' => 'Client-side: Use AJAX to render template when tab is opened by the user.',
			],
		] );

		$repeater->add_control( 'label', [
			'label' => __( 'Label', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::TEXT,
		] );

		$repeater->add_control( 'icon', [
			'label' => __( 'Icon', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::ICONS,
		] );

		$this->add_control( 'ts_tabs', [
			'label' => __( 'Add tabs', 'voxel-elementor' ),
			'type' => \Elementor\Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'title_field' => '{{url_key}}',
		] );

		$this->add_control( 'ts_navbar', [
			'label' => __( 'Link to a Navbar widget', 'voxel-elementor' ),
			'description' => 'The selected Navbar widget will be automatically populated with links to each tab added above.',
			'type' => 'voxel-relation',
			'vx_group' => 'tabsToNavbar',
			'vx_target' => 'elementor-widget-ts-navbar',
			'vx_side' => 'left',
		] );

		$this->add_control( 'ts_url_key', [
			'label' => __( 'URL parameter', 'voxel-elementor' ),
			'description' => 'Set what parameter should be used to directly access a tab through the URL, e.g. `<strong>tab</strong>=reviews`',
			'default' => 'tab',
			'type' => \Elementor\Controls_Manager::TEXT,
		] );

		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		$post = \Voxel\get_current_post();
		if ( ! $post ) {
			$post = \Voxel\Post::get( $this->_get_template_id() );
		}

		if ( ! $post ) {
			return;
		}

		$tabs = $this->_get_tabs_config();
		if ( $tabs === null ) {
			return;
		}

		$url_key = $this->get_settings( 'ts_url_key' ) ?: 'tab';
		$config = [
			'widget_id' => $this->get_id(),
			'template_id' => $this->_get_template_id(),
			'post_id' => $post->get_id(),
			'url_key' => $url_key,
			'default_tab' => $tabs['items'][0]['url_key'],
		];

		?>
		<div class="ts-template-tabs ts-template-tabs-<?= $tabs['id'] ?>" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
			<?php foreach ( $tabs['items'] as $tab ): ?>
				<?php if ( $tab['render_method'] === 'server' || $tabs['active'] === $tab['url_key'] ): ?>
					<div class="ts-template-tab rendered <?= $tabs['active'] === $tab['url_key'] ? 'active-tab' : '' ?>" data-tab="<?= esc_attr( $tab['url_key'] ) ?>">
						<?php \Voxel\print_template( $tab['template_id'] ) ?>
					</div>
				<?php else: ?>
					<div class="ts-template-tab" data-tab="<?= esc_attr( $tab['url_key'] ) ?>"></div>
				<?php endif ?>
			<?php endforeach ?>
		</div>
		<?php
	}

	public function _get_tabs_config() {
		$post = \Voxel\get_current_post();
		if ( ! $post ) {
			$post = \Voxel\Post::get( $this->_get_template_id() );
			$post_link = \Voxel\get_current_url();
		} else {
			$post_link = $post->get_link();
		}

		if ( ! $post ) {
			return null;
		}

		$tabs = (array) $this->get_settings( 'ts_tabs' );
		$url_key = $this->get_settings( 'ts_url_key' ) ?: 'tab';

		// validate
		$tabs = array_values( array_filter( $tabs, function( $tab ) {
			return ! ( empty( $tab['url_key'] ) || empty( $tab['template_id'] ) || empty( $tab['render_method'] ) );
		} ) );

		if ( empty( $tabs ) ) {
			return null;
		}

		// determine active tab
		$active_tab = $tabs[0]['url_key'];
		$url_tab = $_GET[ $url_key ] ?? null;
		if ( ! empty( $url_tab ) ) {
			foreach ( $tabs as $tab ) {
				if ( $tab['url_key'] === $url_tab ) {
					$active_tab = $tab['url_key'];
					break;
				}
			}
		}

		foreach ( $tabs as $i => $tab ) {
			$tabs[ $i ]['_href'] = add_query_arg( $url_key, $tab['url_key'], $post_link );
		}

		return [
			'items' => $tabs,
			'active' => $active_tab,
			'id' => sprintf( '%s-%d', $this->get_id(), $post->get_id() ),
		];
	}

	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}
