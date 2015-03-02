<?
class elastiGallery{
	
	public $prefix = "elG_";
	
	public $hook;
	
	public $options;
	
	function elastiGallery($ops){
		$this->__construct($ops);
	}
	
	function __construct($ops){
		$this->options = $ops;
		
		add_action('admin_init', array(&$this, 'register_admin_deps') );
		add_action('admin_menu', array(&$this, 'load_admin_menu') );
		add_action('admin_enqueue_scripts', array(&$this, 'load_admin_deps') );
		
		// Styling and such
		add_action('init', array( &$this, 'register_frontend_deps') );
		add_action('wp_enqueue_scripts', array(&$this, 'load_frontend_deps') );
		
		
		add_action('elastigallery', array(&$this, 'slide_action'), 10, 2 );
		
		// Backwards compatability
		add_filter('the_content', array(&$this, 'backwards_fix'), 9, 1);
		
		// Filter the gallery
		add_filter('post_gallery', array(&$this, 'gallery_filter'), 9, 2 );
		
		// Add an additional image size for those sites that don't have one.
		add_image_size('eg_image', 70,70,true);
		
	}
	public function register_frontend_deps(){
		wp_register_style('elastislide', plugins_url('css/elastislide.css', dirname(__FILE__) ), '', '1.0', 'all');
		wp_register_style('fancybox_css', plugins_url('/lib/fancybox/jquery.fancybox.css',dirname(__FILE__) ),'','1.0', 'all');
		wp_register_style('elastiGallery', plugins_url('css/elastigallery.css', dirname(__FILE__) ), array('elastislide','fancybox_css'), '1.0', 'all');
		
		
		wp_register_script('fancybox_js', plugins_url('lib/fancybox/jquery.fancybox.js', dirname(__FILE__) ), array('jquery'), '1.0', true);
		wp_register_script('modernizr', plugins_url('js/modernizr.custom.17475.js', dirname(__FILE__) ), array('jquery'), '17475');
		wp_register_script('jquerypp', plugins_url('js/jquerypp.custom.js', dirname(__FILE__) ), array('modernizr'), '1.0');
		wp_register_script('elastislide_js', plugins_url('js/jquery.elastislide.js', dirname(__FILE__) ), array('jquerypp'), '1.0');
		wp_register_script('elastiGallery', plugins_url('js/jquery.elastiGallery.js', dirname(__FILE__) ), array('elastislide_js', 'fancybox_js'), '1.0');
		// Setup dependancy so just enqueue elastiGallery script and.
	}
	
	public function load_frontend_deps(){
		
		wp_enqueue_style('elastiGallery');
		wp_enqueue_script('elastiGallery');
		
	}
	
	public function register_admin_deps(){		
		foreach($this->options as $k => $v)	register_setting($this->prefix.'options', $this->prefix.$k);
		
		wp_register_style('spectrum', plugins_url('css/spectrum.css', dirname(__FILE__) ), '', '1.0.9');
		wp_register_script('spectrum', plugins_url('js/spectrum.js', dirname(__FILE__) ), array('jquery'), '1.0.9' );
		
		wp_register_style( $this->prefix.'admin_css', plugins_url('css/admin.css', dirname(__FILE__) ), '', '1.0');
		wp_register_script( $this->prefix.'admin_js', plugins_url('js/admin.js', dirname(__FILE__) ) , '', '1.0');
		
	}
	
	public function load_admin_deps($hook = FALSE){
		if($hook == $this->hook && $hook != false){
			wp_enqueue_media();
			
			wp_enqueue_style('spectrum');
			wp_enqueue_script('spectrum');
			
			wp_enqueue_script($this->prefix.'admin_js');
			wp_enqueue_style($this->prefix.'admin_css');
		}
	}
	
	public function load_admin_menu(){
		$this->hook = add_options_page('EG Options Page', 'EG Options', 'manage_options', $this->prefix.'options', array(&$this, 'render_options_page') );
	}
	public function render_options_page(){
		
		?>
        	<div class="wrap">
            	<div id="icon-options-general" class="icon32"><br /></div>
                <h2>elastiGallery Options Page</h2>
                <form method="post" action="options.php">
                <? settings_fields($this->prefix.'options'); ?>
                <table class="form-table">
                	<tbody>
                    	<?
						foreach ($this->options as $k => $v){
							?>
							<tr valign="top">
								<th scope="row"><label for="<? echo $this->prefix.$k; ?>"><? echo $v['name']; ?></label></th>
                                <td><? echo $this->render_option_field($k, $v); ?>
									<? if( isset( $v['description'] ) ): ?>
                                        <p class="description"><? echo $v['description']; ?></p>
                                    <? endif; ?>
                                </td>
							</tr>
							<?
						}
						?>
                    </tbody>
                </table>
                <p class="submit">
                	<input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes">
                </p>
                </form>
            </div>
        <?
	}
	
