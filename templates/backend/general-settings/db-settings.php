<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Database settings</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'config.db.type',
			'label' => 'Database type',
			'classes' => 'x-col-12',
			'choices' => [
				'mysql' => 'MySQL',
				'mariadb' => 'MariaDB',
			],
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.db.max_revisions',
			'label' => 'Max revision count (per post)',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>Keyword search</h3>
	</div>
	<div class="x-row">
		<div class="ts-form-group x-col-12">
			<label style="display: inline-block;">Minimum keyword length</label>
			<div class="vx-info-box wide" style="float: right;">
				<?php \Voxel\svg( 'info.svg' ) ?>
				<p>
					Keywords shorter than this value will be stripped from search queries.
					This value shouldn't be lower than your site's <b>innodb_ft_min_token_size</b>
					<?php if ( $innodb_ft_min_token_size = \Voxel\_get_innodb_ft_min_token_size() ): ?>
						(currently set to <b><?= $innodb_ft_min_token_size ?></b>)
					<?php endif ?>
					system variable.
					<br><br>
					See the <b>Advanced configuration</b> section below for more details.
				</p>
			</div>
			<input type="number" v-model="config.db.keyword_search.min_word_length" placeholder="3">
		</div>

		<div class="ts-form-group x-col-12">
			<label>Search stopwords</label>
			<textarea
				v-model="config.db.keyword_search.stopwords"
				placeholder="<?= esc_attr( \Voxel\get_default_stopwords() ) ?>"
				style="height: 100px;"
			></textarea>
			<p style="margin-top: 5px;">Keywords in this list will be stripped from search queries.</p>
		</div>

		<div class="ts-form-group x-col-12">
			<p><a href="#" @click.prevent="db.showAdvanced = !db.showAdvanced">Advanced configuration</a></p>
		</div>

		<template v-if="db.showAdvanced" class="ts-form-group x-col-12">
			<div class="ts-form-group x-col-12">
				<label><strong>Configuring minimum token size</strong></label>
					<p>
						By default, words less than 3 characters in length are excluded from the keyword index. This reduces the size of the index by omitting common words that are unlikely to be significant in a search context, such as the English words “a” and “to”. For content using a CJK (Chinese, Japanese, Korean) character set, the cut off should be set to 1 character.
						<br><br>
						This value is controlled by the `innodb_ft_min_token_size` system variable.
						Typically this is set in your server's <b>my.cnf</b> file as follows:
					</p>
					<pre class="ts-snippet"><span class="ts-red">[mysqld]</span>
<span class="ts-yellow">innodb_ft_min_token_size = 1</span></pre>
					<p>
						This file is usually found in <b>/etc/my.cnf</b> or <b>/usr/local/etc/my.cnf</b>
						<br><br>
						<a href="https://dev.mysql.com/doc/refman/8.0/en/innodb-parameters.html#sysvar_innodb_ft_min_token_size" target="_blank">MySQL reference</a><br>
						<a href="https://mariadb.com/kb/en/innodb-system-variables/#innodb_ft_min_token_size" target="_blank">MariaDB reference</a>
					</p>
			</div>

			<div class="ts-form-group x-col-12">
				<label><strong>Configuring indexing stopwords</strong></label>
				<p>By default, words in the following list are excluded from the keyword index:</p>
				<pre class="ts-snippet" style="font-size: 12px;">a about an are as at be by com de en for from how i in is it la of on or that the this to was what when where who will with und the www</pre>
				<p>
					You can modify this list by following the relevant guide:<br>
					<a href="https://dev.mysql.com/doc/refman/8.0/en/fulltext-stopwords.html#fulltext-stopwords-stopwords-for-innodb-search-indexes" target="_blank">MySQL guide</a><br>
					<a href="https://mariadb.com/kb/en/full-text-index-stopwords/#innodb-stopwords" target="_blank">MariaDB guide</a>
				</p>
			</div>
		</template>
	</div>
</div>
