<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__comment-feed">
	<div class="vxf-comment-level vxf-subgrid" :class="{'vxf-second-level': !!this.parentComment}">
		<a v-if="statusRef?.showActiveComment" class="ts-btn ts-btn-4" href="#" @click.prevent="exitSingleMode">
			<?= _x( 'Show all replies', 'timeline', 'voxel' ) ?>
		</a>
		<template v-if="feed.list.length">
			<template v-for="comment, i in feed.list" :key="comment.id">
				<comment-single
					:status="status"
					:comment="comment"
					@update="feed.list[i] = $event"
					:depth="depth"
					:parent-comment="parentComment"
					@flat-reply="onFlatReply(comment)"
				></comment-single>
			</template>
			<template v-if="feed.hasMore">
				<a href="#" @click.prevent="loadMore" class="ts-btn ts-btn-4" :class="{'vx-pending': feed.loading}">
					<icon-loading/>
					<?= _x( 'Load more replies', 'timeline', 'voxel' ) ?>
				</a>
			</template>
		</template>
		<template v-else-if="feed.loading">
			<div class="ts-no-posts">
				<span class="ts-loader"></span>	
			</div>
		</template>

		<?php if ( is_user_logged_in() ): ?>
			<comment-composer
				v-if="!((statusRef?.showActiveComment && !forceShowComposer) || status.is_pending )"
				ref="composer"
				:status="status"
				:reply-to="parentComment"
				@cancel="$refs.composer.reset(); $refs.composer.isFocused = false;"
				@publish="onPublish"
			></comment-composer>
		<?php endif ?>
	</div>
</script>