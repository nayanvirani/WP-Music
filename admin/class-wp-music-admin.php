<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/nayanvirani/
 * @since      1.0.0
 *
 * @package    Wp_Music
 * @subpackage Wp_Music/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Music
 * @subpackage Wp_Music/admin
 * @author     nayanvirani <virani.nayan@gmail.com>
 */
class Wp_Music_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('save_post',array($this,'save_custom_metadata'));	

	}


	public function register_music_post_type(){
		global $wp_rewrite;
		register_post_type(
			'music',
			array(
				'labels'                => array(
					'name' => _x('Musics', 'post type general name'),
					'name_admin_bar' => _x( 'Music', 'add new from admin bar' ),
				),
				'public'                => true,
				'capability_type'       => 'post',
				'map_meta_cap'          => true,
				'menu_position'         => 22,
				'menu_icon'             => 'dashicons-admin-post',
				'hierarchical'          => false,
				'rewrite'               => false,
				'show_ui'				=> true,
				'query_var'             => false,
				'show_in_menu'			=> true,
				'delete_with_user'      => true,
				'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'post-formats' ),
				'show_in_rest'          => true,
				'register_meta_box_cb' => array($this,'register_custom_metabox')
			)
		);

		$rewrite          = array(
			'category'    => array(
				'hierarchical' => true,
				'slug'         => get_option( 'category_base' ) ? get_option( 'category_base' ) : 'category',
				'with_front'   => ! get_option( 'category_base' ) || $wp_rewrite->using_index_permalinks(),
				'ep_mask'      => EP_CATEGORIES,
			),
			'post_tag'    => array(
				'hierarchical' => false,
				'slug'         => get_option( 'tag_base' ) ? get_option( 'tag_base' ) : 'tag',
				'with_front'   => ! get_option( 'tag_base' ) || $wp_rewrite->using_index_permalinks(),
				'ep_mask'      => EP_TAGS,
			),
		);
		register_taxonomy(
			'gener',
			'music',
			array(
				"label"				=> "Gener",
				'hierarchical'          => true,
				'query_var'             => 'gener_name',
				'rewrite'               => $rewrite['category'],
				'public'                => true,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'show_in_rest'          => true
				
			)
		);
	
		register_taxonomy(
			'music_tag',
			'music',
			array(
				'hierarchical'          => false,
				'query_var'             => 'tag',
				'rewrite'               => $rewrite['post_tag'],
				'public'                => true,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'show_in_rest'          => true
				
			)
		);

	}


	public function register_custom_metabox(){
		add_meta_box('music_post_extra_fields',__( 'Extra Fields', 'wp-music' ),array($this,'set_extra_field_box'));
	}

	protected function addMeta($post_id,$meta_key,$meta_value){
		global $wpdb;
		$table = $wpdb->prefix."custom_meta";
		return $wpdb->insert(
			$table,
			array(
				'post_id'      => $post_id,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
			)
		);
	}

	protected function updateMeta($post_id,$meta_key,$meta_value){
		global $wpdb;
		$table = $wpdb->prefix."custom_meta";
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $table WHERE meta_key = %s AND post_id = %d", $meta_key, $post_id ) );
		if ( empty( $ids ) ) {
			return $this->addMeta( $post_id,$meta_key,$meta_value );
		}
		return $wpdb->update( $table, array('meta_value' => $meta_value), array('meta_key' => $meta_key,'post_id' => $post_id) );
	}

	public function save_custom_metadata($post_id){
		if ( ! current_user_can( 'edit_posts', $post_id )){ 
			return 'not permitted'; 
		}

		if (isset( $_POST['wpmusic-nonce']) && wp_verify_nonce($_POST['wpmusic-nonce'],'wpmusic-nonce' )){
			$this->updateMeta($post_id,'composer_name',$_POST['composer_name']);
			$this->updateMeta($post_id,'publisher',$_POST['publisher']);
			$this->updateMeta($post_id,'year_of_recording',$_POST['year_of_recording']);
			$this->updateMeta($post_id,'additional_contributors',$_POST['additional_contributors']);
			$this->updateMeta($post_id,'url',esc_url($_POST['url']));
			$this->updateMeta($post_id,'price',$_POST['price']);
		}

	}

	

	public function set_extra_field_box($post){
		wp_nonce_field( 'wpmusic-nonce', 'wpmusic-nonce' );
		?>
		<style>
			.field-box{
				margin-bottom: 10px;
			}
			.field-box label, .field-box .field{
				display: block;
				width: 100%;
			}
		</style>
		<div class="extra-fields-wrapper">
			<div class="field-box">
				<label><?php _e('Composer Name','wp-music');?></label>
				<input type="text" name="composer_name" class="field" value="<?php echo getMusicMeta($post->ID,'composer_name');?>">
			</div>
			<div class="field-box">
				<label><?php _e('Publisher','wp-music');?></label>
				<input type="text" name="publisher" class="field" value="<?php echo getMusicMeta($post->ID,'publisher');?>">
			</div>
			<div class="field-box">
				<label><?php _e('Year of recording','wp-music');?></label>
				<input type="text" name="year_of_recording" class="field" value="<?php echo getMusicMeta($post->ID,'year_of_recording');?>">
			</div>
			<div class="field-box">
				<label><?php _e('Additional Contributors','wp-music');?></label>
				<input type="text" name="additional_contributors" class="field" value="<?php echo getMusicMeta($post->ID,'additional_contributors');?>">
			</div>
			<div class="field-box">
				<label><?php _e('URL');?></label>
				<input type="url" name="url" class="field" value="<?php echo getMusicMeta($post->ID,'url');?>">
			</div>
			<div class="field-box">
				<label><?php _e('Price');?></label>
				<input type="number" name="price" step="0.1" min="0" class="field" value="<?php echo getMusicMeta($post->ID,'price');?>">
			</div>
		</div>
		<?php
	}

	public function add_music_setting_menu(){
		$id = add_submenu_page("edit.php?post_type=music", "Settings", 'Settings', 'manage_options', "music-settings",array($this,"load_setting_page" ));
	}

	
	public function wp_music_save_settings(){
		if (isset($_POST['wm_save_settings']) && (isset($_POST['music-settings-form-save']) && wp_verify_nonce($_POST['music-settings-form-save'], 'music-settings-form-save'))) 
        {
        	update_option('number_of_item_per_page',$_POST['number_of_item_per_page']);
	        update_option('wp_music_currency',$_POST['wp_music_currency']);
	        
	        if (wp_get_referer())
            {
                wp_safe_redirect(wp_get_referer());
                exit;
            } else {
                wp_safe_redirect(admin_url('edit.php?post_type=music&page=music-settings'));
                exit;
            }

        }
	}

	public function load_setting_page(){
		?>
		<div class="wrap">
			<h1>Settings</h1>
			<form method="post" action="" novalidate="novalidate">
			<?php wp_nonce_field('music-settings-form-save','music-settings-form-save');?>
				<table class="form-table" role="presentation">
					<tbody>
					<tr>
						<th scope="row">
							<label for="number_of_item_per_page">Music post show per page</label>
						</th>
						<td>
							<input name="number_of_item_per_page" type="number" step="1" min="1" id="number_of_item_per_page" value="<?php echo get_option('number_of_item_per_page');?>" class="small-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wp_music_currency">Currency</label></th>
						<td><input name="wp_music_currency" type="text" id="wp_music_currency" value="<?php echo get_option('wp_music_currency');?>" class="regular-text"></td>
					</tr>
				</tbody>
				</table>
				<p class="submit"><input type="submit" name="wm_save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Music_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Music_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-music-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Music_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Music_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-music-admin.js', array( 'jquery' ), $this->version, false );

	}

}
