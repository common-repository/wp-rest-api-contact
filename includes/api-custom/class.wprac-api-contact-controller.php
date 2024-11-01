<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class WPRAC_Api_Contact_Controller extends WP_REST_Controller{
	protected static $instance;
	protected static $wpdb;
	protected static $table;
	protected static $option;

	// contacts WP_List_Table object
	public $contacts_obj;
	
	protected function __construct() {}
	protected function __clone() {}

	public static function getInstance(){
		if(self::$instance === null){
			self::$instance = new WPRAC_Api_Contact_Controller();
		}
		return self::$instance;
	}

	public static function run(){
		$instance = self::getInstance();

		// Setup
		global $wpdb;
		self::$wpdb = $wpdb;
		
		// Table name
		self::$table = self::$wpdb->prefix . 'wprac_custom_contact';

		// Đăng ký route api
		add_action( 'rest_api_init', function(){
			self::wprac_register_routes();
		});

		// Đăng ký Screen Option
		add_filter('set-screen-option', array($instance, 'wprac_table_set_option'), 10, 3);
		// Đăng ký Contact Menu
		add_action('admin_menu', array($instance, 'wprac_add_contact_menu'));

		
		// Đăng ký fields cho trang contact config
		add_action( 'admin_init', array( $instance, 'wprac_setup_contact_options' ) );

		// Notification count in Newsletter Admin Menu.
		add_action('admin_menu', array($instance, 'wprac_notification_count_in_contact_menu'));

		// Đăng ký ajax
		add_action('wp_ajax_do_generate_contact_token', array($instance, 'do_generate_contact_token'));
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
			id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			full_name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(25) NOT NULL,
			city varchar(255) NOT NULL,
			content text NOT NULL,
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
			'full_name' => 'Thien Pham',
			'email' 	=> 'chithien175@gmail.com',
			'phone'		=> '0905160320',
			'city'		=> 'Nha Trang',
			'content' 	=> 'test contact table',
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

		// Đăng ký route get list all contacts trong bảng wp_wprac_custom_contact
		// register_rest_route( $domain, 'contact-api/(?P<id>[\d]+)', array(
		register_rest_route( $domain, 'contact-api', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( self::$instance, 'wprac_get_custom_contact_data' ),
			'permission_callback' => function(){
				if( current_user_can( 'manage_options' ) ){
					return true;
				}
				return new WP_Error(
					'contact_unauthorized',
					'You do not have permission to read this resource.',
					array( 'status' => is_user_logged_in() ? 403 : 401 )
				);
			},
	    ));

	    // Đăng ký route POST contact data
		register_rest_route( $domain, 'contact-api', array(
		// register_rest_route( $domain, 'contact-api', array(
			'methods'         => WP_REST_Server::CREATABLE,
			'callback'        => array( self::$instance, 'wprac_post_custom_contact_data' ),
			// 'permission_callback' => function(){
			// 	if( current_user_can( 'manage_options' ) ){
			// 		return true;
			// 	}
			// 	return new WP_Error(
			// 		'contact_unauthorized',
			// 		'You do not have permission to read this resource.',
			// 		array( 'status' => is_user_logged_in() ? 403 : 401 )
			// 	);
			// },
	    ));
		
		return $instance;
	}

	//====================================
	// Get Custom Contact Data
	//====================================
	public static function wprac_get_custom_contact_data($data){
		$instance = self::getInstance();
		$table = self::$table;

		$sql = "SELECT * FROM ".$table;
		$results = self::$wpdb->get_results($sql);

		if($results){
			return new WP_REST_Response($results, 200);
		}else{
			return new WP_Error( 'awesome_no_contact', 'Invalid contact', array( 'status' => 404 ) );
		}

		return $instance;
	}

	//====================================
	// Post Custom Contact Data
	//====================================
	public static function wprac_post_custom_contact_data(WP_REST_Request $request){
		$instance = self::getInstance();
		$table = self::$table;
		
		$token_contact = get_option('wprac-token-contact');
		$token = sanitize_text_field($request['token']);

		$full_name = sanitize_text_field($request['full_name']);
		$email = sanitize_email($request['email']);
		$phone = sanitize_text_field($request['phone']);
		$city = sanitize_text_field($request['city']);
		$content = sanitize_text_field($request['content']);

		if(!empty($token) && $token === $token_contact){
			if( empty($full_name) ){
				return new WP_REST_Response(array('status'=> 'error', 'message'=> 'Please enter your full_name'), 200);
			}else if( empty($email) ){
				return new WP_REST_Response(array('status'=> 'error', 'message'=> 'Please enter your email'), 200);
			}else if( empty($phone) ){
				return new WP_REST_Response(array('status'=> 'error', 'message'=> 'Please enter your phone'), 200);
			}else if( empty($city) ){
				return new WP_REST_Response(array('status'=> 'error', 'message'=> 'Please enter your city'), 200);
			}else if( empty($content) ){
				return new WP_REST_Response(array('status'=> 'error', 'message'=> 'Please enter your content'), 200);
			}else{
				// Thêm dữ liệu vào bảng
				$check_insert_db = self::$wpdb->insert(self::$table, array(
					'full_name' => $full_name,
					'email' => $email,
					'phone'	=> $phone,
					'city'	=> $city,
					'content' => $content,
					'time' => current_time('mysql')
				));

				//get option
				self::$option = get_option('contact-config-custom');
				//send mail
				if(self::$option && $check_insert_db == 1){
					$to 		= get_option( 'admin_email' );
					$subject 	= 'The contact from '.$full_name;
					$body		= 'Full name:'.$full_name.'<br>Email:'.$email.'<br>Phone:'.$phone.'<br>City:'.$city.'<br>Content:'.$content;
					$headers = array('Content-Type: text/html; charset=UTF-8');
					wp_mail( $to, $subject, $body, $headers );
				}

				return new WP_REST_Response(array('status'=> 'success', 'message'=> 'send contact success'), 200);
			}
		}else{
			return new WP_Error(
				'Contact Unauthorized',
				'You do not have permission to post this contact.',
				array( 'status' => 401 )
			);
		}

		return $instance;
	}

	public static function wprac_add_contact_menu(){
		$instance = self::getInstance();

		$menuSlug = 'tp-contact';
        $hook = add_menu_page('Contacts', 'Contacts', 'manage_options',
        $menuSlug,array($instance,'wprac_contact_page')
        , 'dashicons-email-alt', null);
        add_action( "load-$hook", array($instance, 'wprac_screen_option') );
        
        add_submenu_page($menuSlug, "All Submissions", "All Submissions", 'manage_options',
        $menuSlug,array($instance,'wprac_contact_page'));

		//====================================
        // Submenu được thêm vào menu Contact
        //====================================
        add_submenu_page($menuSlug, "Configure API", "Configure API", 'manage_options',
        $menuSlug . '-config',array($instance,'wprac_config_page'));

		return $instance;
	}

	public static function wprac_screen_option() {
		$instance = self::getInstance();
		$option = 'per_page';
		$args = array(
			'label'   => 'Show Limit Contacts',
			'default' => 5,
			'option'  => 'contact_per_page'
		     );
		add_screen_option( $option, $args );
		$contacts_obj = new WPRAC_Contact_List();
		return $instance;
	}

	//view contact page admin dashboard
	public static function wprac_contact_page(){
		$instance = self::getInstance();

		//update status newsletter = 1
		$sql = 'SELECT id FROM '.self::$table.' WHERE status = 0';
		$list_mails_status_0 = self::$wpdb->get_results( $sql, ARRAY_A );

		if($list_mails_status_0){
			// print_r($list_mails_status_0);
			// die();
			foreach ($list_mails_status_0 as $value) {
				self::$wpdb->update(self::$table, array('status' => 1), array('id' => $value['id']));
			}
		}
		
		//----------------------------

		$contacts_obj = new WPRAC_Contact_List();
		
		require(WPRAC_PLUGIN_DIR . 'views/backend/contact/list-all-contacts.php');

		return $instance;
	}
	//view config page
	public static function wprac_config_page(){
        $instance = self::getInstance();

        $token = get_option('wprac-token-contact');
		
		require(WPRAC_PLUGIN_DIR . 'views/backend/contact/config-contacts.php');
		
		return $instance;
    }
    public static function wprac_setup_contact_options(){
    	$instance = self::getInstance();

    	register_setting('tp_contact_options_group', 'contact-config-custom', array(self::$instance, 'wprac_save_data'));

    	add_settings_section('tp_contact_options_section', '', function(){
    		echo '';
    	}, 'tp-contact-config');

    	//get option
		self::$option = get_option('contact-config-custom');
    	//label
    	$label_cb = 'The new contact will be send to Email Address ('.get_option( 'admin_email' ).')';
    	
    	add_settings_field('contact-config-custom', $label_cb, function() use ($option) {
    		if(self::$option) { $checked = 'checked'; }
    		echo '<input name="contact-config-custom" type="checkbox" '.$checked.'>';
    	}, 'tp-contact-config', 'tp_contact_options_section');
    }
    public static function wprac_save_data($input){
    	echo '<pre>';
    	print_r($input);
    	echo '</pre>';
    	return $input;
    }

    //====================================
	// Hàm xử lý ajax generate token
	//====================================
    public static function do_generate_contact_token() {
    	$instance = self::getInstance();

		$retrieved_nonce 	= $_POST["wprac_generate_contact_token_security"];

		if ( !wp_verify_nonce( $retrieved_nonce,'wprac_generate_contact_token_action') ) {

		   	wp_send_json_success(array(
				'status'	=> false,
				'message'	=> 'Sorry, your nonce did not verify.'
			));	

		}

		/*************************************
		 *  Kiểm tra token contact option
		 *************************************/
		$token_contact_options = get_option('wprac-token-contact');
		if(!$token_contact_options){
			//tạo mới
			add_option('wprac-token-contact', md5(uniqid(rand(), true)) );
			wp_send_json_success(array(
	   			'status'	=> true,
	   			'message'	=> 'Generate Token Successful !!!',
	   			'token'		=> get_option('wprac-token-contact')
	   		));
		}else{
			//update
			update_option('wprac-token-contact', md5(uniqid(rand(), true)) );
			wp_send_json_success(array(
	   			'status'	=> true,
	   			'message'	=> 'Generate Token Successful !!!',
	   			'token'		=> get_option('wprac-token-contact')
	   		));
		}
    }

    //====================================
	// Notification bubble for newsletter menu
	//====================================
    public static function wprac_notification_count_in_contact_menu()
    {
    	$instance = self::getInstance();

        global $menu;
        
        $sql = 'SELECT COUNT(id) as count FROM '.self::$table.' WHERE status=0';
        
        $post_count = self::$wpdb->get_var($sql);
    
        if(!empty($post_count))
        {
            foreach ( (array)$menu as $key => $item )
            {
                if ( $item[2] == 'tp-contact' )
                {
                    $menu[$key][0] = $menu[$key][0].' <span class="update-plugins count-'.$post_count.'"><span class="plugin-count" aria-hidden="true">'.$post_count.'</span><span class="screen-reader-text">'.$post_count.' notifications</span></span>';
                }
            }
        }
        
        return $instance;
    }
	
}

