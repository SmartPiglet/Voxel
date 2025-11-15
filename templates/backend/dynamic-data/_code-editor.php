<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-code-editor">
	<template v-if="layout === 'input'">
		<div class="dtags-mode-input">
			<div class="dtags-mode-input__content">
				<pre class="ts-snippet nvx-scrollable" ref="highlighter" v-html="highlightedContent"></pre>
				<textarea
					v-model="content"
					rows="1"
					:placeholder="placeholder"
					@input="onInput"
					@click="onClick"
					@keydown="onKeydown"
					@scroll="onScroll"
					@drop="onDrop"
					ref="editor"
					class="nvx-scrollable"
					spellcheck="false"
				></textarea>
			</div>

			<teleport :to="$root.window.document.querySelector('.nvx-editor')" :disabled="!resizeObserverBound">
				<div v-show="dropdown.show" class="dtags-ac nvx-scrollable" :style="dropdown.style" ref="dropdown">
					<template v-if="dropdown.show">
						<template v-if="dropdown.mode === 'insertModifier'">
							<mod-autocomplete
								:search="dropdown.insertModifier.query.content"
								:token="dropdown.insertModifier.tag"
								:key="dropdown.insertModifier.tag"
								ref="suggestions"
								@select-mod="dropdownModSelected($event)"
							></mod-autocomplete>
						</template>
						<template v-else>
							<tag-autocomplete
								:search="dropdown.insertTag.query.content"
								ref="suggestions"
								@select-tag="dropdownTagSelected($event)"
								:key="dropdown.insertTag.context"
								:context="dropdown.insertTag.context || tagAutocompleteContext"
							></tag-autocomplete>
						</template>
					</template>
				</div>
			</teleport>
		</div>
	</template>
	<template v-else>
		<div class="dtags-wrapper">
			<div class="dtags-content" :class="{ 'vxs-font': shouldUseSmallFont }">
				<pre class="ts-snippet nvx-scrollable" ref="highlighter" v-html="highlightedContent"></pre>
				<textarea
					v-model="content"
					rows="4"
					placeholder="Press @ to quickly add a tag, or pick one from the left sidebar"
					@input="onInput"
					@click="onClick"
					@keydown="onKeydown"
					@scroll="onScroll"
					@drop="onDrop"
					ref="editor"
					class="nvx-scrollable"
					spellcheck="false"
				></textarea>
			</div>

			<div v-show="dropdown.show" class="dtags-ac nvx-scrollable" :style="dropdown.style" ref="dropdown">
				<template v-if="dropdown.show">
					<template v-if="dropdown.mode === 'insertModifier'">
						<mod-autocomplete
							:search="dropdown.insertModifier.query.content"
							:token="dropdown.insertModifier.tag"
							:key="dropdown.insertModifier.tag"
							ref="suggestions"
							@select-mod="dropdownModSelected($event)"
						></mod-autocomplete>
					</template>
					<template v-else>
						<tag-autocomplete
							:search="dropdown.insertTag.query.content"
							ref="suggestions"
							@select-tag="dropdownTagSelected($event)"
							:key="dropdown.insertTag.context"
							:context="dropdown.insertTag.context || tagAutocompleteContext"
						></tag-autocomplete>
					</template>
				</template>
			</div>
		</div>
	</template>
</script>