<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nested_Accordion extends \Elementor\Modules\NestedAccordion\Widgets\Nested_Accordion {

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items = $settings['items'];
		$id_int = substr( $this->get_id_int(), 0, 3 );
		$items_title_html = '';
		$icons_content = $this->render_accordion_icons( $settings );
		$this->add_render_attribute( 'elementor-accordion', 'class', 'e-n-accordion' );
		$this->add_render_attribute( 'elementor-accordion', 'aria-label', 'Accordion. Open links with Enter or Space, close with Escape, and navigate with Arrow Keys' );
		$default_state = $settings['default_state'];
		$title_html_tag = \Elementor\Utils::validate_html_tag( $settings['title_tag'] );

		$faq_schema = [];

		foreach ( $items as $index => $item ) {
			$accordion_count = $index + 1;
			$item_setting_key = $this->get_repeater_setting_key( 'item_title', 'items', $index );
			$item_summary_key = $this->get_repeater_setting_key( 'item_summary', 'items', $index );
			$item_classes = [ 'e-n-accordion-item' ];
			$item_id = empty( $item['element_css_id'] ) ? 'e-n-accordion-item-' . $id_int . $index : $item['element_css_id'];
			$item_title = $item['item_title'];
			$is_open = 'expanded' === $default_state && 0 === $index ? 'open' : '';
			$aria_expanded = 'expanded' === $default_state && 0 === $index;

			$this->add_render_attribute( $item_setting_key, [
				'id' => $item_id,
				'class' => $item_classes,
			] );

			$this->add_render_attribute( $item_summary_key, [
				'class' => [ 'e-n-accordion-item-title' ],
				'role' => 'button',
				'data-accordion-index' => $accordion_count,
				'tabindex' => 0 === $index ? 0 : -1,
				'aria-expanded' => $aria_expanded ? 'true' : 'false',
				'aria-controls' => $item_id,
			] );

			$title_render_attributes = $this->get_render_attribute_string( $item_setting_key );
			$title_render_attributes = $title_render_attributes . ' ' . $is_open;

			$summary_render_attributes = $this->get_render_attribute_string( $item_summary_key );

			// items content.
			ob_start();
			if ( isset( $item['_voxel_loop'], $item['_loop_index'], $item['_child_index'] ) ) {
				$child_element = $this->get_children()[ $item['_child_index'] ] ?? null;
				if ( $child_element ) {
					\Voxel\Dynamic_Data\Looper::run_at_index( $item['_voxel_loop'], [
						'index' => $item['_loop_index'],
						'callback' => function() use ( $child_element, $item_id ) {
							$classname = get_class( $child_element );
							$loop_element = new $classname( $child_element->get_data(), [] );

							$add_attribute_to_container = function ( $should_render, $container ) use ( $item_id ) {
								$this->add_attributes_to_container( $container, $item_id );
								return $should_render;
							};

							add_filter( 'elementor/frontend/container/should_render', $add_attribute_to_container, 10, 3 );
							$loop_element->print_element();
							remove_filter( 'elementor/frontend/container/should_render', $add_attribute_to_container );
						},
					] );
				}
			} else {
				$this->print_child( is_numeric( $item['_child_index'] ?? null ) ? $item['_child_index'] : $index, $item_id );
			}
			$item_content = ob_get_clean();

			$faq_schema[ $item_title ] = $item_content;

			ob_start();
			?>
			<details <?php echo wp_kses_post( $title_render_attributes ); ?>>
				<summary <?php echo wp_kses_post( $summary_render_attributes ); ?> >
					<span class='e-n-accordion-item-title-header'><?php echo wp_kses_post( "<$title_html_tag class=\"e-n-accordion-item-title-text\"> $item_title </$title_html_tag>" ); ?></span>
					<?php if ( ! empty( $settings['accordion_item_title_icon']['value'] ) ) {
						echo $icons_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} ?>
				</summary>
				<?php echo $item_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</details>
			<?php
			$items_title_html .= ob_get_clean();
		}

		?>
		<div <?php $this->print_render_attribute_string( 'elementor-accordion' ); ?>>
			<?php echo $items_title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		if ( isset( $settings['faq_schema'] ) && 'yes' === $settings['faq_schema'] ) {
			$json = [
				'@context' => 'https://schema.org',
				'@type' => 'FAQPage',
				'mainEntity' => [],
			];

			foreach ( $faq_schema as $name => $text ) {
				$json['mainEntity'][] = [
					'@type' => 'Question',
					'name' => wp_strip_all_tags( $name ),
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text' => wp_strip_all_tags( $text ),
					],
				];
			}
			?>
			<script type="application/ld+json"><?php echo wp_json_encode( $json ); ?></script>
			<?php
		}
	}

	private function render_accordion_icons( $settings ) {
		$icon_html = \Elementor\Icons_Manager::try_get_icon_html( $settings['accordion_item_title_icon'], [ 'aria-hidden' => 'true' ] );
		$icon_active_html = $this->is_active_icon_exist( $settings )
			? \Elementor\Icons_Manager::try_get_icon_html( $settings['accordion_item_title_icon_active'], [ 'aria-hidden' => 'true' ] )
			: $icon_html;

		ob_start();
		?>
		<span class='e-n-accordion-item-title-icon'>
			<span class='e-opened' ><?php echo $icon_active_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class='e-closed'><?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</span>

		<?php
		return ob_get_clean();
	}

	private function is_active_icon_exist( $settings ):bool {
		return array_key_exists( 'accordion_item_title_icon_active', $settings ) && ! empty( $settings['accordion_item_title_icon_active'] ) && ! empty( $settings['accordion_item_title_icon_active']['value'] );
	}

	protected function content_template() {
		ob_start();
		parent::content_template();
		$template = ob_get_clean();

		$template = str_replace( "{{{ item.item_title }}}", "{{{ typeof item.item_title === 'string' ? item.item_title.replace( '@tags()', '' ).replace( '@endtags()', '' ) : item.item_title }}}", (string) $template );

		echo $template;
	}
}
