<?php
if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' && isset( $_REQUEST['pid'] ) && !empty( $_REQUEST['pid'] ) ){
    global $wpdb;
    $post_ids = $wpdb->get_col( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'izweb_remove_id' AND `meta_value` = {$_REQUEST['pid']}");
    foreach($post_ids as $post_id){
        wp_delete_post( $post_id );
    }
    $wpdb->delete( $wpdb->prefix."remove_posts", array('ID'=>$_REQUEST['pid']));
    wp_redirect( add_query_arg( array( 'page' => $_REQUEST['page'], 'remove' => 'yes'), admin_url("edit.php?post_type=program") ) );
}
include "remove-list-posts.php";