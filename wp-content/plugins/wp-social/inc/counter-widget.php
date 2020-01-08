<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Class Name : xs_counter_widget;
* Class Details : Create Widget for XS Social Login Plugin
* 
* @params : void
* @return : void
*
* @since : 1.0
*/
use \XsSocialCounter\Settings as Settings;

class xs_counter_widget extends WP_Widget {
	public $styleArr = [];
	public $providers = [];
	public function __construct() {
		
		$widget_ops 	= array( 'classname' => 'xs_counter-widget', 'description' => __( 'Wp Social Login System for Facebook, Twitter, Linkedin, Dribble, Pinterest, Post, Comments counter.', 'wp-social' )  );
		//$control_ops 	= array( 'width' => 250, 'height' => 350, 'id_base' => 'xs_counter-widget' );
		parent::__construct( 'xs_counter_widget', __('WSLU Social Counter', 'wp-social'), $widget_ops, []);
		
		$this->styleArr = Settings::$counter_style;
		
		$option_key 	= 'xs_counter_providers_data';
		$xsc_options	 = get_option( $option_key ) ? get_option( $option_key ) : [];
		$counter_provider = isset($xsc_options['social']) ? $xsc_options['social'] : [];
		
		$this->providers = [];
		if(is_array($counter_provider) && sizeof($counter_provider) > 0){
			foreach( $counter_provider AS $k=>$v):
				if( isset($v['enable']) ){
					$this->providers[$k] = $v['label'];
				}
			endforeach;
		}
	}
	
	public static function register(){
		register_widget( 'xs_counter_widget' );
	}
		
	public function widget( $args, $instance ) {
		
		extract( $args );
		
		$title 		= isset($instance['title']) ? $instance['title'] : '';
		$layout 	= isset($instance['layout']) ? $instance['layout'] : '';
		$columns 	= isset($instance['columns']) ? $instance['columns'] : '';
		$providers 	= isset($instance['providers']) ? $instance['providers'] : '';
		$customclass 	= isset($instance['customclass']) ? $instance['customclass'] : '';
		$box_only 	= isset($instance['box_only']) ? $instance['box_only'] : false;
		
		$counter = New \XsSocialCounter\Counter(false);
		
		$config = [];
		$config['class'] = $customclass;
		$config['style'] = $layout;
		$config['columns'] = $columns;
		$providers = (is_array($providers) && empty($providers)) ? $providers : 'all';
		
		if( !$box_only ){
			echo $before_widget . $before_title . $title . $after_title;
		}

		echo  $counter->get_counter_data( $providers , $config);

		if( !$box_only ){
			echo $after_widget;
		}
		
	}

	public function form( $instance ) {
		$defaults = array( 'title' => __( 'Follow us' , 'wp-social' )  , 'layout' => 'block' , 'columns' => 'xs-3-column' , 'box_only' => false, 'providers' => '', 'customclass' => '');
		$instance = wp_parse_args( (array) $instance, $defaults );
		$select_provider = is_array($instance['providers']) && sizeof($instance['providers']) > 0 ? $instance['providers'] : [];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Counter Title :' , 'wp-social' ) ?> </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" type="text" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'providers' ); ?>"><?php _e( 'Providers :' , 'wp-social' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'providers' ); ?>" name="<?php echo $this->get_field_name( 'providers' ); ?>[]" multiple>
				<option value="">All</option>
				<?php
				 foreach($this->providers as $k=>$v):
				?>
					<option value="<?php echo $k;?>" <?php echo (in_array($k, $select_provider)) ? 'selected' : ''; ?>> <?php _e($v, 'wp-social'); ?> </option>
				<?php endforeach;?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Style :' , 'wp-social' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>" >
				<?php
				 foreach($this->styleArr as $k=>$v):
				?>
					<option value="<?php echo $k;?>" <?php echo ($instance['layout'] == $k ) ? 'selected' : ''; ?>> <?php _e($v, 'wp-social'); ?> </option>
				<?php endforeach;?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Column :' , 'wp-social' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" >
				<option value="xs-3-column" <?php echo ($instance['columns'] == 'xs-3-column' ) ? 'selected' : ''; ?> ><?php _e( '3 Column' , 'wp-social' ) ?> </option>
				<option value="xs-2-column" <?php echo ($instance['columns'] == 'xs-2-column' ) ? 'selected' : ''; ?> ><?php _e( '2 Column' , 'wp-social' ) ?> </option>
				<option value="xs-1-column" <?php echo ($instance['columns'] == 'xs-1-column' ) ? 'selected' : ''; ?> ><?php _e( '1 Column' , 'wp-social' ) ?> </option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'box_only' ); ?>"><?php _e( 'Show the Social Box only :' , 'wp-social' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'box_only' ); ?>" name="<?php echo $this->get_field_name( 'box_only' ); ?>" value="true" <?php if( $instance['box_only'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<br /><small><?php _e( 'Will show only counter block without title.' , 'wp-social' ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'customclass' ); ?>"><?php _e( 'Custom Class :' , 'wp-social' ) ?> </label>
			<input id="<?php echo $this->get_field_id( 'customclass' ); ?>" name="<?php echo $this->get_field_name( 'customclass' ); ?>" value="<?php echo $instance['customclass']; ?>" class="widefat" type="text" />
		</p>
	<?php 
	}
		 
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['providers'] 	= $new_instance['providers'] ;
		$instance['layout'] 	= $new_instance['layout'] ;
		$instance['columns'] 	= $new_instance['columns'] ;
		$instance['title'] 		= $new_instance['title'] ;
		$instance['box_only'] 	= $new_instance['box_only'] ;
		$instance['customclass'] 	= $new_instance['customclass'] ;
		return $instance;
	}
} 

add_action( 'widgets_init', 'xs_counter_widget::register' );
