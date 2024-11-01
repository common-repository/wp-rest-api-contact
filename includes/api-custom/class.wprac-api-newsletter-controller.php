<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class WPRAC_Api_Newsletter_Controller extends WP_REST_Controller{
	protected static $instance;
	protected static $wpdb;
	protected static $table;
	
	protected function __construct() {}
	protected function __clone() {}

	public static function getInstance(){
		if(self::$instance === null){
			self::$instance = new WPRAC_Api_Newsletter_Controller();
		}
		return self::$instance;
	}

	public static function run(){
		$instance = self::getInstance();

		// Setup
		global $wpdb;
		self::$wpdb = $wpdb;
		
		// Table name
		self::$table = self::$wpdb->prefix . 'wprac_custom_newsletter';

		// Đăng ký route api
		add_action( 'rest_api_init', function(){
			self::wprac_register_routes();
		});

		// Đăng ký Screen Option
		add_filter('set-screen-option', array($instance, 'wprac_table_set_option'), 10, 3);

		// Đăng ký Newsletter Menu
		add_action('admin_menu', array($instance, 'wprac_add_newsletter_menu'));

		// Đăng ký ajax
		add_action('wp_ajax_do_send_mail_newsletter', array($instance, 'do_send_mail_newsletter'));
		add_action('wp_ajax_do_generate_newsletter_token', array($instance, 'do_generate_newsletter_token'));

		// Notification count in Newsletter Admin Menu.
		add_action('admin_menu', array($instance, 'wprac_notification_count_in_newsletter_menu'));

		return $instance;
	}

	public static function wprac_table_set_option($status, $option, $value) {
		$instance = self::getInstance();
  		return $value;
  		return $instance;
	}

	//====================================
	// Hàm tạo bảng
	//====================================
	public static function wprac_create_table(){
		$instance = self::getInstance();
		$table = self::$table;
		//set charset
		$charset_collate = '';
		if( !empty( $wpdb->charset ) ){
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if( !empty( $wpdb->collate ) ){
			$charset_collate .= "COLLATE {$wpdb->collate}";
		}

		//SQL
		$sql = "CREATE TABLE IF NOT EXISTS $table (
			email varchar(255) NOT NULL PRIMARY KEY,
			status int(1) DEFAULT 0 NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		return $instance;
	}

	//====================================
	// Hàm xóa bảng
	//====================================
	public static function wprac_drop_table(){
		$instance = self::getInstance();

		$table = self::$table;
		$sql = "DROP TABLE IF EXISTS $table";
		self::$wpdb->query($sql);

		return $instance;
	}

	//====================================
	// Hàm thêm dữ liệu mặc định cho bảng
	//====================================
	public static function wprac_insert_table_default(){
		$instance = self::getInstance();
		
		// Tạo dữ liệu cho bảng
		self::$wpdb->insert(self::$table, array(
			'email' 	=> 'chithien175@gmail.com',
			'time' 		=> current_time('mysql')
		));
		
		return $instance;
	}

	//====================================
	// Hàm đăng ký route api
	//====================================
	public function wprac_register_routes(){
		
		$instance = self::getInstance();
		$domain = 'wp/v2';

	    // Đăng ký route POST newsletter data
		register_rest_route( $domain, 'newsletter-api', array(
		// register_rest_route( $domain, 'contact-api', array(
			'methods'         => WP_REST_Server::CREATABLE,
			'callback'        => array( self::$instance, 'wprac_post_custom_newsletter_data' ),
			// 'permission_callback' => function(){
			// 	if( current_user_can( 'manage_options' ) ){
			// 		return true;
			// 	}
			// 	return new WP_Error(
			// 		'newsletter_unauthorized',
			// 		'You do not have permission to read this resource.',
			// 		array( 'status' => is_user_logged_in() ? 403 : 401 )
			// 	);
			// },
	    ));
		
		return $instance;
	}

	//====================================
	// Post Custom Newsletter Data
	//====================================
	public static function wprac_post_custom_newsletter_data(WP_REST_Request $request){
		$instance = self::getInstance();
		$table = self::$table;
		
		$token_newsletter = get_option('wprac-token-newsletter');

		$token = sanitize_text_field($request['token']);

		if(!empty($token) && $token === $token_newsletter){
			$email = sanitize_email($request['email']);
			if( empty($email) ){
				return new WP_REST_Response(array('status'=> 'error', 'message'=> 'Please enter your email'), 200);
			}else{
				// Thêm dữ liệu vào bảng
				$check_insert_db = self::$wpdb->insert(self::$table, array(
					'email' => $email,
					'time' => current_time('mysql')
				));

				if( $check_insert_db == 1){
					return new WP_REST_Response(array('status'=> 'success', 'message'=> 'subscribe newsletter success'), 200);
				}else{
					return new WP_REST_Response(array('status'=> 'error', 'message'=> 'This email already exists '), 200);
				}
			}
		}else{
			return new WP_Error(
				'Newsletter Unauthorized',
				'You do not have permission to post this newsletter.',
				array( 'status' => 401 )
			);
		}
		

		return $instance;
	}

	//====================================
	// Hàm đăng ký menu
	//====================================
	public static function wprac_add_newsletter_menu(){
		$instance = self::getInstance();

		$menuSlug = 'wprac-newsletter';
        $hook = add_menu_page('Newsletter', 'Newsletter', 'manage_options',
        $menuSlug,array($instance,'wprac_newsletter_page')
        , 'dashicons-warning', null);
        add_action( "load-$hook", array($instance, 'wprac_screen_option') );
        
        add_submenu_page($menuSlug, "All Subscribers", "All Subscribers", 'manage_options',
        $menuSlug,array($instance,'wprac_newsletter_page'));

		//====================================
        // Submenu Send Mail được thêm vào menu Newsletter
        //====================================
        add_submenu_page($menuSlug, "Send Mail", "Send Mail", 'manage_options',
        $menuSlug . '-send-mail',array($instance,'wprac_newsletter_send_mail_page'));
		//====================================
        // Submenu Configure API được thêm vào menu Newsletter
        //====================================
        add_submenu_page($menuSlug, "Configure API", "Configure API", 'manage_options',
        $menuSlug . '-config',array($instance,'wprac_newsletter_config_page'));

		return $instance;
	}

	//screen option
	public static function wprac_screen_option() {
		$instance = self::getInstance();
		$option = 'per_page';
		$args = array(
			'label'   => 'Show Limit Newsletter',
			'default' => 5,
			'option'  => 'newsletter_per_page'
		     );
		add_screen_option( $option, $args );
		$newsletter_obj = new WPRAC_Newsletter_List();
		return $instance;
	}

	//view newsletter page admin dashboard
	public static function wprac_newsletter_page(){
		$instance = self::getInstance();

		//update status newsletter = 1
		$sql = 'SELECT email FROM '.self::$table.' WHERE status = 0';
		$list_mails_status_0 = self::$wpdb->get_results( $sql, ARRAY_A );

		if($list_mails_status_0){
			// print_r($list_mails_status_0);
			// die();
			foreach ($list_mails_status_0 as $value) {
				self::$wpdb->update(self::$table, array('status' => 1), array('email' => $value['email']));
			}
		}
		
		//----------------------------
		$newsletter_obj = new WPRAC_Newsletter_List();
		
		require(WPRAC_PLUGIN_DIR . 'views/backend/newsletter/list-all-newsletters.php');

		return $instance;
	}
	// View send mail page
	public static function wprac_newsletter_send_mail_page(){
        $instance = self::getInstance();
		
        $table = self::$table;

        $sql = 'SELECT email FROM '.$table.'';
		$list_mails = self::$wpdb->get_results( $sql, ARRAY_A );

        //include view và xử lý send mail
		require(WPRAC_PLUGIN_DIR . 'views/backend/newsletter/send-mail.php');
		
		return $instance;
    }

    //====================================
	// Hàm xử lý ajax send mail
	//====================================
    public static function do_send_mail_newsletter() {
    	$instance = self::getInstance();
    	$table = self::$table;

    	$subject 			= $_POST["wpracSubject"];
		$message 			= $_POST["wpracMessage"];
		$check_sm_all		= $_POST["wpracCheck_sm_all"];
		$wprac_select_mail	= $_POST["wpracSelect_mail"];
		$retrieved_nonce 	= $_POST["wprac_send_mail_security"];

		if ( !wp_verify_nonce( $retrieved_nonce,'wprac_send_mail_action') ) {

		   	wp_send_json_success(array(
				'status'	=> false,
				'message'	=> 'Sorry, your nonce did not verify.'
			));	

		}
		// kiểm tra gửi mail đến tất cả
		if( !empty($check_sm_all) && $check_sm_all == 'yes' ){
			if( empty($subject) || empty($message) ){
				wp_send_json_success(array(
					'status'	=> false,
					'message'	=> 'Please enter the subject or message!'
				));
			}else{
				$sql = 'SELECT email FROM '.$table.' WHERE status = 1';
				$list_mails = self::$wpdb->get_results( $sql, ARRAY_A );

				//send mail
	   			$body		= $message;
	   			$headers 	= array('Content-Type: text/html; charset=UTF-8');
	   			if($list_mails){
		   		   	foreach ( $list_mails as $value ) {
			   			wp_mail( $value['email'], $subject, $body, $headers );
		   		   	}
	   		   	   	wp_send_json_success(array(
	   		   			'status'	=> true,
	   		   			'message'	=> 'Send to all emails successful'
	   		   		));
	   			}else{
	   				wp_send_json_success(array(
	   		   			'status'	=> false,
	   		   			'message'	=> 'No email in the list all contacts'
	   		   		));
	   			}
			}
		   	
		// kiểm tra gửi mail lựa chọn
		}else if( !empty($check_sm_all) && $check_sm_all == 'no' ){
			if( empty($subject) || empty($message) ){
				wp_send_json_success(array(
					'status'	=> false,
					'message'	=> 'Please enter the subject or message!'
				));
			}
			if( !empty($wprac_select_mail) ){
				//send mail
				$body		= $message;
	   			$headers 	= array('Content-Type: text/html; charset=UTF-8');
				foreach ( $wprac_select_mail as $value ) {
			   			wp_mail( $value, $subject, $body, $headers );
	   		   	}
			   	wp_send_json_success(array(
						'status'	=> true,
						'message'	=> 'Send to selected email successfully'
					));
			}else{
		   	   	wp_send_json_success(array(
		   			'status'	=> false,
		   			'message'	=> 'No email is selected'	
		   		));
			}
		   	
		}
    }

    //====================================
	// Hàm xử lý ajax generate token
	//====================================
    public static function do_generate_newsletter_token() {
    	$instance = self::getInstance();

		$retrieved_nonce 	= $_POST["wprac_generate_newsletter_token_security"];

		if ( !wp_verify_nonce( $retrieved_nonce,'wprac_generate_newsletter_token_action') ) {

		   	wp_send_json_success(array(
				'status'	=> false,
				'message'	=> 'Sorry, your nonce did not verify.'
			));	

		}

		/*************************************
		 *  Kiểm tra token newsletter option
		 *************************************/
		$token_newsletter_options = get_option('wprac-token-newsletter');
		if(!$token_newsletter_options){
			//tạo mới
			add_option('wprac-token-newsletter', md5(uniqid(rand(), true)) );
			wp_send_json_success(array(
	   			'status'	=> true,
	   			'message'	=> 'Generate Token Successful !!!',
	   			'token'		=> get_option('wprac-token-newsletter')
	   		));
		}else{
			//update
			update_option('wprac-token-newsletter', md5(uniqid(rand(), true)) );
			wp_send_json_success(array(
	   			'status'	=> true,
	   			'message'	=> 'Generate Token Successful !!!',
	   			'token'		=> get_option('wprac-token-newsletter')
	   		));
		}
    }

	//====================================
	// View config page
	//====================================
	public static function wprac_newsletter_config_page(){
        $instance = self::getInstance();
		
        $token = get_option('wprac-token-newsletter');

		require(WPRAC_PLUGIN_DIR . 'views/backend/newsletter/config-newsletter.php');
		
		return $instance;
    }


    //====================================
	// Notification bubble for newsletter menu
	//====================================
    public static function wprac_notification_count_in_newsletter_menu()
    {
    	$instance = self::getInstance();

        global $menu;
        
        $sql = 'SELECT COUNT(email) as count FROM '.self::$table.' WHERE status=0';
        
        $post_count = self::$wpdb->get_var($sql);
    
        if(!empty($post_count))
        {
            foreach ( (array)$menu as $key => $item )
            {
                if ( $item[2] == 'wprac-newsletter' )
                {
                    $menu[$key][0] = $menu[$key][0].' <span class="update-plugins count-'.$post_count.'"><span class="plugin-count" aria-hidden="true">'.$post_count.'</span><span class="screen-reader-text">'.$post_count.' notifications</span></span>';
                }
            }
        }
        
        return $instance;
    }
    
}

/*
 * CLASS WPRAC_Newsletter_List
 */
class WPRAC_Newsletter_List extends WP_List_Table {
	protected static $instance;
	public static $wpdb;
	protected static $table;

	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Newsletter List', 'tp-custom' ), //singular name of the listed records
			'plural'   => __( 'Newsletter List', 'tp-custom' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		] );
	}
	protected function __clone() {}

	public function wprac_get_newsletters( $per_page = 5, $page_number = 1 ){
		
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}wprac_custom_newsletter";
		if ( ! empty( $_REQUEST['orderby'] ) ) {
		    $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		    $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}else{
			$sql .= ' ORDER BY time desc';
		}
		$sql .= " LIMIT $per_page";

		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$results = $wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $results;
	}

	public static function wprac_delete_customer( $email ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}wprac_custom_newsletter",
			[ 'email' => $email ],
			[ '%s' ]
		);
	}

	public static function wprac_record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wprac_custom_newsletter";

		return $wpdb->get_var( $sql );
	}

	public function no_items() {
	 	_e( 'No newsletters avaliable.', 'tp-custom' );
	}

	function column_name( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'tp_delete_newsletter' );

		$title = '<strong>' . $item['email'] . '</strong>';

		$actions = [
		'delete' => sprintf( '<a href="?page=%s&action=%s&newsletter_item=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['email'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	
	function get_columns() {
	  $columns = array(
	    'cb'      => '<input type="checkbox" />', 
	    'email'    => __( 'Email', 'tp-custom' ),
	    'time'    => __( 'Date', 'tp-custom' )
	  );

	  return $columns;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'cb':
		    case 'email':
		    case 'time':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'email' => array( 'email', true ),
			'time' => array( 'time', false ),
		);

		return $sortable_columns;
	}

	public function get_bulk_actions() {
	  $actions = [
	    'bulk-delete' => 'Delete'
	  ];

	  return $actions;
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['email']
		);
	}

	public function prepare_items() {

	  $this->_column_headers = $this->get_column_info();
		// $columns = $this->get_columns();
		// $hidden = array();
		//$sortable = $this->get_sortable_columns();
		// $this->_column_headers = array($columns, $hidden, $sortable);

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'newsletter_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::wprac_record_count();

		$this->set_pagination_args( [
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = $this->wprac_get_newsletters( $per_page, $current_page );
	}


	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'tp_delete_newsletter' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$this->wprac_delete_customer( absint( $_GET['newsletter_item'] ) );

		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
		                wp_redirect( esc_url(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {


			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			if($delete_ids){
				foreach ( $delete_ids as $email ) {
					$this->wprac_delete_customer( $email );
				}
			}
			
			// wp_redirect( esc_url(add_query_arg()) );
			// exit;
		}
	}

}