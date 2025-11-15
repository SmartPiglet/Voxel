<div class="ts-user-area">
	<ul class="flexify simplify-ul user-area-menu">

		<?php foreach ( $this->get_settings('ts_userbar_items') as $i => $component ): ?>

			<?php if ( is_user_logged_in() && $component['ts_component_type'] === 'notifications'): ?>

				<?php require locate_template('templates/widgets/user-bar/notifications.php') ?>

			<?php elseif ( $component['ts_component_type'] === 'cart'): ?>

				<?php require locate_template('templates/widgets/user-bar/cart.php') ?>

			<?php elseif ( is_user_logged_in() && $component['ts_component_type'] === 'messages'): ?>

				<?php require locate_template('templates/widgets/user-bar/messages.php') ?>

			<?php elseif (is_user_logged_in() && $component['ts_component_type'] === 'user_menu'):
				$user = \Voxel\current_user(); ?>

					<li class="ts-popup-component ts-user-area-avatar elementor-repeater-item-<?= $component['_id'] ?>">
						<a ref="target" @click.prevent @mousedown="active = true" href="#" role="button" aria-label="<?= esc_html( $user->get_display_name() ) ?>">
							<div class="ts-comp-icon flexify">
								<?= $user->get_avatar_markup() ?>
							</div>
							<span class="ts_comp_label"><?= esc_html( $user->get_display_name() ) ?></span>
							<div class="ts-down-icon"></div>
						</a>

						<?php if ( isset( get_nav_menu_locations()[ $component['ts_choose_menu'] ] ) ): ?>
							<popup v-cloak>
								<div class="ts-popup-head flexify ts-sticky-top">
									<div class="ts-popup-name flexify">
										<?= $user->get_avatar_markup() ?>
										<span><?= esc_html( $user->get_display_name() ) ?></span>
									</div>

									<ul class="flexify simplify-ul">
										<li class="flexify ts-popup-close">
											<a role="button" @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
												<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_close_ico') ) ?: \Voxel\svg( 'close.svg' ) ?>
											</a>
										</li>
									</ul>
								</div>
								<transition-group :name="'slide-from-'+slide_from" tag="div" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown" @before-enter="beforeEnter" @before-leave="beforeLeave">
									<?php wp_nav_menu( [
										'echo' => true,
										'theme_location' => $component['ts_choose_menu'],
										'container' => false,
										'items_wrap' => '%3$s',
										'walker' => new \Voxel\Utils\Popup_Menu_Walker,
										'_arrow_right' => $this->get_settings( 'ts_arrow_right' ),
										'_arrow_left' => $this->get_settings( 'ts_arrow_left' ),
									] ) ?>
								</transition-group>
							</popup>
						<?php endif ?>
					</li>

			<?php elseif ($component['ts_component_type'] === 'select_wp_menu'): ?>

					<li class="ts-popup-component elementor-repeater-item-<?= $component['_id'] ?>">
						<a ref="target" @click.prevent @mousedown="active = true" href="#" role="button" aria-label="<?= $component['wp_menu_title'] ?>">
							<div class="ts-comp-icon flexify">
								<?= \Voxel\get_icon_markup( $component['choose_component_icon'] ) ?>
							</div>
							<span class="ts_comp_label" ><?= $component['wp_menu_title'] ?></span>
						</a>

						<?php if ( isset( get_nav_menu_locations()[ $component['ts_choose_menu'] ] ) ): ?>
							<popup v-cloak>
								<div class="ts-popup-head flexify ts-sticky-top">
									<div class="ts-popup-name flexify">
										<?= \Voxel\get_icon_markup( $component['choose_component_icon'] ) ?>
										<span><?= $component['wp_menu_title'] ?></span>
									</div>

									<ul class="flexify simplify-ul">
										<li class="flexify ts-popup-close">
											<a role="button" @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
												<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_close_ico') ) ?: \Voxel\svg( 'close.svg' ) ?>

											</a>
										</li>
									</ul>
								</div>
								<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
									<transition-group :name="'slide-from-'+slide_from">
										<?php wp_nav_menu( [
											'echo' => true,
											'theme_location' => $component['ts_choose_menu'],
											'container' => false,
											'items_wrap' => '%3$s',
											'walker' => new \Voxel\Utils\Popup_Menu_Walker,
											'_arrow_right' => $this->get_settings( 'ts_arrow_right' ),
											'_arrow_left' => $this->get_settings( 'ts_arrow_left' ),
										] ) ?>
									</transition-group>
								</div>
							</popup>
						<?php endif ?>
					</li>

			<?php elseif ($component['ts_component_type'] === 'link'): ?>
				<li class="elementor-repeater-item-<?= $component['_id'] ?>">
					<?php $this->add_link_attributes( 'ts_action_link_'.$i, $component['component_url'] ) ?>
					<a <?= $this->get_render_attribute_string( 'ts_action_link_'.$i ) ?>>
						<div class="ts-comp-icon flexify">
							<?= \Voxel\get_icon_markup( $component['choose_component_icon'] ) ?>
						</div>
						<span class="ts_comp_label"><?= $component['component_title'] ?></span>
					</a>
				</li>
			<?php endif ?>
		<?php endforeach ?>
	</ul>
</div>
