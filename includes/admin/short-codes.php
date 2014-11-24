<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/24/14
 * Time: 3:54 PM
 */
add_shortcode( 'search_program', 'hh_search_program' );
function hh_search_program( $att, $content=null){
    $search_results = '';
    if(isset( $_POST['izweb-search'] ) ){
        $data = array(
            'condition'     => $_POST['izw_condition'],
            'living'        => $_POST['izw_living']
        );
        $args = array(
            'post_type' => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array()
        );
        if( !empty($data['condition']) ){
            $args['meta_query'][] = array(
                'key'     => 'condition',
                'value'   => $data['condition'],
                'compare' => 'LIKE',
            );
        }
        if( !empty($data['living']) ){
            $args['meta_query'][] = array(
                'key'     => 'living',
                'value'   => $data['living'],
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
                            <?php do_action( 'izweb_after_search_content', get_the_ID() ); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php
        }
        $search_results = ob_get_clean();
    }
    ob_start();
    ?>
    <div id="izweb-search" class="izweb-search">
        <div class="izweb-search-form">
            <form name="" action="" method="post">
                <label for="izweb-condition"><?php _e( "I am looking for EAP's for:", __TEXTDOMAIN__) ?></label>
                <input type="text" name="izw_condition" id="izweb-condition" value="" />
                <label for="izweb-living"><?php _e( "I'm living in:", __TEXTDOMAIN__) ?></label>
                <input type="text" name="izw_living" id="izweb-living" value="" />
                <input type="submit" value="<?php _e( "SEARCH", __TEXTDOMAIN__) ?>" name="izweb-search" />
            </form>
        </div>
    </div><!-- END #izweb-search -->
    <?php
    echo @$search_results;
    return ob_get_clean();
}