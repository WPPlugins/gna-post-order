<?php
if (!class_exists('GNA_PostOrder')) {

	class GNA_PostOrder {

		var $plugin_url;
		var $admin_init;
		var $configs;
		
		public function init() {
			$class = __CLASS__;
			new $class;
		}
		
		public function __construct() {
			$this->load_configs();
			$this->define_constants();
			$this->define_variables();
			$this->includes();
			$this->loads();

			add_action('init', array(&$this, 'plugin_init'), 0);
			add_filter('plugin_row_meta', array(&$this, 'filter_plugin_meta'), 10, 2);
			add_filter('pre_get_posts', array(&$this, 'gna_pre_get_posts'));
			add_filter('posts_orderby', array(&$this, 'gna_posts_orderby'), 111, 2);
		}
		
		public function load_configs() {
			include_once('inc/gna-post-order-config.php');
			$this->configs = GNA_PostOrder_Config::get_instance();
		}
		
		public function define_constants() {
			define('GNA_POST_ORDER_VERSION', '1.0.5');
			
			define('GNA_POST_ORDER_BASENAME', plugin_basename(__FILE__));
			define('GNA_POST_ORDER_URL', $this->plugin_url());
			define('GNA_POST_ORDER_MENU_SLUG_PREFIX', 'gna-post-order');
		}
		
		public function define_variables() {
			
		}
			
		public function includes() {
			if(is_admin()) {
				include_once('admin/gna-post-order-admin-init.php');
			}
			
			add_action('wp_footer', array(&$this, 'add_script_front'));
		}
			
		public function loads() {
			if(is_admin()){
				$this->admin_init = new GNA_PostOrder_Admin_Init();
			}
		}

		public function plugin_init() {
			load_plugin_textdomain('gna-post-order', false, dirname(plugin_basename(__FILE__ )) . '/languages/');
		}

		public function plugin_url() { 
			if ($this->plugin_url) return $this->plugin_url;
			return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		}

		public function filter_plugin_meta($links, $file) {
			if( strpos( GNA_POST_ORDER_BASENAME, str_replace('.php', '', $file) ) !== false ) { /* After other links */
				$links[] = '<a target="_blank" href="https://profiles.wordpress.org/chris_dev/" rel="external">' . __('Developer\'s Profile', 'gna-post-order') . '</a>';
			}
			
			return $links;
		}
		
		public function install() {
		}
		
		public function uninstall() {
		}
		
		public function activate_handler() {
		}
		
		public function deactivate_handler() {
		}
		
		public function add_script_front() {
		}
		
		function gna_pre_get_posts($query) {
            global $post;
			
            if(is_object($post) && isset($post->ID) && $post->ID < 1) {
				return $query;
			}
            
			if (is_admin()) {
                return $query;
			}
            
            if (isset($query->query_vars['ignore_custom_sort']) && $query->query_vars['ignore_custom_sort'] === TRUE) {
                return $query;
			}
            
            if(isset($query->query_vars)    &&  isset($query->query_vars['post_type'])   && $query->query_vars['post_type'] ==  "nav_menu_item") {
                return $query;
			}
                
            if(true) {
				if(isset($query->query['suppress_filters'])) {
					$query->query['suppress_filters'] = FALSE;
				}
				
				if(isset($query->query_vars['suppress_filters'])) {
					$query->query_vars['suppress_filters'] = FALSE;
				}
			}
                
            return $query;
        }
		
		public function gna_posts_orderby($orderBy, $query) {
            global $wpdb;
            
            if(isset($query->query_vars['ignore_custom_sort']) && $query->query_vars['ignore_custom_sort'] === TRUE) {
                return $orderBy;
			}
			
            if(isset($query->query_vars['post_type']) && ((is_array($query->query_vars['post_type']) && in_array("reply", $query->query_vars['post_type'])) || ($query->query_vars['post_type'] == "reply"))) {
                return $orderBy;
			}
			
            if(isset($query->query_vars['post_type']) && ((is_array($query->query_vars['post_type']) && in_array("topic", $query->query_vars['post_type'])) || ($query->query_vars['post_type'] == "topic"))) {
                return $orderBy;
			}
                
            if(isset($_GET['orderby']) && $_GET['orderby'] !=  'menu_order') {
                return $orderBy;
			}
            
            if(is_admin()) {
				if(true || (defined('DOING_AJAX') && isset($_REQUEST['action']) && $_REQUEST['action'] == 'query-attachments')) {
					global $post;
						
					if(is_object($post) && $post->post_type == "acf-field-group" || (defined('DOING_AJAX') && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'acf/') < 1)) {
						return $orderBy;
					}
							
					$orderBy = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";
				} else {
					return $orderBy;
				}
			} else {
				if($query->is_search()) {
					return($orderBy);
				}
				
				if(true) {
					if(trim($orderBy) == '') {
						$orderBy = "{$wpdb->posts}.menu_order ";
					} else {
						$orderBy = "{$wpdb->posts}.menu_order, " . $orderBy;
					}
				}
			}

            return($orderBy);
        }
	}
}
$GLOBALS['g_postorder'] = new GNA_PostOrder();
