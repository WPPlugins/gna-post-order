<?php
if (!class_exists('GNA_PostOrder_Settings_Menu')) {
	class GNA_PostOrder_Settings_Menu extends GNA_PostOrder_Admin_Menu {
		var $menu_page_slug = 'gna-settings-menu';
		
		/* Specify all the tabs of this menu in the following array */
		var $menu_tabs;

		var $menu_tabs_handler = array(
			'tab1' => 'render_tab1', 
			);

		public function __construct() {
			$this->render_menu_page();
		}

		public function set_menu_tabs() {
			$this->menu_tabs = array(
				'tab1' => __('General Settings', 'gna-post-order'),
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
			echo '<h2>'.__('Settings','gna-post-order').'</h2>';//Interface title
			$this->set_menu_tabs();
			$tab = $this->get_current_tab();
			$this->render_menu_tabs();
			?>
			<div id="poststuff"><div id="post-body">
			<?php 
				//$tab_keys = array_keys($this->menu_tabs);
				call_user_func(array(&$this, $this->menu_tabs_handler[$tab]));
			?>
			</div></div>
			</div><!-- end of wrap -->
			<?php
		}
			
		public function render_tab1() {
			global $g_postorder;
			if(isset($_POST['gna_save_postorder_settings'])) {
				$nonce = $_REQUEST['_wpnonce'];
				if(!wp_verify_nonce($nonce, 'n_gna-save-settings')) {
					die("Nonce check failed on save settings!");
				}

				$g_postorder->configs->set_value('g_will_show', isset($_POST["g_will_show"]) ? $_POST["g_will_show"] : '');
				$g_postorder->configs->save_config();
				$this->show_msg_settings_updated();
			}

			?>
			<div class="postbox">
				<h3 class="hndle"><label for="title"><?php _e('GNA Post Order', 'gna-post-order'); ?></label></h3>
				<div class="inside">
					<p><?php _e('Thank you for using our GNA Post Order plugin.', 'gna-post-order'); ?></p>
				</div>
			</div> <!-- end postbox-->
			
			<div class="postbox">
				<h3 class="hndle"><label for="title"><?php _e('Show / Hide ReOrder Interface', 'gna-post-order'); ?></label></h3>
				<div class="inside">
					<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
						<?php wp_nonce_field('n_gna-save-settings'); ?>
						<table class="form-table">
							<?php
								$args = array(
									'public'   => true
								);
								
								$output = 'objects'; // names or objects
								$post_types = get_post_types( $args, $output );
								foreach ( $post_types  as $post_type ) {
									if( $post_type->labels->name == 'Pages' ) {
										continue;
									}
							?>
							<tr valign="top">
								<th scope="row"><?php _e($post_type->labels->name, 'gna-post-order')?>:</th>
								<td>
									<input name="g_will_show['<?php echo $post_type->name; ?>']" type="checkbox" value="1" <?php echo (isset($g_postorder->configs->get_value('g_will_show')[$post_type->name]) && $g_postorder->configs->get_value('g_will_show')[$post_type->name] == '1') ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							<?php
								}
							?>
						</table>
						<input type="submit" name="gna_save_postorder_settings" value="<?php _e('Save Settings', 'gna-post-order')?>" class="button" />
					</form>
				</div>
			</div> <!-- end postbox-->
			<?php
		}
	} //end class
}