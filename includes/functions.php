<?php
function getMusicMeta($post_id,$meta_key){
    global $wpdb;
    $table = $wpdb->prefix."custom_meta";
    $result = $wpdb->get_row("SELECT * FROM $table WHERE post_id = $post_id AND meta_key = '$meta_key'");
    if(!empty($result)){
        return $result->meta_value;
    }
    return "";
}