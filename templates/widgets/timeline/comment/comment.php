<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__comment">
	<template v-if="screen === 'edit'">
		<comment-composer
			:comment="comment"
			@cancel="screen = null"
			@update="onUpdate"
		></comment-composer>
	</template>
	<template v-else-if="screen === 'deleted' || comment._deleted" class="hidden"></template>
	<div v-else class="vxf-post vxf-comment" :class="{'vx-pending': state.deleting}">
		<div class="vxf-head flexify">
			<a :href="comment.publisher.link" class="vxf-avatar flexify">
				<img :src="comment.publisher.avatar_url" :alt="comment.publisher.display_name">
			</a>
			<div class="vxf-user flexify">
				<a :href="comment.publisher.link">
					{{ comment.publisher.display_name }}
					<div v-if="comment.publisher.is_verified" class="vxf-icon vxf-verified">
						<icon-verified/>
					</div>
				</a>

				<span>
					<template v-if="comment.publisher.username">
						<a :href="comment.publisher.link">@{{ comment.publisher.username }}</a>
					</template>
					<a :href="comment.link" :title="comment.edited_at ? $root.config.l10n.editedOn.replace('@date',comment.edited_at) : null">{{ comment.created_at }}</a>
					<span v-for="badge in comment.badges" :data-badge="badge.key" class="vxf-badge">
						{{ badge.label }}
					</span>
				</span>
			</div>
			<a href="#" class="vxf-icon vxf-more" ref="actionsTarget" @click.prevent @mousedown="showActions = true">
				<icon-more/>
			</a>
			<dropdown-list v-if="showActions" :target="$refs.actionsTarget" @blur="showActions = false">
				<li>
					<a href="#" class="flexify" @click.prevent="copyLink">
						<span><?= _x( 'Copy link', 'timeline', 'voxel' ) ?></span>
					</a>
				</li>
				<li v-if="$root.$w.navigator.share">
					<a href="#" class="flexify" @click.prevent="share">
						<span><?= _x( 'Share', 'timeline', 'voxel' ) ?></span>
					</a>
				</li>
				<li v-if="comment.current_user.can_edit && $root.config.settings.replies.editable">
					<a href="#" class="flexify" @click.prevent="screen = 'edit'; showActions = false;">
						<span><?= _x( 'Edit', 'timeline', 'voxel' ) ?></span>
					</a>
				</li>
				<template v-if="comment.current_user.can_moderate">
					<li v-if="comment.is_pending">
						<a href="#" class="flexify" @click.prevent="markApproved">
							<span><?= _x( 'Approve', 'timeline', 'voxel' ) ?></span>
						</a>
					</li>
					<li v-if="!comment.is_pending">
						<a href="#" class="flexify" @click.prevent="markPending">
							<span><?= _x( 'Mark as pending', 'timeline', 'voxel' ) ?></span>
						</a>
					</li>
				</template>
				<li v-if="comment.current_user.can_delete">
					<a href="#" class="flexify" @click.prevent="deleteComment">
						<span><?= _x( 'Delete', 'timeline', 'voxel' ) ?></span>
					</a>
				</li>
			</dropdown-list>
		</div>
		<div class="vxf-body">
			<div class="vxf-body-text" v-html="!truncatedContent.exists || readMore ? highlightedContent : truncatedContent.content"></div>
			<a href="#" v-if="truncatedContent.exists" @click.prevent="readMore = !readMore" class="vxfeed__read-more">
				<template v-if="readMore">
					<?= _x( 'Read less &#9652;', 'timeline', 'voxel' ) ?>
				</template>
				<template v-else>
					<?= _x( 'Read more &#9662;', 'timeline', 'voxel' ) ?>
				</template>
			</a>
			<ul v-if="comment.files.length" class="vxf-gallery simplify-ul">
				<li v-for="file in comment.files">
					<a :href="file.url">
						<img :src="file.preview" :alt="file.alt">
					</a>
				</li>
			</ul>
		</div>
		<div class="vxf-footer flexify">
			<div class="vxf-actions flexify">
				<a href="#" @click.prevent="likeComment" ref="likeBtn" class="vxf-icon" :class="{'vxf-liked': comment.current_user.has_liked, 'vx-inert': state.liking, 'vx-pending': ( status.is_pending || comment.is_pending ) && ! comment.current_user.has_liked }">
					<template v-if="comment.current_user.has_liked">
						<icon-liked/>
					</template>
					<template v-else>
						<icon-like/>
					</template>
					<div class="ray-holder">
						<div v-for="n in 8" class="ray"></div>
					</div>
				</a>
				<a href="#" class="vxf-icon" @click.prevent="depth >= maxDepth ? $emit('flat-reply') : writeReply()"
					:class="{'vx-pending': status.is_pending || comment.is_pending}">
					<icon-reply/>
				</a>
				<a v-if="comment.replies.count" href="#" class="vxf-icon vxf-has-replies" @click.prevent="showComments = !showComments">
					<icon-comment/>
				</a>
			</div>
			<div v-if="comment.likes.count > 0 || comment.replies.count > 0" class="vxf-details flexify">
				<div v-if="comment.likes.last3.length" class="vxf-recent-likes flexify">
					<template v-for="like in comment.likes.last3">
						<img :src="like.avatar_url" :alt="like.display_name" :title="like.display_name">
					</template>
				</div>
				<span>
					<template v-if="comment.likes.count">
						<span>{{ comment.likes.count === 1 ? $root.config.l10n.oneLike : $root.config.l10n.countLikes.replace('@count',comment.likes.count) }}</span>
					</template>
					<a v-if="comment.replies.count" href="#" @click.prevent="showComments = !showComments">
						{{ comment.replies.count === 1 ? $root.config.l10n.oneReply : $root.config.l10n.countReplies.replace('@count',comment.replies.count) }}
					</a>
				</span>
			</div>
			
		</div>
		<template v-if="showComments">
			<comment-feed ref="commentFeed" :status="status" :parent-comment="comment" :depth="depth + 1"></comment-feed>
		</template>
	</div>
</script>