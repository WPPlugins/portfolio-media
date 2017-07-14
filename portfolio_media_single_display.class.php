<?php 
require_once( 'portfolio_media_display.class.php' );
class PortfolioMediaSingleDisplay extends PortfolioMediaDisplay {
	
	/* option variables */
	private $single_layout_options;
	private $single_display_options;
	
	/* contruct sets option variables and will call parent contruct */
	public function __construct() {
		parent::__construct();
		$this->single_layout_options = get_option( 'pm_single_layout' );
		$this->single_display_options = get_option( 'pm_single_display' );
	}
	
	/* function which displays the single page */
	public function generate_page( $page_post, $portfolio_media_id ) {
		
		/* set the page post and get the portfoio media post */
		$this->page_post = $page_post;
		$post = get_post( $portfolio_media_id );
		
		/* display back_link */
		if ( $this->single_display_options['back_link'] ) {
			$this->display_back_link();
		}
		
		/* display the portfolio media */
		if ( $this->single_display_options['portfolio_media'] ) {
			
			/* get width and height from general basic options and call parent method to display portfolio media */
			$width = $this->single_layout_options['portfolio_media_dim']['width'];
			$height = $this->single_layout_options['portfolio_media_dim']['height'];
			parent::display_portfolio_media( $post, $width, $height );
		}
		
		/* display the title */
		if ( $this->single_display_options['title'] ) {
			$this->display_tilte( $post );
		}
		
		/* display the description */
		if ( $this->single_display_options['desc'] ) {
			$this->display_desc( $post );
		}
		
		/* display when posted */
		if ( $this->single_display_options['when_posted'] ) {
			$this->display_when_posted( $post, true );
		}
		
		/* display categories */
		if ( $this->single_display_options['category'] ) {
			$this->display_categories( $post );
		}		
	}
	
	public function display_tilte( $post ) {
		$title = $post->post_title;
		?><h3 class="portfolio_media_heading"><?php echo $title; ?></h3><?php
	}
	
	public function display_desc( $post ) {
		$desc = $post->post_content;
		?><p class="portfolio_media_single_desc"><?php echo $desc; ?></p><?php
	}
	
	/* called from parent method display_portfolio_media to print the HTML for both video and image */
	public function display_portfolio_media_image( $media_object, $width, $height ) {
		$link_to_thickbox = $this->media_image_options['use_thickbox'] ? true : false;
		$use_auto_height = $this->media_image_options['single_height_auto'] ? true : false;
		
		if ($link_to_thickbox) { ?><a class="thickbox" href="<?php echo $media_object->guid; ?>"><?php }
		
		?><img style=" <?php echo $use_auto_height ? 'height:auto;' : ''; ?>" src="<?php echo $media_object->guid; ?>" 
        width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="" /><?php
		
		if ($link_to_thickbox) { ?></a><?php }
	}
	
	public function display_portfolio_media_video( $media_object, $width, $height ) {
		?>
		<video id="portfolio_media_id_<?php echo $post->ID; ?>" class="video-js vjs-default-skin" controls preload="none" 
		width="<?php echo $width; ?>" height="<?php echo $height; ?>" data-setup="{}">
        
		<source src="<?php echo $media_object->guid; ?>" type="<?php echo $media_object->post_mime_type; ?>">
		Your browser does not supmort the video tag.
		</video>
		<?php
	}
	
	/* print formated date() within HTML */
	public function display_when_posted( $post, $full_date = true ) {
		$date = strtotime( $post->post_date_gmt );
		$format = 'jS F o';

		?><div id="portfolio_media_single_whenposted"><?php echo 'Posted: ' . date($format, $date); ?></div><?php
	}
	
	/* gets category names and puts them into a string embedded into HTML */
	public function display_categories( $post ) {
		if ( $terms = wp_get_post_terms($post->ID, 'portfolio_category' ) ) {

			foreach($terms as $term) {
				$terms_array[] = $term->name;
			}
			
			if(isset($terms_array)) {
				$string =  implode(', ',$terms_array);	 
			}
		} else {
			$string = 'not in a category';
		}
		
		?><div id="portfolio_media_single_categories">Categories: <?php echo $string; ?></div><?php
	}
	
	/* displays back link to grid page */
	public function display_back_link() {
		?><div id="portfolio_media_back_link"><a href="<?php echo $this->page_post->guid; ?>">&lt;&lt; Back</a></div><?php
	}
}
?>