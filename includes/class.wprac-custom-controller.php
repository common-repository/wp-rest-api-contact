<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class WPRAC_Controller{
	protected static $instance;
	
	protected function __construct() {}
	protected function __clone() {}

	public static function getInstance(){
		if(self::$instance === null){
			self::$instance = new WPRAC_Controller();
		}
		return self::$instance;
	}

	public static function run(){
		$instance = self::getInstance();

		$tools_options = get_option('wprac-tools-options-custom');

		/*********************
		 * Dang ky scripts
		 *********************/
		add_action('admin_enqueue_scripts', function(){
			self::wprac_register_scripts();
		});

		//========================
		// Run Tools Controller
		//========================
		
		WPRAC_Tools_Controller::run();

		//========================
		// Run API Contact
		//========================
		if( $tools_options && $tools_options['wprac_contact_api'] == 'on' ){
			WPRAC_Api_Contact_Controller::run();
		}
		

		//========================
		// Run API Newsletter
		//========================
		if( $tools_options && $tools_options['wprac_newsletter_api'] == 'on' ){
			WPRAC_Api_Newsletter_Controller::run();
		}

		return $instance;
	}

	public static function wprac_plugin_activation(){
		//====================================
		// Kiem tra version WP
		//====================================
		if(version_compare($GLOBALS['wp_version'], WPRAC_MINIMUM_WP_VERSION, '<')){
			die('Phien ban toi thieu:' . WPRAC_MINIMUM_WP_VERSION);
		}
		/**************************
		 * Kiem tra version plugin
		 **************************/
		$version = get_option('wprac_plugin_version');
		if(!$version){
			// Tạo bảng tp-custom-contact
			WPRAC_Api_Contact_Controller::wprac_create_table();
			WPRAC_Api_Contact_Controller::wprac_insert_table_default();
			// Tạo bảng tp-custom-newsletter
			WPRAC_Api_Newsletter_Controller::wprac_create_table();
			WPRAC_Api_Newsletter_Controller::wprac_insert_table_default();

			add_option('wprac_plugin_version', WPRAC_VERSION);
		}else{
			update_option('wprac_plugin_version', WPRAC_VERSION);
		}

		/*************************************
		 *  Kiểm tra tools option
		 *************************************/
		$tools_options = get_option('wprac-tools-options-custom');
		if(!$tools_options){
			add_option('wprac-tools-options-custom',array(
				'wprac_contact_api' => 'on',
				'wprac_newsletter_api' => 'on'
			) );
		}

		/*************************************
		 *  Kiểm tra contact custom option
		 *************************************/
		$default_options = get_option('contact-config-custom');
		if(!$default_options){
			add_option('contact-config-custom', 'on');
		}

		/*************************************
		 *  Kiểm tra token newsletter option
		 *************************************/
		$token_newsletter_options = get_option('wprac-token-newsletter');
		if(!$token_newsletter_options){
			add_option('wprac-token-newsletter', md5(uniqid(rand(), true)) );
		}

		/*************************************
		 *  Kiểm tra token contact option
		 *************************************/
		$token_contact_options = get_option('wprac-token-contact');
		if(!$token_contact_options){
			add_option('wprac-token-contact', md5(uniqid(rand(), true)) );
		}
	}

	public static function wprac_plugin_deactivation(){	
		/**********************
		 * Xóa Table Contact
		 **********************/
		WPRAC_Api_Contact_Controller::wprac_drop_table();

		/**********************
		 * Xóa Table Newsletter
		 **********************/
		WPRAC_Api_Newsletter_Controller::wprac_drop_table();

		delete_option('wprac_plugin_version');
		
		/**********************************
		 * Hủy contact tools option
		 **********************************/
		delete_option('wprac-tools-options-custom');

		/**********************************
		 * Hủy contact config custom option
		 **********************************/
		delete_option('contact-config-custom');

		/*************************************
		 *  Hủy token_newsletter option
		 *************************************/
		delete_option('wprac-token-newsletter');

		/*************************************
		 *  Hủy token_contact option
		 *************************************/
		delete_option('wprac-token-contact');
	}

	public static function wprac_register_scripts(){
		/**********************
		 * Admin css
		 **********************/
		wp_register_style('tp_admin_css', WPRAC_PLUGIN_URL . 'css/admin-style.css');
		wp_enqueue_style('tp_admin_css');

		/**********************
		 * Admin js
		 **********************/
		wp_register_script('tp_admin_js', WPRAC_PLUGIN_URL . 'js/admin-js.js', array('jquery'));
		wp_enqueue_script('tp_admin_js');

		// truyền dữ liệu qua admin-js.js
		wp_localize_script('tp_admin_js', 'wprac_ajax_config', array(
			"url" => admin_url('admin-ajax.php')
		));
	}
}