<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__review-score">
	<template v-if="config.input_mode === 'stars'">
		<div class="vxf-create-section review-cats">
			<div v-for="category in config.categories" class="ts-form-group review-category">
				<label>
					{{ category.label }}
					<span v-if="getActiveScore(category)">{{ getActiveScore(category).label }}</span>
				</label>
				<ul class="rs-stars simplify-ul flexify">
					<template v-for="level in config.rating_levels">
						<li
							class="flexify"
							@click.prevent="setScore(category, level)"
							:class="{active: isScoreCovered(category, level), selected: isScoreSelected(category, level)}"
							:style="{'--active-accent': isScoreCovered(category, level) && getActiveScore(category) ? getActiveScore(category).color : null}"
						>
							<template v-if="isScoreCovered(category, level)">
								<div class="ts-star-icon" v-html="config.active_icon || config.default_icon"></div>
							</template>
							<template v-else>
								<div  class="ts-star-icon" v-html="config.inactive_icon || config.default_icon"></div>
							</template>
							<div class="ray-holder">
								<div v-for="n in 8" class="ray"></div>
							</div>
						</li>
					</template>
				</ul>
			</div>
		</div>
		
	</template>
	<template v-else>
		<div class="vxf-create-section review-cats">
			<div v-for="category in config.categories" class="ts-form-group review-category">
			<label>{{ category.label }}</label>
			<ul class="rs-num simplify-ul flexify">
				<template v-for="level in config.rating_levels">
					<li
						@click.prevent="setScore(category, level)"
						:class="{active: isScoreSelected(category, level)}"
						:style="{'--active-accent': isScoreSelected(category, level) ? level.color : null}"
					>
						{{ level.score + 3 }}
						<span>{{ level.label }}</span>
					</li>
				</template>
			</ul>
			</div>
		</div>
	</template>
</script>