	public function render_color_select($key, $data){
		
		$opData = get_option($this->prefix.$key, $data);
		
		$output = '<!-- Color Selects -->';
		foreach($opData as $k => $v){
			$output .= '<input type="text" id="'.$key.'_'.$k.'" name="'.$this->prefix.$key.'['.$k.']" value="'.$v.'" class="color_select">';
		}
		
		return $output;
		
	}
	
	public function render_option_field($key, $data){
		switch($data['type']){
			case 'text':
				$output = '<input type="text" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" value="'.get_option($this->prefix.$key, $data['default']).'" class="regular-text" />';
				break;
			case 'password':
				$output = '<input type="password" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" value="'.get_option($this->prefix.$key).'" class="regular-text" />';
				break;
			case 'number':
				$output = '<input type="number" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" value="'.get_option($this->prefix.$key, $data['default']).'" />';
				break;
			case 'data_array':
				$output = $this->buildDataArrayFields($key, $data['fields']);
				break;
			case 'select':
				$output = $this->buildSelectOptions($key, $data['fields'], $data['default']);
				break;
			case 'color':
				$output = $this->render_color_select($key, $data['fields']);
				break;
			case 'media':
				$output = $this->buildMediaOption($key);
				break;
			default:
			break;
		}
		return $output;
	}
	
	public function buildMediaOption($key){
		
		$opData = get_option($this->prefix.$key);
		
		$output = '<div class="uploader">';
		$output .= '<input type="text" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" class="regular-text" value="'.$opData.'" />';
		$output .= '<input type="button" id="'.$this->prefix.$key.'_upload" value="Upload" class="button upload_image_button" data-id="'.$this->prefix.$key.'" />';
		$output .= '</div>';
		
		return $output;
	}
	
	public function buildSelectOptions($key, $data, $def = false){
		
		$opData = get_option($this->prefix.$key, $def);
		
		$output = '<select name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'">';
		foreach($data as $k => $v){
			$output .= '<option value="'.$k.'" '.selected($opData, $k, false).'>'.$v.'</option>';
		}
		$output .= '</select>';
		
		return $output;
		
	}
	
	public function buildDataArrayFields($key, $fields){
		$opData = get_option($this->prefix.$key);
		?>
        	<a href="javascript:;" class="addrow">[+] Add Row</a>
        	<table class="dataArrayFields" id="<? echo $key; ?>">
			<? $rowBase = 1; ?>
            <? if(!empty($opData) && is_array($opData)) :?>
            	<? foreach ($opData as $row): ?>
                	<tr id="data_row_<? echo $rowBase; ?>" class="data_row">
					<? foreach ($fields as $colName): ?>
                        <td class="data_col <? echo $colName; ?>"><input type="text" name="<? echo $this->prefix.$key ?>[<? echo $rowBase; ?>][<? echo $colName; ?>]" value="<? echo $row[$colName]; ?>"/></td>
                    <? endforeach; ?>
                        <td><a href="javascript:;" id="<? echo $rowBase; ?>" class="removerow">[X]</a></td>
                    </tr>                    
                    <? $rowBase++; ?>
                <? endforeach; ?>
            <? else: ?>
            	<tr id="data_row_<? echo $rowBase; ?>" class="data_row">
            	<? foreach ($fields as $colName): ?>
	                <td class="data_col <? echo $colName; ?>"><input type="text" name="<? echo $this->prefix.$key ?>[<? echo $rowBase; ?>][<? echo $colName; ?>]" /></td>
                <? endforeach; ?>
                	<td><a href="javascript:;" id="<? echo $rowBase; ?>" class="removerow">[X]</a></td>
                </tr>
            <? endif; ?>
            </table>
        <?
	}
	
