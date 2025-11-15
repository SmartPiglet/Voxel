<?php
$current_post = \Voxel\get_current_post();
if ( ! $current_post ) {
	return;
}

wp_enqueue_script('vx:share.js');
?>
<?= $start_action ?>
<div class="ts-action-wrap ts-share-post" data-config="<?= esc_attr( wp_json_encode( [
	'title' => $current_post->get_title(),
	'link' => $current_post->get_link(),
] ) ) ?>">
	<a href="#" ref="target" class="ts-action-con" role="button" @click.prevent @mousedown="open" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
		<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
		<?= $action['ts_acw_initial_text'] ?>
	</a>
	<teleport to="body" class="hidden">
		<transition name="form-popup">
			<popup :show-save="false" :show-clear="false" v-if="active" ref="popup" @blur="active = false" :target="$refs.target">
				<div class="ts-popup-head ts-sticky-top flexify hide-d">
					<div class="ts-popup-name flexify">
						<?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?>
						<span><?= _x( 'Share post', 'share post action', 'voxel' ) ?></span>
					</div>
					<ul class="flexify simplify-ul">
						<li class="flexify ts-popup-close">
							<a role="button" @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_close_ico') ) ?: \Voxel\svg( 'close.svg' ) ?>
							</a>
						</li>
					</ul>
				</div>
				<div v-if="loading" class="ts-empty-user-tab">
					<div class="ts-loader"></div>
				</div>
				<div v-else class="ts-term-dropdown ts-md-group">
					<ul class="simplify-ul ts-term-dropdown-list min-scroll ts-social-share">
						<template v-for="item in list">
							<template v-if="item.type === 'ui-heading'">
								<li class="ts-parent-item vx-noevent">
									<a href="#" class="flexify">
										<span>{{ item.label }}</span>
									</a>
								</li>
							</template>
							<template v-else>
								<li :class="'ts-share-'+item.type" v-if="shouldShow(item)">
									<a :href="item.link" target="_blank" class="flexify" rel="nofollow" @click.prevent="share(item)">
										<div class="ts-term-icon">
											<span v-html="item.icon"></span>
										</div>
										<span>{{ item.label }}</span>
									</a>
								</li>
							</template>
						</template>
					</ul>
				</div>
			</popup>
		</transition>
	</teleport>
</div>
<?= $end_action ?>
