<script type="text/html" id="create-post-media-popup">
	<slot>
		<a @click.prevent href="#" ref="popupTarget" @mousedown="openLibrary" class="ts-btn ts-btn-4 form-btn">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_media_ico') ) ?: \Voxel\svg( 'gallery.svg' ) ?>
			<span><?= _x( 'Media library', 'media library', 'voxel' ) ?></span>
		</a>
	</slot>
	<teleport to="body">
		<transition name="form-popup">
			<form-popup
				ref="popup"
				v-if="active"
				class="ts-media-library prmr-popup"
				:target="customTarget || $refs.popupTarget"
				@blur="$emit('blur'); active = false; selected = {};"
				:save-label="saveLabel || <?= esc_attr( wp_json_encode( _x( 'Save', 'media library', 'voxel' ) ) ) ?>"
				@save="save"
				@clear="clear"
			>
				<div class="ts-sticky-top uib b-bottom">
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
						<input
							v-model="search.term" ref="searchInput" type="text" class="autofocus"
							:placeholder="<?= esc_attr( wp_json_encode( _x( 'Search files', 'media library', 'voxel' ) ) ) ?>"
						>
					</div>
				</div>

				<div v-if="search.term.trim()" class="ts-form-group min-scroll ts-list-container" :class="{'vx-disabled': search.loading}">
					<template v-if="search.list.length">
						<div class="ts-file-list">
							<div
								v-for="file in search.list"
								class="ts-file"
								:style="getStyle(file)"
								:class="{selected: selected[ file.id ], 'ts-file-img': isImage(file)}"
								@click="selectFile(file)"
							>
								<div class="ts-file-info">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?><code>{{ file.name }}</code>
								</div>
								<div class="ts-remove-file ts-select-file">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_select_ico') ) ?: \Voxel\svg( 'checkmark.svg' ) ?>
								</div>
							</div>
						</div>
						<div>
							<a href="#" v-if="search.has_more" @click.prevent="search.loading_more = true; serverSearchFiles(this, true)" class="ts-btn ts-btn-4" :class="{'vx-pending': search.loading_more}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_load_ico') ) ?: \Voxel\svg( 'reload.svg' ) ?>
								<?= __( 'Load more', 'voxel' ) ?>
							</a>
						</div>
					</template>
					<div v-else class="ts-empty-user-tab">
						<p v-if="search.loading"><?= _x( 'Searching files', 'media library', 'voxel' ) ?></p>
						<p v-else><?= _x( 'No files found', 'media library', 'voxel' ) ?></p>
					</div>
				</div>
				<div v-else class="ts-form-group min-scroll ts-list-container">
					<div class="ts-file-list">
						<div
							v-for="file in sessionFiles()"
							class="ts-file"
							:style="getStyle(file)"
							:class="{selected: selected[ file._id ], 'ts-file-img': isImage(file)}"
							@click="selectSessionFile(file)"
						>
							<div class="ts-file-info">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?><code>{{ file.name }}</code>
							</div>
							<div class="ts-remove-file">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?>
							</div>
							<div class="ts-remove-file ts-select-file">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_select_ico') ) ?: \Voxel\svg( 'checkmark.svg' ) ?>
							</div>
						</div>
						<div
							v-for="file in files"
							class="ts-file"
							:style="getStyle(file)"
							:class="{selected: selected[ file.id ], 'ts-file-img': isImage(file)}"
							@click="selectFile(file)"
						>
							<div class="ts-file-info">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?><code>{{ file.name }}</code>
							</div>
							<div class="ts-remove-file ts-select-file">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_select_ico') ) ?: \Voxel\svg( 'checkmark.svg' ) ?>
							</div>
						</div>
					</div>

					<div v-if="!loading && !files.length" class="ts-empty-user-tab">
						<span><?= _x( 'You have no files in your media library.', 'media library', 'voxel' ) ?></span>
					</div>
					<div v-else>
						<a v-if="loading" href="#" class="ts-btn ts-btn-4 load-more-btn">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_load_ico') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							<?= __( 'Loading', 'voxel' ) ?>
						</a>
						<a
							v-else-if="has_more && !loading"
							@click.prevent="loadMore"
							href="#"
							class="ts-btn ts-btn-4"
						>	
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_load_ico') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							<?= __( 'Load more', 'voxel' ) ?>
						</a>
					</div>
				</div>
			</form-popup>
		</transition>
	</teleport>
</script>
