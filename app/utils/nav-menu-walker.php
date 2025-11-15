<?php

namespace Voxel\Utils;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Nav_Menu_Walker extends \Walker {

	public $tree_type = [ 'post_type', 'taxonomy', 'custom' ];

	public $db_fields = [
		'parent' => 'menu_item_parent',
		'id'     => 'db_id',
	];

	public $last_item = [];
	public $submenus = [];
	public $submenus_to_merge = '';
	public $current_path = [ 'main' ];
	public $merged_mobile_menu = false;

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$classes = [ 'simplify-ul ts-term-dropdown-list sub-menu' ];
		$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		// start ts-term-dropdown
		if ( $depth === 0 ) {
			$output .= '<popup ref="popup" v-cloak>';
			$output .= '<transition-group :name="\'slide-from-\'+slide_from" tag="div" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown" @before-enter="beforeEnter" @before-leave="beforeLeave">';
		}

		// start submenu
		$submenu_id = $this->_current_submenu();
		$content = sprintf(
			'<ul %s key="%s" v-show="screen === \'%s\'">',
			$class_names, $submenu_id, $submenu_id
		);

		// for nested submenus, include a "Go Back" button
		if ( $depth > 0 ) {
		    $back_icon = '<div class="ts-left-icon"></div>';  // Use div instead of SVG icon
		    $content .= sprintf(
		        '
		        <li class="ts-term-centered">
		            <a href="#" class="flexify" @click.prevent="slide_from=\'left\'; screen=\'%s\';">
		                %s
		                <span>%s</span>
		            </a>
		        </li>
		        ',
		        $this->_parent_submenu(),
		        $back_icon,
		        __( 'Go back', 'voxel' )
		    );
		}

		// include the sub-menu trigger item as the main item in the sub-menu
		$content .= sprintf( '
			<li class="ts-parent-menu">
				<a href="%s" class="flexify">
					%s
					<span>%s</span>
				</a>
			</li>',
			esc_url( $this->last_item['atts']['href'] ),
			$this->last_item['icon'],
			$this->last_item['title']
		);

