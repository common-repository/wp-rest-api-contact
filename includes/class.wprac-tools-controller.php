<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class WPRAC_Tools_Controller extends WP_REST_Controller{
	protected static $instance;
	protected static $option;
	
	protected function __construct() {}
	protected function __clone() {}

	public static function getInstance(){
		if(self::$instance === null){
			self::$instance = new WPRAC_Tools_Controller();
		}
		return self::$instance;
	}

	public static function run(){
		$instance = self::getInstance();

		// Đăng ký Tools NPA Menu
		add_action('admin_menu', array($instance, 'wprac_add_tools_menu'));

		// Đăng ký fields cho trang tools
		add_action( 'admin_init', array( $instance, 'wprac_tools_options' ) );

		return $instance;
	}

	//====================================
	// Hàm đăng ký menu
	//====================================
	public static function wprac_add_tools_menu(){
		$instance = self::getInstance();

		$menuSlug = 'wprac-tools-npa';
        
        add_submenu_page('tools.php', "NPA Tools", "NPA Tools", 'manage_options',
        $menuSlug,array($instance,'wprac_tools_page'));

		return $instance;
	}

	//====================================
	// View Tools Page
	//====================================
	public static function wprac_tools_page(){
		$instance = self::getInstance();

		require(WPRAC_PLUGIN_DIR . 'views/backend/tools/tools.php');

		return $instance;
	}

	//====================================
	// wprac_tools_options
	//====================================
	public static function wprac_tools_options(){
    	$instance = self::getInstance();

    	register_setting('wprac_tools_options_group', 'wprac-tools-options-custom', array(self::$instance, 'wprac_save_data'));

    	add_settings_section('wprac_tools_options_section', '', function(){
    		echo 'This page is used to open / close modules of the WP REST API NPA plugin';
    	}, 'wprac-tools-npa');

    	//get option
		self::$option = get_option('wprac-tools-options-custom');
    	
    	add_settings_field('wprac-tools-contact-cb', 'Contact API Controller', function() use ($option) {
    		if(self::$option && self::$option['wprac_contact_api']) { $checked = 'checked'; }
    		echo '<input name="wprac-tools-options-custom[wprac_contact_api]" type="checkbox" '.$checked.'>';
    	}, 'wprac-tools-npa', 'wprac_tools_options_section');

    	add_settings_field('wprac-tools-newsletter-cb', 'Subscribe Newsletter API Controller', function() use ($option) {
    		if(self::$option && self::$option['wprac_newsletter_api']) { $checked = 'checked'; }
    		echo '<input name="wprac-tools-options-custom[wprac_newsletter_api]" type="checkbox" '.$checked.'>';
    	}, 'wprac-tools-npa', 'wprac_tools_options_section');

    	
    }
    public static function wprac_save_data($input){
    	echo '<pre>';
    	print_r($input);
    	echo '</pre>';
    	return $input;
    }
}