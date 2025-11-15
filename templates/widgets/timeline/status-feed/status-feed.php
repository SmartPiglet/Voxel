<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__feed">
	
	<?php if ( is_user_logged_in() ): ?>
		<template v-if="config.composer.can_post && !isSingle">
			<div style="min-height: 61px;">
				<status-composer
					@publish="onPublish"
					@cancel="$refs.composer.reset(); $refs.composer.isFocused = false;"
					ref="composer"
				></status-composer>
			</div>
		</template>
	<?php endif ?>
	
	<div class="vxf-filters" v-if="!isSingle && showFilters">
		<div v-if="$root.config.settings.search.enabled" class="ts-form">
			<div class="ts-input-icon flexify">
				<icon-search/>
				<input :value="search.query" @keydown.enter="runSearch" type="text" placeholder="<?= esc_attr( _x( 'Search', 'timeline', 'voxel' ) ) ?>" class="autofocus" :maxlength="config.settings.search.maxlength">
			</div>
		</div>

		<template v-if="Object.keys(config.settings.filtering_options).length >= 2">
			<a href="#" @click.prevent @mousedown="filterBy.showList = true" ref="filterBy">
				{{ config.settings.filtering_options[filterBy.active] }}
				<div class="ts-down-icon"></div>
			</a>
			<dropdown-list v-if="filterBy.showList" :target="$refs.filterBy" @blur="filterBy.showList = false">
				<li v-for="filterLabel, filterKey in config.settings.filtering_options">
					<a href="#" class="flexify" @click.prevent="setActiveFilter(filterKey)">
						<span>{{ filterLabel }}</span>
					</a>
				</li>
			</dropdown-list>
		</template>

		<template v-if="config.settings.ordering_options.length">
			<a href="#" @click.prevent @mousedown="orderBy.showList = true" ref="orderBy">
				{{ orderBy.active.label }}
				<div class="ts-down-icon"></div>
			</a>
			<dropdown-list v-if="orderBy.showList" :target="$refs.orderBy" @blur="orderBy.showList = false">
				<li v-for="order in config.settings.ordering_options">
					<a href="#" class="flexify" @click.prevent="setActiveOrder(order)">
						<span>{{ order.label }}</span>
					</a>
				</li>
			</dropdown-list>
		</template>
	</div>

	<template v-if="list.length">
		<template v-for="status, i in list" :key="status.id">
			<status-single
				:status="status"
				@update="list[i] = $event"
				@quote="onQuote"
				@repost="onRepost"
				:feedRef="this"
			></status-single>
		</template>
		<template v-if="hasMore">
			<a href="#" @click.prevent="loadMore" class="ts-load-more ts-btn ts-btn-1" :class="{'vx-pending': loading}">
				<icon-loading/>
				<?= __( 'Load more', 'voxel' ) ?>
			</a>
		</template>
	</template>
	<template v-else>
		<div class="ts-no-posts">
			<template v-if="loading">
				<span class="ts-loader"></span>
			</template>
			<template v-else>
				<icon-no-post/>
				<p>{{ $root.l10n.no_activity }}</p>
			</template>
		</div>
	</template>
</script>