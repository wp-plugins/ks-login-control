<?php 
/* 
Plugin Name: KS Login Control
Plugin URI: http://karunshakya.com.np
Description: A plugin to handle simple login activities.
Version: 1.1
Author: Karun Shakya
Author URI: http://karunshakya.com.np

Copyright 2014  Karun Shakya  (email : karunshakya@live.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class KsLoginControl{
	/**
	* The Constructor
	*/
	private $content;
	
	public function __construct(){
		session_start();
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('login_head', array(&$this, 'client_init'));
		add_action('admin_menu', array(&$this, 'add_menu'));
		//add_action('wp_login', array(&$this, 'clear_session'));
		add_filter('login_errors', array(&$this, 'login_error_message'));		
	}
	
	public function login_error_message($error){
		$this->content = get_option('ksLoginControl');
		
		$this->failed_login_trace();
		
		if(isset($this->content) && $this->content['login_msg'] != ''){
			$error = $this->content['login_msg'];
		}
		if( isset($_SESSION['login_fail'])){
			$error .="<br><p>Login Attempt ".$_SESSION['login_fail'];
		}
			return $error;
		
	}
	function clear_session(){
		unset($_SESSION['login_fail']);
	}
	private function failed_login_trace(){
		if( !isset($_COOKIE["LoginAccess"])){
			$this->content = get_option('ksLoginControl');
			if(isset($this->content['login_limit']) && $this->content['login_limit'] > 0){
				$login_limit = $this->content['login_limit'];
			}else{
				$login_limit = 3;
			}
			if(!isset($_SESSION['login_fail'])){
				$_SESSION['login_fail'] = 1;
			}else{
				$_SESSION['login_fail'] += 1;
			}
			if( $_SESSION['login_fail'] >= $login_limit-1 ){
				$this->disable_login();
			}
		}
	}
	
	private function disable_login(){
		$this->content = get_option('ksLoginControl');
		if(isset($this->content['disable_time']) && $this->content['disable_time'] > 0){
			$disable_time = $this->content['disable_time'];
		}else{
			$disable_time = 120;
		}
		echo $disable_time;
		$value = 0;

		setcookie("LoginAccess", $value, time()+$disable_time);  /* expire in 2 min */
		
		$this->inform_admin();
		
		add_action('wp_login', array(&$this, 'clear_session'));
	}
	
	private function inform_admin(){
		$admin_email = get_option ( 'admin_email' );
		
		$multiple_to_recipients = array(
			$admin_email
		);

		add_filter( 'wp_mail_content_type', array(&$this, 'set_html_content_type') );
		
		$message = '<h1>Multiple Invalid Login Attempts</h1>
			<p>We Detected multiple invalid login attempts to your site '.get_bloginfo('url').' from IP: '.$_SERVER['REMOTE_ADDR'].'</p>
			<p>Please be aware</p>
			<br>
			<p>Regards<br>
			KS Login Control</p>
		';

		wp_mail( $multiple_to_recipients, 'Multiple Invalid Login Attempts', $message );

		// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
		remove_filter( 'wp_mail_content_type', array(&$this, 'set_html_content_type') );
		
	}
	public function set_html_content_type() {

		return 'text/html';
	}
	/**
	* Activate Plugin
	*/
	public static function activate(){
	
	}
	/**
	* Plugin Deactivate
	*/
	public static function deactivate(){
	
	}
	/**
	 * hook into WP's admin_init action hook
	 */
	public function admin_init(){
		// Set up the settings for this plugin
		$this->init_settings();
		// Possibly do additional admin_init tasks
	}
	/**
	 * Initialize some custom settings
	 */     
	public function init_settings(){
		// register the settings for this plugin
		register_setting('ks_login_controller-group', 'ksLoginControl');
	}
	/**
	 * add a menu
	 */     
	public function add_menu(){
		add_options_page('KS Login Control', 'Login Control', 'manage_options', 'ks_login_controller', array(&$this, 'ks_login_control'));
	}
	
	function client_init(){
	if( isset($_COOKIE["LoginAccess"]) && $_COOKIE["LoginAccess"] == 0){
		?>
			<script>
				jQuery(document).ready(function($){
					$('#loginform').html("<h2>Login Disabled.</h2>");
				});
			</script>
		<?php
		}
	}
	/**
	 * Menu Callback
	 */     
	public function ks_login_control(){
		if(!current_user_can('manage_options')){
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		// Render the settings template
		sprintf("%s/templates/settings.php", dirname(__FILE__));
		include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
	}
	
	function __destruct() {
		//session_destroy();
	}
}

if(class_exists('KsLoginControl')){
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('KsLoginControl', 'activate'));
    register_deactivation_hook(__FILE__, array('KsLoginControl', 'deactivate'));

    // instantiate the plugin class
    $KsLoginControl = new KsLoginControl();
}