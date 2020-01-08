<?php
/*
Plugin Name: WooCommerce Menu Extension
Plugin URI: http://www.augustinfotech.com
Description: You can now add woocommerce links in your WP menus.
Version: 1.6.2
Text Domain: woocommerce-menu-extension
Author: August Infotech
Author URI: http://www.augustinfotech.com
*/

define( 'AIWOO_VERSION', '1.6.2' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('AI_WOO_PATH', plugin_dir_path( __FILE__ ));

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) { 
		add_action( 'plugins_loaded', 'aiwoo_plugin_include', 10, 1);
	
		// edit menu walker
		add_filter( 'wp_edit_nav_menu_walker', 'aiwoo_edit_walker', 10, 2 );
		
		// save menu custom fields
		add_action( 'wp_update_nav_menu_item', 'aiwoo_update_custom_nav_fields', 10, 3 );
		
		add_filter( 'wp_setup_nav_menu_item', 'aiwoo_nav_menu_type_label' );
		if(!is_admin()){
			add_filter( 'wp_get_nav_menu_items', 'aiwoo_exclude_menu_items');		
		}		
		
} else {
	add_action('admin_notices', 'aiwoo_plugin_admin_notices');
}

function aiwoo_plugin_include(){
	$filename  = "include/";
	$filename .= is_admin() ? "backend.inc.php" : "frontend.inc.php";
	if( file_exists( plugin_dir_path( __FILE__ ) . $filename ) )
		include( plugin_dir_path( __FILE__ ) . $filename );
}

function aiwoo_plugin_admin_notices() 
{
	   $msg = sprintf( __( 'Please install or activate : %s.', $_SERVER['SERVER_NAME'] ), '<a href=https://wordpress.org/plugins/woocommerce style="color: #ffffff;text-decoration:none;font-style: italic;" target="_blank"/><strong>WooCommerce - excelling eCommerce</strong></a>' );
	   
	   echo '<div id="message" class="error" style="background-color: #DD3D36;"><p style="font-size: 16px;color: #ffffff">' . $msg . '</p></div>';   
	   
	   deactivate_plugins('woocommerce-menu-extension/woocommerce-menu-extension.php');
}
include_once( 'include/edit_custom_walker.php' );

function aiwoo_nav_menu_type_label( $menu_item )
{	
	$elems = array( '#aiwooshop#', '#aiwoocart#', '#aiwoobasket#', '#aiwoologin#', '#aiwoologout#', '#aiwoologinout#', '#aiwoocheckout#', '#aiwooterms#', '#aiwoomyaccount#', '#aiwoosearch#' );
	
	$menu_item_array = explode('#', $menu_item->url);
	$menu_item_url = '';
	if(!empty($menu_item_array[1]))
	{	
		$menu_item_url = '#'.$menu_item_array[1].'#';
	}
		
	if ( isset($menu_item->object, $menu_item->url) && $menu_item->object == 'custom' && in_array($menu_item_url, $elems) ){
		$menu_item->type_label = ( 'AI WooCommerce' );
	}
	
	if(isset($menu_item->ID) && ($menu_item->ID != 0)){
		
		if( get_post_meta( $menu_item->ID, '_menu_item_condition', true ) ){
			$menu_item->condition = get_post_meta( $menu_item->ID, '_menu_item_condition', true );
		}
	}
	
		
	return $menu_item;
}

function aiwoo_exclude_menu_items( $menu_item ) 
{
		$hide_children_of = array();
		
		// Iterate over the items to search and destroy
		foreach ( $menu_item as $key => $item ) {

			$visible = true;
			
			// hide any item that is the child of a hidden item
			if( in_array( $item->menu_item_parent, $hide_children_of ) ){
				
				$visible = false;
				$hide_children_of[] = $item->ID; // for nested menus
			}
			
			// check any item that has NMR roles set
			if( $visible ) {
				if($item->condition == 1){
					if(!is_user_logged_in()){
						$visible = false;						
					}else{
						$visible = true;
					}

				}
			}
			// unset non-visible item
			if ( ! $visible ) {
				$hide_children_of[] = $item->ID; // store ID of item
				unset( $menu_item[$key] ) ;
			}

		}
		return $menu_item;
}