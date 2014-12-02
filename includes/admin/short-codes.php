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
    global $post;
    // Attributes
    $exc = get_option( 'izweb_post_excerpt' );
    $exc = !empty($exc) ? $exc : 'brief_summary';
    extract( shortcode_atts(
            array(
                'key1' => 'condition',
                'key2' => 'living',
                'caption1' => 'I am looking for EAP\'s for:',
                'caption2' => 'I\'m living in:',
                'excerpt' => $exc
            ), $atts )
    );
    $search_results = '';
    if(isset( $_REQUEST['izweb-search'] ) ){
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
        if( !empty($data[$key1]) ){
            $args['meta_query'][] = array(
                'key'     => $key1,
                'value'   => $data[$key1],
                'compare' => 'LIKE',
            );
        }
        if( !empty($data[$key2]) ){
			$args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = array(
                'key'     => $key2,
                'value'   => $data[$key2],
                'compare' => 'LIKE',
            );
        }
        $args = apply_filters( 'izweb_arg_search', $args );
        $program = new WP_Query( $args );
        ob_start();
        if( $program->have_posts() ){
            ?>
            <div class="izweb-search-results">
                <?php dynamic_sidebar('izw-below-search'); ?>
                <?php while($program->have_posts()): $program->the_post();$terms = wp_get_post_terms( get_the_ID(), 'program_cat', $args ); $termslug = array(); foreach( $terms as $term){ $termslug[] = $term->slug;} ?>
                    <div class="izweb-item <?php echo implode(" ", $termslug ); ?>">
                        <div class="post-title"><a class="izw-title-link" href="<?php the_permalink(); ?>"><?php echo _substr(get_the_title(), 50); ?></a><a class="izw-detail" href="<?php the_permalink(); ?>">Details</a></div>
                        <div class="post-content">
                            <?php do_action( 'izweb_before_search_content', get_the_ID() ); ?>
                            <?php $exc_text = get_post_meta( get_the_ID(), $excerpt, true ); if( !empty( $exc_text ) ) echo _substr($exc_text,300); else the_excerpt(); ?>
                            <?php izweb_show_custom_field( get_the_ID() ); ?>
                            <?php do_action( 'izweb_after_search_content', get_the_ID() ); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
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
                    <a class="next" href="<?php echo add_query_arg( array( 'paged'=> $paged+1) ); ?>">Prev</a>
                <?php endif; ?>
            </nav>
            <?php
            }
        }
        // Restore original Post Data
        wp_reset_postdata();

        $search_results = ob_get_clean();
    }
    ob_start();
    ?>
    <div id="izweb-search" class="izweb-search" style="width: 100%; float: left;">
        <div class="izweb-search-form">
            <form name="" action="<?php echo get_the_permalink( $post->ID ); ?>" method="get">
                <input type="hidden" name="page_id" value="<?php echo  @$_REQUEST['page']; ?>">
                <div class="izw-left" style="float: left;">
                    <label for="<?php echo $key1; ?>"><?php _e( $caption1, __TEXTDOMAIN__) ?></label>
                    <input type="text" name="<?php echo $key1; ?>" id="<?php echo $key1; ?>" value="" />
                </div>
                <div class="izw-right" style="float: right;width: 49%;">
                <label for="<?php echo $key2; ?>"><?php _e( $caption2, __TEXTDOMAIN__) ?></label>
                    <input type="text" name="<?php echo $key2; ?>" id="<?php echo $key2; ?>" value="" />
                    <input type="submit" class="nectar-button large extra-color-1 has-icon regular-button" value="<?php _e( "SEARCH", __TEXTDOMAIN__) ?>" name="izweb-search" data-color-override="false" data-hover-color-override="false" data-hover-text-color-override="#fff" />
                    <label style="display: block;">include available Clinical Trials in search <input type="checkbox" name="include_trial" value="1" <?php if (!empty($_POST['include_trial'])) echo 'checked="checked"';?> /></label>
                </div>
            </form>
            <?php
            global $wpdb;
            $tag1 = array();
            $tag2 = array();
            $sql1 = $wpdb->get_results( 'SELECT DISTINCT `meta_value` FROM '.$wpdb->postmeta.' INNER JOIN '.$wpdb->posts.' ON '.$wpdb->posts.'.`ID` = '.$wpdb->postmeta.'.`post_id` AND '.$wpdb->posts.'.`post_status` = "publish"  AND `meta_key` = "'.$key1.'"', ARRAY_A );
            $sql2 = $wpdb->get_results( 'SELECT DISTINCT `meta_value` FROM '.$wpdb->postmeta.' INNER JOIN '.$wpdb->posts.' ON '.$wpdb->posts.'.`ID` = '.$wpdb->postmeta.'.`post_id` AND '.$wpdb->posts.'.`post_status` = "publish"  AND `meta_key` = "'.$key2.'"', ARRAY_A );
            foreach( $sql1 as $rows){
                if( !empty($rows['meta_value'] ) ) {
                    $multi = explode("\n", $rows['meta_value']);
                    if (is_array($multi)) {
                        foreach($multi as $country) $tag1[] = '"'.trim($country).'"';
                    } else $tag1[] = '"'.trim($rows['meta_value']).'"';
                }
            }
            foreach( $sql2 as $rows){
                if( !empty($rows['meta_value'] ) ) {
					$multi = explode("\n", $rows['meta_value']);
					if (is_array($multi)) {
						foreach($multi as $country) $tag2[] = '"'.trim($country).'"';
					} else $tag2[] = '"'.trim($rows['meta_value']).'"';
				}
            }
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    var tag1 = [<?php echo implode(",", array_unique($tag1)) ?>];
                    var tag2 = [<?php echo implode(",", array_unique($tag2)) ?>];
                    $( "#<?php echo $key1; ?>" ).autocomplete({
                        source: tag1
                    });
                    $( "#<?php echo $key2; ?>" ).autocomplete({
                        source: tag2
                    });
					<?php if (!empty($_POST['include_trial'])) {?>
						$(".izweb-item.include-clinical-trials").show();
					<?php }?>
                    $('input[name="include_trial"]').change(function (){
                        if($(this).is(":checked")){
                            $(".izweb-item.include-clinical-trials").show();
                        }else{
                            $(".izweb-item.include-clinical-trials").hide();
                        }
                    });
                });
            </script>
        </div>
    </div><!-- END #izweb-search -->
    <?php
    echo @$search_results;
    return ob_get_clean();
}