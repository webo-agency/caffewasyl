<?php
/**
 * GPL Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package gpl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if( !class_exists('GPL_Init') ){
	class GPL_Init{
		
		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	
	
		/**
		 * Constructor
		 */
		public function __construct() {
			// Hook: Frontend assets.
			add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'front_end_assets' ) );
			
			// Hook: Editor assets.
			add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ) );
			
			add_filter( 'block_categories', array( $this, 'register_block_category' ), 1, 2 );
			add_action( 'after_setup_theme', array( $this, 'guten_post_layout_blocks_plugin_setup') );
		}
		
		public function register_block_category($categories, $post) {
			return array_merge(
				array(
					array(
						'slug'  => 'guten-post-layout',
						'title' => __('Guten Post Layout', 'guten-post-layout')
					),
				),
				$categories
			);
	
		}
		
		public function guten_post_layout_blocks_plugin_setup() {
			if (!current_theme_supports('align-wide')) {
				add_theme_support( 'align-wide' );
			}
		}
	
	    public function block_assets(){
			wp_enqueue_style(
				'guten-post-layout-style-css',
				GUTEN_POST_LAYOUT_DIR_URL.'dist/blocks.style.build.css',
				array(),
				filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'dist/blocks.style.build.css')
			);

		    wp_enqueue_style(
			    'slick',
			    GUTEN_POST_LAYOUT_DIR_URL.'src/assets/css/slick.css',
			    array(),
			    filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'src/assets/css/slick.css')
		    );

		    wp_enqueue_style(
			    'slick-theme',
			    GUTEN_POST_LAYOUT_DIR_URL. 'src/assets/css/slick-theme.css',
			    array(),
			    filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'src/assets/css/slick-theme.css')
		    );



		    wp_register_script(
			    'slick',
			    GUTEN_POST_LAYOUT_DIR_URL.'src/assets/js/slick.min.js',
			    array('jquery'),
			    filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'src/assets/js/slick.min.js'),
			    true
		    );

		    wp_register_script(
			    'guten-post-layout-custom',
			    GUTEN_POST_LAYOUT_DIR_URL. 'src/assets/js/custom.js',
			    array('jquery'),
			    filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'src/assets/js/custom.js'),
			    true
		    );
		    
		}

	     public function editor_assets(){
			wp_enqueue_script(
				'gute-post-layout-js',
				GUTEN_POST_LAYOUT_DIR_URL.'dist/blocks.build.js',
				array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' , 'wp-editor' ),
				filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'dist/blocks.build.js'),
				true
			);
	
			wp_enqueue_style(
				'guten-post-layout-editor-css',
				GUTEN_POST_LAYOUT_DIR_URL.'dist/blocks.editor.build.css',
				array('wp-edit-blocks'),
				filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'dist/blocks.editor.build.css')
			);
	
			wp_enqueue_style(
				'guten-post-layout-font-icons',
				GUTEN_POST_LAYOUT_DIR_URL .'src/assets/css/font-icons.css',
				array(),
				filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'src/assets/css/font-icons.css')
			);
		}

		public function front_end_assets(){
			wp_enqueue_style(
				'guten-post-layout-font-icons',
				GUTEN_POST_LAYOUT_DIR_URL.'src/assets/css/font-icons.css',
				array(),
				filemtime(GUTEN_POST_LAYOUT_DIR_PATH.'src/assets/css/font-icons.css')
			);
		}

	}
}

GPL_Init::get_instance();
