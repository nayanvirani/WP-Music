<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/nayanvirani/
 * @since      1.0.0
 *
 * @package    Wp_Music
 * @subpackage Wp_Music/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Music
 * @subpackage Wp_Music/public
 * @author     nayanvirani <virani.nayan@gmail.com>
 */
class Wp_Music_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_shortcode('music',array($this,'render_music_shortcode'));
	}

	public function render_music_shortcode($atts){
		global $wpdb;
		
		$atts = shortcode_atts( array(
			'year' => '',
			'gener' => ''
		), $atts,'music' );
		
		$post_ids = array();
		$year = $atts['year'];
		$category = $atts['gener'];
		
		if(!empty($atts['year'])){
			
			$results = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}custom_meta WHERE meta_key = 'year' AND meta_value = '{$year}'",ARRAY_A);

			if(!empty($results)){
				$post_ids = wp_list_pluck($results,'post_id');
			}
		}
		$tax_query = [];
		
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $big = 999999999; // need an unlikely integer
        $number_of_item_per_page = get_option('number_of_item_per_page',12);
		$wp_music_currency = get_option('wp_music_currency','USD');
        $post_args=array(
            'post_type' => "music",
            'posts_per_page' => $number_of_item_per_page,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'paged'          => $paged
			
        );
		if(!empty($post_ids)){
			$post_args['include'] = $post_ids;
		}
		if(!empty($category)){
			$post_args['tax_query'] = array(
					array(
						'taxonomy' => 'gener',
						'field' => 'slug',
						'terms' => $category,
					)
				);
		}
		$wp_query = new WP_Query( $post_args);
		
		$item_html='<div class="row">';
		if($wp_query->have_posts()):
            
           
			while ( $wp_query->have_posts() ) : $wp_query->the_post();
			$item_html.='<div class="col-sm-6">
				<div class="item-inner">
					<div class="thumb">'.get_the_post_thumbnail(get_the_ID(),'post-thumbnail',array('class'=>'img-thumbnail')).'</div>
					<div class="music-content">
						<h4><a class="item-link" href="'.get_the_permalink(get_the_ID()).'">'.get_the_title().'</a></h4>
						<div class="meta-music">
							Price:
							<span> '.$wp_music_currency.' '.getMusicMeta(get_the_ID(),'price').'</span>
						</div>
						<div class="meta-music">
						Composer Name: 
							<span>'.getMusicMeta(get_the_ID(),'composer_name').'</span>
						</div>
						<div class="meta-music">
							Publisher: 
							<span> '.getMusicMeta(get_the_ID(),'publisher').'</span>
						</div>
						<div class="meta-music">
						Year of recording: 
							<span> '.getMusicMeta(get_the_ID(),'year_of_recording').'</span>
						</div>
						<div class="meta-music">
						Additional Contributors:
							<span> '.getMusicMeta(get_the_ID(),'additional_contributors').'</span>
						</div>
						<div class="meta-music">
						URL: 
							<span> '.getMusicMeta(get_the_ID(),'url').'</span>
						</div>
					</div>
				</div></div>';
				
			endwhile;
		endif;
		$item_html.='</div>';
		$item_html.='<nav class="paginations">'.$this->custom_pagination($wp_query->max_num_pages, "", $paged,false).'</nav>';	
		return '<div class="container">'.$item_html.'</div>';
		
	}
	protected function custom_pagination($numpages = '', $pagerange = '', $paged='',$echo=true) {
 
        if (empty($pagerange)) {
            $pagerange = 2;
        }
     
        global $paged;
         
        if (empty($paged)) {
            $paged = 1;
        }
         
        if ($numpages == '') {
            global $wp_query;
             
            $numpages = $wp_query->max_num_pages;
             
            if(!$numpages) {
                $numpages = 1;
            }
        }
     
        $pagination_args = array(
            'format'          => 'page/%#%',
            'total'           => $numpages,
            'current'         => $paged,
            'show_all'        => false,
            'end_size'        => 1,
            'mid_size'        => $pagerange,
            'prev_next'       => True,
            'prev_text'       => __('&laquo;'),
            'next_text'       => __('&raquo;'),
            'type'            => 'plain',
            'add_args'        => false,
            'add_fragment'    => ''
        );
        $paginate_links = paginate_links($pagination_args);
        if($echo==false){
            if ($paginate_links) {
                return '<div class="pagination">'.$paginate_links.'</div>';
            }    
        }else{
            if ($paginate_links) {
                echo "<div class='pagination'>";
                    echo $paginate_links;
                echo "</div>";
            }
        }
        
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-music-public.css', array(), $this->version, 'all' );
		wp_enqueue_style('bootsrap','https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-music-public.js', array( 'jquery' ), $this->version, false );

	}

}
