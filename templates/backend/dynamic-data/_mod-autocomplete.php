<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-mod-autocomplete">
	<ul class="nvx-quick-tags nvx-insert-mod">
		<template v-for="category in categorizedResults">
			<li class="vx-inert">
				<span>{{ category.label }}</span>
			</li>
			<template v-for="result, index in category.items">
				<li :class="{'is-active': focusedResult === result.resultIndex}" ref="result" @click="selectItem(result)">
					<p>
						{{ result.data.modifier.label }}
						<code>{{ `.${result.data.modifier.key}(${result.data.modifier.arguments.map( arg => arg.label ).join(', ')})` }}</code>
					</p>
				</li>
			</template>
		</template>

		<template v-if="!categorizedResults.length">
			<li><p>No results found.</p></li>
		</template>
	</ul>
</script>
