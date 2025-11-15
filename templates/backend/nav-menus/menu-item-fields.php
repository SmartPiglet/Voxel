<?php
/**
 * Menu item custom fields.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="description description-wide">
	<label style="display: block;">Icon</label>
	<div class="ts-icon-picker" style="display: inline-block">
		<div class="icon-preview"></div>
		<input type="hidden" value="<?= esc_attr( $icon_string ) ?>" name="voxel_item_icon[<?= $item_id ?>]">
		<a href="#" class="button button-small choose-icon">Choose Icon</a>
		<a href="#" class="button button-small upload-svg">Upload SVG</a>
		<a href="#" class="button button-small clear-icon">Remove</a>
	</div>
</div>

<script type="text/javascript">
	if ( typeof window.voxel_init_icon_pickers === 'function' ) {
		window.voxel_init_icon_pickers();
	}
</script>

<?php if ( $item->type === 'custom' ): ?>
	<p class="description description-wide" onclick="Voxel_Backend._nav_dtags(this)">
		<label>Dynamic URL</label>
		<input type="text" readonly name="voxel_item_url[<?= $item_id ?>]" value="<?= esc_attr( $url ) ?>" class="widefat">
	</p>
<?php endif ?>

<p class="description description-wide" onclick="Voxel_Backend._nav_dtags(this)">
	<label>Dynamic label</label>
	<input type="text" readonly name="voxel_item_label[<?= $item_id ?>]" value="<?= esc_attr( $label ) ?>" class="widefat">
</p>

<p class="description description-wide">
	<label>Visibility</label>
	<select name="voxel_visibility_behavior[<?= $item_id ?>]" class="widefat">
		<option value="show" <?php selected( $visibility_behavior === 'show' ) ?>>
			Show this menu item if
		</option>
		<option value="hide" <?php selected( $visibility_behavior === 'hide' ) ?>>
			Hide this menu item if
		</option>
	</select>
</p>

<div class="description description-wide nav-item-visibility-rules" onclick="Voxel_Backend._nav_visibility(this)">
	<div class="vx-visibility-rules">
		<span class="elementor-control-field-description">
			No visibility rules added yet.
		</span>
	</div>
	<span class="button button-small choose-icon">Edit rules</span>
	<input type="hidden" readonly name="voxel_visibility_rules[<?= $item_id ?>]" value="<?= esc_attr( $visibility_rules ) ?>" class="widefat">
</div>
