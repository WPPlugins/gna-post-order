<?php
/*
Plugin Name: GNA Post Order
Version: 1.0.5
Plugin URI: http://wordpress.org/plugins/gna-google-analytics/
Author: Chris Dev
Author URI: http://webgna.com/
Description: Post order and custom post type objects (posts, any custom post types) using a drag and drop sortable javascript ajax user interface. 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: gna-post-order
*/

if(!defined('ABSPATH'))exit; //Exit if accessed directly

include_once('gna-post-order-core.php');

register_activation_hook(__FILE__, array('GNA_PostOrder', 'activate_handler'));		//activation hook
register_deactivation_hook(__FILE__, array('GNA_PostOrder', 'deactivate_handler'));	//deactivation hook
