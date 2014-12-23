<?php
ob_start();
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/20/14
 * Time: 8:32 AM
 */
/**
 * Check Node Exits
 * @param $node
 * @return int|null|string
 */
function check_node_exits($node){
    global $wpdb;
    $table = $wpdb->prefix."izweb_import";
    $return = 0;
    $return = $wpdb->get_var( "SELECT COUNT(ID) FROM {$table} WHERE `dom_name` = '{$node}'");
    return $return;
}

/**
 * Get same data with meta key
 * @param string $meta_key
 * @return null|string
 */
function get_same_data( $meta_key = '' ){
    global $wpdb;
    $content = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = '{$meta_key}'");
    return $content;
}

/**
 * Substring text
 * @param $str
 * @param $length
 * @param int $minword
 * @return string
 */
function _substr($str, $length, $minword = 3)
{
    $sub = '';
    $len = 0;
    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);
        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }
    return $sub . (($len < strlen($str)) ? '...' : '');
}

/**
 * Show custom fields
 *
 * @param $post_id
 */
function izweb_show_custom_field( $post_id ){
    $options = get_option( 'izweb_custom_fields' );
    $caption = get_option( 'izweb_custom_fields_cation' );
    do_action( 'izweb_before_show_custom_fields' );
    if( !empty( $options ) && is_array( $options ) ){
        $i=0;
        echo '<div class="izweb-list-fields"><ul class="izw-list-field">';
        foreach( $options as $option ){
            $val = get_post_meta( $post_id, $option );
			
			if ($option == 'location_countries') {
				
				$multi = explode("\n", $val[0]);
				if (is_array($multi)) $val = $multi;
			}
            echo '<li><label>'.$caption[$i].': </label><span class="izweb-field-value">'. implode( ",", $val ).'</span></li>';
            $i++;
        }
        echo "</ul></div>";
    }
    do_action( 'izweb_after_show_custom_fields' );
}
function search_link( $short_code = ''){
    global $wpdb;
    $SQL = "SELECT ID FROM {$wpdb->posts}
            WHERE `post_content` LIKE '%[{$short_code}%' AND `post_type`='page'
            LIMIT 1";

    return $wpdb->get_var( $SQL );
}