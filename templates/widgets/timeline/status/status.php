<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/widgets/timeline/status/_quoted-status.php' );
?>
<script type="text/html" id="vxfeed__status">
	<template v-if="screen === 'edit'">
		<status-composer
			:status="status"
			@cancel="screen = null"
			@update="onUpdate"
		></status-composer>
	</template>
	<div v-else-if="screen === 'deleted'" class="hidden"></div>
	<div v-else ref="wrapper" class="vxf-subgrid">
		<template v-if="status.repost_of">
			<status-single
				:status="status.repost_of"
				@update="status.repost_of = $event"
				:reposted-by="status"
				@quote="$emit('quote', $event)"
				@repost="$emit('repost', $event)"
				@delete="screen = 'deleted'"
			></status-single>
		</template>
		<template v-else>
			<div class="vxf-post" :class="{'vx-pending': state.deleting}">
				<div v-if="status.annotation" class="vxf-highlight flexify">
					<div class="vxf-icon"><component :is="status.annotation.icon"/></div>
					<span>{{ status.annotation.text }}</span>
				</div>
				<div v-else-if="repostedBy" class="vxf-highlight flexify">
					<template v-if="repostedBy.annotation">
						<div class="vxf-icon"><component :is="repostedBy.annotation.icon"/></div>
						<span>{{ repostedBy.annotation.text }}</span>
					</template>
					<template v-else>
						<div class="vxf-icon"><icon-repost/></div>
						<span>
							<a :href="repostedBy.publisher.link">
							{{ repostedBy.publisher.display_name }}</a>
							<?= _x( 'reposted', 'timeline', 'voxel' ) ?>
						</span>
					</template>
				</div>
				<div class="vxf-head flexify">
					<a :href="status.publisher.link" class="vxf-avatar flexify">
						<img :src="status.publisher.avatar_url" :alt="status.publisher.display_name">
					</a>
					<div class="vxf-user flexify">
						<a :href="status.publisher.link">
							{{ status.publisher.display_name }}
							<div v-if="status.publisher.is_verified" class="vxf-icon vxf-verified">
								<icon-verified/>
							</div>
						</a>
						<span>
							<template v-if="status.publisher.username">
								<a :href="status.publisher.link">@{{ status.publisher.username }}</a>
							</template>
							<template v-if="showPostLink">
								<a :href="status.post.link">{{ status.post.title }}</a>
							</template>
							<a :href="status.link" :title="status.edited_at ? $root.config.l10n.editedOn.replace('@date',status.edited_at) : null">{{ status.created_at }}</a>
							<span v-for="badge in status.badges" :data-badge="badge.key" class="vxf-badge">
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
								<span><?= _x( 'Share via', 'timeline', 'voxel' ) ?></span>
							</a>
						</li>
						<li v-if="status.current_user.can_edit && status.link_preview">
							<a href="#" class="flexify" @click.prevent="removeLinkPreview">
								<span><?= _x( 'Remove link preview', 'timeline', 'voxel' ) ?></span>
							</a>
						</li>
						<li v-if="status.current_user.can_edit && $root.config.settings.posts.editable">
							<a href="#" class="flexify" @click.prevent="screen = 'edit'; showActions = false;">
								<span><?= _x( 'Edit', 'timeline', 'voxel' ) ?></span>
							</a>
						</li>
						<template v-if="status.current_user.can_moderate">
							<li v-if="status.is_pending">
								<a href="#" class="flexify" @click.prevent="markApproved">
									<span><?= _x( 'Approve', 'timeline', 'voxel' ) ?></span>
								</a>
							</li>
							<li v-if="!status.is_pending">
								<a href="#" class="flexify" @click.prevent="markPending">
									<span><?= _x( 'Mark as pending', 'timeline', 'voxel' ) ?></span>
								</a>
							</li>
						</template>
						<li v-if="status.current_user.can_delete">
							<a href="#" class="flexify" @click.prevent="deleteStatus">
								<span><?= _x( 'Delete', 'timeline', 'voxel' ) ?></span>
							</a>
						</li>
					</dropdown-list>
				</div>
				<div class="vxf-body">
					<template v-if="review">
						<div class="rev-score" :style="{'--ts-accent-1': review.level.color}">
							<ul v-if="review.config.input_mode === 'stars'" class="rev-star-score flexify simplify-ul">
								<li v-for="level_score in [-2, -1, 0, 1, 2]" :class="{active: status.review.score >= (level_score - 0.5) }">
									<span v-if="status.review.score >= (level_score - 0.5)" v-html="review.config.active_icon || review.config.default_icon"></span>
									<span v-else v-html="review.config.inactive_icon || review.config.default_icon"></span>
								</li>
							</ul>
							<div v-else class="rev-num-score flexify">
								{{ status.review.formatted_score }}
							</div>
							<span>{{ review.level.label }}</span>
						</div>
						<div class="rev-cats" v-if="review.categories.length >= 2">
							<template v-for="category in review.categories">
								<div class="review-cat" :style="{'--ts-accent-1': category.level.color}">
									<span>{{ category.label }}</span>
									<ul class="rev-chart simplify-ul">
										<template v-for="level_score in [-2, -1, 0, 1, 2]">
											<li :class="{active: category.score >= (level_score - 0.5) }"></li>
										</template>
									</ul>
								</div>
							</template>
						</div>
					</template>

					<div class="vxf-body-text" v-html="!truncatedContent.exists || readMore ? highlightedContent : truncatedContent.content"></div>
					<a href="#" v-if="truncatedContent.exists" @click.prevent="readMore = !readMore" class="vxfeed__read-more">
						<template v-if="readMore">
							<?= _x( 'Read less &#9652;', 'timeline', 'voxel' ) ?>
						</template>
						<template v-else>
							<?= _x( 'Read more &#9662;', 'timeline', 'voxel' ) ?>
						</template>
					</a>

					<div v-if="status.private" class="vxf-body-text" style="opacity: .5;">
						<?= _x( 'This post has restricted visibility.', 'timeline', 'voxel' ) ?>
					</div>

					<ul v-if="status.files.length" class="vxf-gallery simplify-ul">
						<li v-for="file in status.files">
							<a :href="file.url" data-elementor-open-lightbox="yes" :data-elementor-lightbox-slideshow="status.files.length > 1 ? 'vxtl_'+status.id : null">
								<img :src="file.preview" :alt="file.alt">
							</a>
						</li>
					</ul>

					<template v-if="status.link_preview">
						<!-- YouTube Embed -->
						<template v-if="status.link_preview.type === 'youtube'">
							<div class="vxf-youtube-embed">
								<iframe 
									:src="status.link_preview.embed_url + '?rel=0&modestbranding=1&showinfo=0&controls=1&disablekb=1&iv_load_policy=3&fs=1&autoplay=0&mute=0&loop=0&playlist=' + status.link_preview.video_id" 
									frameborder="0" 
									allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
									allowfullscreen
									loading="lazy"
									class="vxf-youtube-iframe">
								</iframe>
							</div>
						</template>
						
						<!-- Regular Link Preview -->
						<template v-else>
							<a :href="status.link_preview.url" target="_blank" rel="noopener noreferrer nofollow" class="vxf-link flexify">
								<img :src="status.link_preview.image">
								<div class="vxf-link-details flexify">
									<b>{{ status.link_preview.title }}</b>
									<span href="#" class="vxf-icon vxf-link-source">
										{{ status.link_preview.domain }}
										<icon-external-link/>
									</span>
								</div>
							</a>
						</template>
					</template>

					<template v-if="status.quote_of">
						<quoted-status :quote-of="status.quote_of"></quoted-status>
					</template>
				</div>
				<div class="vxf-footer flexify">
					<div class="vxf-actions flexify">
						<a href="#" @click.prevent="likeStatus" ref="likeBtn" class="vxf-icon" :class="{'vxf-liked': status.current_user.has_liked, 'vx-inert': state.liking, 'vx-pending': status.is_pending && ! status.current_user.has_liked}">
							<template v-if="status.current_user.has_liked">
								<icon-liked/>
							</template>
							<template v-else>
								<icon-like/>
							</template>
							<div class="ray-holder">
								<div v-for="n in 8" class="ray"></div>
							</div>
						</a>
						<a v-if="$root.config.settings.reposts.enabled" href="#" class="vxf-icon" ref="repostBtn" @click.prevent @mousedown="showRepost = true" :class="{'vxf-reposted': status.current_user.has_reposted, 'vx-inert': state.reposting, 'vx-pending': status.is_pending}">
							<icon-repost/>
							<div class="ray-holder">
								<div v-for="n in 8" class="ray"></div>
							</div>
						</a>
						<a :class="{'vx-pending': status.is_pending}" href="#" @click.prevent="writeReply" class="vxf-icon">
							<icon-reply/>
						</a>
						
						<a v-if="status.replies.count" href="#" @click.prevent="showComments = !showComments" class="vxf-icon vxf-has-replies">
							<icon-comment/>
						</a>
					</div>
					<div v-if="status.likes.count || status.replies.count" class="vxf-details flexify">
						<div v-if="status.likes.last3.length" class="vxf-recent-likes flexify">
							<template v-for="like in status.likes.last3">
								<img :src="like.avatar_url" :alt="like.display_name" :title="like.display_name">
							</template>
						</div>
						<span>
							<template v-if="status.likes.count">
								<span>{{ status.likes.count === 1 ? $root.config.l10n.oneLike : $root.config.l10n.countLikes.replace('@count',status.likes.count) }}</span>
							</template>
							<a v-if="status.replies.count" href="#" @click.prevent="showComments = !showComments">
								{{ status.replies.count === 1 ? $root.config.l10n.oneReply : $root.config.l10n.countReplies.replace('@count',status.replies.count) }}
							</a>
						</span>
					</div>
					
					<teleport to="body">
						<transition name="form-popup">
							<form-popup class="xs-width" v-if="showRepost" :target="$refs.repostBtn" @blur="showRepost = false">
								<div class="ts-term-dropdown ts-md-group">
									<ul class="simplify-ul ts-term-dropdown-list min-scroll">
										<li>
											<a href="#" class="flexify" @click.prevent="repostStatus(); showRepost = false;">
												<span>
													<template v-if="status.current_user.has_reposted">
														<?= _x( 'Unrepost', 'timeline', 'voxel' ) ?>
													</template>
													<template v-else>
														<?= _x( 'Repost', 'timeline', 'voxel' ) ?>
													</template>
												</span>
											</a>
										</li>
										<li>
											<a href="#" class="flexify" @click.prevent="quoteStatus(); showRepost = false;">
												<span><?= _x( 'Quote', 'timeline', 'voxel' ) ?></span>
											</a>
										</li>
									</ul>
								</div>
								<template #controller><template></template></template>
							</form-popup>
						</transition>
					</teleport>
				</div>
			</div>
			<div class="vxf__quote-composer vxf-subgrid" v-if="showQuoteBox">
				<status-composer
					@publish="onQuotePublish"
					@cancel="$refs.quoter.reset(); showQuoteBox = false;"
					@mounted="$refs.quoter.focus()"
					ref="quoter"
					:quote-of="status"
				></status-composer>
			</div>
			<template v-if="showComments">
				<comment-feed
					@ready="commentFeed.ready = true"
					ref="commentFeed"
					:status="status"
					:statusRef="this"
					:depth="1"
				></comment-feed>
			</template>
		</template>
	</div>
</script>