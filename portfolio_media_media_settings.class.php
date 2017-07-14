<?php
class PortfolioMediaMediaSettings {
	
	/* option key varibales */
	private $image_options_key = 'pm_media_image';
	private $video_options_key = 'pm_media_video';
	
	/* option varibales */
	private $image_options;
	private $video_options;
	
	/* tab variables */
	private $plugin_options_key = 'portfolio-media-media';
	private $plugin_settings_tabs = array();
	
	private $general_basic_options;
	
	public function __construct() {
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_image' ) );
		add_action( 'admin_init', array( &$this, 'register_video' ) );
	}
	
	/* clean data */
	public function sanatize_image( $input ) {
		$output['use_thickbox'] = isset( $input['use_thickbox'] );
		
		if ( $this->general_basic_options['use_single_page'] ) {
			$output['single_height_auto'] = isset( $input['single_height_auto'] );
		} else {
			$output['single_height_auto'] = $this->image_options['single_height_auto'];
		}
		
		return $output;
	}
	
	/* clean data */
	public function sanatize_video( $input ) {
		return $output;
	}
	
	/* get option data and run it against its default */
	public function load_settings() {
		$this->image_options = (array)get_option( $this->image_options_key );
		$this->video_options = (array)get_option( $this->video_options_key );
		
		/* merge with defaults */
   		$this->image_options = array_merge( $this->get_image_options_default(), $this->image_options );
		$this->video_options = array_merge( $this->get_image_video_default(), $this->video_options );
		
		/* get general settings */
		$this->general_basic_options = get_option( 'pm_general_basic' );
	}
	
	/* option defaults */
	public function get_image_options_default() {
		return array(
			'use_thickbox' => 1,
			'height_auto' => 1,
			'single_height_auto' => 1
		);
	}
	
	public function get_image_video_default() {
		return array(
		);
	}
	
	/* this function displays the HTML for the page */
	public function media_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->image_options_key;
		?><div class="wrap">
        <div class="icon32" id="icon-generic"><br /><br /></div>
        <h2>Media Settings</h2>
        <?php $this->tabs(); ?>
        <form method="post" action="options.php">
        	<?php wp_nonce_field( 'update-options' ); ?>
            <?php settings_fields( $tab ); ?>
            <?php do_settings_sections( $tab ); ?>
            <?php submit_button(); ?>
        </form>
    	</div>
   		<?php
	}
	
	/* displays the tabs for the page */
	public function tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->image_options_key;
  		echo '<h3 class="nav-tab-wrapper">';
		
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		
		echo '</h3>';
	}
	
	/* settings API for image options */
	public function register_image() {
		$this->plugin_settings_tabs[$this->image_options_key] = 'Image Settings';
		register_setting( $this->image_options_key, $this->image_options_key, array( &$this, 'sanatize_image' ) );
			
		add_settings_section( 'pm_media_image', 'Image Settings', array( &$this, 
		'pm_media_image_callback' ), $this->image_options_key );
		
		add_settings_field( 'pm_media_image_thickbox', 'Use thickbox', array( &$this, 'pm_media_image_thickbox_callback' ), 
		$this->image_options_key, 'pm_media_image' );
		
		if ( $this->general_basic_options['use_single_page'] ) {
			add_settings_field( 'pm_media_single_height_auto', 'Use auto height', 
			array( &$this, 'pm_media_single_height_auto_callback' ), 
			$this->image_options_key, 'pm_media_image' );
		}
	}
	
	/* settings API for video options */
	public function register_video() {
		$this->plugin_settings_tabs[$this->video_options_key] = 'Video Settings';
		register_setting( $this->video_options_key, $this->video_options_key, array( &$this, 'sanatize_video' ) );
		
		add_settings_section( 'pm_media_video', 'Video Settings', array( &$this, 
		'pm_media_video_callback' ), $this->video_options_key );
	}
	
	/********************************** FORM ELEMENTS -> IMAGE **********************************/
	
	public function pm_media_image_callback() {
		?><?php
	}
	
	public function pm_media_image_thickbox_callback() {
		?><input type="checkbox" name="pm_media_image[use_thickbox]" 
        value="1" <?php echo $this->image_options['use_thickbox'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_media_single_height_auto_callback() {
		?><input type="checkbox" name="pm_media_image[single_height_auto]" 
        value="1" <?php echo $this->image_options['single_height_auto'] ? 'checked' : ''; ?> /> (Prevents image distoration, only for single page)<?php
	}
	
	/********************************** FORM ELEMENTS -> VIDEO **********************************/
	
	public function pm_media_video_callback() {
		?><?php
	}
}
?>