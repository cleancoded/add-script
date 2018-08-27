<?php
/**
* Plugin Name: CLEANCODED Add Script
* Plugin URI: https://github.com/cleancoded/add-script
* Version: 1.0
* Author: CLEANCODED
* Author URI: https://CLEANCODED.com
* Description: Add extra code or scripts to the header and footer of your WordPress website by hooking into wp_head and wp_footer.
* License: GPL2
*/

/**
* Add Headers and Footers Class
*/
class CLEANCODEDAddScript {
	/**
	* Constructor
	*/
	public function __construct() {

		// Plugin Details
        $this->plugin               = new stdClass;
        $this->plugin->name         = 'CLEANCODED-Add-Script'; // Plugin Folder
        $this->plugin->displayName  = 'Add Script'; // Plugin Name
        $this->plugin->version      = '1.0';
        $this->plugin->folder       = plugin_dir_path( __FILE__ );
        $this->plugin->url          = plugin_dir_url( __FILE__ );
        $this->plugin->db_welcome_dismissed_key = $this->plugin->name . '_welcome_dismissed_key';

   		// Hooks
		add_action( 'admin_init', array( &$this, 'CLEANCODED_registerSettings' ) );
        add_action( 'admin_menu', array( &$this, 'CLEANCODED_adminPanelsAndMetaBoxes' ) );
 
        // Frontend Hooks
        add_action( 'wp_head', array( &$this, 'CLEANCODED_frontendHeader' ) );
		add_action( 'wp_footer', array( &$this, 'CLEANCODED_frontendFooter' ) );

		// Filters
		add_filter( 'dashboard_secondary_items', array( &$this, 'dashboardSecondaryItems' ) );
	}

    /**
     * Number of Secondary feed items to show
     */
	function dashboardSecondaryItems() {
		return 6;
	}

    	/**
	* Register Settings
	*/
	function CLEANCODED_registerSettings() {
		register_setting( $this->plugin->name, 'ccd_add_header', 'trim' );
		register_setting( $this->plugin->name, 'ccd_add_footer', 'trim' );
	}

	/**
    * Register the plugin settings panel
    */
    function CLEANCODED_adminPanelsAndMetaBoxes() {
    	add_submenu_page( 'options-general.php', $this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array( &$this, 'adminPanel' ) );
	}

    /**
    * Output the Administration Panel
    * Save POSTed data from the Administration Panel into a WordPress option
    */
    function adminPanel() {
		// only admin user can access this page
		if ( !current_user_can( 'administrator' ) ) {
			echo '<p>' . __( 'Sorry, you are not allowed to access this page.', $this->plugin->name ) . '</p>';
			return;
		}

    	// Save Settings
        if ( isset( $_REQUEST['submit'] ) ) {
        	// Check nonce
			if ( !isset( $_REQUEST[$this->plugin->name.'_nonce'] ) ) {
	        	// Missing nonce
	        	$this->errorMessage = __( 'nonce field is missing. Settings NOT saved.', $this->plugin->name );
        	} elseif ( !wp_verify_nonce( $_REQUEST[$this->plugin->name.'_nonce'], $this->plugin->name ) ) {
	        	// Invalid nonce
	        	$this->errorMessage = __( 'Invalid nonce specified. Settings NOT saved.', $this->plugin->name );
        	} else {
	        	// Save	
				// so do nothing before saving
	    		update_option( 'ccd_add_header', $_REQUEST['ccd_add_header'] );
	    		update_option( 'ccd_add_footer', $_REQUEST['ccd_add_footer'] );
	    		update_option( $this->plugin->db_welcome_dismissed_key, 1 );
				$this->message = __( 'Settings Saved.', $this->plugin->name );
			}
        }

        // Get latest settings
        $this->settings = array(
			'ccd_add_header' => esc_html( wp_unslash( get_option( 'ccd_add_header' ) ) ),
			'ccd_add_footer' => esc_html( wp_unslash( get_option( 'ccd_add_footer' ) ) ),
        );

    	// Load Settings Form
        include_once( WP_PLUGIN_DIR . '/' . $this->plugin->name . '/views/settings.php' );
    }

    /**
	* Loads plugin textdomain
	*/
	function loadLanguageFiles() {
		load_plugin_textdomain( $this->plugin->name, false, $this->plugin->name . '/languages/' );
	}

	/**
	* Outputs script / CSS to the frontend header
	*/
	function CLEANCODED_frontendHeader() {
		$this->output( 'ccd_add_header' );
	}

	/**
	* Outputs script / CSS to the frontend footer
	*/
	function CLEANCODED_frontendFooter() {
		$this->output( 'ccd_add_footer' );
	}

	/**
	* Outputs the given setting, if conditions are met
	*
	* @param string $setting Setting Name
	* @return output
	*/
	function output( $setting ) {
		// Ignore admin, feed, robots or trackbacks
		if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
			return;
		}

		// provide the opportunity to Ignore ccd - both headers and footers via filters
		if ( apply_filters( 'disable_ccd', false ) ) {
			return;
		}

		// provide the opportunity to Ignore ccd - footer only via filters
		if ( 'ccd_add_footer' == $setting && apply_filters( 'disable_ccd_footer', false ) ) {
			return;
		}

		// provide the opportunity to Ignore ccd - header only via filters
		if ( 'ccd_add_header' == $setting && apply_filters( 'disable_ccd_header', false ) ) {
			return;
		}

		// Get meta
		$meta = get_option( $setting );
		if ( empty( $meta ) ) {
			return;
		}
		if ( trim( $meta ) == '' ) {
			return;
		}

		// Output
		echo wp_unslash( $meta );
	}
}

$ccd = new CLEANCODEDAddScript();
