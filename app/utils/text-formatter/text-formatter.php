<?php

namespace Voxel\Utils\Text_Formatter;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Formatter {

	const MATCH_CODE_BLOCK = '/^```([A-Za-z0-9._-]{0,24})\r?\n([\s\S]*?)\r?\n```$/m';
	const MATCH_INLINE_CODE = '/(^|\s)`(\S(?:.*?\S)?)`/';
	const MATCH_USERNAME = '/(^|\s)(@(?<username>[A-Za-z0-9._·@-]{1,63}))/';
	const MATCH_HASHTAG = '/(^|\s)(\#[\p{L}\p{N}\p{M}\p{S}_\.]{1,63})/u';
	const MATCH_REGULAR_LINK = '/(^|\s)(?<link>https?:\/\/\S+)/';

	protected static $_instance = null;

	public static function get(): self {
		return new static;
	}

	public function format( string $content ): string {
		$content = esc_html( $content );

		$replacements = [];

		$createReplacement = function( $content ) use ( &$replacements ) {
			$id = ' {' . bin2hex(random_bytes(16)) . '} ';
			$replacements[] = [
				'id' => $id,
				'content' => $content
			];
			return $id;
		};

		// Code blocks
		$content = preg_replace_callback( static::MATCH_CODE_BLOCK, function ($matches) use ( $createReplacement ) {
			$lang = !empty($matches[1]) ? 'data-lang="' . $matches[1] . '"' : '';
			$codeBlock = $matches[2];
			return $createReplacement( "<pre class=\"min-scroll\" $lang>$codeBlock</pre>" );
		}, $content );

		// Inline code
		$content = preg_replace_callback( static::MATCH_INLINE_CODE, function ($matches) use ( $createReplacement ) {
			$inlineCode = $matches[2];
			return $createReplacement( "$matches[1]<code>$inlineCode</code>" );
		}, $content );

		// Usernames
		$content = preg_replace_callback( static::MATCH_USERNAME, function ($matches) use ( $createReplacement ) {
			$href = esc_url( add_query_arg( 'username', mb_substr( $matches[2], 1 ), home_url('/?vx=1&action=user.profile') ) );
			return $createReplacement( "$matches[1]<a href='{$href}'>$matches[2]</a>" );
		}, $content );

		// Hashtags
		$content = preg_replace_callback( static::MATCH_HASHTAG, function ($matches) use ( $createReplacement ) {
			$href = esc_url( add_query_arg( 'q', urlencode( $matches[2] ), get_permalink( \Voxel\get( 'templates.timeline' ) ) ) );
			return $createReplacement( "$matches[1]<a href='{$href}'>$matches[2]</a>" );
		}, $content );

		// Regular links
		$content = preg_replace_callback( static::MATCH_REGULAR_LINK, function ($matches) use ( $createReplacement ) {
			$link = esc_url( $matches[2] );
			return $createReplacement( "$matches[1]<a href='$link' rel='noopener noreferrer nofollow' target='_blank'>$link</a>" );
		}, $content );

		// Text formatting
		$content = preg_replace('/(^|\s)\*(\S(?:.*?\S)?)\*/', '$1<strong>$2</strong>', $content);
		$content = preg_replace('/(^|\s)\_(\S(?:.*?\S)?)\_/', '$1<em>$2</em>', $content);
		$content = preg_replace('/(^|\s)\~(\S(?:.*?\S)?)\~/', '$1<del>$2</del>', $content);

		// Apply replacements
		foreach ($replacements as $replacement) {
			$content = str_replace($replacement['id'], $replacement['content'], $content);
		}

		return $content;
	}

	public function find_first_link( string $content ): ?string {
		$content = preg_replace( static::MATCH_CODE_BLOCK, '', $content );
		$content = preg_replace( static::MATCH_INLINE_CODE, '', $content );

		preg_match( static::MATCH_REGULAR_LINK, $content, $matches );

		if ( ! empty( $matches['link'] ) && is_string( $matches['link'] ) ) {
			return mb_substr( $matches['link'], 0, 500 );
		}

		return null;
	}

	public function find_mentions( string $content ): array {
		$mentions = [];

		$content = preg_replace( static::MATCH_CODE_BLOCK, '', $content );
		$content = preg_replace( static::MATCH_INLINE_CODE, '', $content );

		preg_match_all( static::MATCH_USERNAME, $content, $matches );

		if ( empty( $matches['username'] ) ) {
			return $mentions;
		}

		foreach ( $matches['username'] as $username ) {
			if ( empty( $username ) ) {
				continue;
			}

			$mentions[] = $username;
		}

		return array_values( array_unique( $mentions ) );
	}

