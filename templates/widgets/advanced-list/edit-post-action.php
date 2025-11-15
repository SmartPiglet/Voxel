<?php
$current_post = \Voxel\get_current_post();
if ( ! ( $current_post && $current_post->is_editable_by_current_user() ) ) {
	return;
}

$edit_steps = array_filter( $current_post->get_fields(), function( $field ) {
	return $field->get_type() === 'ui-step' && $field->passes_visibility_rules();
} ); ?>

<?= $start_action ?>
<?php if ( count( $edit_steps ) > 1 ): ?>
	<div class="ts-action-wrap ts-popup-component">
		<a href="#" @click.prevent @mousedown="active = true" ref="target" class="ts-action-con" role="button">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
			<?= $action['ts_acw_initial_text'] ?>
		</a>
		<popup v-cloak>
			<div class="ts-popup-head ts-sticky-top flexify hide-d">
				<div class="ts-popup-name flexify">
					<?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?>
					<span><?= _x( 'Edit', 'edit post action', 'voxel' ) ?></span>
				</div>
				<ul class="flexify simplify-ul">
					<li class="flexify ts-popup-close">
						<a role="button" @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_close_ico') ) ?: \Voxel\svg( 'close.svg' ) ?>
						</a>
					</li>
				</ul>
			</div>
			<div class="ts-term-dropdown ts-md-group">
				<ul class="simplify-ul ts-term-dropdown-list min-scroll">
					<?php foreach ( $edit_steps as $field ): ?>
						<li>
							<a href="<?= esc_url( add_query_arg( 'step', $field->get_key(), $current_post->get_edit_link() ) ) ?>" class="flexify">

								<span><?= $field->get_label() ?></span>

							</a>
						</li>
					<?php endforeach ?>
				</ul>
			</div>
		</popup>
	</div>
<?php else: ?>
	<a href="<?= esc_url( $current_post->get_edit_link() ) ?>" class="ts-action-con" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
		<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
		<?= $action['ts_acw_initial_text'] ?>
	</a>
<?php endif ?>
<?= $end_action ?>
