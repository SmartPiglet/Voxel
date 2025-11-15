<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-tag-autocomplete">
	<ul class="nvx-quick-tags">
		<template v-for="result, index in results">
			<template v-if="result.data.type === 'method'">
				<li
					:class="{'is-active': focusedResult === index}"
					ref="result"
					@click="selectItem(result)"
					:title="result.data.meta.parentLabels.join(' / ') + ' / ' + result.data.method.label"
				>
					<span>{{ result.data.meta.parentLabels.join(' / ') }}</span>
					<p>{{ result.data.method.label }}</p>
				</li>
			</template>
			<template v-else>
				<li
					:class="{'is-active': focusedResult === index}"
					ref="result"
					@click="selectItem(result)"
					:title="result.data.meta.parentLabels.join(' / ') + ' / ' + result.data.property.label"
				>
					<span>{{ result.data.meta.parentLabels.join(' / ') }}</span>
					<p>{{ result.data.property.label }}</p>
				</li>
			</template>
		</template>

		<template v-if="!results.length">
			<li><p>No results found.</p></li>
		</template>
	</ul>
</script>
