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
class Elastigallery {

	const VERSION = '0.1.0';

	/**
	 * Sets up our plugin
	 * @since  0.1.0
	 */
//	public function __construct() {
//
//	}

	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'setup_scripts' ) );
		add_filter( 'post_gallery', array( $this, 'gallery_filter' ), 10, 4 );
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
	}

	/**
	 * Gallery Filter
	 *
	 * Filters the default WP gallery, duh!
	 *
	 * @param string $output
	 * @param array $atts
	 * @param bool $content
	 * @param bool $tag
	 */
	public function gallery_filter( $output = '', $atts = array(), $content = false, $tag = false ) {

		if ( empty( $atts ) ) {
			return $output;
		}

		$defaults = array(
			'orderby' => 'rand',
		);
		$atts     = wp_parse_args( $atts, $defaults );

		if ( ! isset( $atts['ids'] ) || empty( $atts['ids'] ) ) {
			return $output;
		}

		$ids          = explode( $atts['ids'] );
		$before_after = elastigallery_get_option( 'pos' );

		$output = "<div class='elastigallery'>";

		if ( 'before' === $before_after ) {
			$output .= $this->padded_posts();
		}

		$output .= $this->attachment_posts( $ids );

		if ( 'before' !== $before_after ) {
			$output .= $this->padded_posts();
		}

		$output .= "</div>";

		return apply_filters( 'elastigallery_render', $ids, $atts );
	}

	public function padded_posts() {
		// Padded posts
		$thumb_size = elastigallery_get_option( 'thumbnail_size' );
		$query      = array(
			'post_type'     => 'post',
			'no_found_rows' => true,
			'post_status'   => 'publish',
		);
		$query      = apply_filters( 'elastigallery_padded_posts_query', $query );

		return '';
	}

	public function attachment_posts( $ids = array() ) {
		// Attachment posts

		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return false;
		}

		$thumb_size = elastigallery_get_option( 'thumbnail_size' );

		return '';
	}

	/**
	 * Include a file from the includes directory
	 * @since  0.1.0
	 *
	 * @param  string $filename Name of the file to be included
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/' . $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}

	}

	/**
	 * This plugin's directory
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

}

// init our class
$GLOBALS['Elastigallery'] = new Elastigallery();
$GLOBALS['Elastigallery']->hooks();