		if ( $depth === 0 ) {
			$output .= $content;
		} else {
			$this->submenus[] = $content;
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '</ul>';
			$output .= $this->submenus_to_merge;
			$output .= '</transition-group></popup>';
		} else {
			$this->submenus_to_merge .= array_pop( $this->submenus ).'</ul>';
			unset( $this->current_path[ count( $this->current_path ) - 1 ] );
			$this->current_path = array_values( $this->current_path );
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$args = (object) apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		if ( $depth === 0 && ! empty( $args->_widget ) && ! $this->merged_mobile_menu ) {
			$output .= $this->_mobile_menu_markup( $args->_widget, $args );
			$this->merged_mobile_menu = true;
		}

		$classes = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		if ( $depth === 0 && $args->walker->has_children ) {
			$classes[] = 'ts-popup-component ts-trigger-on-hover';
		}

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$_output = '<li' . $id . $class_names . ' >';

		$atts = [];
		$atts['title'] = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		if ( '_blank' === $item->target && empty( $item->xfn ) ) {
			$atts['rel'] = 'noopener';
		} else {
			$atts['rel'] = $item->xfn;
		}
		$atts['href'] = ! empty( $item->url ) ? $item->url : '';
		$atts['aria-current'] = $item->current ? 'page' : '';
		$atts['class'] = $depth === 0 ? 'ts-item-link' : 'flexify';
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		// menu item icon
		$icon_string = get_post_meta( $item->ID, '_voxel_item_icon', true );
		$icon = $wrapped_icon = '';

		if ( ! empty( $icon_string ) ) {
			$icon = $wrapped_icon = \Voxel\get_icon_markup( $icon_string );
			// dd($icon, \Voxel\parse_icon_string( $icon_string ));
			if ( $depth === 0 ) {
				$wrapped_icon = '<div class="ts-item-icon flexify">'.$icon.'</div>';
			} else {
				$wrapped_icon = '<div class="ts-term-icon">'.$icon.'</div>';
			}
		}

		// $tag = $args->walker->has_children && $depth === 0 ? 'span' : 'a';
		$tag = 'a';
		$ref = $depth === 0 && $args->walker->has_children ? ' ref="target"  @mousedown="active = true" ' : '';

		ob_start();

	  	if ( $depth === 0 ) {
            echo '<div class="ts-down-icon"></div>';
        } else {
            echo '<div class="ts-right-icon"></div>';
        }

		$arrow_icon = ob_get_clean();
		$arrow = $args->walker->has_children ? $arrow_icon : '';

		// onclick trigger submenu
		$onclick = '';
		if ( $args->walker->has_children && $depth > 0 ) {
			$this->current_path[] = sprintf( '_submenu-%s', wp_unique_id() );
			$onclick = sprintf( ' @click.prevent="slide_from=\'right\'; screen=\'%s\';" ', $this->_current_submenu() );
		}

		$item_output  = $args->before;
		$item_output .= '<'.$tag.' '. $ref . $onclick . $attributes . '>';
		$item_output .= $args->link_before . $wrapped_icon . '<span>' . $title . '</span>' . $arrow . $args->link_after;
		$item_output .= '</'.$tag.'>';
		$item_output .= $args->after;

		$this->last_item = [
			'title' => $title,
			'icon' => $icon,
			'atts' => $atts,
		];

		$_output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

		if ( $depth <= 1 ) {
			$output .= $_output;
		} else {
			$last_index = count( $this->submenus ) - 1;
			if ( isset( $this->submenus[ $last_index ] ) ) {
				$this->submenus[ $last_index ] .= $_output;
			}
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$_output = '</li>';

		if ( $depth <= 1 ) {
			$output .= $_output;
		} else {
			$last_index = count( $this->submenus ) - 1;
			if ( isset( $this->submenus[ $last_index ] ) ) {
				$this->submenus[ $last_index ] .= $_output;
			}
		}
	}

	private function _current_submenu() {
		return $this->current_path[ count( $this->current_path ) - 1 ];
	}

	private function _parent_submenu() {
		return $this->current_path[ count( $this->current_path ) - 2 ] ?? 'main';
	}

	private function _mobile_menu_markup( $widget, $args ) {
		$source = $widget->get_settings( 'ts_choose_mobile_menu' );
		if ( ! isset( get_nav_menu_locations()[ $source ] ) ) {
			$source = $widget->get_settings( 'ts_choose_menu' );
		}
		ob_start(); ?>
		<li class="ts-popup-component ts-mobile-menu">
			<button 
				type="button"
				class="ts-item-link" 
				ref="target" 
				@click.prevent 
				@mousedown="active = true"
				@keydown.enter.prevent="active = true"
				@keydown.space.prevent="active = true"
				aria-haspopup="true"
				aria-expanded="false"
				aria-label="<?= esc_attr( $widget->get_settings_for_display( 'hamburger_title' ) ?: 'Menu' ) ?>"
			>
				<div class="ts-item-icon flexify">
					<?= \Voxel\get_icon_markup( $args->_icon_mobile ) ?: \Voxel\svg( 'menu.svg' ); ?>
				</div>
				<?php if ($widget->get_settings('show_menu_label') === 'yes'): ?>
					<span><?= $widget->get_settings_for_display( 'hamburger_title' ) ?></span>
				<?php endif ?>

			    <popup v-cloak>
			    	<div class="ts-popup-head flexify hide-d">
						<div class="ts-popup-name flexify">
							<?= \Voxel\get_icon_markup( $args->_icon_mobile ) ?: \Voxel\svg( 'menu.svg' ); ?>
							<span><?= $widget->get_settings_for_display( 'hamburger_title' ) ?></span>
						</div>

						<ul class="flexify simplify-ul">
							<li class="flexify ts-popup-close">
								<a role="button" @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
									<?= \Voxel\get_icon_markup( $args->_icon_close ) ?: \Voxel\svg( 'close.svg' ); ?>
								</a>
							</li>
						</ul>
					</div>
					<transition-group :name="'slide-from-'+slide_from" tag="div" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown" @before-enter="beforeEnter" @before-leave="beforeLeave">
						<?php wp_nav_menu( [
							'echo' => true,
							'theme_location' => $source,
							'container' => false,
							'items_wrap' => '%3$s',
							'walker' => new \Voxel\Utils\Popup_Menu_Walker,
							'_arrow_right' => $args->_arrow_right,
							'_arrow_left' => $args->_arrow_left,
						] ) ?>
					</transition-group>
			    </popup>
			</button>
		</li>
		<?php
		return ob_get_clean();
	}
}