	public function uninstall(){
		if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) return false;
		// Remove options
		foreach ($this->options as $k => $v) delete_option($this->prefix.$k);
	}
	
	public function gallery_filter($output = false, $attr){
		global $post;

		if( empty( $attr['ids'] ) ) return $output;

		$attr['orderby'] = empty( $attr['orderby'] ) ? 'menu_order' : $attr['orderby'];
		
		$featured_image_size=empty( $featured_image_size ) ? "single" : $featured_image_size;
		$featured_image_size_responsive= empty($featured_image_size_responsive) ? "single-medium" : $featured_image_size_responsive;
		$attrData = explode( ',', $attr['ids'] );
		do_action( 'elastigallery', $attrData, $attr['orderby'] );
		$output = '<!-- Gallery Filtered -->';
		
		return $output;
	}
	
	public function backwards_fix($content){
		global $post;
		
		// Return, don't filter content that has galleries
		if(has_shortcode($content, 'gallery')) return $content;
		
		// Don't deal with non-single pages
		if(!is_single()) return $content;
		$currentPost = $post->ID;
		// Deal with attachment pages
		if(is_attachment()) $currentPost = $post->post_parent;
		
		$pType = get_post_type($post->ID);
		$tmpOutput = '';
		if($pType == 'post'){
			$m = get_post_meta($post->ID, 'ifwt-post-has-slide', true);
			if($m == 1){
				// Has old post_meta
				$attachments = get_posts( array(
					'posts_per_page'	=>	-1,
					'nopaging'		=>	true,
					'post_type' 	=>	'attachment',
					'orderby'		=>	'menu_order',
					'order'			=>	$o,
					'post_parent' 	=>	$currentPost,
					'no_found_rows'	=>	true,
					'fields'	=>	'ids'
				));
				if($attachments){
					ob_start();
					$this->slide_action($attachments, 'menu_order');
					$tmpOutput = ob_get_clean();
				}
			}
		}
		
		return $tmpOutput.$content;
		
	}
	
	public function slide_action($attIDs = false, $order = 'menu_order'){
		global $post; 
			
			$numberThumbs = get_option($this->prefix.'number_thumbs', "none");
			$padMethod = get_option($this->prefix.'a_thumb_method', 'cat');
			$padNumber = get_option($this->prefix.'add_thumb', 0);
			$thSize = get_option($this->prefix.'img_size_name', 'eg_image');
			$pData = $post->ID;
			$curImg = false;
			if(is_attachment()){
				// Attachments will not have $attIDs or $order
				$pData = $post->post_parent;
				$curImg = get_the_ID();
				$theData = get_post($pData);
				$scRegEx = get_shortcode_regex();
				preg_match( '/'.$scRegEx.'/', $theData->post_content, $matches);
				if($matches[2] == 'gallery'){
					$attStr = str_replace(" ", "&", trim($matches[3]));
					$attStr = str_replace('"','',$attStr);
				}
				$attributes = wp_parse_args($attStr, array());
				$attIDs = explode( ',', $attributes['ids'] );
				
			}
			
			// Using get_posts instead of WP_Query because for some reason WP_Query will return $query->found_posts = 0 if post_type == attachment and/or post_parent == $n
			$gpargs = array('numberposts' => -1, 'nopaging' => true, 'post_type' => 'attachment', 'supress_filters' => true, 'no_found_rows'=>true);
			if(!empty($order) && $order == 'rand'){
				$gpargs['orderby'] = 'rand';
			}else{
				$gpargs['orderby'] = 'menu_order';
			}
			
			if(!empty($attIDs)){
				$gpargs['post__in'] = $attIDs;
					
				$atc = get_posts($gpargs);
			}
			?><ul id="carousel" class="elastislide-list"><?
			
			if($numberThumbs == 'desc'){
				$n = intval($attachments->found_posts) + intval($padNumber);
			}else{
				$n = '1';
			}
			if(!empty($atc)) :
				foreach($atc as $image):
					$current = ($curImg && $curImg == $image->ID) ? 'current' : '';
					$xn = convertImg($image->ID, $thSize);
					
					?><li data-id="<? echo $image->ID; ?>" class="image attachment <? echo $current; ?>"><? 
						 echo wp_get_attachment_link($image->ID, $thSize, true);
	//					 echo '<div class="number">'.$n.'</div>';
					?></li><?
					
					if($numberThumbs == 'desc'){
						$n--; 
					}else{
						$n++;
					}
				endforeach;
			endif;
			
// The crashing query....			
			/*$pID = is_attachment() ? $post->post_parent : $post->ID;
			$fOutput = wp_cache_get('fw_gal_'.$pID);
			if(false===$fOutput){
	
				// Now for the padding.
				$padConfig = array(
					'posts_per_page'		=>	$padNumber,
					'no_found_rows'			=>	true,
					'post__not_in'			=> array($pID)
				);
				
				if($padMethod == 'cat'){
					// Regardless, let's do categories for now.
					$catList = wp_get_post_categories($pData);
					$catString = array();
					foreach($catList as $k => $v){
						array_push($catString, $v);
					}
					$padConfig['cat'] = implode(',',$catString);
				}
				
				query_posts($padConfig);
				
				if(have_posts()):
					ob_start();
						while(have_posts()):
							the_post();
							?><li data-id="<? the_ID(); ?>" class="image post"><a href="<? the_permalink(); ?>" title="<? the_title(); ?>" ><? 
								
								$xnn = convertImg(get_post_thumbnail_id($post->ID), $thSize);
								
								the_post_thumbnail($thSize);
							?></a><?
							?></li><?
						endwhile;
					$fOutput = ob_get_clean();
				endif;
				
				wp_reset_query();
				wp_reset_postdata();
				
				wp_cache_set('fw_gal_'.$pID, $fOutput);
			}// cache block
			echo $fOutput;*/

// End crashing query

			?></ul><?
	}
	
}
?>
