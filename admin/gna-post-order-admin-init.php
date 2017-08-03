<?php
/* 
 * Inits the admin dashboard side of things.
 * Main admin file which loads all settings panels and sets up admin menus. 
 */
if (!class_exists('GNA_PostOrder_Admin_Init')) {
	class GNA_PostOrder_Admin_Init {
		var $main_menu_page;
		//var $dashboard_menu;
		var $settings_menu;
		var $sorting_menu;
		
		public function __construct() {
			//This class is only initialized if is_admin() is true
			$this->admin_includes();
			
			add_action('admin_menu', array(&$this, 'create_admin_menus'));
			add_action('admin_menu', array(&$this, 'create_sub_menus_each_type'));
			
			//make sure we are on our plugin's menu pages
			if (isset($_GET['page']) && strpos($_GET['page'], GNA_POST_ORDER_MENU_SLUG_PREFIX ) !== false ) {
				add_action('admin_init', array(&$this, 'admin_menu_page_scripts'));
				add_action('admin_print_styles', array(&$this, 'admin_menu_page_styles'));
			}
		}
		
		public function admin_includes() {
			include_once('gna-post-order-admin-menu.php');
		}

		public function admin_menu_page_scripts() {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('postbox');
			wp_enqueue_script('dashboard');
			wp_enqueue_script('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('gna-post-order-admin-js', GNA_POST_ORDER_URL. '/assets/js/gna-post-order-admin-js.js');
		}
		
		function admin_menu_page_styles() {
			wp_enqueue_style('dashboard');
			wp_enqueue_style('thickbox');
			wp_enqueue_style('global');
			wp_enqueue_style('wp-admin');
			wp_enqueue_style('gna-post-order-admin-css', GNA_POST_ORDER_URL. '/assets/css/gna-post-order-admin-styles.css');
		}
		
		public function create_admin_menus() {
			$this->main_menu_page = add_menu_page( __('GNA Post Order', 'gna-post-order'), __('GNA Post Order', 'gna-post-order'), 'manage_options', 'gna-po-settings-menu', array(&$this, 'handle_settings_menu_rendering'), GNA_POST_ORDER_URL . '/assets/images/gna_20x20.png' );
			
			add_submenu_page('gna-po-settings-menu', __('Settings', 'gna-post-order'),  __('Settings', 'gna-post-order'), 'manage_options', 'gna-po-settings-menu', array(&$this, 'handle_settings_menu_rendering'));
			
			add_action( 'admin_init', array(&$this, 'register_gna_post_order_settings') );
		}

		public function create_sub_menus_each_type() {
			$post_types = get_post_types();
			
			global $g_postorder;
			$capability = $g_postorder->configs->get_value('g_po_min_cap');
			if(isset($capability) && !empty($capability)) {
			} else {
				$capability = 'manage_options';  
			}

			foreach($post_types as $post_type) {
				if($post_type == 'reply' || $post_type == 'topic')
					continue;

				if(is_post_type_hierarchical($post_type))
					continue;

				$post_type_data = get_post_type_object( $post_type );
				if($post_type_data->show_ui === FALSE)
					continue;

				//if(isset($options['show_reorder_interfaces'][$post_type]) && $options['show_reorder_interfaces'][$post_type] != 'show')
				//	continue;

				if($post_type == 'post') {
					add_submenu_page('edit.php', __('ReOrder', 'gna-post-order'), __('ReOrder', 'gna-post-order'), $capability, 'gna-post-order-'.$post_type, array(&$this, 'handle_sortpage_menu_rendering') );
				} else if ($post_type == 'attachment') {
					add_submenu_page('upload.php', __('ReOrder', 'gna-post-order'), __('ReOrder', 'gna-post-order'), $capability, 'gna-post-order-'.$post_type, array(&$this, 'handle_sortpage_menu_rendering') );
				} else {
					add_submenu_page('edit.php?post_type='.$post_type, __('ReOrder', 'gna-post-order'), __('ReOrder', 'gna-post-order'), $capability, 'gna-post-order-'.$post_type, array(&$this, 'handle_sortpage_menu_rendering') );
				}
			}
		}

		public function register_gna_post_order_settings() {
			register_setting( 'gna-post-order-setting-group', 'g_post_order_configs' );
		}

		public function handle_settings_menu_rendering() {
			include_once('gna-post-order-admin-settings-menu.php');
			$this->settings_menu = new GNA_PostOrder_Settings_Menu();
		}
		
		public function handle_sortpage_menu_rendering() {
			include_once('gna-post-order-admin-sortpage-menu.php');
			$this->sorting_menu = new GNA_PostOrder_Sortpage_Menu();
		}
	}
}
