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
    // Attributes
    extract( shortcode_atts(
            array(
                'key1' => 'condition',
                'key2' => 'living',
                'caption1' => 'I am looking for EAP\'s for:',
                'caption2' => 'I\'m living in:'
            ), $atts )
    );
    $search_results = '';
    if(isset( $_POST['izweb-search'] ) ){
        $data = array(
            $key1     => $_POST[$key1],
            $key2     => $_POST[$key2],
        );
        $args = array(
            'post_type' => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array()
        );
        if( !empty($data[$key1]) ){
            $args['meta_query'][] = array(
                'key'     => $key1,
                'value'   => $data[$key1],
                'compare' => 'LIKE',
            );
        }
        if( !empty($data[$key2]) ){
            $args['meta_query'][] = array(
                'key'     => $key2,
                'value'   => $data[$key1],
                'compare' => 'LIKE',
            );
        }
        $args = apply_filters( 'izweb_arg_search', $args );
        $program = new WP_Query( $args );
        ob_start();
        if( $program->have_posts() ){
            ?>
            <div class="izweb-search-results">
                <?php while($program->have_posts()): $program->the_post(); ?>
                    <div class="izweb-item">
                        <div class="post-title"><h2><?php echo _substr(get_the_title(), 30); ?></h2><a class="izw-detail" href="<?php the_permalink(); ?>">Detail</a></div>
                        <div class="post-content">
                            <?php do_action( 'izweb_before_search_content', get_the_ID() ); ?>
                            <?php the_excerpt(); ?>
                            <?php izweb_show_custom_field( get_the_ID() ); ?>
                            <?php do_action( 'izweb_after_search_content', get_the_ID() ); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php
        }
        // Restore original Post Data
        wp_reset_postdata();

        $search_results = ob_get_clean();
    }
    ob_start();
    ?>
    <div id="izweb-search" class="izweb-search">
        <div class="izweb-search-form">
            <form name="" action="" method="post">
                <label for="<?php echo $key1; ?>"><?php _e( $caption1, __TEXTDOMAIN__) ?></label>
                <input type="text" name="<?php echo $key1; ?>" id="<?php echo $key1; ?>" value="" />
                <label for="<?php echo $key2; ?>"><?php _e( $caption2, __TEXTDOMAIN__) ?></label>
                <input type="text" name="<?php echo $key2; ?>" id="<?php echo $key2; ?>" value="" />
                <input type="submit" value="<?php _e( "SEARCH", __TEXTDOMAIN__) ?>" name="izweb-search" />
            </form>
        </div>
    </div><!-- END #izweb-search -->
    <?php
    echo @$search_results;
    return ob_get_clean();
}