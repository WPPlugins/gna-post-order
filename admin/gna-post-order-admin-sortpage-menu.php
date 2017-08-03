<?php
if (!class_exists('GNA_PostOrder_Sortpage_Menu')) {
	class GNA_PostOrder_Sortpage_Menu extends GNA_PostOrder_Admin_Menu {
		var $menu_page_slug = 'gna-sortpage-menu';
		
		/* Specify all the tabs of this menu in the following array */
		var $menu_tabs;
		
		var $post_type_of_this;

		var $menu_tabs_handler = array(
			'tab1' => 'render_tab1', 
			);

		public function __construct() {
			include_once('gna-post-order-worker.php');
			
			if(isset($_GET['page']) && strpos($_GET['page'], GNA_POST_ORDER_MENU_SLUG_PREFIX.'-') == 0) {
				$this->post_type_of_this = str_replace(GNA_POST_ORDER_MENU_SLUG_PREFIX.'-', '', $_GET['page']);
			}
			
			$this->render_menu_page();
			
			//add_action( 'wp_ajax_update_gna_post_order', array(&$this, 'updateAjaxGnaPostOrder') );
		}

		function updateAjaxGnaPostOrder() {
			global $wpdb;

			parse_str($_POST['order'], $received_data);

			if (is_array($received_data)) {
				foreach($received_data as $key => $values) {
					if($key == 'item') {
						foreach($values as $position => $id) {
							$data = array('menu_order' => $position);
							//$data = apply_filters('gna-post-order_save-ajax-order', $data, $key, $id);

							$wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
						}
					} else {
						foreach($values as $position => $id) {
							$data = array('menu_order' => $position, 'post_parent' => str_replace('item_', '', $key));
							//$data = apply_filters('gna-post-order_save-ajax-order', $data, $key, $id);

							$wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
						}
					}
				}
			}

			wp_die();
		}

		public function set_menu_tabs() {
			$this->menu_tabs = array(
				'tab1' => __(ucwords(strtolower($this->post_type_of_this)).' Sorting', 'gna-post-order'),
			);
		}

		public function get_current_tab() {
			$tab_keys = array_keys($this->menu_tabs);
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $tab_keys[0];
			return $tab;
		}

		/*
		 * Renders our tabs of this menu as nav items
		 */
		public function render_menu_tabs() {
			$current_tab = $this->get_current_tab();

			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->menu_tabs as $tab_key => $tab_caption ) 
			{
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->menu_page_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
			}
			echo '</h2>';
		}
		
		/*
		 * The menu rendering goes here
		 */
		public function render_menu_page() {
			echo '<div class="wrap">';
			echo '<h2>'.__('Sorting','gna-post-order').'</h2>';//Interface title
			$this->set_menu_tabs();
			$tab = $this->get_current_tab();
			$this->render_menu_tabs();
			?>
			<div id="poststuff"><div id="post-body">
			<?php
				call_user_func(array(&$this, $this->menu_tabs_handler[$tab]));
			?>
			</div></div>
			</div><!-- end of wrap -->
			<?php
		}

		public function render_tab1() {
			global $g_postorder;

			if(isset($_POST['gna_save_post_order'])) {
				$nonce = $_REQUEST['_wpnonce'];
				if(!wp_verify_nonce($nonce, 'n_gna_postorder-save-settings')) {
					die("Nonce check failed on save settings!");
				}

				global $wpdb;

				parse_str($_POST['order'], $received_data);

				if (is_array($received_data)) {
					foreach($received_data as $key => $values) {
						if($key == 'item') {
							foreach($values as $position => $id) {
								$data = array('menu_order' => $position);

								$wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
							}
						} else {
							foreach($values as $position => $id) {
								$data = array('menu_order' => $position, 'post_parent' => str_replace('item_', '', $key));

								$wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
							}
						}
					}
					$this->show_msg_settings_updated();
				}
			}
		?>
			<div class="postbox">
				<h3 class="hndle"><label for="title"><?php _e('GNA Post Order', 'gna-post-order'); ?></label></h3>
				<div class="inside">
					<p><?php _e('Thank you for using our GNA Post Order plugin.', 'gna-post-order'); ?></p>
				</div>
			</div> <!-- end postbox-->
			
			<div id="ajax-response"></div>

			<div class="postbox">
				<h3 class="hndle"><label for="title"><?php _e('Content List', 'gna-post-order'); ?></label></h3>
				<div class="inside">
					<form id="frm_order" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
						<?php wp_nonce_field('n_gna_postorder-save-settings'); ?>
						
						<div id="gna_post_order">
							<ul id="gna_sortable">
								<?php $this->listPages('hide_empty=0&title_li=&post_type='.$this->post_type_of_this); ?>
							</ul>
						</div>

						<input type="hidden" id="order" name="order" value="" />
						<input type="submit" id="gna_save_post_order" name="gna_save_post_order" class="button" value="<?php _e('Save Settings', 'gna-post-order')?>" />
					</form>
				</div>
			</div> <!-- end postbox-->
			<?php
		}
		
		public function listPages($args = '') {
			$defaults = array(
				'depth'             => -1, 
				'show_date'         => '',
				'date_format'       => get_option('date_format'),
				'child_of'          => 0, 
				'exclude'           => '',
				'title_li'          => __('Pages'), 
				'echo'              => 1,
				'authors'           => '', 
				'sort_column'       => 'menu_order',
				'link_before'       =>  '<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>',
				'link_after'        => '', 
				'walker'            => '',
				'post_status'       =>  'any' 
			);

			$r = wp_parse_args( $args, $defaults );
			extract( $r, EXTR_SKIP );

			$output = '';

			$r['exclude'] = preg_replace('/[^0-9,]/', '', $r['exclude']);
			$exclude_array = ( $r['exclude'] ) ? explode(',', $r['exclude']) : array();
			$r['exclude'] = implode( ',', apply_filters('wp_list_pages_excludes', $exclude_array) );

			// Query pages.
			$r['hierarchical'] = 0;
			$args = array(
				'sort_column'       =>  'menu_order',
				'post_type'         =>  $post_type,
				'posts_per_page'    => -1,
				'post_status'       =>  'any',
				'orderby'            => array(
					'menu_order'    => 'ASC',
					'post_date'     =>  'DESC'
				)
			);

			$the_query = new WP_Query($args);
			$pages = $the_query->posts;

			if ( !empty($pages) ) {
				$output .= $this->walkTree($pages, $r['depth'], $r);
			}

			$output = apply_filters('wp_list_pages', $output, $r);

			if ( $r['echo'] ) {
				echo $output;
			} else {
				return $output;
			}
		}

		function walkTree($pages, $depth, $r) {
			if ( empty($r['walker']) ) {
				$walker = new GNA_PostOrder_Worker();
			} else {
				$walker = $r['walker'];
			}

			$args = array($pages, $depth, $r);
			return call_user_func_array(array(&$walker, 'walk'), $args);
		}
	} //end class
}