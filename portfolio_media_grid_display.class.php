<?php
require_once( 'portfolio_media_display.class.php' );
class PortfolioMediaGridDisplay extends PortfolioMediaDisplay {

	#grid settings
	protected $grid_layout_options;
	protected $grid_display_options;
	
	/* construct will get grid options */
	public function __construct() {
		parent::__construct();
		$this->grid_layout_options = get_option( 'pm_grid_layout' );
		$this->grid_display_options = get_option( 'pm_grid_display' );
	}
	
	/* enque stylesheet */
	public function load_grid_style_script() {
	  if ( is_page() ) { 
	  	  	  global $post;
		  if ( false !== strpos($post->post_content, '[portfolio_media') ) { 
			 wp_enqueue_style( 'grid_style', plugins_url('grid_style.css', __FILE__) );
		  } 
	   } 
	}
	
	/* print the image media object */
	public function display_portfolio_media_image( $media_object, $width, $height ) {
		$link_to_thickbox = $this->media_image_options['use_thickbox'] ? true : false;
			
		# if LINK 
		if ($link_to_thickbox) {
			?><a class="thickbox" href="<?php echo $media_object->guid; ?>"><img src="<?php echo $media_object->guid; ?>" 
			width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="" /></a><?php
		} else {
			?><img src="<?php echo $media_object->guid; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="" /><?php
		}
	}
	
	/* print the video media object, uses video-js */
	public function display_portfolio_media_video( $media_object, $width, $height ) {
		?>   
		<video id="portfolio_media_id_<?php echo $media_object->ID; ?>" class="video-js vjs-default-skin" controls preload="none" 
		width="<?php echo $width; ?>" height="<?php echo $height; ?>" data-setup="{}">
        
		<source src="<?php echo $media_object->guid; ?>" type="<?php echo $media_object->post_mime_type; ?>">
		Your browser does not supmort the video tag.
		</video>
		<?php
	}
	
	/* print portfolio media title */
	public function display_tilte( $post ) {
		
		$title = $post->post_title;
		$char_limit = $this->grid_display_options['title_length'];
		
		#0 ignores
		if ( $char_limit != 0 && strlen( $title ) > $char_limit - 3 ) {
			$title = substr($title, 0, $char_limit - 3) . '...';
		}
		
		?><h3 class="portfolio_media_heading"><?php
		
		if ( $this->general_basic_options['use_single_page'] ) {
			?><a href="<?php echo $this->page_post->guid; ?>&amp;portfolio_media_id=<?php echo $post->ID; ?>"><?php echo $title; ?></a><?php
		} else {
			echo $title;
		}
		
		?></h3><?php	
	}
	
	/* print portfolio media desc */
	public function display_desc( $post ) {
		$desc = $post->post_content;
		$char_limit = $this->grid_display_options['desc_length'];
		
		#0 ignores
		if ( $char_limit != 0 && strlen( $desc ) > $char_limit - 3 ) {
			$desc = substr($desc, 0, $char_limit - 3) . '...';
		}
		
		?><p class="portfolio_media_desc"><?php echo $desc; ?></p><?php
	}
	
	/* print pagination links */
	public function display_paging_links( $wp_query ) {
		?><div class="portfolio_media_paging"><?php
		
		global $post;
		
		$args = array(
		'base'         => $post->guid.'%_%',
		'format'       => '&page=%#%',
		'total'        => $wp_query->max_num_pages,
		'current'      => max( 1, get_query_var( 'page' ) ),
		'show_all'     => false,
		'prev_next'    => true,
		'prev_text'    => __('« Previous'),
		'next_text'    => __('Next »'),
		);

		echo paginate_links( $args );
		
		?></div><?php
	}
	
	/*print grid styles for styles that require PHP output to them */
	public function run_grid_style() {
		?>
        <style type="text/css" scoped>
		div#pbm_portfolio_list div.pbm_portfolio_list_item {
			margin-left: <?php if ( $spacing = $this->grid_layout_options['spacing'] ) echo $spacing['column']; ?>px;
		}
		
		div#portfolio_media_list div.portfolio_media_wrap {
			margin-left: <?php if ( $spacing = $this->grid_layout_options['spacing'] ) echo $spacing['row']; ?>px;
			margin-top: <?php if ( $spacing = $this->grid_layout_options['spacing'] ) echo $spacing['column']; ?>px;
			width: <?php echo $this->grid_layout_options['portfolio_media_dim']['width']; ?>px
		}
		
		div#portfolio_media_list div.portfolio_media_wrap img, div#portfolio_media_list div.portfolio_media_wrap a img {
			height: <?php echo $this->grid_layout_options['portfolio_media_dim']['height']; ?>px; !important;
		}
		
		div#portfolio_media_list div.first_column {
			margin-left: 0px; !important
		}

		div#portfolio_media_list div.first_row {
			margin-top: 0px; !important
		}
		</style> 
        <?php
	}
	
	
	/**************************** PAGES ****************************/
	
	/* this function prints the whole grid page to the screen, it queries the 
		portfolio_media post type and prints the data */
	public function generate_page( $page_post ) {
		$this->page_post = $page_post;
		#run STYLE tag data
		$this->run_grid_style();

		$page = (get_query_var('page')) ? get_query_var('page') : 1;

		$wp_query = new WP_Query( array(
			'post_type' => 'portfolio_media',
			'post_status' => 'publish',
			'is_paged' => true,
			'posts_per_page' => $this->grid_layout_options['per_page'],
			'paged' => $page,
			'order' => $this->grid_layout_options['order'] ? $this->grid_layout_options['order'] : 'title',
			'order_by' => $this->grid_layout_options['order_by'] ? $this->grid_layout_options['order_by'] : 'ASC'
		));
	
		?>
		<?php $this->display_paging_links( $wp_query ); ?>
		<div id="portfolio_media_list">
		<?php
		$per_row_counter = 1;
		$top_row = true;
		
		while ( $wp_query->have_posts() ) : $wp_query->the_post();
			?><div class="portfolio_media_wrap <?php if($per_row_counter == 1) 
			{ echo 'first_column'; } if ($top_row) { echo ' first_row'; } ?>"><?php
			
			global $post;
			
			/* display portfolio media */
			if ( $this->grid_display_options['portfolio_media'] ) { 
				$width = $this->grid_layout_options['portfolio_media_dim']['width'];
				$height = $this->grid_layout_options['portfolio_media_dim']['height'];
				$this->display_portfolio_media( $post, $width, $height );
			}
			
			/* display title */
			if( $this->grid_display_options['title'] ) { 
				$this->display_tilte( $post );
			} 
			
			/* display content */
			if( $this->grid_display_options['desc'] ) { 
				$this->display_desc( $post );
			} 
			
			?></div><?php
			
			if ($per_row_counter == $this->grid_layout_options['per_row'] ) {
				$per_row_counter = 1;
				$top_row = false;
				echo '<div class="clear"></div>';
			} else {
				$per_row_counter++;
			}
			
		endwhile;
		wp_reset_query();
		?></div><?php
	}
}
?>