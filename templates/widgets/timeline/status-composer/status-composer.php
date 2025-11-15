<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/widgets/create-post/_media-popup.php' );
require_once locate_template( 'templates/widgets/timeline/partials/_file-upload.php' );
require_once locate_template( 'templates/widgets/timeline/partials/_emoji-picker.php' );
require_once locate_template( 'templates/widgets/timeline/status-composer/_review-score.php' );
?>
<script type="text/html" id="vxfeed__composer">
	<template v-if="quoteOf">
		<div class="vxf-split flexify">
			<span><?= _x( 'Quote post', 'timeline', 'voxel' ) ?></span>
		</div>
	</template>
	<div class="vxf-create-post flexify" :class="{'vxf-expanded': isExpanded}" ref="wrapper" :id="uniqueId" @dragenter="onDragEnter">
		<div
			v-if="isDragOver"
			class="vxf__dropmask"
			@dragleave.prevent="isDragOver = false"
			@drop.prevent="onDrop"
			@dragenter.prevent
			@dragover.prevent
		></div>
		<div v-if="avatarUrl" class="vxf-avatar flexify">
			<img :src="avatarUrl">
		</div>
		
		<div class="vxf-create-post__content">
			<div class="vxf-content__highlighter" ref="highlighter" v-html="highlightedContent"></div>
			<textarea
				class="vxf-content__textarea"
				ref="textarea"
				v-model="data.content"
				:placeholder="placeholder"
				@focus="isFocused = true"
				@input="onInput"
				@compositionstart="onCompositionStart"
				@compositionupdate="onCompositionUpdate"
				@compositionend="onCompositionEnd"
				@keydown="onKeydown"
				@scroll="onScroll"
				:maxlength="settings.content_maxlength"
			></textarea>
		</div>

		<transition-height>
			<div v-if="isExpanded" class="vxf-footer-wrapper">
				<template v-if="reviewConfig">
					<review-score :config="reviewConfig" v-model="data.rating"></review-score>
				</template>

				<template v-if="settings.gallery_enabled">
					<file-upload
						v-model="data.files"
						:sortable="false"
						:allowed-file-types="settings.gallery_allowed_formats.join(',')"
						:max-file-count="settings.gallery_max_uploads"
						ref="mediaUploader"
						:context="this"
					></file-upload>
				</template>

				<div class="vxf-footer flexify">
					<div class="vxf-actions flexify">
						<template v-if="settings.gallery_enabled">
							<a href="#" @click.prevent @mousedown="$refs.mediaUploader.$refs.mediaLibrary.openLibrary()" class="vxf-icon vxf-media-target">
								<icon-gallery/>
							</a>
							<a href="#" @click.prevent="$refs.mediaUploader.$refs.input.click()" class="vxf-icon">
								<icon-upload/>
							</a>
						</template>
						<emoji-picker @select="insertText($event)" :composer="this"></emoji-picker>
					</div>
					<div class="vxf-buttons flexify">
						<a href="#" @click.prevent="onCancel" class="ts-btn ts-btn-1"><?= _x( 'Cancel', 'timeline composer', 'voxel' ) ?></a>
						<a href="#" @click.prevent="publish" class="ts-btn ts-btn-2" :class="{'vx-pending': submitting}">
							<template v-if="submitting">
								<div class="ts-loader-wrapper">
									<span class="ts-loader"></span>
								</div>
							</template>
							<template v-else>
								<template v-if="status">
									<?= _x( 'Update', 'timeline composer', 'voxel' ) ?>
								</template>
								<template v-else>
									<?= _x( 'Publish', 'timeline composer', 'voxel' ) ?>
								</template>
							</template>
						</a>
					</div>
				</div>
			</div>
		</transition-height>
	</div>
	
	<teleport to="body">
		<div v-show="mentions.show" class="vxfeed__mentions" :style="mentions.style" ref="mentions">
			<template v-if="mentions.show && mentions.list.length">
				<ul class="simplify-ul">
					<li v-for="mention, index in mentions.list">
						<a href="#" @click.prevent="selectMention(mention)" :class="{'is-active': mentions.focused === index}">
							<strong>{{ mention.display_name }}</strong>
							<span>@{{ mention.username }}</span>
						</a>
					</li>
				</ul>
			</template>
		</div>
	</teleport>
</script>