<?php 
class PortfolioMediaGeneralSettings {

	/* basic options */
	private $basic_options_key = 'pm_general_basic';
	private $basic_options;
	
	/* tab variables */	
	private $plugin_options_key = 'portfolio-media-grid';
	private $plugin_settings_tabs = array();
	
	/* constructer adds hooks for settings  */
	public function __construct() {
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_basic' ) );
	}
	
	/* clean data */
	public function sanatize_basic( $input ) {
		$output['use_single_page'] = isset( $input['use_single_page'] );
		return $output;
	}
	
	/*get defaults */
	public function get_basic_options_default() {
		return array(
			'use_single_page' => 1
		);
	}
	
	/* get options and merge with defaults */
	public function load_settings() {
		$this->basic_options = (array)get_option( $this->basic_options_key );
		
		#Merge with defaults
   		$this->basic_options = array_merge( $this->get_basic_options_default(), $this->basic_options );
	}
	
	/* settings API */
	public function register_basic() {
		$this->plugin_settings_tabs[$this->basic_options_key] = 'Basic Settings';
		register_setting( $this->basic_options_key, $this->basic_options_key, array( &$this, 'sanatize_basic' ) );
			
		add_settings_section( 'pm_general_basic_page', 'Use The Single Portfolio Page', array( &$this, 
		'pm_general_basic_page_callback' ), $this->basic_options_key );
		
		add_settings_field( 'pm_general_basic_page_single', 'Use A Portfolio Single Page',
		 array( &$this, 'pm_general_basic_page_single_callback' ), 
		$this->basic_options_key, 'pm_general_basic_page' );
	}
	
	/* page display */
	public function general_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->basic_options_key;
		?><div class="wrap">
        <div class="icon32" id="icon-generic"><br /><br /></div>
        <h2>General Settings</h2>
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
	
	/* display tabs */
	public function tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->basic_options_key;
  		echo '<h3 class="nav-tab-wrapper">';
		
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		
		echo '</h3>';
	}
	
	/********************************** FORM ELEMENTS -> BASIC **********************************/
	
	public function pm_general_basic_page_callback() {
	}
	
	public function pm_general_basic_page_single_callback() {
		?><input type="checkbox" name="pm_general_basic[use_single_page]" 
        value="1" <?php echo $this->basic_options['use_single_page'] ? 'checked' : ''; ?> /><?php
	}
}