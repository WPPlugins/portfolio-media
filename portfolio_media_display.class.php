<?php 

abstract class PortfolioMediaDisplay {
	
	/* general settings */
	protected $general_basic_options;
	
	/* media settings */
	protected $media_image_options;
	protected $media_video_options;	
	
	/* master page object */
	protected $page_post;
	
	/* construct will set the option values as variables */
	protected function __construct() {
		$this->general_basic_options = get_option( 'pm_general_basic' );	
		$this->media_image_options = get_option( 'pm_media_image' );
		$this->media_video_options = get_option( 'pm_media_video' );
	}
	
	/* abstract methods */
	abstract protected function display_portfolio_media_image( $media_object, $width, $height );
	abstract protected function display_portfolio_media_video( $media_object, $width, $height );
	abstract protected function display_tilte( $post );
	abstract protected function display_desc( $post );

	
	/* get portfolio media type */
	protected function get_media_type( $string ) {
		$pos = strpos( $string, '/' );
		$type = substr( $string, 0, $pos );
		
		if ( $type == 'video' || $type == 'audio' || $type == 'image' ) {
			return $type;
		}
	}
	
	/* print portfolio media, this calls abstract methods as grid and single print the data differently */
	public function display_portfolio_media( $post, $width, $height ){
		
		if ( $portfolio_media = get_post_meta( $post->ID, 'pm_portfolio_media', true ) ) {
			
			if ( $media_object = get_post( $portfolio_media['media_id'] ) ) {
				
				if ( $this->get_media_type( $media_object->post_mime_type ) == 'image' ) {
					$this->display_portfolio_media_image( $media_object, $width, $height );
				}
				
				if ( $this->get_media_type( $media_object->post_mime_type ) == 'video' ) {				
					$this->display_portfolio_media_video( $media_object, $width, $height );
				}
			}
		}
	}
	
	/* check is portfolio media */
	static function is_portfolio_media( $id ) {
		if ( $post = get_post( $id ) ) {
			if ( $post->post_type == 'portfolio_media' ) {
				return true;
			}
		}
	}
}