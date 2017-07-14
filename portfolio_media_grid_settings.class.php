<?php

class PortfolioMediaGridSettings {
	
	/* option key varibales */
	private $layout_options_key = 'pm_grid_layout';
	private $display_options_key = 'pm_grid_display';

	/* option varibales */
	private $layout_options;
	private $display_options;
	
	/* tab variables */
	private $plugin_options_key = 'portfolio-media-grid';
	private $plugin_settings_tabs = array();
	
	public function __construct() {
		#GRID SETTINGS
		add_action( 'init', array( &$this, 'load_settings' ) );
		
		add_action( 'admin_init', array( &$this, 'register_layout' ) );
		add_action( 'admin_init', array( &$this, 'register_display' ) );
	}
	
	/* default layout options */	
	public function get_layout_options_default() {
		return array(
			'portfolio_media_dim' => array( 'width' => 200, 'height' => 133 ),
			'per_page' => 4,
			'per_row' => 4,
			'spacing' => array( 'column' => 10, 'row' => 10 ),
			'order_by' => 'title',
			'order' => 'asc'
		);
	}	
	
	/* default display options */	
	public function get_display_options_default() {
		return array(
			'portfolio_media' => true,
			'title' => true,
			'desc' => true,
			'title_length' => 10,
			'desc_length' => 10,
		);
	}	
	
	/* clean data */
	public function sanatize_layout( $input ) {
		
		#dimensions
		$output['portfolio_media_dim']['width'] = intval( $input['portfolio_media_dim']['width'] ); 
		$output['portfolio_media_dim']['height'] = intval( $input['portfolio_media_dim']['height'] ); 
		
		#per row + page
		$output['per_row'] = intval( $input['per_row'] );
		$output['per_page'] = intval( $input['per_page'] );
		
		#spacing
		$output['spacing']['row'] = intval( $input['spacing']['row'] );
		$output['spacing']['column'] = intval( $input['spacing']['column'] );
		
		#order
		$output['order'] = strip_tags( $input['order'] );
		$output['order_by'] = strip_tags( $input['order_by'] );
		
		return $output;
	}
	
	/* clean data */
	public function sanataize_display( $input ) {
		
		$output['portfolio_media'] = isset( $input['portfolio_media'] );
		$output['title'] = isset( $input['title'] );
		$output['desc'] = isset( $input['desc'] );
		
		$output['title_length'] = intval( $input['title_length'] );
		$output['desc_length'] = intval( $input['desc_length'] );

		return $output;
	}
	
	/* get option data and run it against its default */
	public function load_settings() {
		$this->layout_options = (array)get_option( $this->layout_options_key );
		$this->display_options = (array)get_option( $this->display_options_key );
		
		/* merge with defaults */
   		$this->layout_options = array_merge( $this->get_layout_options_default(), $this->layout_options );
		$this->display_options = array_merge( $this->get_display_options_default(), $this->display_options );
	}
	
	/* settings API for layout options */
	public function register_layout() {
		$this->plugin_settings_tabs[$this->layout_options_key] = 'Layout Settings';
		register_setting( $this->layout_options_key, $this->layout_options_key, array( &$this, 'sanatize_layout' ) );
		
		add_settings_section('pm_grid_layout', 'Layout Settings', array( &$this, 'pm_grid_layout_callback' ), $this->layout_options_key );
		
		add_settings_field( 'pm_grid_layout_portfolio_media_dim', 'Portfolio Media Dimensions', array( &$this, 'pm_grid_layout_portfolio_media_dim_callback' ), 
		$this->layout_options_key, 'pm_grid_layout' );
		
		add_settings_field( 'pm_grid_layout_per_page', 'Per Page', array( &$this, 'pm_grid_layout_per_page_callback' ), 
		$this->layout_options_key, 'pm_grid_layout' );
		
		add_settings_field( 'pm_grid_layout_per_row', 'Per Row', array( &$this, 'pm_grid_layout_per_row_callback' ), 
		$this->layout_options_key, 'pm_grid_layout' );
		
		add_settings_field( 'pm_grid_layout_spacing', 'Spacing', array( &$this, 'pm_grid_layout_spacing_callback' ), 
		$this->layout_options_key, 'pm_grid_layout' );
		
		add_settings_field( 'pm_grid_layout_order_by', 'Order By', array( &$this, 'pm_grid_layout_order_by_callback' ), 
		$this->layout_options_key, 'pm_grid_layout' );
		
		add_settings_field( 'pm_grid_layout_order', 'Order', array( &$this, 'pm_grid_layout_order_callback' ), 
		$this->layout_options_key, 'pm_grid_layout' );
	}
	
