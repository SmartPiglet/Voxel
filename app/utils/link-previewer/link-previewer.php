<?php

namespace Voxel\Utils\Link_Previewer;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Link_Previewer {

	protected static $_instance = null;

	public static function get(): self {
		return new static;
	}

	/**
	 * Main method to generate link preview data
	 */
	public function preview( string $url ): ?array {
		// Check if it's a YouTube link
		if ( $this->is_youtube_url( $url ) ) {
			return $this->generate_youtube_preview( $url );
		}

		$request = $this->fetch_url( $url );
		if ( $request === null ) {
			return null;
		}

		$data = $this->extract_preview_data( $request['body'] );
		
		// Only generate preview if we have a title
		if ( $data['title'] === null ) {
			return null;
		}

		$data['url'] = $request['url'];
		return $data;
	}

	/**
	 * Check if URL is a YouTube link
	 */
	protected function is_youtube_url( string $url ): bool {
		$youtube_patterns = [
			'/youtube\.com\/watch\?/',
			'/youtu\.be\//',
			'/youtube\.com\/embed\//',
			'/youtube\.com\/v\//',
		];
		
		foreach ( $youtube_patterns as $pattern ) {
			if ( preg_match( $pattern, $url ) ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Generate YouTube preview data for iframe embedding
	 */
	protected function generate_youtube_preview( string $url ): array {
		$video_id = $this->extract_youtube_video_id( $url );
		
		if ( $video_id === null ) {
			return null;
		}
		
		return [
			'type' => 'youtube',
			'url' => $url,
			'video_id' => $video_id,
			'embed_url' => "https://www.youtube.com/embed/{$video_id}",
			'title' => null, // YouTube will handle the title in the iframe
			'image' => null,
			'description' => null,
		];
	}

	/**
	 * Extract YouTube video ID from various URL formats
	 */
	protected function extract_youtube_video_id( string $url ): ?string {
		// youtube.com/watch?v=VIDEO_ID (with any additional parameters)
		if ( preg_match( '/youtube\.com\/watch\?.*?v=([a-zA-Z0-9_-]{11})/', $url, $matches ) ) {
			return $matches[1];
		}
		
		// youtu.be/VIDEO_ID (with any additional parameters)
		if ( preg_match( '/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $matches ) ) {
			return $matches[1];
		}
		
		// youtube.com/embed/VIDEO_ID
		if ( preg_match( '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $matches ) ) {
			return $matches[1];
		}
		
		// youtube.com/v/VIDEO_ID
		if ( preg_match( '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/', $url, $matches ) ) {
			return $matches[1];
		}
		
		return null;
	}

	/**
	 * Fetch URL content with proper headers and error handling
	 */
	protected function fetch_url( string $url ): ?array {
		$url = filter_var( $url, FILTER_SANITIZE_URL );
		if ( $url === false || empty( $url ) ) {
			return null;
		}

		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			return null;
		}

		// Simple, effective request configuration
		$request = wp_safe_remote_get( $url, [
			'timeout' => 5,
			'redirection' => 3,
			'limit_response_size' => 3 * MB_IN_BYTES,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'headers' => [
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.9',
				'Accept-Encoding' => 'gzip, deflate',
			],
		] );

		$code = wp_remote_retrieve_response_code( $request );
		if ( ! in_array( $code, [ 200, 201, 202 ] ) ) {
			return null;
		}

		return [
			'url' => $url,
			'body' => wp_remote_retrieve_body( $request ),
		];
	}

	/**
	 * Extract preview data using industry standard priority order
	 */
	protected function extract_preview_data( string $html ): array {
		$data = [
			'title' => null,
			'image' => null,
			'description' => null,
		];

		// Priority 1: Open Graph (most reliable)
		$this->extract_open_graph( $html, $data );
		
		// Priority 2: Twitter Cards
		if ( $data['title'] === null || $data['image'] === null ) {
			$this->extract_twitter_cards( $html, $data );
		}
		
		// Priority 3: Schema.org structured data
		if ( $data['title'] === null || $data['image'] === null ) {
			$this->extract_schema_org( $html, $data );
		}
		
		// Priority 4: Basic HTML elements (fallback)
		if ( $data['title'] === null ) {
			$data['title'] = $this->extract_title_tag( $html );
		}
		
		if ( $data['image'] === null ) {
			$data['image'] = $this->extract_first_image( $html );
		}
		
		if ( $data['description'] === null ) {
			$data['description'] = $this->extract_meta_description( $html );
		}

		return $data;
	}

	/**
	 * Extract Open Graph meta tags (industry standard)
	 */
	protected function extract_open_graph( string $html, array &$data ): void {
		// Open Graph title
		if ( preg_match( '/<meta\s+property\s*=\s*["\']og:title["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			$data['title'] = $this->clean_text( $matches[1], 200 );
		}
		
		// Open Graph image
		if ( preg_match( '/<meta\s+property\s*=\s*["\']og:image["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			$data['image'] = $this->clean_text( $matches[1], 500 );
		}
		
		// Open Graph description
		if ( preg_match( '/<meta\s+property\s*=\s*["\']og:description["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			$data['description'] = $this->clean_text( $matches[1], 300 );
		}
	}

	/**
	 * Extract Twitter Card meta tags
	 */
	protected function extract_twitter_cards( string $html, array &$data ): void {
		// Twitter title
		if ( $data['title'] === null && preg_match( '/<meta\s+name\s*=\s*["\']twitter:title["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			$data['title'] = $this->clean_text( $matches[1], 200 );
		}
		
		// Twitter image
		if ( $data['image'] === null && preg_match( '/<meta\s+name\s*=\s*["\']twitter:image["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			$data['image'] = $this->clean_text( $matches[1], 500 );
		}
		
		// Twitter description
		if ( $data['description'] === null && preg_match( '/<meta\s+name\s*=\s*["\']twitter:description["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			$data['description'] = $this->clean_text( $matches[1], 300 );
		}
	}

	/**
	 * Extract Schema.org structured data (JSON-LD)
	 */
	protected function extract_schema_org( string $html, array &$data ): void {
		preg_match_all( '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches );
		
		foreach ( $matches[1] as $json_content ) {
			$json_data = json_decode( trim( $json_content ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				continue;
			}
			
			$items = is_array( $json_data ) && isset( $json_data[0] ) ? $json_data : [ $json_data ];
			
			foreach ( $items as $item ) {
				if ( ! is_array( $item ) ) continue;
				
				$type = $item['@type'] ?? '';
				if ( ! in_array( $type, [ 'Article', 'WebPage', 'BlogPosting', 'NewsArticle' ] ) ) {
					continue;
				}
				
				// Extract title
				if ( $data['title'] === null ) {
					if ( isset( $item['headline'] ) ) {
						$data['title'] = $this->clean_text( $item['headline'], 200 );
					} elseif ( isset( $item['name'] ) ) {
						$data['title'] = $this->clean_text( $item['name'], 200 );
					}
				}
				
				// Extract image
				if ( $data['image'] === null && isset( $item['image'] ) ) {
					if ( is_array( $item['image'] ) && isset( $item['image']['url'] ) ) {
						$data['image'] = $this->clean_text( $item['image']['url'], 500 );
					} elseif ( is_string( $item['image'] ) ) {
						$data['image'] = $this->clean_text( $item['image'], 500 );
					}
				}
				
				if ( $data['title'] !== null && $data['image'] !== null ) {
					break 2;
				}
			}
		}
	}

	/**
	 * Extract HTML title tag
	 */
	protected function extract_title_tag( string $html ): ?string {
		if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
			return $this->clean_text( $matches[1], 200 );
		}
		return null;
	}

	/**
	 * Extract first meaningful image
	 */
	protected function extract_first_image( string $html ): ?string {
		preg_match_all( '/<img[^>]*src\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches );
		
		foreach ( $matches[1] as $image_url ) {
			$image_url = trim( $image_url );
			
			// Skip common non-content images
			if ( preg_match( '/\/(icon|logo|avatar|banner|ad|ads|pixel|tracking|analytics|social)\//i', $image_url ) ) {
				continue;
			}
			
			if ( ! empty( $image_url ) && filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
				return $this->clean_text( $image_url, 500 );
			}
		}
		
		return null;
	}

	/**
	 * Extract meta description
	 */
	protected function extract_meta_description( string $html ): ?string {
		if ( preg_match( '/<meta\s+name\s*=\s*["\']description["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			return $this->clean_text( $matches[1], 300 );
		}
		return null;
	}

	/**
	 * Clean and sanitize text content
	 */
	protected function clean_text( string $text, int $max_length ): string {
		// Decode HTML entities
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		
		// Remove extra whitespace
		$text = preg_replace( '/\s+/', ' ', $text );
		
		// Trim and limit length
		$text = trim( $text );
		$text = mb_substr( $text, 0, $max_length );
		
		return $text;
	}

	/**
	 * Debug method for development
	 */
	public function debug_preview( string $url ): array {
		// Check if it's a YouTube link
		if ( $this->is_youtube_url( $url ) ) {
			$youtube_data = $this->generate_youtube_preview( $url );
			return [
				'url' => $url,
				'type' => 'youtube',
				'youtube_data' => $youtube_data,
				'video_id' => $youtube_data['video_id'] ?? null,
				'embed_url' => $youtube_data['embed_url'] ?? null,
			];
		}

		$request = $this->fetch_url( $url );
		if ( $request === null ) {
			return [ 'error' => 'Failed to fetch URL' ];
		}

		$data = $this->extract_preview_data( $request['body'] );
		
		return [
			'url' => $request['url'],
			'response_code' => 200,
			'content_length' => strlen( $request['body'] ),
			'extracted_data' => $data,
			'html_sample' => substr( $request['body'], 0, 1000 ),
		];
	}
}
