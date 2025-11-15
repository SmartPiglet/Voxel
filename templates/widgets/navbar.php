
<?php if ($this->get_settings('navbar_choose_source') === 'add_links_manually'): ?>
	<nav class="ts-nav-menu ts-custom-links flexify">
		<ul class="ts-nav ts-nav-<?= $this->get_settings('ts_navbar_orientation') ?> flexify simplify-ul min-scroll min-scroll-h <?= $this->get_settings_for_display('ts_collapsed') ? 'ts-nav-collapsed' : '' ?>">
			<?php foreach ($this->get_settings('ts_navbar_items') as $i => $action): ?>
				<li class="menu-item <?= $action['navbar_item__active'] ?>">
					<?php $this->add_link_attributes( 'ts_action_link_'.$i, $action['ts_navbar_item_link'] ) ?>
					<a <?= $this->get_render_attribute_string( 'ts_action_link_'.$i ) ?> class="ts-item-link">
						<div class="ts-item-icon flexify">
							<?php \Voxel\render_icon( $action['ts_navbar_item_icon'] ); ?>
						</div>
						<span><?= $action['ts_navbar_item_text'] ?></span>
					</a>
				</li>
			<?php endforeach ?>
		</ul>
	</nav>
<?php elseif ($this->get_settings('navbar_choose_source') === 'template_tabs'): ?>
	<?php
	$widget_config = \Voxel\get_related_widget( $this, $this->_get_template_id(), 'tabsToNavbar', 'right' );
	if ( ! $widget_config ) {
		return;
	}

	$widget = new \Voxel\Widgets\Template_Tabs( $widget_config, [] );
	?>
	<?php if ( $tabs = $widget->_get_tabs_config() ): ?>
		<nav class="ts-nav-menu ts-tab-triggers ts-tab-triggers-<?= $tabs['id'] ?> flexify">
			<ul class="ts-nav ts-nav-<?= $this->get_settings('ts_navbar_orientation') ?> flexify simplify-ul min-scroll min-scroll-h <?= $this->get_settings_for_display('ts_collapsed') ? 'ts-nav-collapsed' : '' ?>">
				<?php foreach ( $tabs['items'] as $tab ): ?>
					<li class="menu-item <?= $tabs['active'] === $tab['url_key'] ? 'current-menu-item' : '' ?>" data-tab="<?= esc_attr( $tab['url_key'] ) ?>">
						<a href="<?= esc_url( $tab['_href'] ) ?>"
							onclick="Voxel.loadTab( event, <?= esc_attr( wp_json_encode( $tabs['id'] ) ) ?>, <?= esc_attr( wp_json_encode( $tab['url_key'] ) ) ?> )"
							class="ts-item-link"
						>
							<div class="ts-item-icon flexify">
								<?php \Voxel\render_icon( $tab['icon'] ); ?>
							</div>
							<span><?= $tab['label'] ?: $tab['url_key'] ?></span>
						</a>
					</li>
				<?php endforeach ?>
			</ul>
		</nav>
	<?php endif ?>
<?php elseif ($this->get_settings('navbar_choose_source') === 'search_form'): ?>
	<?php
	$widget_config = \Voxel\get_related_widget( $this, $this->_get_template_id(), 'searchToNavbar', 'right' );
	if ( ! $widget_config ) {
		return;
	}

	$widget = new \Voxel\Widgets\Search_Form( $widget_config, [] );
	$post_type_keys = (array) $widget->get_settings( 'ts_choose_post_types' );
	$post_types = [];
	$active_type = $widget->_get_default_post_type();

	foreach ( $post_type_keys as $post_type_key ) {
		if ( $post_type = \Voxel\Post_Type::get( $post_type_key ) ) {
			$post_types[] = $post_type;
		}
	}
	?>
	<?php if ( ! empty( $post_types ) ): ?>
		<nav class="ts-nav-menu flexify ts-nav-sf ts-nav-sf-<?= esc_attr( $widget->get_id() ) ?>">
			<ul class="ts-nav ts-nav-<?= $this->get_settings('ts_navbar_orientation') ?> flexify simplify-ul min-scroll min-scroll-h <?= $this->get_settings_for_display('ts_collapsed') ? 'ts-nav-collapsed' : '' ?>">
				<?php foreach ( $post_types as $post_type ): ?>
					<li class="menu-item <?= ( $active_type && $active_type->get_key() === $post_type->get_key() ) ? 'current-menu-item' : '' ?>" data-post-type="<?= esc_attr( $post_type->get_key() ) ?>">
						<a href="#" class="ts-item-link">
							<div class="ts-item-icon flexify">
								<?php \Voxel\render_icon( $post_type->get_icon() ); ?>
							</div>
							<span><?= $post_type->get_label() ?></span>
						</a>
					</li>
				<?php endforeach ?>
			</ul>
		</nav>
	<?php endif ?>
<?php else: ?>
	<nav class="ts-nav-menu ts-wp-menu <?= $this->get_settings_for_display('ts_collapsed') ? 'ts-nav-collapsed' : '' ?>">
		<?php if ( isset( get_nav_menu_locations()[ $this->get_settings( 'ts_choose_menu' ) ] ) ): ?>
			<?php wp_nav_menu( [
				'echo' => true,
				'theme_location' => $this->get_settings( 'ts_choose_menu' ),
				'container' => false,
				'menu_class' => sprintf( 'ts-nav ts-nav-%s flexify simplify-ul %s', $this->get_settings('ts_navbar_orientation'), $this->get_settings('ts_navbar_orientation') === 'horizontal' ? 'min-scroll min-scroll-h' : '' ),
				'walker' => new \Voxel\Utils\Nav_Menu_Walker,
				'_widget' => $this,
				'_arrow_right' => $this->get_settings( 'ts_arrow_right' ),
				'_arrow_left' => $this->get_settings( 'ts_arrow_left' ),
				'_icon_mobile' => $this->get_settings( 'ts_mobile_menu_icon' ),
				'_icon_close'  => $this->get_settings( 'ts_close_ico' ),
			] ) ?>
		<?php endif ?>
	</nav>
<?php endif ?>
