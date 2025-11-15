<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<li class="elementor-repeater-item-<?= $action['_id'] ?> flexify ts-action <?= $this->get_settings_for_display('ts_al_columns_no') ?>"
	<?php if ($action['ts_enable_tooltip'] === 'yes'): ?>
		data-tooltip="<?= esc_attr( $action['ts_tooltip_text'] ) ?>"
		data-tooltip-default="<?= esc_attr( $action['ts_tooltip_text'] ) ?>"
	<?php endif ?>
	<?php if ($action['ts_acw_enable_tooltip'] === 'yes'): ?>
		data-tooltip-active="<?= esc_attr( $action['ts_acw_tooltip_text'] ) ?>"
	<?php endif ?>
>
	<a role="button" href="#" class="ts-action-con ts-use-addition" data-id="<?= esc_attr( $action['ts_addition_id'] ) ?>" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
		<span class="ts-initial">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
		</span>
		<span class="ts-reveal">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_reveal_icon'] ) ?></div><?= $action['ts_acw_reveal_text'] ?>
		</span>
	</a>
</li>
