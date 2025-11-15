<?php

use Voxel\Modules\Collections as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

$current_post = \Voxel\get_current_post();
if ( ! $current_post ) {
	return;
}

$is_active = false;
$current_user = \Voxel\get_current_user();
if ( $current_user ) {
	if ( isset( $GLOBALS['vx_preview_card_current_ids'] ) ) {
		Module\prime_collection_cache( $current_user->get_id(), $GLOBALS['vx_preview_card_current_ids'] );
	}

	$is_active = Module\has_saved_post( $current_user->get_id(), $current_post->get_id() );
}

wp_enqueue_style('vx:forms.css');
wp_enqueue_script('vx:collections.js');

?>
<li class="elementor-repeater-item-<?= $action['_id'] ?> flexify ts-action <?= $widget->get_settings_for_display('ts_al_columns_no') ?>"
	<?php if ($action['ts_enable_tooltip'] === 'yes'): ?>
		data-tooltip-default="<?= esc_attr( $action['ts_tooltip_text'] ) ?>"
	<?php endif ?>
	<?php if ($action['ts_acw_enable_tooltip'] === 'yes'): ?>
		data-tooltip-active="<?= esc_attr( $action['ts_acw_tooltip_text'] ) ?>"
	<?php endif ?>
>
	<div class="ts-action-wrap ts-collections" data-post-id="<?= esc_attr( $current_post->get_id() ) ?>">
		<a href="#" ref="target" class="ts-action-con <?= $is_active ? 'active' : '' ?>" role="button" @click.prevent @mousedown="open" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
			<span class="ts-initial">
				<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
				<?= $action['ts_acw_initial_text'] ?>
			</span>
			<span class="ts-reveal">
				<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_reveal_icon'] ) ?></div>
				<?= $action['ts_acw_reveal_text'] ?>
			</span>
		</a>
		<teleport to="body" class="hidden">
			<transition name="form-popup">
				<popup class="md-width" :show-save="false" :show-clear="false" v-if="active" ref="popup" @blur="active = false" :target="$refs.target">
					<div v-if="screen !== 'create'" class="ts-popup-head ts-sticky-top flexify hide-d">
						<div class="ts-popup-name flexify">
							<?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?>
							<span><?= _x( 'Save post', 'save post action', 'voxel' ) ?></span>
						</div>
						<ul class="flexify simplify-ul">
							<li class="flexify ts-popup-close">
								<a role="button" @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
									<?= \Voxel\get_icon_markup( $widget->get_settings_for_display('ts_close_ico') ) ?: \Voxel\svg( 'close.svg' ) ?>
								</a>
							</li>
						</ul>
					</div>
					<template v-if="screen === 'create'">
						<div class="ts-create-collection" :class="{'vx-pending': create.loading}">
							<div class="uib b-bottom ts-name-col">
								<input type="text" ref="input" class="border-none" v-model="create.title" placeholder="<?= esc_attr( _x( 'Name collection', 'save post action', 'voxel' ) ) ?>" @keyup.enter="createCollection">
							</div>
						</div>
						<div class="ts-popup-controller create-controller">
							<ul class="flexify simplify-ul">
								<li class="flexify"><a href="#" @click.prevent="screen = 'main'" class="ts-btn ts-btn-1"><?= __( 'Cancel', 'voxel' ) ?></a></li>
								<li class="flexify"><a href="#" @click.prevent="createCollection" class="ts-btn ts-btn-2"><?= __( 'Create', 'voxel' ) ?></a></li>
							</ul>
						</div>
					</template>
					<template v-else>
						<div v-if="items.loading" class="ts-empty-user-tab">
							<div class="ts-loader"></div>
						</div>
						<div v-else class="ts-term-dropdown ts-md-group">
							<ul class="simplify-ul ts-term-dropdown-list" :class="{'vx-pending': toggling}">
								<li class="ts-term-centered">
									<a href="#" @click.prevent="showCreateScreen" class="flexify">
										<div class="ts-term-icon">
											<?php \Voxel\svg('plus.svg') ?>
										</div>
										<span><?= _x( 'Create collection', 'save post action', 'voxel' ) ?></span>
									</a>
								</li>
								<li v-for="item in items.list" :class="{'ts-selected': item.selected}">
									<a href="#" class="flexify" @click.prevent="toggleItem( item )">
										<div class="ts-checkbox-container">
											<label class="container-checkbox">
												<input type="checkbox" :checked="item.selected" disabled hidden>
												<span class="checkmark"></span>
											</label>
										</div>
										<span>{{ item.title }}</span>
									</a>
								</li>
								<div class="n-load-more" v-if="items.hasMore">
									<a href="#" @click.prevent="loadMore" class="ts-btn ts-btn-4" :class="{'vx-pending': items.loadingMore}">
										<?php \Voxel\svg( 'reload.svg' ) ?>
										<?= __( 'Load more', 'voxel' ) ?>
									</a>
								</div>
							</ul>
						</div>
					</template>
				</popup>
			</transition>
		</teleport>
	</div>
</li>
