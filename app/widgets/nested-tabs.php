<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nested_Tabs extends \Elementor\Modules\NestedTabs\Widgets\NestedTabs {

	protected function render() {
		$settings = $this->get_settings_for_display();
		$widget_number = $this->get_id_int();

		if ( ! empty( $settings['link'] ) ) {
			$this->add_link_attributes( 'elementor-tabs', $settings['link'] );
		}

		$this->add_render_attribute( 'elementor-tabs', [
			'class' => 'e-n-tabs',
			'data-widget-number' => $widget_number,
			'aria-label' => esc_html__( 'Tabs. Open items with Enter or Space, close with Escape and navigate using the Arrow keys.', 'elementor' ),
		] );

		$this->add_render_attribute( 'tab-title-text', 'class', 'e-n-tab-title-text' );
		$this->add_render_attribute( 'tab-icon', 'class', 'e-n-tab-icon' );
		$this->add_render_attribute( 'tab-icon-active', 'class', [ 'e-n-tab-icon' ] );

		$tab_titles_html = '';
		$tab_containers_html = '';

		$children = $this->get_children();

		$child_ids = [];

		foreach ( $children as $child ) {
			$child_ids[] = $child->get_id();
		}

		foreach ( $settings['tabs'] as $index => $item ) {
			$tab_count = $index + 1;

			$tab_id = empty( $item['element_id'] )
				? 'e-n-tabs-title-' . $widget_number . $tab_count
				: $item['element_id'];

			$item_settings = [
				'index' => $index,
				'tab_count' => $tab_count,
				'tab_id' => $tab_id,
				'container_id' => 'e-n-tab-content-' . $widget_number . $tab_count,
				'widget_number' => $widget_number,
				'item' => $item,
				'settings' => $settings,
			];

			ob_start();
			$this->render_tab_titles_html( $item_settings );
			$tab_titles_html .= ob_get_clean();

			ob_start();

			if ( isset( $item['_voxel_loop'], $item['_loop_index'], $item['_child_index'] ) ) {
				$child_element = $children[ $item['_child_index'] ] ?? null;
				if ( $child_element ) {
					\Voxel\Dynamic_Data\Looper::run_at_index( $item['_voxel_loop'], [
						'index' => $item['_loop_index'],
						'callback' => function() use ( $child_element, $item_settings, $child_ids ) {
							$classname = get_class( $child_element );
							$loop_element = new $classname( $child_element->get_data(), [] );

							// Add data-tab-index attribute to the content area.
							$add_attribute_to_container = function( $should_render, $container ) use ( $item_settings, $child_ids ) {
								if ( in_array( $container->get_id(), $child_ids ) ) {
									$this->add_attributes_to_container( $container, $item_settings );
								}

								return $should_render;
							};

							add_filter( 'elementor/frontend/container/should_render', $add_attribute_to_container, 10, 3 );
							$loop_element->print_element();
							remove_filter( 'elementor/frontend/container/should_render', $add_attribute_to_container );
						},
					] );
				}
			} else {
				$this->print_child( is_numeric( $item['_child_index'] ?? null ) ? $item['_child_index'] : $index, $item_settings );
			}

			// $this->print_child( $item_settings['index'], $item_settings );
			$tab_containers_html .= ob_get_clean();
		}
		?>
		<div <?php $this->print_render_attribute_string( 'elementor-tabs' ); ?>>
			<div class="e-n-tabs-heading" role="tablist">
				<?php echo $tab_titles_html;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="e-n-tabs-content">
				<?php echo $tab_containers_html;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
	}

	public function print_child( $index, $item_settings = [] ) {
		$children = $this->get_children();
		if ( ! empty( $children[ $index ] ) ) {
			$child_ids = [];

			foreach ( $children as $child ) {
				$child_ids[] = $child->get_id();
			}

			// Add data-tab-index attribute to the content area.
			$add_attribute_to_container = function ( $should_render, $container ) use ( $item_settings, $child_ids ) {
				if ( in_array( $container->get_id(), $child_ids ) ) {
					$this->add_attributes_to_container( $container, $item_settings );
				}

				return $should_render;
			};

			add_filter( 'elementor/frontend/container/should_render', $add_attribute_to_container, 10, 3 );
			$children[ $index ]->print_element();
			remove_filter( 'elementor/frontend/container/should_render', $add_attribute_to_container );
		}
	}

	protected function content_template() {
		ob_start();
		parent::content_template();
		$template = ob_get_clean();

		$template = str_replace( "{{{ item.tab_title }}}", "{{{ typeof item.tab_title === 'string' ? item.tab_title.replace( '@tags()', '' ).replace( '@endtags()', '' ) : item.tab_title }}}", (string) $template );

		echo $template;
	}
}
