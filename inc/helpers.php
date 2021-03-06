<?php
/**
 * WordPress Helpers
 *
 * This file contains a mix of useful functions and classes
 * to ease the work with the template.
 *
 * @link https://github.com/creativecommons/wp-theme-base
 *
 * @package WordPress
 * @subpackage wp-theme-base
 * @since 2020.04.01
 */

/**
 * Generate a exceprt from whatever argument it's passed to it
 *
 * @param object|string       $post Post object with excerpt and/or content OR string.
 * @param object|array|string $args Arguments: length, echo, strict mode.
 *
 * @return string Formatted excerpt
 * @author Felipe Lavín <felipe@yukei.net>
 * */
function do_excerpt( $post, $args = null ) {
	$params = wp_parse_args(
		$args,
		array(
			'length'     => 255,
			'echo'       => false,
			'strict'     => false,
			'wrap'       => null,
			'wrap_id'    => null,
			'wrap_class' => 'entry-excerpt',
			'hellip'     => false,
			'append'     => null,
		)
	);
	$out    = $wrap = '';
	if ( $params['wrap'] ) {
		$wrap_id    = $params['wrap_id'] ? ' id="' . esc_attr( $params['wrap_id'] ) . '"' : null;
		$wrap_class = $params['wrap_class'] ? ' class="' . esc_attr( $params['wrap_class'] ) . '"' : null;
		$wrap       = '<' . $params['wrap'] . $wrap_id . $wrap_class . '>';
	}
	if ( is_string( $post ) ) {
		$excerpt = strip_shortcodes( $post );
		$excerpt = strip_tags( $excerpt );
		if ( strlen( $excerpt ) > $params['length'] ) {
			$excerpt = smart_substr( $excerpt, $params['length'] );
			if ( $params['hellip'] ) {
				$excerpt .= ' ' . $params['hellip'];
			}
		}
		if ( $params['append'] ) {
			$excerpt .= ' ' . $params['append'];
		}
		$out .= apply_filters( 'the_excerpt', $excerpt );
	} elseif ( is_object( $post ) ) {
		if ( isset( $post->post_excerpt ) && ! empty( $post->post_excerpt ) ) {
			if ( $params['strict'] && strlen( $post->post_excerpt ) > $params['length'] ) {
				$buff = smart_substr( $post->post_excerpt, $params['length'] );
				if ( $params['hellip'] ) {
					$buff .= ' ' . $params['hellip'];
				}
				if ( $params['append'] ) {
					$buff .= ' ' . $params['append'];
				}
				$out .= apply_filters( 'the_excerpt', $buff );
			} else {
				if ( $params['append'] ) {
					$post->post_excerpt .= ' ' . $params['append'];
				}
				$out .= apply_filters( 'the_excerpt', $post->post_excerpt );
			}
		} elseif ( isset( $post->post_content ) && ! empty( $post->post_content ) ) {
			return do_excerpt( $post->post_content, $params );
		}
	}

	if ( ! $out ) {
		return false;
	}

	if ( $out ) {
		if ( $params['wrap'] ) {
			$wrap .= $out . '</' . $params['wrap'] . '>';
			$out   = $wrap;
		}
		if ( $params['echo'] ) {
			echo $out;
		} else {
			return $out;
		}
	}
}

/**
 * Smarter text cutting
 *
 * @param string  $str : Content to cut.
 * @param string  $char : Characters number to show.
 * @param boolean $hellip : whether to show or not the hellip.
 * @return string Formated text.
 * @author Basilio Cáceres <bcaceres@ayerviernes.com>
 */
function smart_substr( $str, $char, $hellip = true ) {
	if ( strlen( $str ) > $char ) {
		$out = substr( strip_tags( $str ), 0, $char );
		$out = explode( ' ', $out );
		array_pop( $out );
		$out = implode( ' ', $out );
		if ( $hellip ) {
			$out .= ' [&hellip;]';
		}
	} else {
		$out = $str;
	}
	return $out;
}

/**
 * Helper Class to deal with videos from Youtube & Vimeo
 *
 *  @author @hugosolar
 */
class Videos {
	/**
	 *  Extract and return video id from youtbe & vimeo videos
	 *
	 *  @param string $url : Video url ( whether youtube or vimeo ).
   *  @param string $width : Video embed width (100% by default).
   *  @param int    $height : Video embed height (450px by default).
   *  @return string formated embed layout.
	 */
	public static function get_video_embed( $url, $width = '100%', $height = 450 ) {
		if ( strstr( $url, 'youtube' ) ) {
			$parse_url = parse_url( $url );
			$output    = array();
			parse_str( $parse_url['query'], $output );
			$id     = $output['v'];
			$iframe = '<iframe width="' . $width . '" height="' . $height . '" src="http://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
			return $iframe;
		}
		if ( strstr( $url, 'youtu.be' ) ) {
			$parse_url = parse_url( $url );
			$id        = array_pop( explode( '/', $parse_url ) );
			$iframe    = '<iframe width="' . $width . '" height="' . $height . '" src="http://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
			return $iframe;
		}
		if ( strstr( $url, 'vimeo' ) ) {
			if ( ! empty( $url ) ) {
				$id     = array_pop( explode( '/', $url ) );
				$iframe = '<iframe src="http://player.vimeo.com/video/' . $id . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
				return $iframe;
			}
		}
		return false;

	}
	/**
	 * Returns a contextual class to add in layout whether the video is youtbe or vimeo
	 *
	 * @param string $url : the url of the video.
	 * @return string class name
	 */
	public static function get_video_class( $url ) {
		if ( strstr( $url, 'youtube' ) ) {
			return 'youtube-video';
		}
		if ( strstr( $url, 'vimeo' ) ) {
			return 'vimeo-video';
		}
	}
	/**
	 * Extract and return video id from youtbe & vimeo videos
	 *
	 * @param string $url : Video url.
	 * @param string $size : thumbnail sizes `small` or `full`.
	 * @return string video thumb url
	 */
	public static function get_video_thumb( $url, $size = 'full' ) {
		if ( strstr( $url, 'youtube' ) ) {
			$parse_url = parse_url( $url );
			parse_str( $parse_url['query'] );
			$id       = $v;
			$def_size = ( $size == 'small' ) ? 1 : 0;
			$thumb    = 'http://img.youtube.com/vi/' . $id . '/' . $def_size . '.jpg';
			return $thumb;
		}
		if ( strstr( $url, 'youtu.be' ) ) {
			$parse_url = parse_url( $url );
			$id        = array_pop( explode( '/', $parse_url ) );
			$def_size  = ( $size == 'small' ) ? 1 : 0;
			$thumb     = 'http://img.youtube.com/vi/' . $id . '/' . $def_size . '.jpg';
			return $thumb;
		}
		if ( strstr( $url, 'vimeo' ) ) {
			$thumb = '';
			if ( ! empty( $url ) ) {
				if ( false === ( $thumb = get_transient( 'vimeo_' . $id ) ) ) {
					$id      = array_pop( explode( '/', $url ) );
					$request = new WP_Http();
					$result  = $request->request( 'http://vimeo.com/api/v2/video/' . $id . '.php', $args );
					if ( ! is_wp_error( $result ) ) {
						$body = maybe_unserialize( $result['body'] );
						if ( $size == 'medium' ) {
							$thumb = $body[0]['thumbnail_medium'];
						} else {
							$thumb = $body[0]['thumbnail_large'];
						}
						set_transient( 'vimeo_' . $id, $thumb, 60 * 60 * 24 );
					}
				}
				return $thumb;
			}
		}
		return false;

	}
}
