<?php
class PortfolioMediaSingleSettings {
	
	/* option key varibales */
	private $layout_options_key = 'pm_single_layout';
	private $display_options_key = 'pm_single_display';
	
	/* option varibales */
	private $display_options;
	private $layout_options;
	
	/* tab variables */
	private $plugin_options_key = 'portfolio-media-single';
	private $plugin_settings_tabs = array();
	
	public function __construct() {
		#GRID SETTINGS
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_layout' ) );
		add_action( 'admin_init', array( &$this, 'register_display' ) );
	}
	
	/* defaults for display options */
	public function get_display_options_default() {
		return array(
			'portfolio_media' => true,
			'title' => true,
			'desc' => true,
			'when_posted' => true,
			'category' => true,
			'back_link' => true 
		);
	}	
	/* defaults for layout options */
	public function get_layout_options_default() {
		return array(
			'portfolio_media_dim' => array( 'width' => 500, 'height' => 333 )
		);
	}
	
	/* clean data */
	public function sanataize_display( $input ) {
		$output['portfolio_media'] = intval( $input['portfolio_media'] );
		$output['title'] = intval( $input['title'] ); 
		$output['desc'] = intval( $input['desc'] ); 
		$output['when_posted'] = intval( $input['when_posted'] ); 
		$output['category'] = intval( $input['category'] ); 
		$output['back_link'] = intval( $input['back_link'] );  
		return $output;
	}
	
	/* clean data */
	public function sanataize_layout( $input ) {
		$output['portfolio_media_dim']['width'] = intval( $input['portfolio_media_dim']['width'] );
		$output['portfolio_media_dim']['height'] = intval( $input['portfolio_media_dim']['height'] );
		return $output;
	}
	
	/* get option data and run it against its default */
	public function load_settings() {
		$this->layout_options = (array)get_option( $this->layout_options_key );
		$this->display_options = (array)get_option( $this->display_options_key );
		
		#Merge with defaults
		$this->display_options = array_merge( $this->get_display_options_default(), $this->display_options );
		$this->layout_options = array_merge( $this->get_layout_options_default(), $this->layout_options );
	}
	
	/* settings API for display options */
	public function register_display() {
		$this->plugin_settings_tabs[$this->display_options_key] = 'Display Settings';
		register_setting( $this->display_options_key, $this->display_options_key, array( &$this, 'sanataize_display' ) );
		
		add_settings_section('pm_single_display', 'Display Settings', array( &$this, 'pm_single_layout_callback' ), $this->display_options_key );
		
		add_settings_field( 'pm_single_display_portfolio_media', 'Display Portfolio Media', array( &$this, 'pm_single_display_portfolio_media_callback' ), 
		$this->display_options_key, 'pm_single_display' );
		
		add_settings_field( 'pm_single_display_title', 'Display Title', array( &$this, 'pm_single_display_title_callback' ), 
		$this->display_options_key, 'pm_single_display' );
		
		add_settings_field( 'pm_single_display_desc', 'Display Desc', array( &$this, 'pm_single_display_desc_callback' ), 
		$this->display_options_key, 'pm_single_display' );	
		
		add_settings_field( 'pm_single_display_when_posted', 'Display When Posted', array( &$this, 'pm_single_display_when_posted_callback' ), 
		$this->display_options_key, 'pm_single_display' );	
		
		add_settings_field( 'pm_single_display_category', 'Display Category', array( &$this, 'pm_single_display_category_callback' ), 
		$this->display_options_key, 'pm_single_display' );
		
		add_settings_field( 'pm_single_display_back_link', 'Display Back Link', array( &$this, 'pm_single_display_back_link_callback' ), 
		$this->display_options_key, 'pm_single_display' );
	}
	
	/* settings API for layout options */
	public function register_layout() {
		$this->plugin_settings_tabs[$this->layout_options_key] = 'Layout Settings';
		register_setting( $this->layout_options_key, $this->layout_options_key, array( &$this, 'sanataize_layout' ) );
		
		add_settings_section('pm_single_layout', 'Layout Settings', array( &$this, 'pm_single_display_callback' ), $this->layout_options_key );
		
		add_settings_field( 'pm_single_layout_media_dim', 'Portfolio Media Dimensions', array( &$this, 'pm_single_layout_media_dim_callback' ), 
		$this->layout_options_key, 'pm_single_layout' );
	}
	
	/* this function displays the HTML for the page */
	public function single_page() {
		 $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->layout_options_key;
		?><div class="wrap">
        <div class="icon32" id="icon-generic"><br /><br /></div>
        <h2>Single Page Settings</h2>
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
		 $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->layout_options_key;
  		 echo '<h3 class="nav-tab-wrapper">';
		 
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
    	echo '</h3>';
	}
	
	/********************************** FORM ELEMENTS -> DISPLAY **********************************/
	
	public function pm_single_display_callback() {
	}
	
	public function pm_single_display_portfolio_media_callback() {
		?><input type="checkbox" name="pm_single_display[portfolio_media]" value="1" 
		<?php echo $this->display_options['portfolio_media'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_single_display_title_callback() {
		?><input type="checkbox" name="pm_single_display[title]" value="1" 
		<?php echo $this->display_options['title'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_single_display_desc_callback() {
		?><input type="checkbox" name="pm_single_display[desc]" value="1" 
		<?php echo $this->display_options['desc'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_single_display_when_posted_callback() {
		?><input type="checkbox" name="pm_single_display[when_posted]" value="1" 
		<?php echo $this->display_options['when_posted'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_single_display_category_callback() {
		?><input type="checkbox" name="pm_single_display[category]" value="1" 
		<?php echo $this->display_options['category'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_single_display_back_link_callback() {
		?><input type="checkbox" name="pm_single_display[back_link]" value="1" 
		<?php echo $this->display_options['back_link'] ? 'checked' : ''; ?> /><?php
	}
	
	/********************************** FORM ELEMENTS -> LAYOUT **********************************/
	
	public function pm_single_layout_callback() {
	}
	
	public function pm_single_layout_media_dim_callback() {
		?>Width(px): <input type="text" name="pm_single_layout[portfolio_media_dim][width]" 
        value="<?php echo $this->layout_options['portfolio_media_dim']['width']; ?>" class="small-text"  /><?php
		?>Height(px): <input type="text" name="pm_single_layout[portfolio_media_dim][height]" 
        value="<?php echo $this->layout_options['portfolio_media_dim']['height'];  ?>" class="small-text" /><?php
	}
}

?>