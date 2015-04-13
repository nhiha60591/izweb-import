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
function wirte_text_autocomplete( $meta_key = '', $page = 1 ){
    global $wpdb;
    $izw_sort_filter = $wpdb->prefix.'izw_sort_filter';
    $tag1 = array();
    $start = ((int)$page - 1) * 50;
    $SQL = "SELECT DISTINCT B.meta_value FROM (select ID,post_type from {$wpdb->posts} where post_type = 'program') A INNER JOIN (select meta_value, meta_key,post_id from {$wpdb->postmeta} WHERE meta_key = 'condition') B ON A.ID = B.post_id LIMIT {$start},50";
    $sql1 = $wpdb->get_results( $SQL, ARRAY_A );
    foreach( $sql1 as $rows){
        if( !empty( $rows['meta_value'])){
            $tag1[] = $rows['meta_value'];
        }
    }

    $condition =  array_intersect_key(
        $tag1,
        array_unique(array_map("hh_replace_text",$tag1)
        ) );
    $fp=fopen(__IZIPPATH__."autocomplete-{$meta_key}.txt","a+") or die( "Do not open file");
    fwrite($fp,implode( ",", $tag1  )."," );
    fclose($fp);
}
function hh_replace_text( $text ){
    $str = strtolower( str_replace( "-", " ", $text, $count ) );
    if( $count >= 1)
        return trim( $str  );
    return trim( $text );
}
function hh_replace_text2( $text ){
    $str = str_replace( "-", " ", strtolower( $text ), $count );
    if( $count >= 1)
        return ucwords( str_replace( "-", " ", strtolower( $text ) ) );
    return $text;
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
        'Denmark',
        'France',
        'Germany',
        'Italy',
        'Netherlands',
        'Russia',
        'Spain',
        'Sweden',
        'Afghanistan',
        'Albania',
        'Algeria',
        'Angola',
        'Argentina',
        'Armenia',
        'Australia',
        'Austria',
        'Azerbaijan',
        'Bahrain',
        'Bangladesh',
        'Barbados',
        'Belarus',
        'Belgium',
        'Benin',
        'Bhutan',
        'Bolivia',
        'Bosnia and Herzegovina',
        'Botswana',
        'Brazil',
        'Brunei Darussalam',
        'Bulgaria',
        'Bulgary',
        'Burkina Faso',
        'Burundi',
        'Cambodia',
        'Cameroon',
        'Canada',
        'Central African Republic',
        'Chile',
        'China',
        'Colombia',
        'Congo',
        'Costa Rica',
        'Côte D\'Ivoire',
        'Croatia',
        'Cuba',
        'Cyprus',
        'Czech Republic',
        'Denmark',
        'Djibouti',
        'Dominican Republic',
        'Ecuador',
        'Egypt',
        'El Salvador',
        'Estonia',
        'Ethiopia',
        'Fiji',
        'Finland',
        'Former Serbia and Montenegro',
        'France',
        'Gabon',
        'Gambia',
        'Georgia',
        'Germany',
        'Ghana',
        'Greece',
        'Guadeloupe',
        'Guatemala',
        'Guinea',
        'Guinea-Bissau',
        'Haiti',
        'Holy See (Vatican City State)',
        'Honduras',
        'Hong Kong',
        'Hungary',
        'Iceland',
        'India',
        'Indonesia',
        'Iran',
        'Ireland',
        'Israel',
        'Italy',
        'Jamaica',
        'Japan',
        'Jordan',
        'Kazakhstan',
        'Kenya',
        'Korea',
        'Kosovo',
        'Kuwait',
        'Kyrgyzstan',
        'Lao People\'s Democratic Republic',
        'Latvia',
        'Lebanon',
        'Lesotho',
        'Liberia',
        'Libyan Arab Jamahiriya',
        'Liechtenstein',
        'Lithuania',
        'Luxembourg',
        'Macedonia',
        'Madagascar',
        'Malawi',
        'Malaysia',
        'Mali',
        'Malta',
        'Martinique',
        'Mauritius',
        'Mexico',
        'Moldova',
        'Monaco',
        'Mongolia',
        'Montenegro',
        'Montserrat',
        'Morocco',
        'Mozambique',
        'Myanmar',
        'Nepal',
        'Netherlands',
        'Netherlands Antilles',
        'New Zealand',
        'Niger',
        'Nigeria',
        'Norway',
        'Oman',
        'Pakistan',
        'Panama',
        'Papua New Guinea',
        'Paraguay',
        'Peru',
        'Philippines',
        'Poland',
        'Portugal',
        'Puerto Rico',
        'Qatar',
        'Réunion',
        'Romania',
        'Russia',
        'Russian Federation',
        'Rwanda',
        'Saudi Arabia',
        'Senegal',
        'Serbia',
        'Sierra Leone',
        'Singapore',
        'Slovakia',
        'Slovenia',
        'Solomon Islands',
        'South Africa',
        'Spain',
        'Sri Lanka',
        'Sudan',
        'Suriname',
        'Swaziland',
        'Sweden',
        'Switzerland',
        'Syrian Arab Republic',
        'Taiwan',
        'Tanzania',
        'Thailand',
        'Togo',
        'Trinidad and Tobago',
        'Tunisia',
        'Turkey',
        'Uganda',
        'Ukraine',
        'United Arab Emirates',
        'United Kingdom',
        'Uruguay',
        'Uzbekistan',
        'Vanuatu',
        'Venezuela',
        'Vietnam',
        'Virgin Islands (U.S.)',
        'Zambia',
        'Zimbabwe',
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
function hh_update_autocomplete_file( $meta_key = 'condition'){
    echo "<pre>";
    $content = file_get_contents( __IZIPURL__."autocomplete-{$meta_key}.txt");
    $tag1 = explode( ",", $content );
    $condition =  array_intersect_key(
        $tag1,
        array_unique(array_map("hh_replace_text",$tag1)
        ) );
    $condition = array_intersect_key(
        $tag1,
        array_unique(array_map("hh_replace_text2",$condition)
        ) );
    $fp=fopen(__IZIPPATH__."autocomplete-{$meta_key}.txt","w+") or die( "Do not open file");
    fwrite($fp,implode( ",", $condition  ) );
    fclose($fp);
}
function hh_meta_form( $post = null ) {
    global $wpdb;
    $post = get_post( $post );

    /**
     * Filter the number of custom fields to retrieve for the drop-down
     * in the Custom Fields meta box.
     *
     * @since 2.1.0
     *
     * @param int $limit Number of custom fields to retrieve. Default 30.
     */
    $limit = apply_filters( 'postmeta_form_limit', 50 );
    $sql = "SELECT meta_key
		FROM $wpdb->postmeta
		GROUP BY meta_key
		HAVING meta_key NOT LIKE %s
		ORDER BY meta_key
		LIMIT %d";
    $keys = $wpdb->get_col( $wpdb->prepare( $sql, $wpdb->esc_like( '_' ) . '%', $limit ) );
    if ( $keys ) {
        natcasesort( $keys );
        $meta_key_input_id = 'metakeyselect';
    } else {
        $meta_key_input_id = 'metakeyinput';
    }
    $options = get_option( 'izweb_custom_fields' );
    $keys = array_merge( $keys, $options );
    ?>
    <p><strong><?php _e( 'Add New Custom Field:' ) ?></strong></p>
    <table id="newmeta">
        <thead>
        <tr>
            <th class="left"><label for="<?php echo $meta_key_input_id; ?>"><?php _ex( 'Name', 'meta name' ) ?></label></th>
            <th><label for="metavalue"><?php _e( 'Value' ) ?></label></th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td id="newmetaleft" class="left">
                <?php if ( $keys ) { ?>
                    <select id="metakeyselect" name="metakeyselect">
                        <option value="#NONE#"><?php _e( '&mdash; Select &mdash;' ); ?></option>
                        <?php

                        foreach ( $keys as $key ) {
                            if ( is_protected_meta( $key, 'post' ) || ! current_user_can( 'add_post_meta', $post->ID, $key ) )
                                continue;
                            echo "\n<option value='" . esc_attr($key) . "'>" . esc_html($key) . "</option>";
                        }
                        ?>
                    </select>
                    <input class="hide-if-js" type="text" id="metakeyinput" name="metakeyinput" value="" />
                    <a href="#postcustomstuff" class="hide-if-no-js" onclick="jQuery('#metakeyinput, #metakeyselect, #enternew, #cancelnew').toggle();return false;">
                        <span id="enternew"><?php _e('Enter new'); ?></span>
                        <span id="cancelnew" class="hidden"><?php _e('Cancel'); ?></span></a>
                <?php } else { ?>
                    <input type="text" id="metakeyinput" name="metakeyinput" value="" />
                <?php } ?>
            </td>
            <td><textarea id="metavalue" name="metavalue" rows="2" cols="25"></textarea></td>
        </tr>

        <tr><td colspan="2">
                <div class="submit">
                    <?php submit_button( __( 'Add Custom Field' ), 'secondary', 'addmeta', false, array( 'id' => 'newmeta-submit', 'data-wp-lists' => 'add:the-list:newmeta' ) ); ?>
                </div>
                <?php wp_nonce_field( 'add-meta', '_ajax_nonce-add-meta', false ); ?>
            </td></tr>
        </tbody>
    </table>
<?php

}