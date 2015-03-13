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

/**
 * Search Link
 * @param string $short_code
 * @return null|string
 */
function search_link( $short_code = ''){
    global $wpdb;
    $SQL = "SELECT ID FROM {$wpdb->posts}
            WHERE `post_content` LIKE '%[{$short_code}%' AND `post_type`='page'
            LIMIT 1";

    return $wpdb->get_var( $SQL );
}

/**
 * Write text_autocomplete
 *
 * @param string $meta_key
 */
function wirte_text_autocomplete( $meta_key = '' ){
    global $wpdb;
    $tag1 = array();
    if( $meta_key == 'condition'){
        $sql1 = $wpdb->get_results( 'SELECT DISTINCT `meta_value` FROM '.$wpdb->postmeta.' INNER JOIN '.$wpdb->posts.' ON '.$wpdb->posts.'.`ID` = '.$wpdb->postmeta.'.`post_id` AND '.$wpdb->posts.'.`post_status` = "publish"  AND ( '.$wpdb->postmeta.'.meta_key = "condition" OR '.$wpdb->postmeta.'.meta_key = "intervention_name")', ARRAY_A );
    }else{
        $sql1 = $wpdb->get_results( 'SELECT DISTINCT `meta_value` FROM '.$wpdb->postmeta.' INNER JOIN '.$wpdb->posts.' ON '.$wpdb->posts.'.`ID` = '.$wpdb->postmeta.'.`post_id` AND '.$wpdb->posts.'.`post_status` = "publish"  AND '.$wpdb->postmeta.'.`meta_key` = "'.$meta_key.'"', ARRAY_A );
    }
    foreach( $sql1 as $rows){
        if( !empty($rows['meta_value'] ) ) {
            $multi = explode("\n", $rows['meta_value']);
            if (is_array($multi)) {
                foreach($multi as $country) {
                    $tag1[] = trim($country);
                }
            } else $tag1[] = trim($rows['meta_value']);
        }
    }
    $condition =  array_intersect_key(
        $tag1,
        array_unique(array_map("StrToLower",$tag1)
        ) );
    $fp=fopen(__IZIPPATH__."autocomplete-{$meta_key}.txt","w+");
    fwrite($fp,implode( ",",$condition ) );
    fclose($fp);
}
function update_data_filter($page = 1, $post_id = 0 ){
    global $wpdb;
    $izw_sort_filter = $wpdb->prefix.'izw_sort_filter';
    if( $post_id ){
        $min_age = explode( " ", get_post_meta( $post_id, 'minimum_age', true) );
        if( sizeof( $min_age ) >=2 ){
            $min_age = $min_age[0];
        }else{
            $min_age = 0;
        }

        $max_age = explode( " ", get_post_meta( $post_id, 'maximum_age', true) );
        if( sizeof( $max_age ) >=2 ){
            $max_age = $max_age[0];
        }else{
            $max_age = 0;
        }
        $terms = wp_get_object_terms( $post_id, 'program_cat' );
        $study = 0;
        foreach( $terms as $term ){
            $study = $term->term_id;
        }

        $data = array(
            'post_status'   => get_post_status( $post_id ),
            'drug'          => get_post_meta( $post_id, 'drug', true),
            'condition'     => get_post_meta( $post_id, 'condition', true),
            'country'       => get_post_meta( $post_id, 'location_countries', true),
            'study'         => $study,
            'gender'        => get_post_meta( $post_id, 'gender', true),
            'min_age'       => $min_age,
            'max_age'       => $max_age,
            'sponsor'       => get_post_meta( $post_id, 'sponsors', true),
        );
        if( check_sort_post_id( $post_id ) ){
            $wpdb->update( $izw_sort_filter, $data, array( 'post_id' => $post_id) );
        }else{
            $data['post_id'] = $post_id;
            $wpdb->insert( $izw_sort_filter, $data );
        }
        return;
    }
    $query = new WP_Query( array(
        'post_type' => 'program',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        'paged' => $page
    ) );
    if( $query->have_posts()){
        while( $query->have_posts()){
            $query->the_post();
            $min_age = explode( " ", get_post_meta( get_the_ID(), 'minimum_age', true) );
            if( sizeof( $min_age ) >=2 ){
                $min_age = $min_age[0];
            }else{
                $min_age = 0;
            }

            $max_age = explode( " ", get_post_meta( get_the_ID(), 'maximum_age', true) );
            if( sizeof( $max_age ) >=2 ){
                $max_age = $max_age[0];
            }else{
                $max_age = 0;
            }
            $terms = wp_get_object_terms( get_the_ID(), 'program_cat' );
            $study = 0;
            foreach( $terms as $term ){
                $study = $term->term_id;
            }

            $data = array(
                'post_status'   => get_post_status( get_the_ID() ),
                'drug'          => get_post_meta( get_the_ID(), 'drug', true),
                'condition'     => get_post_meta( get_the_ID(), 'condition', true),
                'country'       => get_post_meta( get_the_ID(), 'location_countries', true),
                'study'         => $study,
                'gender'        => get_post_meta( get_the_ID(), 'gender', true),
                'min_age'       => $min_age,
                'max_age'       => $max_age,
                'sponsor'       => get_post_meta( get_the_ID(), 'sponsors', true),
            );
            if( check_sort_post_id( get_the_ID() ) ){
                $wpdb->update( $izw_sort_filter, $data, array( 'post_id' => get_the_ID()) );
            }else{
                $data['post_id'] = get_the_ID();
                $wpdb->insert( $izw_sort_filter, $data );
            }
        }
    }
    wp_reset_postdata();
}
function check_sort_post_id( $post_id ){
    global $wpdb;
    $izw_sort_filter = $wpdb->prefix.'izw_sort_filter';
    $ID = $wpdb->get_var( "SELECT ID FROM `{$izw_sort_filter}` WHERE `post_id` = '{$post_id}'");
    return $ID;
}
function izw_all_countries(){
    $countries = array (
        'United States',
        'United Kingdom',
        'Algeria',
        'Argentina',
        'Australia',
        'Austria',
        'Belgium',
        'Brazil',
        'Bulgaria',
        'Bulgary',
        'Canada',
        'Chile',
        'China',
        'Colombia',
        'Croatia',
        'Cyprus',
        'Czech Republic',
        'Denmark',
        'Egypt',
        'Estonia',
        'Finland',
        'France',
        'Germany',
        'Greece',
        'Hong Kong',
        'Hungary',
        'Iceland',
        'India',
        'Ireland',
        'Israel',
        'Italy',
        'Japan',
        'Korea',
        'Kuwait',
        'Latvia',
        'Liechtenstein',
        'Lithuania',
        'Luxembourg',
        'Malaysia',
        'Malta',
        'Mexico',
        'Morocco',
        'Netherlands',
        'New Zealand',
        'Norway',
        'Poland',
        'Portugal',
        'Romania',
        'Russia',
        'Saudi Arabia',
        'Serbia',
        'Singapore',
        'Slovakia',
        'Slovenia',
        'South Africa',
        'Spain',
        'Sweden',
        'Switzerland',
        'Taiwan',
        'Thailand',
        'Tunisia',
        'Turkey',
        'Ukraine',
        'United Arab Emirates',
        'Venezuela'
    );
    return apply_filters( 'izw_all_countries', $countries );
}
function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}