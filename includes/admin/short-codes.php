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
    if(isset( $_REQUEST['izweb-search'] ) ){
		global $is_search_program;
        $post_ids = array();
		$is_search_program = true;
        $data = array(
            $key1     => $_REQUEST[$key1],
            $key2     => $_REQUEST[$key2],
        );
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'program',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'paged' => $paged
        );	
		
		if (empty($_GET['include_trial'])) {
			$args['tax_query'] = array(
									array(
										'taxonomy' => 'program_cat',
										'field'    => 'slug',
										'terms'    => 'exclude-clinical-trials',
									));
		}
		$key_check = array();
        if( !empty($data[$key1]) ){
            $key_check[$key1] = $data[$key1];
            $args['meta_query'][] = array(
                'key'     => $key1,
                'value'   => $data[$key1],
                'compare' => 'LIKE',
            );
        }
        if( !empty($data[$key2]) ){
            $key_check[$key2] = $data[$key2];
            $args['meta_query'][] = array(
                'key'     => $key2,
                'value'   => $data[$key2],
                'compare' => 'LIKE',
            );
        }
        if( sizeof( $filter_fields ) > 0 ){
            foreach( $filter_fields as $k=>$v ){
                if( isset( $_REQUEST[$k]) && $_REQUEST[$k] != '' && $_REQUEST[$k] != '0' ){
                    $key_check[$k] = $_REQUEST[$k];
                    if( !empty( $v['custom_option'])){
                        if( $v['between'] == 'yes'){
                            $list1 = explode( "\n", $v['custom_option'] );
                            foreach( $list1 as $item){
                                $list2 = explode( "-", explode( "|", $item ) );
                                $slq = "SELECT DISTINCT($wpdb->posts.ID)
                                      FROM $wpdb->posts
                                      LEFT JOIN $wpdb->postmeta
                                      ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                                      WHERE $wpdb->posts.post_type = 'program'
                                      AND $wpdb->postmeta.meta_key = '{$k}'
                                      AND CONVERT(SUBSTRING_INDEX({$wpdb->postmeta}.meta_value,' ',1),UNSIGNED INTEGER) BETWEEN {$list2[0]} AND {$list2[1]}";
                                $post_ids[] = $wpdb->get_col( $slq );
                                $post_ids = array_unique( $post_ids );
                                $post_ids = array_filter( $post_ids );
                            }
                        }
                    }
                    $compare = 'LIKE';
                    if( $v['field_type'] == 'select' ) $compare = '=';
                    $args['meta_query'][] = array(
                        'key'     => $k,
                        'value'   => $_REQUEST[$k],
                        'compare' => $compare,
                    );
                }
            }
        }
        if( sizeof( $args['meta_query']) >= 2){
            $args['meta_query']['relation'] = 'AND';
        }
        if( sizeof( $post_ids ) > 0){
            $args['post__in'] = $post_ids;
        }
        $args = apply_filters( 'izweb_arg_search', $args );
        $include = $exclude = 0;
        $program = new WP_Query( $args );
        $error = '';
        $search_results = '';
        if(!empty( $_GET['include_trial'] )){
            $include = (int)$program->found_posts - (int)get_eap( $key_check );
            $exclude = get_eap( $key_check );
        }else{
            $exclude = $program->found_posts;
        }
        ob_start();
        if( $program->have_posts() ){
            ?>
            <div class="izweb-search-results">
                <?php dynamic_sidebar('izw-below-search'); ?>
                <?php while($program->have_posts()): $program->the_post();$terms = wp_get_post_terms( get_the_ID(), 'program_cat', $args ); $termslug = array(); foreach( $terms as $term){ $termslug[] = $term->slug;}  global $post;?>
                    <div class="izweb-item <?php echo implode(" ", $termslug ); ?>">
                        <div class="izweb-item-left">
                            <div class="post-title">
                                <a class="izw-title-link" href="<?php the_permalink(); ?>"><?php echo _substr(get_the_title(), 70); ?></a>
                            </div>
                            <div class="post-content">
                                <?php do_action( 'izweb_before_search_content', get_the_ID() ); ?>
                                <?php $exc_text = get_post_meta( get_the_ID(), $excerpt, true ); if( !empty( $exc_text ) ) echo _substr(strip_tags($exc_text, '<p><a>'),300); else echo _substr(strip_tags($post->post_excerpt, '<p><a>'),300); ?>
                                <?php izweb_show_custom_field( get_the_ID() ); ?>
                                <?php do_action( 'izweb_after_search_content', get_the_ID() ); ?>
                            </div>
                        </div>
                        <div class="izweb-item-right">
                            <a class="izw-detail" href="<?php the_permalink(); ?>">Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
			<div class="search_pagination">
            <?php if( function_exists('wp_pagenavi')){
                wp_pagenavi( array( 'query' => $program) );
            }else{
                ?>
                <?php if( $program->max_num_pages > 1 ){ ?>
                    <nav class="izweb-pagination">
                        <?php if( $paged > 1): ?>
                            <a class="prev" href="<?php echo add_query_arg( array( 'paged'=> $paged-1) ); ?>">Prev</a>
                        <?php endif; ?>
                        <?php for( $i=1; $i<=$program->max_num_pages; $i++){ ?>
                            <?php if( $paged == $i ): ?>
                                <span class="page-current"><?php echo $i ?></span>
                            <?php else: ?>
                                <a href="<?php echo add_query_arg( array( 'paged'=> $i) ); ?>"><?php echo $i ?></a>
                            <?php endif ?>
                        <?php } ?>
                        <?php if( $paged < $program->max_num_pages): ?>
                            <a class="next" href="<?php echo add_query_arg( array( 'paged'=> $paged+1) ); ?>">Next</a>
                        <?php endif; ?>
                    </nav>
                <?php
                }
            }
			?>
			</div>
			<?php
            $search_results = ob_get_clean();
            $found_mes = '<h3 class="izw-found-mes">'.__( "We have found {$exclude} Early Access Programs (green) and {$include} Clinical Trials (red)")."</h3>";
            $search_results = $found_mes.$search_results;
        }else{
            ?>
            <div class="izw-error-mes">
                <h3>Sorry, no results were found!</h3>
                <ul class="izw-error-mes-ul">
                    <li>Try other keywords or check your spelling</li>
                    <li>Check the "include available Clinic Trials in search" option</li>
                    <li>Get notified when we have information for <?php print htmlspecialchars($_REQUEST[$key1]); ?> in <?php print htmlspecialchars($_REQUEST[$key2]); ?> <?php if( function_exists( 'gravity_form')) gravity_form(10, false, false, false, '', false); ?></li>
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
    ?>
    <div id="izweb-search" class="izweb-search" style="width: 100%;">
        <div class="izweb-search-form">
            <form name="" action="<?php echo get_the_permalink( $post->ID ); ?>" method="get">
                <input type="hidden" name="page_id" value="<?php echo @$_REQUEST['page_id']; ?>">
                <div class="default-form">
                    <div class="izw-left">
                        <label for="<?php echo $key1; ?>"><?php _e( $caption1, __TEXTDOMAIN__) ?></label>
                        <input type="text" name="<?php echo $key1; ?>" id="<?php echo $key1; ?>" value="<?php echo $key1value; ?>" placeholder="<?php echo $placeholder1 ?>" />
                    </div>
                    <div class="izw-right">
                    <label for="<?php echo $key2; ?>"><?php _e( $caption2, __TEXTDOMAIN__) ?></label>
                        <input type="text" name="<?php echo $key2; ?>" id="<?php echo $key2; ?>" value="<?php echo $key2value; ?>" placeholder="<?php echo $placeholder2 ?>" />
                        <input type="submit" style="top: 4px;" class="nectar-button large extra-color-1 has-icon regular-button" value="<?php _e( "SEARCH", __TEXTDOMAIN__) ?>" name="izweb-search" data-color-override="false" data-hover-color-override="false" data-hover-text-color-override="#fff" />
                        <label style="display: block;">include available Clinical Trials in search <input type="checkbox" name="include_trial" value="1" <?php if (!isset($_REQUEST['izweb-search']) || !empty($_REQUEST['include_trial'])) echo 'checked="checked"';?> /></label>
                    </div>
                </div>
                <?php if( sizeof( $filter_fields ) > 0 ): ?>
                <div class="izw-filter-fields">
                    <div class="izw-row">
                    <?php $i=1; foreach( $filter_fields as $k=>$v): ?>
                        <div class="filter-item">
                            <?php echo izw_input_html( $k, $v['field_type'], $v ); ?>
                            <div class="clear"></div>
                        </div>
                        <?php if( $i%2 == 0 ) echo "</div><div class=\"izw-row\">"; ?>
                    <?php $i++; endforeach; ?>
                    </div>
                    <div class="clear" style="clear: both;"></div>
                </div>
                <?php endif; ?>
            </form>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    $(".izweb-item.include-clinical-trials").hide();
                    <?php if (!empty($_REQUEST['include_trial'])) {?>
                    $(".izweb-item.include-clinical-trials").show();
                    <?php }?>
                    $('input[name="include_trial"]').change(function (){
                        if($(this).is(":checked")){
                            $(".izweb-item.include-clinical-trials").show();
							$(".search_pagination a").each(function(){
								 this.href += "&include_trial=1";
							});
                        }else{
                            $(".izweb-item.include-clinical-trials").hide();
							$(".search_pagination a").each(function(){
								this.href = this.href.replace("&include_trial=1", "");								 
							});
                        }
                    });
                    $( "#<?php echo $key1; ?>" ).autocomplete({
                        source: function( request, response ) {
                            var data = {
                                'action': 'izw_search_ajax',
                                'meta_key': '<?php echo $key1; ?>',
                                'meta_value': request.term
                            };

                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(tags) {
                                response(JSON.parse(tags));
                            });
                        },
                        autoFocus: true,
                        minLength: 3
                    });
                    $( "#<?php echo $key2; ?>" ).autocomplete({
                        source: function( request, response ) {
                            var data = {
                                'action': 'izw_search_ajax',
                                'meta_key': '<?php echo $key2; ?>',
                                'meta_value': request.term
                            };

                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(tags) {
                                response(JSON.parse(tags));
                            });
                        },
                        autoFocus: true,
                        minLength: 3
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