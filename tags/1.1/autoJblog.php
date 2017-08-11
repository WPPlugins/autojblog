<?php
/**
 * @package AutoJblog
 */
/*
Plugin Name: AutoJblog
Plugin URI: http://gregoire-penverne.fr/1824-jbloguez-automatiquement-a-partir-de-votre-wordpress
Description: Permet l'auto-publication de vos articles sur votre Jblog Jcray
Version: 1.1
Author: Gregoire Penverne
Author URI: http://gregoire-penverne.fr/
License: GPLv2 or later
*/
	set_time_limit(10);
	define('autojblog_path', ABSPATH."wp-content/plugins/autoJblog/");
	include autojblog_path."autoJblog.class.php";
	add_action('admin_menu', 'create_menu');  
	function create_menu(){
		add_options_page("autoJblog", "AutoJblog", 1, "autoJblog", "admin");
	}
	function admin(){
		include autojblog_path."admin.php";
		return false;
	}

  



?>