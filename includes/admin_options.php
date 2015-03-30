<?php

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class ElastiGallery_Admin {

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $key = 'elastigallery_options';

	/**
	 * Options page metabox id
	 * @var string
	 */
	private $metabox_id = 'elastigallery_option_metabox';

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Site Options', 'elastigallery' );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_init', array( $this, 'add_options_page_metabox' ) );

		add_action( 'cmb2_render_number', array( $this, 'render_number' ), 10, 5 );
		add_action( 'cmb2_sanitize_number', array( $this, 'sanitize_number' ), 10, 2 );
	}

	public function render_number( $field, $esc, $obj_id, $obj_type, $fto ) {
		if ( method_exists( $fto, 'input' ) ) {
			echo $fto->input( array( 'class' => 'cmb2-text-small', 'type' => 'number' ) );
		}
	}

	public function sanitize_number( $null, $new ) {
		return preg_replace( '/[^0-9/', '', $new );
	}


	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_options_page( $this->title, $this->title, 'manage_options', $this->key, array(
			$this,
			'admin_page_display'
		) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2_options_page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
	<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	function add_options_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields

		$cmb->add_field( array(
			'name'    => __( 'Number of Posts', 'elastigallery' ),
			'desc'    => __( 'The number of posts to tack onto the available gallery images.', 'elastigallery' ),
			'id'      => 'num_posts',
			'type'    => 'number',
			'default' => 0,
		) );

		$cmb->add_field( array(
			'name'    => __( 'Position', 'elastigallery' ),
			'desc'    => __( 'Where should the padded posts be displayed in the slider?', 'elastigallery' ),
			'id'      => 'pos',
			'type'    => 'select',
			'options' => array(
				'after'  => __( 'After', 'elastigallery' ),
				'before' => __( 'Before', 'elastigallery' ),
			),
			'default' => 'after',
		) );

		$cmb->add_Field( array(
			'name'    => __( 'Thumb Size', 'elastigallery' ),
			'desc'    => __( 'Select the image size you want to use for the thumbnail', 'elastigallery' ),
			'id'      => 'thumbnail_size',
			'type'    => 'select',
			'options' => $this->image_sizes(),
			'default' => 'thumbnail',
		) );


//		$cmb->add_field( array(
//			'name'    => __( 'Test Color Picker', 'elastigallery' ),
//			'desc'    => __( 'field description (optional)', 'elastigallery' ),
//			'id'      => 'test_colorpicker',
//			'type'    => 'rgba_colorpicker',
//			'default' => '#bada55',
//		) );

	}

	public function image_sizes() {
		$sizes = $this->get_image_sizes();
		error_log( print_r( $sizes, 1 ) );
		$tmparr = array();
		if ( empty( $sizes ) ) {
			return $tmparr;
		}

		foreach ( $sizes as $name => $atts ) {
			$h               = isset( $atts['height'] ) ? $atts['height'] : '-';
			$w               = isset( $atts['width'] ) ? $atts['width'] : '-';
			$tmparr[ $name ] = "$name, $w x $h";
		}

		return $tmparr;
	}

	/**
	 * Courtesy of the codex
	 *
	 * @param string $size
	 *
	 * @return array|bool
	 */
	function get_image_sizes( $size = '' ) {

		global $_wp_additional_image_sizes;

		$sizes                        = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach ( $get_intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

				$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );

			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
				);

			}

		}

		// Get only 1 size if found
		if ( $size ) {

			if ( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			} else {
				return false;
			}

		}

		return $sizes;
	}

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 *
	 * @param  string $field Field to retrieve
	 *
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

// Get it started
$GLOBALS['ElastiGallery_Admin'] = new ElastiGallery_Admin();
$GLOBALS['ElastiGallery_Admin']->hooks();

/**
 * Helper function to get/return the elastigallery_Admin object
 * @since  0.1.0
 * @return elastigallery_Admin object
 */
function elastigallery_Admin() {
	global $ElastiGallery_Admin;

	return $ElastiGallery_Admin;
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 *
 * @param  string $key Options array key
 *
 * @return mixed        Option value
 */
function elastigallery_get_option( $key = '' ) {
	global $ElastiGallery_Admin;

	return cmb2_get_option( $ElastiGallery_Admin->key, $key );
}