	/* settings API for display options */
	public function register_display() {
		$this->plugin_settings_tabs[$this->display_options_key] = 'Display Settings';
		register_setting( $this->display_options_key, $this->display_options_key, array( &$this, 'sanataize_display' ) );
		
		register_setting( 'pm_grid_display', 'pm_grid_display' );
		add_settings_section('pm_grid_display', 'Display Settings', array( &$this, 'pm_grid_display_callback' ), $this->display_options_key);
		
		add_settings_field( 'pm_grid_display_title_length', 'Title Length', 
		array( &$this, 'pm_grid_display_title_length_callback' ), $this->display_options_key, 'pm_grid_display' );
		
		add_settings_field( 'pm_grid_display_desc_length', 'Description Length', 
		array( &$this, 'pm_grid_display_desc_length_callback' ), $this->display_options_key, 'pm_grid_display' );
		
		add_settings_field( 'pm_grid_display_portfolio_media', 'Display Portfolio Media', 
		array( &$this, 'pm_grid_display_portfolio_media_callback' ), $this->display_options_key, 'pm_grid_display' );
		
		add_settings_field( 'pm_grid_display_title', 'Display Title', 
		array( &$this, 'pm_grid_display_title_callback' ), $this->display_options_key, 'pm_grid_display' );
		
		add_settings_field( 'pm_grid_display_desc', 'Display Description', 
		array( &$this, 'pm_grid_display_desc_callback' ), $this->display_options_key, 'pm_grid_display' );
	}
	
	/* this function displays the HTML for the page */
	public function grid_page() {
		 $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->layout_options_key;
		?><div class="wrap">
        <div class="icon32" id="icon-generic"><br /><br /></div>
        <h2>Grid Settings</h2>
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

	
	/********************************** FORM ELEMENTS -> LAYOUT **********************************/
	
	public function pm_grid_layout_callback() {
	}
	
	public function pm_grid_layout_portfolio_media_dim_callback() {
		?>Width(px): <input type="text" name="pm_grid_layout[portfolio_media_dim][width]" 
        value="<?php echo $this->layout_options['portfolio_media_dim']['width']; ?>" class="small-text"  /><?php
		?>Height(px): <input type="text" name="pm_grid_layout[portfolio_media_dim][height]" 
        value="<?php echo $this->layout_options['portfolio_media_dim']['height'];  ?>" class="small-text" /><?php
		
	}
	
	public function pm_grid_layout_per_page_callback() {
		?> <select name="pm_grid_layout[per_page]">
		<?php for($i=1;$i<21;$i++) {
			?><option value="<?php echo $i; ?>" <?php echo $i == $this->layout_options['per_page'] ? 'selected' : ''; ?>
            ><?php echo $i; ?></option><?php
		}
		?></select><?php
	}
	
	public function pm_grid_layout_per_row_callback() {
		?> <select name="pm_grid_layout[per_row]">
		<?php for($i=1;$i<21;$i++) {
			?><option value="<?php echo $i; ?>" <?php echo $i == $this->layout_options['per_row'] ? 'selected' : ''; ?>
            ><?php echo $i; ?></option><?php
		}
		?></select><?php
	}
	
	public function pm_grid_layout_spacing_callback() {
		?>Column(px)<input type="text" name="pm_grid_layout[spacing][column]" value="<?php echo $this->layout_options['spacing']['column']; ?>" 
		class="small-text"  /><?php
		?>Row(px): <input type="text" name="pm_grid_layout[spacing][row]" value="<?php echo $this->layout_options['spacing']['row']; ?>" 
		class="small-text" /><?php
	}
	
	public function pm_grid_layout_order_by_callback() {
		$order_array = array('title', 'date');
		?><select name="pm_grid_layout[order_by]">
		<?php foreach($order_array as $order) {
			?><option value="<?php echo $order; ?>" 
			<?php echo $order == $this->layout_options['order_by'] ? 'selected' : ''; ?>><?php echo ucfirst($order); ?></option><?php
		}
		?></select><?php
	}
	
	public function pm_grid_layout_order_callback() {
		?>
		<input type="radio" name="pm_grid_layout[order]" 
        value="ASC" checked />ASC
		<input type="radio" name="pm_grid_layout[order]" 
        value="DESC" <?php echo $this->layout_options['order'] == 'DESC' ? 'checked' : ''; ?>/>DESC
		<?php
	}
	
	/********************************** FORM ELEMENTS -> DISPLAY **********************************/
	
	public function pm_grid_display_callback() {
	}
	
	public function pm_grid_display_title_length_callback() {
		?><input type="text" name="pm_grid_display[title_length]" 
        value="<?php echo $this->display_options['title_length']; ?>" class="small-text"  />(0 to disable)<?php
	}
	
	public function pm_grid_display_desc_length_callback() {
		?><input type="text" name="pm_grid_display[desc_length]" 
        value="<?php echo $this->display_options['desc_length']; ?>" class="small-text"  />(0 to disable)<?php
	}
	
	public function pm_grid_display_portfolio_media_callback() {
		?><input type="checkbox" name="pm_grid_display[portfolio_media]" value="1" 
		<?php echo $this->display_options['portfolio_media'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_grid_display_title_callback() {
		?><input type="checkbox" name="pm_grid_display[title]" value="1" 
		<?php echo $this->display_options['title'] ? 'checked' : ''; ?> /><?php
	}
	
	public function pm_grid_display_desc_callback() {
		?><input type="checkbox" name="pm_grid_display[desc]" value="1" 
		<?php echo $this->display_options['desc'] ? 'checked' : ''; ?> /><?php
	}
	
	
	/********************************** FORM ELEMENTS -> ANIMATION **********************************/
}

?>