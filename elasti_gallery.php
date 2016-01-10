<?php
/**
 * Plugin Name: ElastiGallery
 * Plugin URI:  http://plugish.com
 * Description: A plugin desgined to be a replacement for the default WordPress gallery in a responsive fashion.  This provides a slideshow for the [gallery] shortcode and on the attachment pages along with the ability to add extra slides based on the post tags or category.
 * Version:     0.1.0
 * Author:      Jay Wood
 * Author URI:  http://plugish.com
 * Donate link: http://plugish.com
 * License:     GPLv2+
 * Text Domain: elastigallery
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 Jay Wood (email : jjwood2004@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using grunt-wp-plugin
 * Copyright (c) 2013 10up, LLC
 * https://github.com/10up/grunt-wp-plugin
 */

if ( ! defined( 'CMB2_LOADED' ) ) {
	include_once 'includes/CMB2/init.php';
}

if ( ! class_exists( 'JW_Fancy_Color' ) ) {
	require_once 'includes/CMB2_RGBa/jw-cmb2-rgba-colorpicker.php';
}
require_once 'includes/admin_options.php';

/**
 * Autoloads files with classes when needed
 * @since  0.1.0
 *
 * @param  string $class_name Name of the class being requested
 */
function elastigallery_autoload_classes( $class_name ) {
	if ( class_exists( $class_name, false ) || false === stripos( $class_name, 'Elastigallery_' ) ) {
		return;
	}

	$filename = strtolower( str_ireplace( 'Elastigallery_', '', $class_name ) );

	Elastigallery::include_file( $filename );
}

spl_autoload_register( 'elastigallery_autoload_classes' );

/**
 * Main initiation class
 */
class Elastigallery{

	const VERSION = '0.1.0';

