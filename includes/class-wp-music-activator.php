<?php

/**
 * Fired during plugin activation
 *
 * @link       https://profiles.wordpress.org/nayanvirani/
 * @since      1.0.0
 *
 * @package    Wp_Music
 * @subpackage Wp_Music/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Music
 * @subpackage Wp_Music/includes
 * @author     nayanvirani <virani.nayan@gmail.com>
 */
class Wp_Music_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once(ABSPATH.'wp-admin/includes/upgrade.php' );
		
		$defult_options =array(
			'wp_music_currency'=>"USD",
			'number_of_item_per_page'=>10
		);

		foreach($defult_options as $option=>$value){
			if(!get_option($option)){
				update_option($option,$value);
			}
		}

		$table_name = $wpdb->prefix."custom_meta";
		$sql = "CREATE TABLE $table_name ( `id` INT(11) NOT NULL AUTO_INCREMENT , `post_id` INT(11) NOT NULL , `meta_key` TEXT NOT NULL , `meta_value` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
		dbDelta($sql);	
	}

}
