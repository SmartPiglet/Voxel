<li class="ts-popup-messages elementor-repeater-item-<?= $component['_id'] ?>" data-config="<?= esc_attr( wp_json_encode( [
	'nonce' => wp_create_nonce( 'vx_chat' ),
] ) ) ?>">
	<a ref="target" @mousedown="open" @click.prevent href="#" role="button" aria-label="<?= $component['messages_title'] ?>">
		<div class="ts-comp-icon flexify">
			<?= \Voxel\get_icon_markup( $component['choose_component_icon'] ) ?>
			<?php if ( is_user_logged_in() && \Voxel\current_user()->get_inbox_meta()['unread'] ): ?>
				<span ref="indicator" class="unread-indicator"></span>
			<?php endif ?>
		</div>
		<span class="ts_comp_label" ><?= $component['messages_title'] ?></span>
	</a>
	<teleport to="body" class="hidden">
		<transition name="form-popup">
			<form-popup
				ref="popup"
				v-if="$root.active"
				:target="$refs.target"
				:show-save="false"
				:show-clear="false"
				@blur="active = false"
				class="md-width"
			>
				<div class="ts-popup-head flexify ts-sticky-top">
					<div class="ts-popup-name flexify">
						<?= \Voxel\get_icon_markup( $component['choose_component_icon'] ) ?>
						<span><?= $component['messages_title'] ?></span>
					</div>

					<ul class="flexify simplify-ul">
						<li class="flexify">
							<a href="<?= esc_url( get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/') ) ?>" class="ts-icon-btn">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_envelop_ico') ) ?: \Voxel\get_svg( 'envelope.svg' ) ?>
							</a>
						</li>
						<li class="flexify ts-popup-close">
							<a  @click.prevent="$root.active = false" href="#" class="ts-icon-btn">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_close_ico') ) ?: \Voxel\get_svg( 'close.svg' ) ?>
							</a>
						</li>
					</ul>
				</div>

				<div v-if="chats.loading" class="ts-empty-user-tab">
					<span class="ts-loader"></span>
				</div>
				<div v-else-if="!chats.list.length" class="ts-empty-user-tab">
					<?php \Voxel\render_icon( $component['choose_component_icon'] ) ?>
					<p><?= _x( 'No chats available', 'messages', 'voxel' ) ?></p>
				</div>
				<ul v-else class="ts-notification-list simplify-ul ts-message-notifications">
					<template v-for="chat in chats.list">
						<li :class="{'ts-new-notification': chat.is_new, 'ts-unread-notification': !chat.seen}">
							<a :href="chat.link">
								<div class="notification-image" v-if="chat.target.avatar" v-html="chat.target.avatar"></div>
								<div class="notification-details">
									<b>{{ chat.target.name }}</b>
									<span>{{ chat.excerpt }}</span>
									<span>{{ chat.time }}</span>
								</div>
							</a>
						</li>
					</template>
				</ul>
				<div class="ts-form-group">
					<div class="n-load-more" v-if="chats.hasMore">
						<a href="#" @click.prevent="loadMoreChats" class="ts-btn ts-btn-4" :class="{'vx-pending': chats.loadingMore}">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_load_ico') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							<?= __( 'Load more', 'voxel' ) ?>
						</a>
					</div>
				</div>
			</form-popup>
		</transition>
	</teleport>
</li>