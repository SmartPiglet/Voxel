<div class="ts-inbox" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<div class="inbox-left" :class="{'ts-no-chat': !activeChat}">
		<div v-if="chats.loading" class="ts-empty-user-tab">
			<span class="ts-loader"></span>
		</div>
		<div v-else-if="!chats.list.length" class="ts-empty-user-tab">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_chat') ) ?: \Voxel\svg( 'inbox.svg' ) ?>
			<p><?= _x( 'No chats available', 'messages', 'voxel' ) ?></p>
		</div>
		<template v-else>
			<div class="ts-form ts-inbox-top">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_search') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input type="text" v-model="search.term" placeholder="<?= esc_attr( _x( 'Search inbox', 'messages', 'voxel' ) ) ?>" class="autofocus">
				</div>
			</div>
			<ul v-if="search.term.trim()" class="ts-convo-list simplify-ul min-scroll" :class="{'vx-disabled': search.loading}">
				<template v-if="search.list.length">
					<template v-for="chat in search.list">
						<li :class="{'ts-new-message': chat.is_new, 'ts-unread-message': !chat.seen, 'ts-active-chat': activeChat === chat}">
							<a href="#" @click.prevent="openChat(chat)">
								<div class="convo-avatars" v-if="chat.target.avatar">
									<div class="convo-avatar" v-html="chat.target.avatar"></div>
									<div v-if="chat.author.type === 'post'" class="post-avatar" v-html="chat.author.avatar"></div>
								</div>
								<div class="message-details">
									<b>{{ chat.target.name }}</b>
									<span>{{ chat.excerpt }}</span>
									<span>{{ chat.time }}</span>
								</div>
							</a>
						</li>
					</template>
				</template>
				<template v-else>
					<li class="ts-empty-user-tab">
						<p v-if="search.loading"><?= _x( 'Searching chats', 'messages', 'voxel' ) ?></p>
						<p v-else><?= _x( 'No chats found', 'messages', 'voxel' ) ?></p>
					</li>
				</template>
			</ul>
			<ul v-else class="ts-convo-list simplify-ul min-scroll">
				<template v-for="chat in chats.list">
					<li :class="{'ts-new-message': chat.is_new, 'ts-unread-message': !chat.seen, 'ts-active-chat': activeChat === chat}">
						<a href="#" @click.prevent="openChat(chat)">
							<div class="convo-avatars" v-if="chat.target.avatar">
								<div class="convo-avatar" v-html="chat.target.avatar"></div>
								<div v-if="chat.author.type === 'post'" class="post-avatar" v-html="chat.author.avatar"></div>
							</div>
							<div class="message-details">
								<b>{{ chat.target.name }}</b>
								<span>{{ chat.excerpt }}</span>
								<span>{{ chat.time }}</span>
							</div>
						</a>
					</li>
				</template>
				<div class="ts-btn-group">
					<div class="n-load-more" v-if="chats.hasMore">
						<a href="#" @click.prevent="loadMoreChats" class="ts-btn ts-btn-4" :class="{'vx-pending': chats.loadingMore}">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_load') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							<?= __( 'Load more', 'voxel' ) ?>
						</a>
					</div>
				</div>
			</ul>
		</template>
	</div>
	<div class="ts-message-body" :class="{'vx-disabled': activeChat && activeChat.processing, 'ts-no-chat': !activeChat}">
		<template v-if="activeChat">
			<div class="ts-inbox-top add-spacing flexify">
				<div class="convo-head">
					<div class="ts-convo-name flexify">
						<a class="convo-pic" :href="activeChat.target.link"><span v-html="activeChat.target.avatar"></span></a>
						<a class="convo-name" :href="activeChat.target.link"><span>{{ activeChat.target.name }}</span></a>
						<!-- <template v-if="activeChat.author.type === 'post'">
							<span class="ts-right-icon"></span><a :href="activeChat.author.link"><p>{{ activeChat.author.name }}</p></a>
						</template> -->
					</div>
					<ul class="flexify simplify-ul inbox-top-btns">
						<li class="flexify ">
							<a href="#" @click.prevent="closeActiveChat" class="ts-icon-btn">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_back') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
							</a>
						</li>
						<form-group tag="li" :popup-key="activeChat.key" ref="chatActions" :show-save="false" :show-clear="false" :default-class="false" :show-close="true" class="flexify">
							<template #trigger>
								<a href="#" class="ts-icon-btn" @click.prevent @mousedown="activePopup = activeChat.key">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_more') ) ?: \Voxel\svg( 'menu.svg' ) ?>
								</a>
							</template>
							<template #popup>
								<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
									<ul class="simplify-ul ts-term-dropdown-list min-scroll">
										<li>
											<a :href="activeChat.target.link" class="flexify">
												<div class="ts-term-icon">
													<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_user') ) ?: \Voxel\svg( 'user.svg' ) ?></span>
												</div>
												<span><?= _x( 'View profile', 'messages', 'voxel' ) ?></span>
											</a>
										</li>
										<li>
											<a href="#" @click.prevent="clearChat(activeChat)" class="flexify">
												<div class="ts-term-icon">
													<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_clear') ) ?: \Voxel\svg( 'reload.svg' ) ?></span>
												</div>
												<span><?= _x( 'Clear messages', 'messages', 'voxel' ) ?></span>
											</a>
										</li>
										<li>
											<a href="#" @click.prevent="blockChat(activeChat)" class="flexify">
												<div class="ts-term-icon">
													<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_ban') ) ?: \Voxel\svg( 'ban.svg' ) ?></span>
												</div>
												<span v-if="activeChat.follow_status.author === -1"><?= _x( 'Unblock', 'messages', 'voxel' ) ?></span>
												<span v-else><?= _x( 'Block', 'messages', 'voxel' ) ?></span>
											</a>
										</li>
										<li>
											<a href="#" @click.prevent="clearChat(activeChat, true)" class="flexify">
												<div class="ts-term-icon">
													<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_trash') ) ?: \Voxel\svg( 'trash-can.svg' ) ?></span>
												</div>
												<span><?= _x( 'Leave conversation', 'messages', 'voxel' ) ?></span>
											</a>
										</li>
									</ul>
								</div>
							</template>
						</form-group>
					</ul>
				</div>
			</div>
			<div v-if="activeChat.messages.loading" class="start-convo">
				<span class="ts-loader"></span>
			</div>
			<div v-else-if="!activeChat.messages.list.length" class="start-convo">
				<a :href="activeChat.target.link">
					<span v-html="activeChat.target.avatar"></span>
				</a>
				<span v-if="activeChat.follow_status.author === -1"><?= _x( 'You have blocked this user from messaging you', 'messages', 'voxel' ) ?></span>
				<span v-else-if="activeChat.follow_status.target === -1"><?= _x( 'You have been blocked from messaging this user', 'messages', 'voxel' ) ?></span>
				<span v-else><?= _x( 'Start a conversation with', 'messages', 'voxel' ) ?></span>
				<a :href="activeChat.target.link"><h4>{{ activeChat.target.name }}</h4></a>
			</div>

			<div v-show="activeChat.messages.list && activeChat.messages.list.length" class="ts-conversation-body min-scroll" ref="body">
				<ul class="ts-message-list simplify-ul">
					<li v-if="activeChat.follow_status.author === -1" class="ts-error-message ts-single-message">
						<p><?= _x( 'You have blocked this user from messaging you.', 'messages', 'voxel' ) ?></p>
					</li>
					<li v-else-if="activeChat.follow_status.target === -1" class="ts-error-message ts-single-message">
						<p><?= _x( 'You have been blocked from messaging this user.', 'messages', 'voxel' ) ?></p>
					</li>
					<template v-for="message, message_index in activeChat.messages.list">
						<li
							class="ts-single-message"
							:class="[
								'ts-responder-'+(message.sent_by === 'author' ? 2 : 1),
								'ts-message-id-'+message.id,
								message.sent_by === 'author' && message.seen ? 'ts-message-seen' : '',
								message.tmp ? 'inserted-message' : '',
								message.is_deleted ? 'ts-message-deleted' : '',
								message.is_hidden ? 'ts-message-hidden' : '',
							]"
						>
							<template v-if="message.is_deleted">
								<p class="vx-disabled"><?= _x( 'Deleted', 'messages', 'voxel' ) ?></p>
								<ul class="flexify simplify-ul ms-info">
									<li>{{ message.time }}</li>
								</ul>
							</template>
							<template v-else-if="message.is_hidden">
								<p class="vx-disabled"><?= _x( 'Hidden', 'messages', 'voxel' ) ?></p>
								<ul class="flexify simplify-ul ms-info">
									<li>{{ message.time }}</li>
								</ul>
							</template>
							<template v-else>
								<template v-if="message.files">
									<template v-for="file in message.files">
										<template v-if="file.is_image">
											<a
												:href="file.url"
												class="ts-image-attachment"
												data-elementor-open-lightbox="yes"
												data-elementor-lightbox-slideshow="chat-images"
											>
												<img :src="file.preview" :alt="file.alt" :width="file.width" :height="file.height">
											</a>
										</template>
										<template v-else>
											<p>
												<a :href="file.url" target="_blank">{{ file.name }}</a>
											</p>
										</template>
									</template>
								</template>
								<template v-if="message.has_content">
									<p v-html="message.content"></p>
								</template>
								<template v-if="message._editing">
									<ul class="flexify simplify-ul ms-info">
										<li class="deletems">
											<span @click.prevent="deleteMessage(message)" style="cursor: pointer;">
												<template v-if="message.sent_by === 'author'">
													<?= _x( 'Delete', 'messages', 'voxel' ) ?>
												</template>
												<template v-else>
													<?= _x( 'Hide', 'messages', 'voxel' ) ?>
												</template>
											</span>
										</li>
										<li><span @click="message._editing = false" style="cursor: pointer;"><?= _x( 'Cancel', 'messages', 'voxel' ) ?></span></li>
									</ul>
								</template>
								<template v-else>
									<ul class="flexify simplify-ul ms-info">
										<li @click="message._editing = true" class="message-actions" style="cursor: pointer;"><?= _x( 'More', 'messages', 'voxel' ) ?></li>
										<li class="message-actions">&middot;</li>
										<template v-if="message.sending">
											<li><?= _x( 'Sending', 'messages', 'voxel' ) ?></li>
										</template>
										<template v-else>
											<li>{{ message.time }}</li>
										</template>
									</ul>
								</template>
							</template>
							<template v-if="message.sent_by === 'author' && message.seen && config.seen_badge.enabled">
								<div class="seen-badge"><?= _x( 'Seen', 'messages', 'voxel' ) ?></div>
							</template>
						</li>
					</template>
					<div class="">
						<div class="n-load-more" v-if="activeChat.messages.hasMore">
							<a href="#" @click.prevent="loadMoreMessages(activeChat)" class="ts-btn ts-btn-4" :class="{'vx-pending': activeChat.messages.loadingMore}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_load') ) ?: \Voxel\svg( 'reload.svg' ) ?>
								<?= __( 'Load more', 'voxel' ) ?>
							</a>
						</div>
					</div>
				</ul>
			</div>
			<div class="ts-inbox-bottom" :class="{'vx-disabled': isChatBlocked(activeChat)}">
				<div class="flexify ts-convo-form">
					<span v-html="activeChat.author.avatar" class="active-avatar"></span>
					<div class="compose-message min-scroll">
						<textarea
							ref="composer"
							:value="activeChat.state.content"
							@keydown.enter="enterComposer($event, activeChat)"
							@input="activeChat.state.content = $event.target.value; resizeComposer();"
							:disabled="isChatBlocked(activeChat)"
						></textarea>
						<textarea ref="_composer" disabled style="height:5px;position:fixed;top:-9999px;left:-9999px;visibility:hidden;"></textarea>
						<span v-if="!activeChat.state.content" class="compose-placeholder">
							<template v-if="activeChat.author.type === 'post'">
								<?= \Voxel\replace_vars( _x( 'Reply as @author_name', 'messages', 'voxel' ), [
									'@author_name' => '{{ activeChat.author.name }}',
								] ) ?>
							</template>
							<template v-else>
								<?= _x( 'Your message', 'messages', 'voxel' ) ?>
							</template>
						</span>
					</div>
					<a href="#" v-if="config.files.enabled" @click.prevent="$refs.files.$refs.input.click()" class="ts-icon-btn">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_upload') ) ?: \Voxel\svg( 'upload.svg' ) ?>
					</a>
					<a href="#" v-if="config.files.enabled" ref="mediaTarget" @click.prevent="$refs.files.$refs.mediaLibrary.openLibrary()" class="ts-icon-btn ts-media-lib">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_gallery') ) ?: \Voxel\svg( 'gallery.svg' ) ?>
					</a>
					<form-group
						tag="a"
						href="#"
						@click.prevent
						popup-key="emojiPopup"
						ref="emojiPopup"
						:show-save="false"
						:show-clear="false"
						:default-class="false"
						wrapper-class="ts-emoji-popup"
						class="ts-icon-btn ts-emoji-select"
						@mousedown="showEmojis"
					>
						<template #trigger>
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_emoji') ) ?: \Voxel\svg( 'happy-2.svg' ) ?>
						</template>
						<template #popup>
							<div class="ts-sticky-top uib b-bottom">
								<div class="ts-input-icon flexify">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_ico') ) ?: \Voxel\svg( 'search.svg' ) ?>
									<input type="text" v-model="emojis.search.term" placeholder="<?= esc_attr( _x( 'Search emojis', 'messages', 'voxel' ) ) ?>" class="autofocus">
								</div>
							</div>
							<div class="ts-emoji-list">
								<template v-if="emojis.search.term.trim()">
									<div class="ts-form-group">
										<label v-if="emojis.search.list.length"><?= _x( 'Search results', 'emoji popup', 'voxel' ) ?></label>
										<label v-else><?= _x( 'No emojis found', 'emoji popup', 'voxel' ) ?></label>
										<ul class="flexify simplify-ul">
											<li v-for="emoji in emojis.search.list"><span @click.prevent="insertEmoji( emoji )">{{ emoji }}</span></li>
										</ul>
									</div>
								</template>
								<template v-else>
									<template v-if="emojis.recents.length">
										<div class="ts-form-group">
											<label><?= _x( 'Recently used', 'emoji popup', 'voxel' ) ?></label>
											<ul class="flexify simplify-ul">
												<li v-for="emoji in emojis.recents"><span @click.prevent="insertEmoji( emoji )">{{ emoji }}</span></li>
											</ul>
										</div>
									</template>
									<template v-if="!emojis.loading && emojis.list">
										<template v-for="group, label in emojis.list">
											<div class="ts-form-group">
											    <label>{{ config.l10n.emoji_groups[label] || label }}</label>
												<ul class="flexify simplify-ul">
													<li v-for="emoji in group"><span @click.prevent="insertEmoji( emoji.emoji )">{{ emoji.emoji }}</span></li>
												</ul>
											</div>
										</template>
									</template>
								</template>
							</div>
						</template>
					</form-group>
					<a href="#" @click.prevent="sendMessage(activeChat)" class="ts-icon-btn">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_send') ) ?: \Voxel\svg( 'paper-plane.svg' ) ?>
					</a>
				</div>

				<div class="hidden">
					<field-file
						:field="files"
						:sortable="false"
						ref="files"
						class="ts-status-files"
						media-target=".ts-inbox .ts-media-lib"
						@files-added="sendMessage(activeChat)"
					></field-file>
				</div>
			</div>
		</template>
		<template v-else>
			<div  v-if="chats.loading" class="ts-empty-user-tab">
				<span class="ts-loader"></span>
			</div>
			<div v-else class="ts-empty-user-tab">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ms_chat') ) ?: \Voxel\svg( 'inbox.svg' ) ?>
				<p><?= _x( 'No conversation selected', 'messages', 'voxel' ) ?></p>
			</div>
		</template>
	</div>
</div>

<script type="text/html" id="inbox-file-field">
	<div class="ts-form-group ts-file-upload">
		<label>{{ field.label }}</label>
		<media-popup
			ref="mediaLibrary"
			:custom-target="mediaTarget"
			@save="onMediaPopupSave"
			save-label="<?= esc_attr( _x( 'Send', 'messages', 'voxel' ) ) ?>"
			:multiple="field.props.maxCount > 1"
		></media-popup>
		<input ref="input" type="file" class="hidden" :multiple="field.props.maxCount > 1" :accept="accepts">
	</div>
</script>

<?php require_once locate_template( 'templates/widgets/create-post/_media-popup.php' ) ?>
