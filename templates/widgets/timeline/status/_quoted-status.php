<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__quoted-status">
	<template v-if="quoteOf.exists">
		<a :href="quoteOf.link">
			<div class="vxf-post vxf__quoted-post">
				<div class="vxf-head flexify">
					<a :href="quoteOf.publisher.link" class="vxf-avatar flexify">
						<img :src="quoteOf.publisher.avatar_url" :alt="quoteOf.publisher.display_name">
					</a>
					<div class="vxf-user flexify">
						<a :href="quoteOf.publisher.link">
							{{ quoteOf.publisher.display_name }}
							<div v-if="quoteOf.publisher.is_verified" class="vxf-icon vxf-verified">
								<icon-verified/>
							</div>
						</a>
						<span>{{ titleDetails }}</span>
					</div>
				</div>

				<div class="vxf-body">
					<template v-if="review">
						<div class="rev-score" :style="{'--ts-accent-1': review.level.color}">
							<ul v-if="review.config.input_mode === 'stars'" class="rev-star-score flexify simplify-ul">
								<li v-for="level_score in [-2, -1, 0, 1, 2]" :class="{active: quoteOf.review.score >= (level_score - 0.5) }">
									<span v-if="quoteOf.review.score >= (level_score - 0.5)" v-html="review.config.active_icon || review.config.default_icon"></span>
									<span v-else v-html="review.config.inactive_icon || review.config.default_icon"></span>
								</li>
							</ul>
							<div v-else class="rev-num-score flexify">
								{{ quoteOf.review.formatted_score }}
							</div>
							<span>{{ review.level.label }}</span>
						</div>
					</template>
					<div class="vxf-body-text" v-html="truncatedContent.content"></div>

					<div v-if="quoteOf.private" class="vxf-body-text" style="opacity: .5;">
						<?= _x( 'This post has restricted visibility.', 'timeline', 'voxel' ) ?>
					</div>

					<ul v-if="quoteOf.files.length" class="vxf-gallery simplify-ul">
						<li v-for="file in quoteOf.files">
							<a :href="file.url">
								<img :src="file.preview" :alt="file.alt">
							</a>
						</li>
					</ul>
				</div>
			</div>
		</a>
	</template>
	<template v-else>
		<div class="vxf-post vxf__quoted-post vx-inert">
			<div class="vxf-body-text" style="opacity: .5;">
				<?= _x( 'This post is unavailable.', 'timeline', 'voxel' ) ?>
			</div>
		</div>
	</template>
</script>