	/**
	 * Sets up our plugin
	 * @since  0.1.0
	 */
	public function __construct() {
		// Similar to default thumbnail, but with cropping.
		add_image_size( 'elsatigallery-thumbnail', 150, 150, array( 'top', 'center' ) );
	}

	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'setup_scripts' ) );
		add_filter( 'post_gallery', array( $this, 'gallery_filter' ), 10, 2 );

		add_action( 'print_media_templates', array( $this, 'gallery_settings' ) );
	}

	/**
	 * Init hooks
	 * @since  0.1.0
	 * @return null
	 */
	public function init() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'elastigallery' );
		$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		load_textdomain( 'elastigallery', WP_LANG_DIR . '/elastigallery/elastigallery-' . $locale . '.mo' );
		load_plugin_textdomain( 'elastigallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		wp_register_style( 'owlcarousel', $this->url( "assets/js/vendor/OwlCarousel/owl.carousel.css" ), false, self::VERSION );
		wp_register_style( 'owlcarousel_theme', $this->url( "assets/js/vendor/OwlCarousel/owl.theme.css" ), array( 'owlcarousel' ), self::VERSION );
		wp_register_style( 'elastigallery', $this->url( "assets/css/elastigallery{$min}.css" ), array( 'owlcarousel_theme' ), self::VERSION );

		wp_register_script( 'owlcarousel_js', $this->url( "assets/js/vendor/OwlCarousel/owl.carousel{$min}.js" ), array( 'jquery' ), self::VERSION, true );
		wp_register_script( 'elastigallery_js', $this->url( "assets/js/elastigallery{$min}.js" ), array( 'owlcarousel_js' ), self::VERSION, true );

	}

	public function setup_scripts() {
		wp_enqueue_style( 'elastigallery' );
		wp_enqueue_script( 'elastigallery_js' );
		wp_localize_script( 'elastigallery_js', 'elg_localized', array(
			'script_debug' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? true : false,
		) );
	}

	/**
	 * Gallery Filter
	 *
	 * Filters the default WP gallery, duh!  Bulk of this is copied
	 * directly from the media library file.  No need to reinvent the
	 * wheel? Right!?
	 *
	 * @param string $output HTML output
	 * @param array $attr Short code attributes.
	 * @return string  HTML Gallery content
	 */
	public function gallery_filter( $output = '', $attr = array() ) {

		// Prevent non-elastigallery overrides
		$attr = shortcode_atts( array(
			'elastigallery' => false,
			'ids' => '',
		), $attr, 'gallery' );

		if ( ! isset( $attr['elastigallery'] ) ) {
			return $output;
		}

		$post = get_post();
		$id = isset( $post->ID ) ? $post->ID : 0;

		static $instance = 0;
		$instance++;

		if ( isset( $attr['ids'] ) ) {
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		$atts = shortcode_atts( array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => '',
			'link'       => ''
		), $attr, 'gallery' );

		if ( ! empty( $atts['include'] ) ) {
			$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		} else {
			$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		}

		if ( empty( $attachments ) ) {
			return $output;
		}

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
			}
			return $output;
		}

		$before_after = elastigallery_get_option( 'pos' );

		$output       = "<div id='elastigallery-$instance' class='elastigallery_wrapper'>";
		$output      .= "<div class='elastigallery_slides galleryid-{$id}' style='display: none;'>";

		if ( 'before' === $before_after ) {
			$output .= $this->padded_posts();
		}

		$output .= $this->attachment_posts( $attachments, $atts );

		if ( 'before' !== $before_after ) {
			$output .= $this->padded_posts();
		}

		$output .= "</div>";

		if ( ! empty( $atts['link'] ) ) {
			$output .= $this->image_display( $attachments, $atts );
		}

		$output .= '</div>';

		return apply_filters( 'elastigallery_render', $output, $attachments, $atts );
	}

	/**
	 * Images display blocks
	 *
	 * @param array $attachments
	 * @param array $atts
	 *
	 * @return bool|string
	 */
	public function image_display( $attachments = array(), $atts = array() ) {

		if ( empty( $attachments ) || ! is_array( $attachments ) || is_feed() ) {
			return false;
		}

		$output = '';
		$thumb_size = $atts['size'];
		$counter = 0;
		foreach ( $attachments as $id => $attachment ) {

			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$image_output = wp_get_attachment_link( $id, $thumb_size, false, false, false );
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
				$image_output = wp_get_attachment_image( $id, $thumb_size, false );
			} else {
				$image_output = wp_get_attachment_link( $id, $thumb_size, true, false, false );
			}

			$hidden = 0 !== $counter ? "style='display:none'" : '';
			$element_id = $this->get_url_title( $id );

			$output .= "<div class='entry-attachment attachment-$id' $hidden id='$element_id'>";
			$output .= "	<div class='attachment'>$image_output</div>";
			$output .= "</div>";
			$counter++;
		}

		return $output;
	}

	/**
	 * Helper method to get a URL Friendly post title
	 * @param int $attachment_id
	 *
	 * @return bool|string
	 */
	private function get_url_title( $attachment_id = 0 ) {
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$permalink = get_permalink( $attachment_id );
		return basename( parse_url( $permalink, PHP_URL_PATH ) );
	}

	/**
	 * Creates an anchorable link
	 *
	 * @param int $attachment_id
	 * @param string $html
	 *
	 * @return string
	 */
	public function slides_img_link( $attachment_id = 0, $html = '' ) {
		if ( empty( $html ) || empty( $attachment_id ) ) {
			return $html;
		}

		return sprintf( '<a href="#%s" class="elastigallery-slide">%s</a>', $this->get_url_title( $attachment_id ), $html );
	}

	/**
	 * Gets the post additions to add onto image slides
	 *
	 * @return string
	 */
	public function padded_posts() {
		// Padded posts

		$num_posts = absint( elastigallery_get_option( 'num_posts' ) );
		if ( 0 >= $num_posts ) {
			return '';
		}

		$thumb_size = elastigallery_get_option( 'thumbnail_size' );
		$query      = array(
			'post_type'              => 'post',
			'no_found_rows'          => true,
			'post_status'            => 'publish',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => $num_posts,
//			'nopaging'               => true,
			'ignore_sticky_posts'    => true,
		);
		$query      = apply_filters( 'elastigallery_padded_posts_query', $query );

		$padded_posts = new WP_Query( $query );

		$output = '';
		if ( $padded_posts->have_posts() ) {
			while( $padded_posts->have_posts() ) {
				$padded_posts->the_post();
				if ( ! has_post_thumbnail() ) {
					continue;
				}
				$id = get_the_ID();
				$image_output  = '<a href="' . get_permalink( $id ) . '">';
				$image_output .= get_the_post_thumbnail( $id, $thumb_size, array() );
				$image_output .= '</a>';

				$output .= "<div class='gallery-item post-$id'>";
				$output .= "	<div class='gallery-icon'>$image_output</div>";
				$output .= "</div>";
			}
			wp_reset_postdata();
		}

		return $output;
	}

	/**
	 * Helper function to handle the acquisition of attachments
	 *
	 * @param array $attachments
	 * @param array $atts
	 *
	 * @return bool|string
	 */
	public function attachment_posts( $attachments = array(), $atts = array() ) {
		// Attachment posts

		if ( empty( $attachments ) || ! is_array( $attachments ) ) {
			return false;
		}

		$output = '';
		$thumb_size = elastigallery_get_option( 'thumbnail_size' );
		foreach ( $attachments as $id => $attachment ) {
			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$image_output = wp_get_attachment_link( $id, $thumb_size, false, false, false );
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
				$image_output = $this->slides_img_link( $id, wp_get_attachment_image( $id, $thumb_size, false ) );
			} else {
				$image_output = wp_get_attachment_link( $id, $thumb_size, true, false, false );
			}
			$image_meta  = wp_get_attachment_metadata( $id );
			$orientation = '';
			if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
			}

			$element_id = $this->get_url_title( $id );
			$id_attr = ! empty( $element_id ) ? 'id="elastigallery-' . $element_id . '"' : false;
			$output .= "<div class='gallery-item attachment-$id'>";
			$output .= "<div class='gallery-icon {$orientation}' $id_attr>$image_output</div>";
			$output .= "</div>";
		}

		return $output;
	}

	/**
	 * Adds a settings checkbox to allow users use of
	 * elastigallery layouts instead of defaults.
	 */
	public function gallery_settings() {
		?><script type="text/html" id="tmpl-enable-elastigallery">
			<label class="setting">
				<span><?php _e('Enable elastiGallery', 'elastigallery' ); ?></span>
				<input type="checkbox" data-setting="elastigallery">
			</label>
		</script>

		<script>

			jQuery(document).ready(function(){

				_.extend(wp.media.gallery.defaults, {
					elastigallery: false
				});

				// merge default gallery settings template with yours
				wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
					template: function(view){
						return wp.media.template('gallery-settings')(view)
							+ wp.media.template('enable-elastigallery')(view);
					}
				});

			});

		</script><?php
	}

	/**
	 * Include a file from the includes directory
	 * @since  0.1.0
	 *
	 * @param  string $filename Name of the file to be included
	 * @return null     Will include a file if it exists, null otherwise
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/' . $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'url':
			case 'path':
				return self::$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}


	/**
	 * This plugin's directory
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path
	 *
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );

		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path
	 *
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );

		return $url . $path;
	}

}

// init our class
$GLOBALS['Elastigallery'] = new Elastigallery();
$GLOBALS['Elastigallery']->hooks();