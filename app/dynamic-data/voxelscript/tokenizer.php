<?php

namespace Voxel\Dynamic_Data\VoxelScript;

use Tokens\Plain_Text;
use Tokens\Dynamic_Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Tokenizer {

	protected
		$max_group_key_length = 32,
		$max_property_length = 60,
		$max_property_path_length = 240,
		$max_modifier_key_length = 100;

	protected $property_escape_chars = [
		')' => true,
		'.' => true,
		'\\' => true,
	];

	protected $modifier_escape_chars = [
		'(' => true,
		')' => true,
		',' => true,
		'\\' => true,
	];

	public function tokenize( string $content ): Token_List {
		$content = \Voxel\mb_str_split( $content );
		$length = count( $content );

		$tokens = [];
		$i = 0;

		while ( $i < $length ) {
			if ( $content[ $i ] === '@' ) {
				$i++;

				/**
				 * Parse and validate dynamic group key.
				 */
				$group_key = '';
				$group_key_length = 0;
				while ( $i < $length && $group_key_length <= $this->max_group_key_length ) {
					if ( $content[ $i ] === '(' ) {
						break;
					}

					if ( $content[ $i ] === '@' ) {
						$tokens[] = new Tokens\Plain_Text( '@'.$group_key );
						continue(2);
					}

					$group_key .= $content[ $i ];
					$group_key_length++;
					$i++;
				}

				if ( $i >= $length || $content[ $i ] !== '(' || ! preg_match('/^[a-zA-Z0-9_]+$/', $group_key ) ) {
					$tokens[] = new Tokens\Plain_Text( '@'.$group_key );
					continue;
				}

				/**
				 * Parse group props.
				 */
				$i++; // Skip opening '('

				$prop_index = 0;
				$props = [];
				$props[0] = '';
				$raw_props = '';
				$property_length = 0;
				$property_path_length = 0;
				while ( $i < $length && $property_length <= $this->max_property_length && $property_path_length <= $this->max_property_path_length ) {
					$raw_props .= $content[ $i ];
					$property_path_length++;

					if ( $content[ $i ] === ')' ) {
						break;
					} elseif ( $content[ $i ] === '.' ) {
						$property_length = 0;
						$prop_index++;
						$props[ $prop_index ] = '';
						$i++;
						continue;
					} elseif ( $content[ $i ] === '\\' && isset( $this->property_escape_chars[ $content[ $i + 1 ] ?? '' ] ) ) {
						$props[ $prop_index ] .= $content[ $i + 1 ];
						$i += 2;
						$property_length += 2;
					} else {
						$props[ $prop_index ] .= $content[ $i ];
						$i++;
						$property_length++;
					}
				}

				if ( $i >= $length || $content[ $i ] !== ')' ) {
					$tokens[] = new Tokens\Plain_Text( '@'.$group_key.'('.$raw_props );
					continue;
				}

				$i++; // Skip closing ')'

				$token = new Tokens\Dynamic_Tag( $group_key, $props, [] );
				$token->set_raw_props( mb_substr( $raw_props, 0, -1 ) );
				$tokens[] = $token;

				/**
				 * Parse group modifiers.
				 */
				$modifiers = [];
				while ( $i < $length && $content[ $i ] === '.' ) {
					$i++; // Skip '.'
					$modifier_key = '';
					$modifier_key_length = 0;
					while ( $i < $length && $modifier_key_length <= $this->max_modifier_key_length ) {
						if ( $content[ $i ] === '(' ) {
							break;
						}

						if ( $content[ $i ] === '@' ) {
							$tokens[] = new Tokens\Plain_Text( '.'.$modifier_key );
							continue(3);
						}

						$modifier_key .= $content[ $i ];
						$modifier_key_length++;
						$i++;
					}

					if ( $i >= $length || $content[ $i ] !== '(' || ! preg_match('/^[a-zA-Z0-9_]+$/', $modifier_key ) ) {
						$tokens[] = new Tokens\Plain_Text( '.'.$modifier_key );
						continue(2);
					}

					$modifier_arg_index = 0;
					$modifier_args = [];
					$modifier_args[0] = [ 'content' => '', 'dynamic' => false ];
					$raw_args = '';

					$i++; // Skip opening '('
					$depth_level = 1;
					while ( $i < $length && $depth_level > 0 ) {
						$raw_args .= $content[ $i ];

						if ( $content[ $i ] === '(' ) {
							$depth_level++;
							$modifier_args[ $modifier_arg_index ]['dynamic'] = true; // possibly dynamic flag
							$modifier_args[ $modifier_arg_index ]['content'] .= '(';
							$i++;
						} elseif ( $content[ $i ] === ')' ) {
							$depth_level--;
							if ( $depth_level === 0 ) {
								break;
							}

							$modifier_args[ $modifier_arg_index ]['content'] .= ')';
							$i++;
						} elseif ( $content[ $i ] === ',' && $depth_level === 1 ) {
							$modifier_arg_index++;
							$modifier_args[ $modifier_arg_index ] = [ 'content' => '', 'dynamic' => false ];
							$i++;
						} elseif ( $content[ $i ] === '\\' && isset( $this->modifier_escape_chars[ $content[ $i + 1 ] ?? '' ] ) ) {
							// backward compat for @value() helper which was escaped as @value(\)
							if ( $content[ $i + 1 ] === ')' && str_ends_with( $modifier_args[ $modifier_arg_index ]['content'], '@value(' ) ) {
								$depth_level--;
								if ( $depth_level === 0 ) {
									break;
								}
							}

							if ( $depth_level === 1 ) {
								$modifier_args[ $modifier_arg_index ]['content'] .= $content[ $i + 1 ];
								$i += 2;
							} else {
								$modifier_args[ $modifier_arg_index ]['content'] .= $content[ $i ];
								$i++;
							}
						} else {
							$modifier_args[ $modifier_arg_index ]['content'] .= $content[ $i ];
							$i++;
						}
					}

					if ( $depth_level !== 0 ) {
						$tokens[] = new Tokens\Plain_Text( '.'.$modifier_key.'('.$raw_args );
						continue(2);
					}

					$i++; // Skip closing ')'

					$token->add_modifier( [
						'key' => $modifier_key,
						'args' => $modifier_args,
						'raw_args' => mb_substr( $raw_args, 0, -1 ),
					] );
				}
			} else {
				$text = '';
				while ( $i < $length ) {
					if ( $content[ $i ] === '@' ) {
						break;
					}

					$text .= $content[ $i ];
					$i++;
				}

				if ( $text !== '' ) {
					$tokens[] = new Tokens\Plain_Text( $text );
				}
			}
		}

		return new Token_List( $tokens );
	}
}