/*
 * CLASS WPRAC_Contact_List
 */

class WPRAC_Contact_List extends WP_List_Table {
	protected static $instance;
	public static $wpdb;
	protected static $table;

	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Contacts List', 'tp-custom' ), //singular name of the listed records
			'plural'   => __( 'Contacts List', 'tp-custom' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		] );
	}
	protected function __clone() {}

	public function wprac_get_contacts( $per_page = 5, $page_number = 1 ){
		
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}wprac_custom_contact";
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

	public static function wprac_delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}wprac_custom_contact",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	public static function wprac_record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wprac_custom_contact";

		return $wpdb->get_var( $sql );
	}

	public function no_items() {
	 	_e( 'No contacts avaliable.', 'tp-custom' );
	}

	function column_name( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'tp_delete_contact' );

		$title = '<strong>' . $item['full_name'] . '</strong>';

		$actions = [
		'delete' => sprintf( '<a href="?page=%s&action=%s&contact_item=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	
	function get_columns() {
	  $columns = array(
	    'cb'      => '<input type="checkbox" />',
	    'full_name'    => __( 'Name', 'tp-custom' ),
	    'email'    => __( 'Email', 'tp-custom' ),
	    'phone' => __( 'Phone', 'tp-custom' ),
	    'city'    => __( 'City', 'tp-custom' ),
	    'content'    => __( 'Content', 'tp-custom' ),
	    'time'    => __( 'Date', 'tp-custom' )
	  );

	  return $columns;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'cb':
			case 'full_name':
		    case 'email':
		    case 'phone':
		    case 'city':
		    case 'content':
		    case 'time':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'full_name' => array( 'full_name', true ),
			'city' => array( 'city', false ),
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
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
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

		$per_page     = $this->get_items_per_page( 'contact_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::wprac_record_count();

		$this->set_pagination_args( [
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = $this->wprac_get_contacts( $per_page, $current_page );
	}


	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'tp_delete_contact' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				$this->wprac_delete_customer( absint( $_GET['contact_item'] ) );

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
				foreach ( $delete_ids as $id ) {
					$this->wprac_delete_customer( $id );
				}
			}
			
			// wp_redirect( esc_url(add_query_arg()) );
			// exit;
		}
	}

}