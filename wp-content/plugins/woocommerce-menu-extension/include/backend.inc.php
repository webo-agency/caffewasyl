<?php 
if( !defined( 'ABSPATH' ) )
	die( 'Error' );

/* Add a metabox in admin menu page */
add_action('admin_head-nav-menus.php', 'aiwoo_add_nav_menu_metabox');
function aiwoo_add_nav_menu_metabox() {
	add_meta_box( 'aiwoo', __( 'AI WooCommerce Links' ) . ' v' . AIWOO_VERSION, 'aiwoo_nav_menu_metabox', 'nav-menus', 'side', 'default' );
}

/* The metabox code */
function aiwoo_nav_menu_metabox( $object )
{
	global $nav_menu_selected_id;

	$elems = array( '#aiwooshop#' => __( 'Shop' ), '#aiwoocart#' => __( 'Cart' ), '#aiwoobasket#' => __( 'Basket' ), '#aiwoologin#' => __( 'Log In' ), '#aiwoologout#' => __( 'Log Out' ), '#aiwoologinout#' => __( 'Log In' ).'|'.__( 'Log Out' ), '#aiwoocheckout#' => __( 'Checkout' ), '#aiwooterms#' => __( 'Terms' ), '#aiwoomyaccount#' => __( 'My Account' ), '#aiwoosearch#' => __( 'Search Product' ).'|'.__( 'Search' )  );
	class aiwoologItems {
		public $ID = 0;
		public $db_id = 0;
		public $object = 'aiwoolog';
		public $object_id;
		public $menu_item_parent = 0;
		public $type = 'custom';
		public $title;
		public $url;
		public $target = '';
		public $attr_title = '';
		public $classes = array();
		public $xfn = '';
	}

	$elems_obj = array();
	foreach ( $elems as $value => $title ) {
		$elems_obj[$title] = new aiwoologItems();
		$elems_obj[$title]->object_id	= esc_attr( $value );
		$elems_obj[$title]->title		= esc_attr( $title );
		$elems_obj[$title]->url			= esc_attr( $value );
	}
	$walker = new Walker_Nav_Menu_Checklist( array() );
	?>
	<div id="login-links" class="loginlinksdiv">

		<div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
			<ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $elems_obj ), 0, (object)array( 'walker' => $walker ) ); ?>
			</ul>
		</div>

		<p class="button-controls">
			<span class="list-controls hide-if-no-js">
				<a href="javascript:void(0);" class="help" onclick="jQuery( '#help-login-links' ).toggle();"><?php _e( 'Help' ); ?></a>
				<span class="hide-if-js" id="help-login-links"><br /><a name="help-login-links"></a>
					<?php
						echo 'You can add a redirection page after the user\'s logout simply adding a relative link after the link\'s keyword, example <code>#aiwoologinout#index.php</code> or <code>#aiwoologout#index.php</code>.';	
					?>
				</span>
			</span>
			<span class="add-to-menu">
				<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-login-links-menu-item" id="submit-login-links" />
				<span class="spinner"></span>
			</span>
		</p>

	</div>
	<?php
}

/* Modify the "type_label" */

function aiwoo_update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {
			
		$saved_data = false;
		if ( isset( $_POST['menu-item-condition'][$menu_item_db_id]  )  && $_POST['menu-item-condition'][$menu_item_db_id] == '1' && ! empty ( $_POST['menu-item-condition'][$menu_item_db_id] ) ) {
			$saved_data = $_POST['menu-item-condition'][$menu_item_db_id];
			update_post_meta( $menu_item_db_id, '_menu_item_condition', $saved_data );
		} else {
			delete_post_meta( $menu_item_db_id, '_menu_item_condition' );
		}			
			
	}
function aiwoo_edit_walker($walker,$menu_id) {
		return 'AI_Walker_Nav_Menu_Edit_Custom';  
	}
