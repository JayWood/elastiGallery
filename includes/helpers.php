<?php
/**
 * Functions from the old one that I may need.
 */


/**
 *    Determines if the post object has a gallery or not.
 */
if ( ! function_exists( 'hasgallery' ) ) {
	function hasgallery( $i ) {
		return ( strpos( $i->post_content, '[gallery' ) !== false );
	}
}
/*
 * @param int $id Image attachment ID
 * @param string $size_name Name of custom image size as added with add_image_size()
 * return bool True if intermediate image exists or was created. False if failed to create.
 */
if ( ! function_exists( 'convertImg' ) ) {
	function convertImg( $id, $size_name ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$sizes = image_get_intermediate_size( $id, $size_name );

		if ( ! $sizes ) { //if size doesn't exist for given image
			//echo "new thumb need for id $id<br />";

			$upload_dir = wp_upload_dir();
			$image_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], wp_get_attachment_url( $id ) );
			$new        = wp_generate_attachment_metadata( $id, $image_path );
			wp_update_attachment_metadata( $id, $new );

			if ( image_get_intermediate_size( $id, $size_name ) ) {
				//echo "new image size created for id $id<br />";
				return true;
			} else {
				//echo "failed to create new image size for id $id<br />";
				return false;
			}

		} else {
			//echo 'already exists<br />';
			return true;
		}

	}
}

/**
 * Old Options, will move to CMB2
 */
$old_options = array(
	'min_slides'     => array(
		'type'        => 'number',
		'name'        => 'Minimum Slides',
		'description' => 'Ensures that this # of thumbnails is shown at all times.  Will scale accordingly.',
		'default'     => '3'
	),
	/*'number_thumbs'	=> array(
		'type'	=>	'select',
		'name'	=>	'Number Thumbnails',
		'description'	=>	'Show numbers on the thumbnails in either an ascending or decending order. Will compensate for addon thumbnails aswell.',
		'fields'	=>	array(
			'none'	=>	'Disable Numbering',
			'asc'	=>	'Ascending Numbering',
			'desc'	=>	'Descending Numbering',
		),
		'default'	=>	'none'
	),*/
	'add_thumb'      => array(
		'type'        => 'number',
		'name'        => 'Thumbnail Addon #',
		'description' => 'How many posts to push at the end.',
		'default'     => '0'
	),
	'a_thumb_method' => array(
		'type'        => 'select',
		'name'        => 'Addon Method',
		'description' => 'Add posts according to primary parent category or tag name.  This does not take into account multiple categories or tags.',
		'fields'      => array(
			'tag' => 'Tag',
			'cat' => 'Category'
		),
		'default'     => 'cat'
	),
	/*'def_thumb'	=>	array(
		'type'	=>	'media',
		'name'	=>	'Default Thumbnail',
		'description'	=>	'A thumbnail to show if none exist for the additional thumbnails.'
	),*/
	'img_size_name'  => array(
		'type'        => 'text',
		'name'        => 'Image Size Name',
		'description' => '<b>(Advanced)</b> The name of the image size defined in your functions.php file for your theme or plugin.  If unsure, leave this be, default \'eg_image\'. <br />Image sizes will not take effect on images uploaded before plugin activation.  I recommend a thumbnail recalculation plugin, just google \'wordpress, recalculate thumbnail sizes\'.',
		'default'     => 'eg_image'
	),
	'inc_method'     => array(
		'type'        => 'select',
		'name'        => 'Inclusion Method',
		'description' => '<b>(Advanced)</b> Method of inclusion for the gallery.  If unsure, leave this one alone.',
		'fields'      => array(
			'filter' => 'Filter Based',
			'action' => 'Action Based'
		),
		'default'     => 'filter'
	)

);
