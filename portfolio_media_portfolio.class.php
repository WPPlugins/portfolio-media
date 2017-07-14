<?php
class PortfolioMediaPortfolio{
	
	function __construct() {
	}
	
	/* return media type */
	public static function get_media_type( $string ) {
		$pos = strpos( $string, '/' );
		$type = substr( $string, 0, $pos );
		
		if ( $type == 'video' || $type == 'image' ) {
			return $type;
		}
	}
	
	/* register and enque scripts and styles */
	public function register_client_scripts() { 
		wp_enqueue_script( 'video-js' );
		wp_enqueue_script( 'video-js-flash' );
		wp_enqueue_style( 'video-js-css',  plugins_url( 'scripts/video-js/video-js.css' , __FILE__),false );
	}
	
	public function register_scripts( $hook ) {
		wp_register_script( 'portfolio_admin', plugins_url( 'scripts/portfolio_admin.js' , __FILE__), false );
		wp_enqueue_script( 'video-js', plugins_url( 'scripts/video-js/video.js' , __FILE__), false );
		wp_enqueue_script( 'video-js-flash', plugins_url( 'scripts/video-js-flash.js' , __FILE__), false );
		wp_enqueue_style( 'video-js-css',  plugins_url( 'scripts/video-js/video-js.css' , __FILE__),false );
	}
	
	public function setup_thickbox() {
		if(!is_admin()) {    
			wp_enqueue_script('jquery');
			wp_enqueue_script('thickbox',null,array('jquery'));
			wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		 }
	}
	
	/* add link in the form to set as the portfolio image */
	public function add_portfolio_media_link($form_fields, $post) {

		if ( isset( $_GET['referer'] ) && isset( $_GET['tab'] ) ) {
			if ( $_GET['referer'] == 'pm-portfolio-media-object' && $_GET['tab'] == 'gallery' ) {
				$gallery_tab = true;
			}
		}
		
		/* check only for admin upload script or gallery tab */
		if ( $_SERVER['REQUEST_URI'] == '/wp-admin/async-upload.php' || isset( $gallery_tab ) ) { 
		
			/* check for only video + image */
			if ( PortfolioMediaPortfolio::get_media_type( $post->post_mime_type ) ) {
				
				if ( $parent_id = $post->post_parent ) {
		
					if ( get_post_type( $parent_id ) == 'portfolio_media' ) {	
						$form_fields['portfolio_media_button'] = array(
							'label' => 'Set As Portfolio Media',
							'input' => 'html',
							'html' => '<input type="submit" class="button" name="portfolio_media_button_id_'.$post->ID.'" value="Set As Portfolio Media" />',
							'show_in_modal' => false
						);
					}
				}
			}
		}
		return $form_fields;
	}
	
	/* check for submit and save new portfolio media and then run portfolio_admin script which makes an ajax call 
	to get the new portfolio media and display it to mimmic 'featured image' */
	public function portfolio_media_set( $post, $attachment ) {
		if( isset($_POST['portfolio_media_button_id_'.$post['ID']] ) ) {
			
			if( isset( $_GET['post_id'] ) ) {
				
				$id = intval( $_GET['post_id'] );
				$media_id = intval( $post['ID'] );
				
				if ($parent = get_post($id)) {
			
					$this->set_new_portfolio_media( $id, $media_id );
					wp_enqueue_script( 'portfolio_admin' );
					$params = array( 'post_id' => $id );
					wp_localize_script( 'portfolio_admin', 'MyScriptParams', $params );
				}
			}
		}
		return $post;
	}
	
	/* this function saves the ID of the portfolio media media object  to the post's post meta */
	public function set_new_portfolio_media( $post_id, $media_id ) {
		$data = array( 'media_id' => $media_id );
		
		if ( $options = get_post_meta( $post_id, 'pm_portfolio_media', true ) ) {
			update_post_meta( $post_id, 'pm_portfolio_media', $data );
		} else {
			add_post_meta( $post_id, 'pm_portfolio_media', $data );
		}
	}
	
	/* hide unused headings in the media libary */
	public function media_upload_portfolio_tabs( $fields ) {
		$referer_portfolio_check_get = strpos(wp_get_referer(), 'pm-portfolio-media-object');
		$referer_portfolio_check_post = strpos(wp_referer_field(), 'pm-portfolio-media-object');
		
		if ( $referer_portfolio_check_get != '' || $referer_portfolio_check_post != '' ) {
			unset($fields['gallery']);
			unset($fields['type_url']);
		}
		return $fields;
	}
	
	/* add meta box in portfolio admin */
	public function add_portfolio_media_box() {
		add_meta_box( 'portfolio_media', 'Portfolio Media', array( &$this, 'portfolio_media_box_print' ), 'portfolio_media', 'side', 'low' );
	}
	
	/* called from the portfolio_admin script this function gets the new portfolio media object 
	to display it using javascript */
	public function portfolio_meta() {
		$id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
		$id = intval($id);
		$post = get_post( $id );
		
		$this->portfolio_meta_print( $post );
		die();
	}
	
	/* prints the admin box for the portfolio admin */
	public function portfolio_media_box_print( $post ) {
		?> 
        <div id="portfolio_media_wrap"><?php $this->portfolio_meta_print( $post ); ?></div>
        <p><a class="thickbox add_media" href="/wp-admin/media-upload.php?referer=pm-portfolio-media-object&post_id=<?php echo $post->ID; ?>&TB_iframe=1&width=640&height=224&type=media">Set New Media</a></p>
		<?php
	}
	
			
	/* print portfolio image meta in portfolio admin page */
	public function portfolio_meta_print( $post ) {
		if ( $portfolio_media = get_post_meta( $post->ID, 'pm_portfolio_media', true ) ) {
			$media_object = get_post( $portfolio_media['media_id'] );

			/* check is an image */
			if ( PortfolioMediaPortfolio::get_media_type( $media_object->post_mime_type ) == 'image' ) {	
				?><img style="height: auto" id="portfolio_media_thumbnail" src="<?php echo $media_object->guid; ?>" 
				width="250" height="167" alt="Portfolio Media" /><?php
			}
			
			/* check is an video */
			if ( PortfolioMediaPortfolio::get_media_type( $media_object->post_mime_type ) == 'video' ) {	
				?>
				    <video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" 
                      width="250" 
                      height="167"
      				  data-setup="{}">
  						<source src="<?php echo $media_object->guid; ?>" type="<?php echo $media_object->post_mime_type; ?>">
  						Your browser does not support the video tag.
                    </video>
				<?php
			}
		}
	}
}
?>