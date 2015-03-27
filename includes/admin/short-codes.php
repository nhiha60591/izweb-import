<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/24/14
 * Time: 3:54 PM
 */
/**
 * Add short code to search program
 */
add_shortcode( 'search_program', 'hh_search_program' );
function hh_search_program( $atts ){
    global $post,$wpdb;
    // Attributes
    $exc = get_option( 'izweb_post_excerpt' );
    $filter_fields = get_option( 'izw_filters_ctf' );
    $noti_mess = '';
    $exc = !empty($exc) ? $exc : 'brief_summary';
    extract( shortcode_atts(
            array(
                'key1' => 'condition',
                'caption1' => 'I am looking for EAP\'s for:',
                'placeholder1' => 'e.g. Depression',
                'key2' => 'living',
                'caption2' => 'I\'m living in:',
                'placeholder2' => 'e.g. United States',
                'excerpt' => $exc
            ), $atts )
    );
    $search_results = '';
    $data_search = array(
        'drug_condition' => @$_REQUEST['drug_condition'],
        'country' => @$_REQUEST['country'],
        'study' => @$_REQUEST['study'],
        'gender' => @$_REQUEST['gender'],
        'age_group' => @$_REQUEST['age_group'],
        'sponsor' => @$_REQUEST['sponsor'],
    );
    if( isset( $_POST['send_mail']) ){
        $izw_notification = $wpdb->prefix."subscription";
        $noti_ID = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT `ID` FROM `{$izw_notification}`
                            WHERE `search_condition` = '%s'
                            AND `search_country` = '%s'
                            AND `email` = '%s'",
                            $_POST['noti_condition'],
                            $_POST['noti_country'],
                            $_POST['noti_email']
                        )
        );
        if( empty( $noti_ID ) ){
            $data = array(
                'search_condition' => $_POST['noti_condition'],
                'search_country' => $_POST['noti_country'],
                'date' => date("Y-m-d H:i:s"),
                'email' => $_POST['noti_email'],
            );
            $wpdb->insert( $izw_notification, $data );
            $noti_mess = "Your notification sent! Thank you for send notification for us.";
        }
    }
    if(isset( $_REQUEST['izw_search'] ) ){
		global $is_search_program;
        $post_ids = array();
		$is_search_program = true;
        $data_search = array(
            'drug_condition' => @$_REQUEST['drug_condition'],
            'country' => @$_REQUEST['country'],
            'study' => @$_REQUEST['study'],
            'gender' => @$_REQUEST['gender'],
            'age_group' => @$_REQUEST['age_group'],
            'sponsor' => @$_REQUEST['sponsor'],
        );
        $izw_sort_filter = $wpdb->prefix.'izw_sort_filter';
        $search_sql = 'SELECT `post_id` FROM '.$izw_sort_filter.' WHERE 1=1';
        $where = '';
        if( isset( $data_search['drug_condition'] ) && !empty( $data_search['drug_condition'] )){
            $where .= ' AND ( `drug` LIKE "%'.$data_search['drug_condition'].'%"';
            $where .= ' OR `condition` LIKE "%'.$data_search['drug_condition'].'%" )';
        }

        if( isset( $data_search['country'] ) && $data_search['country'] != '0'){
            $where .= ' AND country LIKE "%'.$data_search['country'].'%"';
        }


        if( isset( $data_search['gender'] ) && $data_search['gender'] != 'Both'){
            $where .= ' AND gender = "'.$data_search['gender'].'"';
        }
        if( isset( $data_search['age_group'] ) && $data_search['age_group'] != '0'){
            if( strpos( $data_search['age_group'],'-') != FALSE){
                $bt = explode( '-', $data_search['age_group'] );
                $where .= ' AND ( min_age BETWEEN '.$bt[0].' AND '.$bt[1].' )';
            }elseif( strpos( $data_search['age_group'],'+') != FALSE){
                $bt = explode( '+', $data_search['age_group'] );
                $where .= ' AND min_age >= '.$bt[0];
            }
        }

        if( isset( $data_search['sponsor'] ) && !empty( $data_search['sponsor'] )){
            $where .= 'AND sponsor LIKE "%'.$data_search['sponsor'].'%"';
        }
        $exc_count = 0;
        if( isset( $data_search['study'] ) && $data_search['study'] != '0'){
            $where .= ' AND study = '.$data_search['study'];
        }else{
            $exc = get_term_by( 'name', 'Early Access Program', 'program_cat' );
            $SQL = "SELECT count(*) FROM `{$izw_sort_filter}` WHERE `study` = {$exc->term_id}". $where;
            $exc_count = $wpdb->get_var( $SQL );
        }
        $count_p = $wpdb->get_col( $search_sql.$where );
        $page = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        if( (int)$page < 0 ) $page = 1;
        $page = ceil($page) - 1;
        $perpage = 20;
        $start = $page*$perpage;
        $post_ids = $wpdb->get_col( $search_sql.$where." ORDER BY `study` DESC LIMIT {$start},{$perpage}" );
        $search_results = '';
        if( isset( $data_search['study'] ) && $data_search['study'] != '0'){
            $exc = get_term_by( 'id', $data_search['study'], 'program_cat' );
            if( $exc->name == "Early Access Program"){
                $exc_count = count( $count_p );
            }
        }
        ob_start();
        ?>
        <script type="text/javascript">
            <?php foreach( $data_search as $k=>$v): if( empty( $v ) ) continue; ?>
            _gaq.push(['_trackEvent', 'common-web', 'search', '<?php echo $k; ?>']);
            <?php endforeach; ?>
        </script>
        <?php
        if( sizeof( $post_ids ) > 0 ){
            ?>
            <div class="izweb-search-results">
                <?php dynamic_sidebar('izw-below-search'); ?>
                <?php foreach( $post_ids as $id){$post = get_post($id);setup_postdata($post);$terms = wp_get_post_terms( get_the_ID(), 'program_cat' ); $termslug = array(); foreach( $terms as $term){ $termslug[] = $term->slug;}?>
                    <div class="izweb-item <?php echo implode(" ", $termslug ); ?>">
                        <div class="izweb-item-left">
                            <div class="post-title">
                                <a class="izw-title-link" href="<?php the_permalink(); ?>"><?php echo _substr(get_the_title(), 70); ?></a>
                            </div>
                            <div class="post-content">
                                <?php do_action( 'izweb_before_search_content', $id ); ?>
                                <?php $exc_text = get_post_meta( $id, $excerpt, true ); if( !empty( $exc_text ) ) echo _substr(strip_tags($exc_text, '<p><a>'),300); else echo _substr(strip_tags($post->post_excerpt, '<p><a>'),300); ?>
                                <?php izweb_show_custom_field( $id ); ?>
                                <?php do_action( 'izweb_after_search_content', $id ); ?>
                            </div>
                        </div>
                        <div class="izweb-item-right">
                            <a class="izw-detail" href="<?php the_permalink(); ?>">Details</a>
                        </div>
                    </div>
                <?php }; ?>
            </div>
			<div class="search_pagination">
                <div class="wp-pagenavi">
                    <?php
                        $prev_page = (int)$page;
                        $next_page = (int)$page + 2;
                        $iz_page = ceil( count( $count_p )/ $perpage);
                        if( $iz_page > 1 ) {
                            for ($i = 1; $i <= $iz_page; $i++) {
                                if ($i == 1 && ($page + 1) != 1) {
                                    ?>
                                    <a data-page="<?php echo $i; ?>" class="page larger izw_first"
                                       href="<?php echo add_query_arg(array('paged' => 1)); ?>">First</a>
                                    <a data-page="<?php echo $i; ?>" class="page larger izw_prev"
                                       href="<?php echo add_query_arg(array('paged' => $prev_page)); ?>">Prev</a>
                                <?php
                                }
                                if( (($page+1) - $i) == 3 ){
                                    ?>
                                    <span class="izw_more">...</span>
                                <?php
                                }elseif(  (($page+1) - $i) <3 && (($page+1) - $i) >0 ){
                                    ?>
                                    <a data-page="<?php echo $i; ?>" class="page larger"
                                       href="<?php echo add_query_arg(array('paged' => $i)); ?>"><?php echo $i; ?></a>
                                    <?php
                                }elseif ($i == ($page + 1)) {
                                    ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php
                                }elseif( $i - ($page+1) >0 && $i - ($page+1) <3 ) {
                                    ?>
                                    <a data-page="<?php echo $i; ?>" class="page larger"
                                       href="<?php echo add_query_arg(array('paged' => $i)); ?>"><?php echo $i; ?></a>
                                    <?php
                                }elseif( ($i - ($page+1)) == 3){
                                    ?>
                                    <span class="izw_more">...</span>
                                    <?php
                                }
                                if ($i == $iz_page && ($page + 1) != $iz_page) {
                                    ?>
                                    <a data-page="<?php echo $i; ?>" class="page larger izw_next"
                                       href="<?php echo add_query_arg(array('paged' => $next_page)); ?>">Next</a>
                                    <a data-page="<?php echo $i; ?>" class="page larger izw_last"
                                       href="<?php echo add_query_arg(array('paged' => $iz_page)); ?>">Last</a>
                                <?php
                                }
                            }
                        }
                    ?>
                </div>
			</div>
			<?php
            $search_results = ob_get_clean();
            $include = count( $count_p ) - $exc_count;
            $found_mes = '<h3 class="izw-found-mes">'.__( "We have found {$exc_count} Early Access Programs (green) and {$include} Clinical Trials (red)")."</h3>";
            $search_results = $found_mes.$search_results;
        }else{
            if( $data_search['country'] == '0' ){
                $country = 'All';
            }else{
                $country = $data_search['country'];
            }
            ?>
            <div class="izw-error-mes">
                <h3>Sorry, no results were found!</h3>
                <ul class="izw-error-mes-ul">
                    <li>Try other keywords or check your spelling</li>
                    <li>Check the "include available Clinic Trials in search" option</li>
                    <li>Get notified when we have information for <strong><?php print htmlspecialchars($data_search['drug_condition']); ?></strong> in <strong><?php print htmlspecialchars($country); ?></strong>
                        <p style="color: green;font-size: 1.4em;"><?php echo $noti_mess; ?></p>
                        <form name="notification" action="" method="post" id="notification">
                            <p>
                                <label for="noti_condition">Condition:</label>
                                <input type="text" name="noti_condition" id="noti_condition" value="<?php print str_replace("\'", "'", $data_search['drug_condition']); ?>" />
                            </p>
                            <p>
                                <label for="noti_country">Country:</label>
                                <input type="text" name="noti_country" id="noti_country" value="<?php print str_replace("\'", "'", $country ); ?>" />
                            </p>
                            <p>
                                <label for="noti_email">Email:</label>
                                <input type="text" name="noti_email" id="noti_email" value="" />
                            </p>
                            <p>
                                <input type="submit" name="send_mail" value="Send" />
                            </p>
                        </form>
                    </li>
                </ul>
            </div>
            <?php
            $error = ob_get_clean();
        }
        // Restore original Post Data
        wp_reset_postdata();

    }
    ob_start();
    $key1value = isset($_REQUEST[$key1] ) ? $_REQUEST[$key1] : '';
    $key2value = isset($_REQUEST[$key2] ) ? $_REQUEST[$key2] : '';
    $countries = izw_all_countries();
    ?>
    <div id="izweb-search" class="izweb-search" style="width: 100%;">
        <div class="izw-overLay">
            <h3 style="margin-top: 30px;">Loading all data, Please wait...</h3>
        </div>
        <div class="izweb-search-form">
            <form name="" action="<?php echo get_the_permalink( $post->ID ); ?>" method="get">
                <input type="hidden" name="page_id" value="<?php echo @$_REQUEST['page_id']; ?>">
                <div class="izw-filter-fields">
                    <div class="izw-row">
                        <div class="filter-item">
                            <label for="">Drug or Condition?</label>
                            <input type="text" name="drug_condition" id="drug_condition" value="<?php print htmlspecialchars( str_replace("\'", "'", $data_search['drug_condition'])); ?>" placeholder="e.g. Depression" />
                            <div class="clear"></div>
                        </div>
                        <div class="filter-item">
                            <label for="">Country?</label>
                            <select name="country">
                                <option value="0">All</option>
                                <?php foreach( $countries as $v): ?>
                                    <option <?php selected(  str_replace("\'", "'",$data_search['country']),  str_replace("\'", "'",$v)); ?> value="<?php echo $v; ?>"><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="izw-row">
                        <div class="filter-item">
                            <label for="">Study</label>
                            <p>
                                <label><input type="radio" <?php if( empty( $data_search['study'] ) || $data_search['study'] == '0' ) echo 'checked=""'; ?> name="study" value="0" />Both</label>
                                <?php
                                $terms = get_terms('program_cat');
                                foreach( $terms as $term):
                                    ?>
                                    <label><input type="radio" name="study" <?php checked($data_search['study'], $term->name); ?> value="<?php echo $term->term_id; ?>" /><?php echo $term->name; ?></label>
                                <?php endforeach; ?>
                            </p>
                            <div class="clear"></div>
                        </div>
                        <div class="filter-item">
                            <input type="submit" name="izw_search" style="top: 4px;" class="nectar-button large extra-color-1 has-icon regular-button" value="<?php _e( "SEARCH", __TEXTDOMAIN__) ?>" data-color-override="false" data-hover-color-override="false" data-hover-text-color-override="#fff" />
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="search_advanced" style="display: none;">
                        <div class="izw-row">
                            <div class="filter-item">
                                <label for="gender">Gender</label>
                                <p>
                                    <select name="gender" id="gender">
                                        <option value="Both">Both</option>
                                        <option <?php selected($data_search['gender'], 'Male'); ?> value="Male">Male</option>
                                        <option <?php selected($data_search['Female'], 'Male'); ?> value="Female">Female</option>
                                    </select>
                                </p>
                                <div class="clear"></div>
                            </div>
                            <div class="filter-item">
                                <label for="">Age Group</label>
                                <p>
                                    <label><input type="radio" <?php if( empty( $data_search['age_group'] ) || $data_search['age_group'] == '0' ) echo 'checked=""'; ?> name="age_group" value="0" />Any</label>
                                    <label><input type="radio" <?php checked($data_search['age_group'], '0-17'); ?> name="age_group" value="0-17" />Child (0-17)</label>
                                    <label><input type="radio" <?php checked($data_search['age_group'], '18-65'); ?> name="age_group" value="18-65" />Adult (18-65)</label>
                                    <label><input type="radio" <?php checked($data_search['age_group'], '65+'); ?> name="age_group" value="65+" />Senior (65+)</label>
                                </p>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div><!-- END .search_advanced -->
                    <p style="text-align: right;">
                        <a href="#" class="show_advanced">Advanced Search</a>
                    </p>
                    <div class="clear" style="clear: both;"></div>
                </div>
            </form>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    function unique(list) {
                        var result = [];
                        $.each(list, function(i, e) {
                            if ($.inArray(e, result) == -1) result.push(e);
                        });
                        return result;
                    }
                    /*var k1;
                    $.get("<?php echo __IZIPURL__."autocomplete-{$key1}.txt"; ?>", function(data) {
                        k1 = data.split(',');
                        $( "#drug_condition" ).autocomplete({source:k1,autoFocus: true,minLength: 3})
                    });*/
                    var data = {
                        'action': 'izw_search_ajax'
                    };

                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
                        $( ".izw-overLay").hide();
                        k1 = response.split(',');
                        k1 = unique( k1 );
                        $( "#drug_condition" ).autocomplete({
                            source: function(request, response) {
                                /*var results = $.ui.autocomplete.filter(k1, request.term);
                                response(results.slice(0, 5));*/
                                var re = $.ui.autocomplete.escapeRegex(request.term);
                                var matcher = new RegExp( "^" + re, "i" );
                                var a = $.grep( k1, function(item,index){
                                    return matcher.test(item);
                                });
                                response( a.slice(0, 5) );
                            },
                            autoFocus: true
                        })
                    });
                    $(".page-1").show();
                    $(".search_pagination .wp-pagenavi a").click(function(){
                        $(".izweb-item").hide();
                        var page = $(this).data('page');
                        $(".page-"+page).show();
                        $(".search_pagination .wp-pagenavi a").removeClass('active');
                        $(this).addClass('active');
                    });
                    $("a.show_advanced").click(function(){
                        $(".search_advanced").toggle("slow");
                        return false;
                    });
                    $("#notification").validate({
                        rules: {
                            noti_condition: "required",
                            noti_country: "required",
                            noti_email: {
                                required: true,
                                email: true
                            }
                        },
                        messages: {
                            noti_condition: "Please enter your condition",
                            noti_country: "Please enter your country",
                            noti_email: {
                                required: "Please enter a email",
                                email: "Please enter a valid email address"
                            }
                        }
                    });
                });
            </script>
        </div>
        <?php echo $error; ?>
    </div><!-- END #izweb-search -->
    <?php
    echo @$search_results;
    return ob_get_clean();
}
add_shortcode( 'counter_program', 'hh_counter_program' );
function hh_counter_program( $atts ){
    $atr = shortcode_atts(
            array(
                'field' => 'slug',
                'taxonomy' => 'program_cat',
                'terms' => ''
            ), $atts
    );
    $term_atr = get_term_by( $atr['field'], $atr['terms'], $atr['taxonomy'] );
    return $term_atr->count;
}
function get_eap( $key = array() ){
    global $wpdb;
    if( !is_array( $key ) && sizeof( $key ) <= 0) return 0;
    $term = get_term_by( 'slug', 'exclude-clinical-trials', 'program_cat' );
    $postID = get_objects_in_term( $term->term_id, 'program_cat');
    $exArgs = array(
        'post_type' => 'program',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'post__in' => $postID
    );
    foreach( $key as $k=>$v ){
        $exArgs['meta_query'][] = array(
            'key'     => $k,
            'value'   => $v,
            'compare' => 'LIKE',
        );
    }
    if( sizeof( $key ) >= 2){
        $exArgs['meta_query']['relation'] = 'AND';
    }
    $ex = new WP_Query( $exArgs );
    wp_reset_postdata();
    return $ex->found_posts;
}
function izw_input_html( $key, $type = 'text', $data = array() ){
    global $wpdb;
    switch($type){
        case 'text':
            $vl = isset( $_REQUEST[$key] ) ? $_REQUEST[$key] : '';
            ob_start();
            ?>
            <label><?php echo $data['heading']; ?></label>
            <input class="izw-input-text" type="text" name="<?php echo $key; ?>" value="<?php echo $vl; ?>" placeholder="<?php echo $data['placeholder']; ?>"/>
            <?php
            $result = ob_get_clean();
            return apply_filters( 'izw_input_text_html', $result, $key, $data );
        case 'select':
            ob_start();
            $selected = isset( $_REQUEST[$key] ) ? $_REQUEST[$key] : '';
            $meta_value = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key='{$key}'");
            $value = array_filter(array_intersect_key( $meta_value, array_unique( array_map( "StrToLower", $meta_value) ) ));
            if( sizeof( $value ) > 0 ){
                ?>
                <label><?php echo $data['heading']; ?></label>
                <select name="<?php echo $key; ?>" class="izw-select">
                    <option value=""><?php echo $data['placeholder']; ?></option>
                    <?php foreach( $value as $v ): ?>
                        <option <?php selected( $selected, $v ); ?> value="<?php echo $v; ?>"><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
            }
            return apply_filters( 'izw_select_html', ob_get_clean(), $key, $data );
        case 'radio':
            ob_start();
            $checked = isset( $_REQUEST[$key] ) ? $_REQUEST[$key] : '';
            if( !empty($data['custom_option'])){
                $value =  explode( "\n", $data['custom_option']);
                if( sizeof( $value ) > 0){
                    ?>
                    <label><?php echo $data['heading']; ?></label>
                    <div class="izw-radio">
                    <?php
                    $i=1;
                    foreach( $value as $v ):
                        $v1 = explode( "|", $v);
                    ?>
                        <input type="radio" <?php checked( $checked, $v1[0]); ?> name="<?php echo $key; ?>" id="<?php echo $key,"-",$i; ?>" value="<?php echo $v1[0]; ?>" ><label for="<?php echo $key,"-",$i; ?>"><?php echo $v1[1]; ?></label><br />
                    <?php $i++; endforeach;
                    echo "</div>";
                }
                return apply_filters( 'izw_radio_html', ob_get_clean(), $key, $data );
            }
            $meta_value = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key='{$key}'");
            $value = array_filter(array_intersect_key( $meta_value, array_unique( array_map( "StrToLower", $meta_value) ) ));
            if( sizeof( $value ) > 0 ){
                ?>
                <label><?php echo $data['heading']; ?></label>
                <div class="izw-radio">
                <?php
                $i=1;
                foreach( $value as $v ):
                    ?>
                    <input type="radio" <?php checked( $checked, $v); ?> name="<?php echo $key; ?>" id="<?php echo $key,"-",$i; ?>" value="<?php echo $v; ?>" ><label for="<?php echo $key,"-",$i; ?>"><?php echo $v; ?></label><br />
                    <?php $i++; endforeach;
                echo "</div>";
            }
            return apply_filters( 'izw_radio_html', ob_get_clean(), $key, $data );
        default:
            ob_start();
            ?>
                <label><?php echo $data['heading']; ?></label>
                <input type="text" name="<?php echo $key; ?>" placeholder="<?php echo $data['placeholder']; ?>"/>
            <?php
            $result = ob_get_clean();
            return apply_filters( 'izw_input_text_html', $result, $key, $data );
    }
}