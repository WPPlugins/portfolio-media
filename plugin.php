<?php
/* 
Plugin Name: Portfolio Media
Plugin URI: 
Description: A simple plugin that allows users to post portfolio images and video data.
Version: 1.1 
Author: Dean Dingle 
Author URI: http://deandingle.co.uk 
Author Email: deanjdingle@gmail.com
License: 
 
  Copyright 2013 Dean Dingle (deanjdingle@gmail.com)
   
*/  

/* require all required class files */
require_once( 'portfolio_media_portfolio.class.php' );
require_once( 'portfolio_media_general_settings.class.php' );
require_once( 'portfolio_media_grid_settings.class.php' );
require_once( 'portfolio_media_grid_display.class.php' );
require_once( 'portfolio_media_media_settings.class.php' );
require_once( 'portfolio_media_single_settings.class.php' );
require_once( 'portfolio_media_single_display.class.php' );

if( !array_key_exists( 'portfoliomedia', $GLOBALS ) ) {  
 
    class PortfolioMedia {
	
	/* objects for the classes */ 
	private $portfolio;
	private $general_settings;
	private $grid_settings;
	private $grid_display;
	private $media_settings;
	private $single_settings;
	private $single_display;
	
	/* get_option data */
	private $general_basic_options;
	
		/* The construct will setup most hooks and create the nessary objects */
        function __construct() { 
			$this->general_basic_options = get_option( 'pm_general_basic' );
			$this->portfolio = new PortfolioMediaPortfolio();
			$this->general_settings = new PortfolioMediaGeneralSettings();
			$this->grid_settings = new PortfolioMediaGridSettings();
			$this->grid_display = new PortfolioMediaGridDisplay();
			$this->media_settings = new PortfolioMediaMediaSettings();
			$this->single_settings = new PortfolioMediaSingleSettings();
			$this->single_display = new PortfolioMediaSingleDisplay();
			
			/* HOOKS */
			
			/* add shortcodes */
			add_shortcode('portfolio_media', array( &$this, 'shortcode_portfolio' ) );
			
			/* install */
			register_activation_hook(__FILE__,  array( &$this, 'activate' ) );
			
			/* register post type and taxonomy */
			add_action( 'admin_menu', array( &$this, 'setup_admin_menu' ) );
			
			add_action( 'init', array( &$this, 'portfolio_type_register' ) );
			add_action( 'init', array( &$this, 'portfolio_tax_register' ) );
			
			/* admin hooks */
			add_action( 'admin_init', array( &$this->portfolio, 'add_portfolio_media_box' ) );
			add_filter( 'media_upload_tabs', array( &$this->portfolio, 'media_upload_portfolio_tabs' ) );	
			
			add_filter( 'attachment_fields_to_edit', array( &$this->portfolio, 'add_portfolio_media_link' ), 15, 2);
			add_filter( 'attachment_fields_to_save', array( &$this->portfolio, 'portfolio_media_set' ), 15, 2);
			
			add_action( 'init', array( &$this->portfolio, 'setup_thickbox' ) );
						
			add_action( 'admin_init', array( &$this->portfolio, 'register_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( &$this->portfolio, 'register_client_scripts' ) );
			
			add_action('wp_ajax_portfolio_meta', array( &$this->portfolio, 'portfolio_meta' ) );
			add_action('wp_ajax_nopriv_portfolio_meta', array( &$this->portfolio, 'portfolio_meta' ) );
			
			/* load styles for grid */
			add_action( 'template_redirect', array( &$this->grid_display, 'load_grid_style_script' ) );			
        }
		
		/* This function is called when the plugin is activated */
		function activate() {
			/* set options to their defaults */
			/* general settings */
			update_option( 'pm_general_basic', $this->general_settings->get_basic_options_default() );
			
			/* grid settings */
			update_option( 'pm_grid_layout', $this->grid_settings->get_layout_options_default() );
			update_option( 'pm_grid_display', $this->grid_settings->get_display_options_default() );
			
			/* single page settings */
			update_option( 'pm_single_layout', $this->single_settings->get_layout_options_default() );
			update_option( 'pm_single_display', $this->single_settings->get_display_options_default() );
			
			/* Media settings */
			update_option( 'pm_media_image', $this->media_settings->get_image_options_default() );
	}
		
		/* setup the admin menu, single page admin config will only show when single page is in use  */
		public function setup_admin_menu() {
			add_menu_page( 'Portfolio Press','Portfolio Media','manage_options', 'portfolio-media', array( &$this, 'main_plugin_page' ) );
			
			add_submenu_page( 'portfolio-media', 'General Settings', 'General Settings', 'manage_options', 
			'portfolio-media-general', array( &$this->general_settings, 'general_page' ) );
			
			add_submenu_page( 'portfolio-media', 'Media Settings', 'Media Settings', 'manage_options', 
			'portfolio-media-media', array( &$this->media_settings, 'media_page' ) );
			
			add_submenu_page( 'portfolio-media', 'Grid Settings', 'Grid Settings', 'manage_options', 
			'portfolio-media-grid', array( &$this->grid_settings, 'grid_page' ) );
			
			$basic_options = get_option( 'pm_general_basic' );
			
			if ( $this->general_basic_options['use_single_page'] ) {
			
				add_submenu_page( 'portfolio-media', 'Single Page Settings', 'Single Page Settings', 'manage_options', 
				'portfolio-media-single', array( &$this->single_settings, 'single_page' ) );
			}
		}
			
		/* register the portfolio_media post type */
		public function portfolio_type_register() {
			$args = array(
				'label' =>__('Portfolio'),
				'singular_label' => __('Portfolio'),
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'post',
				'hierarchical' => true,
				'rewrite' => true,
				'supports' => array('title', 'editor', 'page-attributes'),
				'show_in_nav_menus' => true,
				'has_archive' => true
			);
							
			register_post_type( 'portfolio_media',$args );
		}
		
		/* register the categories taxonomy */
		public function portfolio_tax_register() {
			$args = array(
				'hierarchical' => true, 
				'label' => 'Portfolio Categories', 
				'singular_label' => 'Portfolio Category', 
				'rewrite' => true,
			);
			register_taxonomy( 'portfolio_category', array( 'portfolio_media' ), $args );
		}
		
		/* HTML print for the main plugin page */
		public function main_plugin_page() {
			?>
			<div class="wrap">
            	<div class="icon32" id="icon-generic"><br /><br /></div>
                <h2>Portfolio Media</h2>
                <h4>What does this do?</h4>
                <p>
                <ul>
                	<li>-Portfolio Media post type and categories</li>
                    <li>-Create a portfolio Media post and attach an image or video to it</li>
                   	<li>-Grid based layout</li>
                </ul>
                </p>
                <h4>Getting Started</h4>
                 <p>
                 	implement the shortcode [portfolio_media] on any Wordpress page
                 </p>
                 <h4>Feedback</h4>
                 <p>
                 	Feedback is a very important aspect of this plugin and I welcome all sugestions for future development.
                 </p>
                 </div>
            </div>
			<?php
		}
		
		/* function called when short code is implemented, checks the URL state for portfolio_media_id 
		and either loads the gallery or the single page (if enabled) */
		public function shortcode_portfolio() {
			global $post;
			
			if ( $this->general_basic_options['use_single_page'] ) {
			
				if ( isset($_GET['portfolio_media_id'] ) ) {
					$portfolio_media_id = intval( $_GET['portfolio_media_id'] );
					
					if ( portfolioMediaDisplay::is_portfolio_media( $portfolio_media_id ) ) {
						return $this->single_display->generate_page( $post, $portfolio_media_id );
					}
				}
			}
			return $this->grid_display->generate_page( $post );
		}
	}
    $GLOBALS['portfoliopress'] = new PortfolioMedia();   
}
?>