	public function remove_formatting_characters( string $content ) {
		$replacements = [];

		$createReplacement = function( $content ) use ( &$replacements ) {
			$id = ' {' . bin2hex(random_bytes(16)) . '} ';
			$replacements[] = [
				'id' => $id,
				'content' => $content
			];
			return $id;
		};

		// Code blocks
		$content = preg_replace_callback( static::MATCH_CODE_BLOCK, function ($matches) use ($createReplacement) {
			$codeBlock = $matches[2];
			return $createReplacement( $codeBlock );
		}, $content );

		// Inline code
		$content = preg_replace_callback( static::MATCH_INLINE_CODE, function ($matches) use ($createReplacement) {
			$inlineCode = $matches[2];
			return $createReplacement( $matches[1].$matches[2] );
		}, $content );

		// Text formatting
		$content = preg_replace( '/(^|\s)\*(\S(?:.*?\S)?)\*/', '$1$2', $content );
		$content = preg_replace( '/(^|\s)\_(\S(?:.*?\S)?)\_/', '$1$2', $content );
		$content = preg_replace( '/(^|\s)\~(\S(?:.*?\S)?)\~/', '$1$2', $content );

		// Apply replacements
		foreach ($replacements as $replacement) {
			$content = str_replace($replacement['id'], $replacement['content'], $content);
		}

		return $content;
	}

	protected function escape_punct( string $char ): string {
		return sprintf( 'U_%X', mb_ord( $char, 'UTF-8' ) );
	}

	protected function _ft_prepare_usernames( string $content ) {
		return preg_replace_callback( static::MATCH_USERNAME, function ($matches) {
			$conversions = [
				'@' => $this->escape_punct('@'),
				'·' => $this->escape_punct('·'),
				'.' => $this->escape_punct('.'),
				'-' => $this->escape_punct('-'),
			];

			$username = mb_substr( $matches[2], 1 );
			$username_with_prefix = str_replace( array_keys( $conversions ), array_values( $conversions ), $matches[2] );

			return "$matches[1]{$username_with_prefix} {$username}";
		}, $content );
	}

	protected function _ft_prepare_hashtags( string $content ) {
		return preg_replace_callback( static::MATCH_HASHTAG, function ($matches) {
			$conversions = [
				'#' => $this->escape_punct('#'),
				'.' => $this->escape_punct('.'),
			];

			$hashtag = mb_substr( $matches[2], 1 );
			$hashtag_with_prefix = str_replace( array_keys( $conversions ), array_values( $conversions ), $matches[2] );

			return "$matches[1]{$hashtag_with_prefix} {$hashtag}";
		}, $content );
	}

	public function prepare_for_fulltext_indexing( string $content ): string {
		$content = $this->remove_formatting_characters( $content );
		$content = $this->_ft_prepare_usernames( $content );
		$content = $this->_ft_prepare_hashtags( $content );

		$keywords = preg_split( '/\s+/', $content, -1, PREG_SPLIT_NO_EMPTY );
		$normalized_keywords = [];
		foreach ( $keywords as $keyword ) {
			// trim punctuation characters
			$normalized = preg_replace( '/^[[:punct:]]+|[[:punct:]]+$/u', '', $keyword );;

			// handle punctuation within
			$normalized = join( '', array_map( function( $char ) {
				if ( ctype_punct( $char ) && $char !== '_' ) {
					return $this->escape_punct( $char );
				}

				return $char;
			}, \Voxel\mb_str_split( $normalized ) ) );

			if ( ! empty( $normalized ) ) {
				$normalized_keywords[ $normalized ] = true;
			}
		}

		$content = join( ' ', array_keys( $normalized_keywords ) );

		return $content;
	}

	public function prepare_for_fulltext_search( string $raw_content ): string {
		$content = mb_substr( $raw_content, 0, apply_filters( 'voxel/keyword-search/max-query-length', 128 ) );
		$stopwords = \Voxel\get_stopwords();
		$min_word_length = \Voxel\get_keyword_minlength();

		$content = $this->_ft_prepare_usernames( $content );
		$content = $this->_ft_prepare_hashtags( $content );

		$keywords = preg_split( '/\s+/', $content, -1, PREG_SPLIT_NO_EMPTY );
		$normalized_keywords = [];
		foreach ( $keywords as $keyword ) {
			// trim punctuation characters
			$normalized = preg_replace( '/^[[:punct:]]+|[[:punct:]]+$/u', '', $keyword );;

			// handle punctuation within
			$normalized = join( '', array_map( function( $char ) {
				if ( ctype_punct( $char ) && $char !== '_' ) {
					return $this->escape_punct( $char );
				}

				return $char;
			}, \Voxel\mb_str_split( $normalized ) ) );

			if ( empty( $normalized ) ) {
				continue;
			}

			if ( mb_strlen( $normalized ) < $min_word_length ) {
				continue;
			}

			if ( isset( $stopwords[ strtolower( $normalized ) ] ) ) {
				continue;
			}

			$normalized_keywords[ sprintf( '+%s*', $normalized ) ] = true;
		}

		return join( ' ', array_keys( $normalized_keywords ) );
	}
}
